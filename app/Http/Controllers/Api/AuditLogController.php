<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        return AuditLog::query()
            ->with('actor')
            ->when($request->query('actor_id'), fn ($q, $id) => $q->where('actor_id', $id))
            ->orderByDesc('created_at')
            ->paginate(30);
    }
}
