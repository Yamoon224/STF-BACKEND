<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\MentorshipPairing;
use App\Models\MentorshipSession;
use App\Models\Program;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/dashboard/kpis',
        summary: 'Indicateurs clés du tableau de bord',
        security: [['bearerAuth' => []]],
        tags: ['Tableau de bord'],
        responses: [
            new OA\Response(response: 200, description: 'KPIs', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'active_mentees', type: 'integer'),
                new OA\Property(property: 'validated_mentors', type: 'integer'),
                new OA\Property(property: 'active_pairings', type: 'integer'),
                new OA\Property(property: 'sessions_this_month', type: 'integer'),
            ])),
            new OA\Response(response: 403, description: "Permission `users.view` requise"),
        ]
    )]
    public function kpis()
    {
        return [
            'active_mentees' => User::role('mentee')->where('status', 'active')->count(),
            'validated_mentors' => MentorProfile::whereNotNull('validated_at')->count(),
            'active_pairings' => MentorshipPairing::where('status', 'actif')->count(),
            'sessions_this_month' => MentorshipSession::whereBetween('scheduled_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->count(),
        ];
    }

    #[OA\Get(
        path: '/dashboard/activity-by-program',
        summary: 'Activité par programme (mentées et sessions)',
        security: [['bearerAuth' => []]],
        tags: ['Tableau de bord'],
        responses: [
            new OA\Response(response: 200, description: 'Activité', content: new OA\JsonContent(type: 'array', items: new OA\Items(properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'mentees_count', type: 'integer'),
                new OA\Property(property: 'sessions_count', type: 'integer'),
            ]))),
            new OA\Response(response: 403, description: "Permission `users.view` requise"),
        ]
    )]
    public function activityByProgram()
    {
        return Program::withCount(['pairings as mentees_count', 'pairings as sessions_count' => function ($q) {
            $q->join('mentorship_sessions', 'mentorship_sessions.pairing_id', '=', 'mentorship_pairings.id');
        }])->get(['id', 'name']);
    }

    #[OA\Get(
        path: '/dashboard/alerts',
        summary: "Alertes (mentores en attente, signalements ouverts, binômes inactifs)",
        security: [['bearerAuth' => []]],
        tags: ['Tableau de bord'],
        responses: [
            new OA\Response(response: 200, description: 'Alertes', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'pending_mentors', type: 'integer'),
                new OA\Property(property: 'open_reports', type: 'integer'),
                new OA\Property(property: 'inactive_pairings', type: 'integer', description: 'Binômes actifs sans session depuis 30 jours'),
            ])),
            new OA\Response(response: 403, description: "Permission `users.view` requise"),
        ]
    )]
    public function alerts()
    {
        $pendingMentors = User::role('mentor')->where('status', 'pending')->count();
        $openReports = Report::where('status', '!=', 'resolu')->count();
        $staleThreshold = Carbon::now()->subDays(30);

        $inactivePairings = MentorshipPairing::where('status', 'actif')
            ->whereDoesntHave('sessions', fn ($q) => $q->where('scheduled_at', '>=', $staleThreshold))
            ->count();

        return [
            'pending_mentors' => $pendingMentors,
            'open_reports' => $openReports,
            'inactive_pairings' => $inactivePairings,
        ];
    }
}
