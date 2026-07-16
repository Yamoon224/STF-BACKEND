<?php

namespace Tests\Feature\Api;

use App\Models\AuditLog;
use App\Models\Report;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportAndAuditLogTest extends TestCase
{
    public function test_any_authenticated_user_can_create_a_report(): void
    {
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $response = $this->postJson('/api/reports', [
            'context_type' => 'messagerie_pairing',
            'context_id' => 1,
            'description' => 'Message inapproprié.',
        ]);

        $response->assertCreated()->assertJsonPath('status', 'nouveau');
    }

    public function test_reporter_only_sees_her_own_reports(): void
    {
        $reporter = $this->makeUser('mentee');
        Report::create(['reporter_id' => $reporter->id, 'context_type' => 'messagerie_pairing', 'description' => 'A']);
        Report::create(['reporter_id' => $this->makeUser('mentee')->id, 'context_type' => 'messagerie_pairing', 'description' => 'B']);

        Sanctum::actingAs($reporter, ['*']);

        $this->getJson('/api/reports')->assertOk()->assertJsonCount(1);
    }

    public function test_staff_sees_all_reports_and_can_change_status(): void
    {
        $report = Report::create(['reporter_id' => $this->makeUser('mentee')->id, 'context_type' => 'groupe', 'description' => 'X']);
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $this->getJson('/api/reports')->assertOk()->assertJsonCount(1);

        $response = $this->patchJson("/api/reports/{$report->id}", ['status' => 'resolu']);
        $response->assertOk()->assertJsonPath('status', 'resolu');
        $this->assertNotNull($report->fresh()->resolved_at);
    }

    public function test_mentee_cannot_change_report_status(): void
    {
        $report = Report::create(['reporter_id' => $this->makeUser('mentee')->id, 'context_type' => 'groupe', 'description' => 'X']);
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->patchJson("/api/reports/{$report->id}", ['status' => 'resolu'])->assertForbidden();
    }

    public function test_audit_log_can_be_recorded_and_listed_by_authorized_staff(): void
    {
        $actor = $this->makeUser('admin');
        AuditLog::record($actor, 'compte.suspendu', $actor);

        Sanctum::actingAs($actor, ['*']);

        $response = $this->getJson('/api/audit-logs');

        $response->assertOk()->assertJsonPath('data.0.action', 'compte.suspendu');
    }

    public function test_mentee_cannot_view_audit_logs(): void
    {
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->getJson('/api/audit-logs')->assertForbidden();
    }
}
