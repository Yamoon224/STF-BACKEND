<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return Group::query()
            ->withCount('members')
            ->when(! $user->can('groups.manage'), fn ($q) => $q->whereHas('members', fn ($q) => $q->whereKey($user->id)))
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->orderBy('name')
            ->get();
    }

    public function show(Group $group)
    {
        $this->authorize('view', $group);

        return $group->load(['members', 'program']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Group::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => [Rule::in(['automatique', 'travail', 'mentorat'])],
            'program_id' => ['nullable', 'exists:programs,id'],
            'status' => [Rule::in(['en_validation', 'actif', 'archive'])],
        ]);

        $data['type'] ??= 'travail';
        $data['status'] ??= 'en_validation';
        $data['created_by'] = $request->user()->id;

        $group = Group::create($data);
        $group->members()->attach($request->user()->id, [
            'role_in_group' => 'animatrice',
            'joined_at' => now(),
        ]);

        return response()->json($group, 201);
    }

    public function update(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => [Rule::in(['automatique', 'travail', 'mentorat'])],
            'status' => [Rule::in(['en_validation', 'actif', 'archive'])],
        ]);

        $group->update($data);

        return $group;
    }

    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);

        $group->delete();

        return response()->noContent();
    }

    public function addMember(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_in_group' => [Rule::in(['membre', 'animatrice'])],
        ]);

        $group->members()->syncWithoutDetaching([
            $data['user_id'] => [
                'role_in_group' => $data['role_in_group'] ?? 'membre',
                'joined_at' => now(),
            ],
        ]);

        return response()->json($group->load('members'));
    }

    public function removeMember(Request $request, Group $group, int $userId)
    {
        $this->authorize('update', $group);

        $group->members()->detach($userId);

        return response()->noContent();
    }
}
