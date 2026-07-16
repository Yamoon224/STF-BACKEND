<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\MentorshipPairing;
use App\Models\User;

class StatsController extends Controller
{
    /**
     * Public impact figures displayed on the vitrine site.
     */
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
