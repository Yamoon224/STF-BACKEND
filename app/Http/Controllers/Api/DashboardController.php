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

class DashboardController extends Controller
{
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

    public function activityByProgram()
    {
        return Program::withCount(['pairings as mentees_count', 'pairings as sessions_count' => function ($q) {
            $q->join('mentorship_sessions', 'mentorship_sessions.pairing_id', '=', 'mentorship_pairings.id');
        }])->get(['id', 'name']);
    }

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
