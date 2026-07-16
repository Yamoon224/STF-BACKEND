<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        return Certificate::query()
            ->when(! $request->user()->can('users.manage'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->query('user_id') && $request->user()->can('users.manage'), fn ($q) => $q->where('user_id', $request->query('user_id')))
            ->with('program')
            ->orderByDesc('issued_at')
            ->get();
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('users.manage'), 403);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'title' => ['required', 'string', 'max:255'],
            'file_path' => ['nullable', 'string'],
        ]);

        $data['serial_number'] = 'STF-'.strtoupper(Str::random(8));
        $data['issued_at'] = now();

        $certificate = Certificate::create($data);

        AuditLog::record($request->user(), 'certificat.emis', $certificate);

        return response()->json($certificate, 201);
    }
}
