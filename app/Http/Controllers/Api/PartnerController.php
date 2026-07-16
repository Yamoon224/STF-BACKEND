<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        return Partner::orderBy('order')->get();
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        return response()->json(Partner::create($data), 201);
    }

    public function update(Request $request, Partner $partner)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $partner->update($data);

        return $partner;
    }

    public function destroy(Request $request, Partner $partner)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $partner->delete();

        return response()->noContent();
    }
}
