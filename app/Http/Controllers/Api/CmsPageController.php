<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CmsPageController extends Controller
{
    public function index(Request $request)
    {
        return CmsPage::query()
            ->when(! $request->user()?->can('cms.manage'), fn ($q) => $q->where('status', 'publie'))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->orderByDesc('published_at')
            ->get();
    }

    public function show(Request $request, string $slug)
    {
        $page = CmsPage::where('slug', $slug)->firstOrFail();

        abort_if($page->status !== 'publie' && ! $request->user()?->can('cms.manage'), 404);

        return $page;
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => [Rule::in(['page', 'article'])],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['author_id'] = $request->user()->id;
        $data['type'] ??= 'page';
        $data['status'] ??= 'brouillon';
        if ($data['status'] === 'publie') {
            $data['published_at'] = now();
        }

        return response()->json(CmsPage::create($data), 201);
    }

    public function update(Request $request, CmsPage $page)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        if (($data['status'] ?? null) === 'publie' && ! $page->published_at) {
            $data['published_at'] = now();
        }

        $page->update($data);

        return $page;
    }

    public function destroy(Request $request, CmsPage $page)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $page->delete();

        return response()->noContent();
    }
}
