<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        return Faq::query()
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->orderBy('order')
            ->get();
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        return response()->json(Faq::create($data), 201);
    }

    public function update(Request $request, Faq $faq)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'question' => ['sometimes', 'string', 'max:255'],
            'answer' => ['sometimes', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $faq->update($data);

        return $faq;
    }

    public function destroy(Request $request, Faq $faq)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $faq->delete();

        return response()->noContent();
    }
}
