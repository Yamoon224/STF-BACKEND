<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        return ContactMessage::orderByDesc('created_at')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'audience' => [Rule::in(['generale', 'mentorat', 'partenaire', 'institution', 'media'])],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        return response()->json(ContactMessage::create($data), 201);
    }
}
