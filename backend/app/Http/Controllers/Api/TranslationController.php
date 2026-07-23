<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $query = Translation::query();

        if ($request->filled('key')) {
            $query->where('key', $request->input('key'));
        }

        if ($request->filled('locale')) {
            $query->where('locale', $request->input('locale'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('value', 'like', "%{$q}%")
                    ->orWhere('key', 'like', "%{$q}%");
            });
        }

        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->input('tags')));
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('name', $tags);
            });
        }

        $perPage = min($request->input('per_page', 50), 200);
        $result = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string',
            'locale' => 'required|string',
            'value' => 'required|string',
            'tags' => 'nullable|array',
            'context' => 'nullable|string',
        ]);

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $translation = Translation::updateOrCreate(
            ['key' => $data['key'], 'locale' => $data['locale']],
            $data
        );

        // sync tags
        if (! empty($tags)) {
            $tagIds = [];
            foreach ($tags as $t) {
                $tagModel = \App\Models\Tag::firstOrCreate(['name' => $t]);
                $tagIds[] = $tagModel->id;
            }
            $translation->tags()->sync($tagIds);
        }

        Cache::forget("translations_export_{$translation->locale}");

        return response()->json($translation->load('tags'), 201);
    }

    public function show(Translation $translation)
    {
        return response()->json($translation);
    }

    public function update(Request $request, Translation $translation)
    {
        $data = $request->validate([
            'value' => 'sometimes|string',
            'tags' => 'nullable|array',
            'context' => 'nullable|string',
        ]);

        $tags = $data['tags'] ?? null;
        if (isset($data['tags'])) { unset($data['tags']); }

        $translation->update($data);

        if (is_array($tags)) {
            $tagIds = [];
            foreach ($tags as $t) {
                $tagModel = \App\Models\Tag::firstOrCreate(['name' => $t]);
                $tagIds[] = $tagModel->id;
            }
            $translation->tags()->sync($tagIds);
        }

        Cache::forget("translations_export_{$translation->locale}");

        return response()->json($translation->load('tags'));
    }

    public function destroy(Translation $translation)
    {
        $locale = $translation->locale;
        $translation->delete();

        Cache::forget("translations_export_{$locale}");

        return response()->noContent();
    }

    public function export(Request $request)
    {
        $locale = $request->input('locale', 'en');
        // Stream JSON object to avoid building large arrays in memory
        $response = new StreamedResponse(function () use ($locale) {
            $first = true;
            echo '{';

            $query = Translation::where('locale', $locale)
                ->select('key','value')
                ->orderBy('key')
                ->cursor();

            foreach ($query as $row) {
                if (! $first) {
                    echo ',';
                }

                echo json_encode($row->key, JSON_UNESCAPED_UNICODE);
                echo ':';
                echo json_encode($row->value, JSON_UNESCAPED_UNICODE);

                $first = false;
            }

            echo '}';
        }, 200, ['Content-Type' => 'application/json']);

        return $response;
    }

    public function uploadExport(Request $request)
    {
        $request->validate(['locale' => 'nullable|string']);
        $locale = $request->input('locale', 'en');

        $first = true;
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, '{');

        $query = Translation::where('locale', $locale)
            ->select('key', 'value')
            ->orderBy('key')
            ->cursor();

        foreach ($query as $row) {
            if (! $first) {
                fwrite($stream, ',');
            }

            fwrite($stream, json_encode($row->key, JSON_UNESCAPED_UNICODE));
            fwrite($stream, ':');
            fwrite($stream, json_encode($row->value, JSON_UNESCAPED_UNICODE));

            $first = false;
        }

        fwrite($stream, '}');
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        $path = "translations_{$locale}.json";
        Storage::disk('public')->put($path, $contents);

        $url = Storage::disk('public')->url($path);

        return response()->json(['url' => $url]);
    }
}
