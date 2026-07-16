<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        return Testimonial::query()
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->orderBy('order')
            ->get();
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'quote' => ['required', 'string'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        return response()->json(Testimonial::create($data), 201);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', 'string', 'max:255'],
            'quote' => ['sometimes', 'string'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $testimonial->update($data);

        return $testimonial;
    }

    public function destroy(Request $request, Testimonial $testimonial)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $testimonial->delete();

        return response()->noContent();
    }
}
