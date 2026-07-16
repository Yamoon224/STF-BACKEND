<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return User::query()
            ->with(['mentorProfile', 'menteeProfile', 'roles'])
            ->when($request->query('role'), fn ($q, $role) => $q->role($role))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('search'), function ($q, $search) {
                $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(20);
    }

    public function show(User $user)
    {
        return $user->load(['mentorProfile', 'menteeProfile', 'roles', 'badges', 'certificates']);
    }

    /**
     * Admin-created account (e.g. "Inviter une collaboratrice"). Unlike self-registration,
     * this can create staff/admin accounts. The user resets their password via the normal flow.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'staff', 'mentor', 'mentee', 'donor'])],
            'country' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Str::password(20),
            'country' => $data['country'] ?? null,
            'status' => 'active',
        ]);

        $user->assignRole($data['role']);

        AuditLog::record($request->user(), 'compte.invite', $user, ['role' => $data['role']]);

        return response()->json($user->load('roles'), 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'locale' => ['sometimes', 'string', 'max:5'],
        ]);

        $user->update($data);

        return $user;
    }

    public function suspend(Request $request, User $user)
    {
        $user->update(['status' => 'suspended']);

        AuditLog::record($request->user(), 'compte.suspendu', $user);

        return $user;
    }

    public function activate(Request $request, User $user)
    {
        $user->update(['status' => 'active']);

        AuditLog::record($request->user(), 'compte.active', $user);

        return $user;
    }

    public function validateMentor(Request $request, User $user)
    {
        $profile = $user->mentorProfile;

        abort_if(! $profile, 422, "Cette utilisatrice n'a pas de profil mentore.");

        $profile->update([
            'validated_at' => now(),
            'validated_by' => $request->user()->id,
        ]);
        $user->update(['status' => 'active']);

        AuditLog::record($request->user(), 'mentore.validee', $user);

        return $user->load('mentorProfile');
    }

    public function assignRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'staff', 'mentor', 'mentee', 'donor'])],
        ]);

        $user->syncRoles([$data['role']]);

        AuditLog::record($request->user(), 'role.modifie', $user, ['role' => $data['role']]);

        return $user->load('roles');
    }
}
