<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->boolean('mine') && $request->user()) {
            return $request->user()->badges;
        }

        return Badge::all();
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('users.manage'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'criteria' => ['nullable', 'string'],
        ]);

        return response()->json(Badge::create($data), 201);
    }

    public function award(Request $request, Badge $badge)
    {
        abort_unless($request->user()->can('users.manage'), 403);

        $data = $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $badge->users()->syncWithoutDetaching([
            $data['user_id'] => ['awarded_at' => now(), 'awarded_by' => $request->user()->id],
        ]);

        $awardedUser = User::findOrFail($data['user_id']);
        AuditLog::record($request->user(), 'badge.attribue', $awardedUser, ['badge_id' => $badge->id]);

        return response()->json($badge->load('users'));
    }
}
