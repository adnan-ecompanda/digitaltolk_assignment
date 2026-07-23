<#
Automates: backup existing `backend`, create fresh Laravel app in `backend`,
copy scaffold files into new project, install deps, generate key, migrate & seed.

Run from repo root with PowerShell as:
  .\scripts\setup_backend.ps1
#>

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# Resolve repository root (parent of the scripts folder)
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$repoRoot = Resolve-Path (Join-Path $scriptDir '..')
$backend = Join-Path $repoRoot 'backend'
$backup = Join-Path $repoRoot 'backend.bak'

function Exec([string]$cmd) {
    Write-Host "> $cmd"
    $proc = Start-Process -FilePath pwsh -ArgumentList "-NoProfile","-Command",$cmd -NoNewWindow -Wait -PassThru
    return $proc.ExitCode
}

if (-not (Test-Path $backend)) {
    Write-Host "No existing 'backend' folder found. Creating a new Laravel project..."
} else {
    $ts = Get-Date -Format 'yyyyMMddHHmmss'
    if (Test-Path $backup) {
        $backup = Join-Path $repoRoot "backend.bak.$ts"
    }
    Write-Host "Backing up existing 'backend' to '$backup'"
    Rename-Item -Path $backend -NewName (Split-Path $backup -Leaf)
}

# Ensure composer is available
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Error "Composer was not found in PATH. Please install Composer and re-run this script."
    exit 1
}

Write-Host "Creating new Laravel project in 'backend' (this may take a few minutes)..."
$proc = Start-Process -FilePath composer -ArgumentList 'create-project','--prefer-dist','laravel/laravel','backend' -NoNewWindow -Wait -PassThru
if ($proc.ExitCode -ne 0) {
    Write-Error "composer create-project failed with exit code $($proc.ExitCode). Aborting."
    exit $proc.ExitCode
}

# Copy scaffold files from backup into new project (only if backup exists)
if (Test-Path $backup) {
    Write-Host "Copying scaffold files from backup into new project..."

    $pairs = @(
        @{src='database\migrations'; dst='database\migrations'},
        @{src='app\Models'; dst='app\Models'},
        @{src='app\Http\Controllers\Api'; dst='app\Http\Controllers\Api'},
        @{src='database\factories'; dst='database\factories'},
        @{src='database\seeders'; dst='database\seeders'},
        @{src='routes'; dst='routes'},
        @{src='tests\Feature'; dst='tests\Feature'},
        @{src='Dockerfile'; dst='Dockerfile'},
        @{src='.env.example'; dst='.env.example'}
    )

    foreach ($p in $pairs) {
        $src = Join-Path $backup $p.src
        $dst = Join-Path $backend $p.dst
        if (-not (Test-Path $src)) { continue }

        Write-Host "Copying $src -> $dst"

        $pathType = (Get-Item $src).PSIsContainer
        if ($pathType) {
            # directory: use robocopy
            New-Item -ItemType Directory -Force -Path $dst | Out-Null
            $robocmd = "robocopy `"$src`" `"$dst`" /E /NFL /NDL /NJH /NJS /MT:8"
            cmd.exe /c $robocmd | Out-Default
        } else {
            # file: copy directly
            $dstDir = Split-Path $dst -Parent
            if ($dstDir -and -not (Test-Path $dstDir)) { New-Item -ItemType Directory -Force -Path $dstDir | Out-Null }
            Copy-Item -Force -Path $src -Destination $dst
        }
    }
}

Write-Host "Installing composer dependencies in backend..."
Push-Location $backend
composer install | Out-Default

if ((Test-Path '.env.example') -and -not (Test-Path '.env')) {
    Copy-Item .env.example .env
}

Write-Host "Generating application key..."
php artisan key:generate | Out-Default

Write-Host "Running migrations and seeders (this will use TRANSLATION_SEED_COUNT env var if set)..."
php artisan migrate --seed | Out-Default

Write-Host "Setup complete. Running tests..."
php artisan test | Out-Default

Pop-Location

Write-Host "Done. If any step failed above, review the output and re-run the failing command manually."
