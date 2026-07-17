<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class CertificateController extends Controller
{
    #[OA\Get(
        path: '/certificates',
        summary: 'Lister les certificats',
        description: 'Sans `users.manage`, ne renvoie que les certificats de l’utilisatrice connectée.',
        security: [['bearerAuth' => []]],
        tags: ['Certificats'],
        parameters: [new OA\QueryParameter(name: 'user_id', description: "Filtrer par utilisatrice (nécessite `users.manage`)", schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Certificats', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Certificate')))]
    )]
    public function index(Request $request)
    {
        return Certificate::query()
            ->when(! $request->user()->can('users.manage'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->query('user_id') && $request->user()->can('users.manage'), fn ($q) => $q->where('user_id', $request->query('user_id')))
            ->with('program')
            ->orderByDesc('issued_at')
            ->get();
    }

    #[OA\Post(
        path: '/certificates',
        summary: 'Émettre un certificat',
        description: 'Génère un numéro de série unique (`serial_number`) et horodate l’émission.',
        security: [['bearerAuth' => []]],
        tags: ['Certificats'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['user_id', 'title'], properties: [
                new OA\Property(property: 'user_id', type: 'integer'),
                new OA\Property(property: 'program_id', type: 'integer', nullable: true),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'file_path', type: 'string', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Émis', content: new OA\JsonContent(ref: '#/components/schemas/Certificate')),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
        ]
    )]
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
