<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NewsletterSubscriberController extends Controller
{
    #[OA\Get(
        path: '/newsletter/subscribers',
        summary: 'Lister les abonnées à la newsletter',
        security: [['bearerAuth' => []]],
        tags: ['Newsletter'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['actif', 'desabonne'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page paginée (20/page)', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/NewsletterSubscriber')),
            ])),
            new OA\Response(response: 403, description: "Permission `newsletter.manage` requise"),
        ]
    )]
    public function index(Request $request)
    {
        abort_unless($request->user()->can('newsletter.manage'), 403);

        $query = NewsletterSubscriber::orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $query->paginate(20);
    }

    #[OA\Post(
        path: '/newsletter/subscribe',
        summary: "S'abonner à la newsletter",
        description: 'Public — utilisé par le site vitrine. Réabonne automatiquement une adresse désabonnée.',
        tags: ['Newsletter'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['email'], properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'name', type: 'string', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Abonnée', content: new OA\JsonContent(ref: '#/components/schemas/NewsletterSubscriber')),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $data['email']]);
        $subscriber->name = $data['name'] ?? $subscriber->name;
        $subscriber->status = 'actif';
        $subscriber->subscribed_at = now();
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        return response()->json($subscriber, 201);
    }

    #[OA\Post(
        path: '/newsletter/unsubscribe',
        summary: "Se désabonner de la newsletter",
        description: 'Public — utilisé par le site vitrine (lien de désabonnement).',
        tags: ['Newsletter'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['email'], properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Désabonnée'),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function unsubscribe(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::where('email', $data['email'])->first();

        if ($subscriber) {
            $subscriber->update(['status' => 'desabonne', 'unsubscribed_at' => now()]);
        }

        return response()->json(['message' => 'Désabonnée avec succès.']);
    }

    #[OA\Delete(
        path: '/newsletter/subscribers/{subscriber}',
        summary: 'Supprimer une abonnée',
        security: [['bearerAuth' => []]],
        tags: ['Newsletter'],
        parameters: [new OA\Parameter(name: 'subscriber', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Permission `newsletter.manage` requise"),
        ]
    )]
    public function destroy(Request $request, NewsletterSubscriber $subscriber)
    {
        abort_unless($request->user()->can('newsletter.manage'), 403);

        $subscriber->delete();

        return response()->noContent();
    }
}
