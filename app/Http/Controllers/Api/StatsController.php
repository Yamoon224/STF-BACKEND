<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\MentorshipPairing;
use App\Models\User;
use OpenApi\Attributes as OA;

class StatsController extends Controller
{
    /**
     * Public impact figures displayed on the vitrine site.
     */
    #[OA\Get(
        path: '/stats/impact',
        summary: "Statistiques d'impact publiques",
        description: 'Public — affichées sur le site vitrine (bénéficiaires, mentores actives, binômes, pays).',
        tags: ['Statistiques'],
        responses: [new OA\Response(response: 200, description: 'Statistiques', content: new OA\JsonContent(properties: [
            new OA\Property(property: 'beneficiaries', type: 'integer'),
            new OA\Property(property: 'active_mentors', type: 'integer'),
            new OA\Property(property: 'pairings', type: 'integer'),
            new OA\Property(property: 'countries', type: 'integer'),
        ]))]
    )]
    public function impact()
    {
        return [
            'beneficiaries' => User::role('mentee')->count(),
            'active_mentors' => MentorProfile::whereNotNull('validated_at')->count(),
            'pairings' => MentorshipPairing::count(),
            'countries' => User::whereNotNull('country')->distinct('country')->count('country'),
        ];
    }
}
