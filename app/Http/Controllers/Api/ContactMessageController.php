<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ContactMessageController extends Controller
{
    #[OA\Get(
        path: '/contact-messages',
        summary: 'Lister les messages de contact reçus',
        security: [['bearerAuth' => []]],
        tags: ['Contact'],
        responses: [
            new OA\Response(response: 200, description: 'Page paginée (20/page)', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ContactMessage')),
            ])),
            new OA\Response(response: 403, description: "Permission `reports.view` requise"),
        ]
    )]
    public function index(Request $request)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        return ContactMessage::orderByDesc('created_at')->paginate(20);
    }

    #[OA\Post(
        path: '/contact',
        summary: 'Envoyer un message via le formulaire de contact',
        description: 'Public — utilisé par le site vitrine.',
        tags: ['Contact'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['name', 'email', 'subject', 'message'], properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'audience', type: 'string', enum: ['generale', 'mentorat', 'partenaire', 'institution', 'media'], default: 'generale'),
                new OA\Property(property: 'subject', type: 'string'),
                new OA\Property(property: 'message', type: 'string'),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Envoyé', content: new OA\JsonContent(ref: '#/components/schemas/ContactMessage')),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
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
