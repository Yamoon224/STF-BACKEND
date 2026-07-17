<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    #[OA\Get(
        path: '/roles',
        summary: 'Lister les rôles et leurs permissions',
        security: [['bearerAuth' => []]],
        tags: ['Rôles'],
        responses: [
            new OA\Response(response: 200, description: 'Rôles', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Role'))),
            new OA\Response(response: 403, description: "Permission `users.view` requise"),
        ]
    )]
    public function index()
    {
        return Role::with('permissions:id,name')->get(['id', 'name'])->map(fn (Role $role) => [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }
}
