<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GenericContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminContentController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category' => ['nullable', Rule::in(['workout', 'nutrition', 'wellness'])],
            'content_type' => ['nullable', Rule::in(['article', 'video', 'image'])],
            'difficulty' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = GenericContent::query()->orderByDesc('created_at');

        foreach (['category', 'content_type', 'difficulty'] as $f) {
            if (! empty($validated[$f])) {
                $query->where($f, $validated[$f]);
            }
        }

        return response()->json($query->paginate($validated['per_page'] ?? 20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', Rule::in(['workout', 'nutrition', 'wellness'])],
            'content_type' => ['required', Rule::in(['article', 'video', 'image'])],
            'content_url' => ['required', 'string', 'max:2048'],
            'thumbnail_url' => ['nullable', 'string', 'max:2048'],
            'difficulty' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $content = GenericContent::create(array_merge($validated, [
            'views_count' => 0,
        ]));

        return response()->json(['content' => $content], 201);
    }

    public function update(Request $request, int $id)
    {
        $content = GenericContent::query()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category' => ['sometimes', Rule::in(['workout', 'nutrition', 'wellness'])],
            'content_type' => ['sometimes', Rule::in(['article', 'video', 'image'])],
            'content_url' => ['sometimes', 'string', 'max:2048'],
            'thumbnail_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'difficulty' => ['sometimes', 'nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1000000'],
            'views_count' => ['sometimes', 'integer', 'min:0'],
        ]);

        $content->fill($validated)->save();

        return response()->json(['content' => $content->fresh()]);
    }

    public function destroy(int $id)
    {
        $content = GenericContent::query()->findOrFail($id);
        $content->delete();

        return response()->json(['message' => 'Content deleted.']);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $path = $request->file('csv')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return response()->json(['message' => 'Unable to read CSV.'], 422);
        }

        $header = fgetcsv($handle);
        if (! is_array($header)) {
            fclose($handle);
            return response()->json(['message' => 'Invalid CSV header.'], 422);
        }

        $header = array_map(fn ($h) => trim((string) $h), $header);
        $required = ['title', 'category', 'content_type', 'content_url'];
        foreach ($required as $col) {
            if (! in_array($col, $header, true)) {
                fclose($handle);
                return response()->json(['message' => "CSV missing required column: {$col}"], 422);
            }
        }

        $created = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = [];
            foreach ($header as $i => $col) {
                $data[$col] = $row[$i] ?? null;
            }

            $v = Validator::make($data, [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'category' => ['required', Rule::in(['workout', 'nutrition', 'wellness'])],
                'content_type' => ['required', Rule::in(['article', 'video', 'image'])],
                'content_url' => ['required', 'string', 'max:2048'],
                'thumbnail_url' => ['nullable', 'string', 'max:2048'],
                'difficulty' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
                'duration' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            ]);

            if ($v->fails()) {
                $errors[] = ['row' => $data, 'errors' => $v->errors()];
                continue;
            }

            GenericContent::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'content_type' => $data['content_type'],
                'content_url' => $data['content_url'],
                'thumbnail_url' => $data['thumbnail_url'] ?? null,
                'difficulty' => $data['difficulty'] ?? null,
                'duration' => isset($data['duration']) && $data['duration'] !== '' ? (int) $data['duration'] : null,
                'views_count' => 0,
            ]);

            $created++;
        }

        fclose($handle);

        return response()->json([
            'created' => $created,
            'failed' => count($errors),
            'errors' => $errors,
        ]);
    }
}

