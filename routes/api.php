<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CmsPageController;
use App\Http\Controllers\Api\CohortController;
use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExperimentController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\GroupCommentController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\GroupFileController;
use App\Http\Controllers\Api\GroupPostController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\LiveSessionController;
use App\Http\Controllers\Api\MatchingController;
use App\Http\Controllers\Api\MentorshipPairingController;
use App\Http\Controllers\Api\MentorshipSessionController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SecuritySettingsController;
use App\Http\Controllers\Api\SessionNoteController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// --- Auth ---------------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('mfa/verify', [AuthController::class, 'verifyMfa']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('mfa/setup', [SecuritySettingsController::class, 'setupMfa']);
        Route::post('mfa/confirm', [SecuritySettingsController::class, 'confirmMfa']);
        Route::post('mfa/disable', [SecuritySettingsController::class, 'disableMfa']);
        Route::post('password', [SecuritySettingsController::class, 'changePassword']);
    });
});

// --- Public vitrine content ----------------------------------------------
Route::get('programs', [ProgramController::class, 'index']);
Route::get('programs/{program:slug}', [ProgramController::class, 'show']);
Route::get('testimonials', [TestimonialController::class, 'index']);
Route::get('partners', [PartnerController::class, 'index']);
Route::get('faqs', [FaqController::class, 'index']);
Route::get('cms/pages', [CmsPageController::class, 'index']);
Route::get('cms/pages/{slug}', [CmsPageController::class, 'show']);
Route::get('stats/impact', [StatsController::class, 'impact']);
Route::post('contact', [ContactMessageController::class, 'store']);

// --- Cours de renforcement, labo virtuel & sessions live (parcours public niveau → matière) ---
Route::get('levels', [LevelController::class, 'index']);
Route::get('subjects', [SubjectController::class, 'index']);
Route::get('courses', [CourseController::class, 'index']);
Route::get('courses/{course}', [CourseController::class, 'show']);
Route::get('experiments', [ExperimentController::class, 'index']);
Route::get('experiments/{experiment}', [ExperimentController::class, 'show']);
Route::get('live-sessions', [LiveSessionController::class, 'index']);
Route::get('live-sessions/{liveSession}', [LiveSessionController::class, 'show']);

// --- Authenticated API ----------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Users / RBAC administration
    Route::get('users', [UserController::class, 'index'])->middleware('permission:users.view');
    Route::post('users', [UserController::class, 'store'])->middleware('permission:users.manage');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:users.view');
    Route::patch('users/{user}', [UserController::class, 'update'])->middleware('permission:users.manage');
    Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->middleware('permission:users.manage');
    Route::post('users/{user}/activate', [UserController::class, 'activate'])->middleware('permission:users.manage');
    Route::post('users/{user}/validate-mentor', [UserController::class, 'validateMentor'])->middleware('permission:users.manage');
    Route::post('users/{user}/role', [UserController::class, 'assignRole'])->middleware('permission:settings.manage');
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:users.view');

    // Programs & cohorts
    Route::post('programs', [ProgramController::class, 'store']);
    Route::patch('programs/{program}', [ProgramController::class, 'update']);
    Route::delete('programs/{program}', [ProgramController::class, 'destroy']);
    Route::apiResource('cohorts', CohortController::class)->except(['index', 'show']);
    Route::get('cohorts', [CohortController::class, 'index']);
    Route::get('cohorts/{cohort}', [CohortController::class, 'show']);

    // Matching & mentorship pairings
    Route::get('matching/suggestions', [MatchingController::class, 'suggestions']);
    Route::apiResource('pairings', MentorshipPairingController::class);

    // Sessions & session notes
    Route::apiResource('sessions', MentorshipSessionController::class);
    Route::get('sessions/{session}/notes', [SessionNoteController::class, 'index']);
    Route::post('sessions/{session}/notes', [SessionNoteController::class, 'store']);
    Route::patch('notes/{note}', [SessionNoteController::class, 'update']);
    Route::delete('notes/{note}', [SessionNoteController::class, 'destroy']);

    // Modules, quizzes & progress
    Route::get('modules', [ModuleController::class, 'index']);
    Route::get('modules/{module}', [ModuleController::class, 'show']);
    Route::post('modules', [ModuleController::class, 'store'])->middleware('permission:programs.manage');
    Route::patch('modules/{module}', [ModuleController::class, 'update'])->middleware('permission:programs.manage');
    Route::delete('modules/{module}', [ModuleController::class, 'destroy'])->middleware('permission:programs.manage');
    Route::post('modules/{module}/progress', [ModuleController::class, 'updateProgress']);
    Route::post('modules/{module}/quizzes', [QuizController::class, 'store'])->middleware('permission:programs.manage');
    Route::get('quizzes/{quiz}', [QuizController::class, 'show']);
    Route::post('quizzes/{quiz}/attempts', [QuizController::class, 'attempt']);

    // Cours de renforcement, labo virtuel & sessions live
    Route::post('courses', [CourseController::class, 'store'])->middleware('permission:programs.manage');
    Route::patch('courses/{course}', [CourseController::class, 'update'])->middleware('permission:programs.manage');
    Route::delete('courses/{course}', [CourseController::class, 'destroy'])->middleware('permission:programs.manage');
    Route::post('courses/{course}/progress', [CourseController::class, 'updateProgress']);
    Route::post('experiments', [ExperimentController::class, 'store'])->middleware('permission:programs.manage');
    Route::patch('experiments/{experiment}', [ExperimentController::class, 'update'])->middleware('permission:programs.manage');
    Route::delete('experiments/{experiment}', [ExperimentController::class, 'destroy'])->middleware('permission:programs.manage');
    Route::post('live-sessions', [LiveSessionController::class, 'store'])->middleware('permission:programs.manage');
    Route::patch('live-sessions/{liveSession}', [LiveSessionController::class, 'update'])->middleware('permission:programs.manage');
    Route::delete('live-sessions/{liveSession}', [LiveSessionController::class, 'destroy'])->middleware('permission:programs.manage');

    // Badges & certificates
    Route::get('badges', [BadgeController::class, 'index']);
    Route::post('badges', [BadgeController::class, 'store']);
    Route::post('badges/{badge}/award', [BadgeController::class, 'award']);
    Route::get('certificates', [CertificateController::class, 'index']);
    Route::post('certificates', [CertificateController::class, 'store']);

    // Mentee projects
    Route::apiResource('projects', ProjectController::class);

    // Groups
    Route::apiResource('groups', GroupController::class);
    Route::post('groups/{group}/members', [GroupController::class, 'addMember']);
    Route::delete('groups/{group}/members/{userId}', [GroupController::class, 'removeMember']);
    Route::get('groups/{group}/posts', [GroupPostController::class, 'index']);
    Route::post('groups/{group}/posts', [GroupPostController::class, 'store']);
    Route::delete('posts/{post}', [GroupPostController::class, 'destroy']);
    Route::post('posts/{post}/comments', [GroupCommentController::class, 'store']);
    Route::delete('comments/{comment}', [GroupCommentController::class, 'destroy']);
    Route::get('groups/{group}/files', [GroupFileController::class, 'index']);
    Route::post('groups/{group}/files', [GroupFileController::class, 'store']);
    Route::delete('files/{file}', [GroupFileController::class, 'destroy']);

    // Messaging
    Route::get('conversations', [ConversationController::class, 'index']);
    Route::post('conversations', [ConversationController::class, 'store']);
    Route::get('conversations/{conversation}/messages', [ConversationController::class, 'messages']);
    Route::post('conversations/{conversation}/messages', [ConversationController::class, 'sendMessage']);
    Route::post('conversations/{conversation}/read', [ConversationController::class, 'markRead']);

    // Reports (signalements) & audit logs
    Route::get('reports', [ReportController::class, 'index']);
    Route::post('reports', [ReportController::class, 'store']);
    Route::patch('reports/{report}', [ReportController::class, 'update']);
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit-logs.view');
    Route::get('contact-messages', [ContactMessageController::class, 'index'])->middleware('permission:reports.view');

    // CMS content management
    Route::post('cms/pages', [CmsPageController::class, 'store'])->middleware('permission:cms.manage');
    Route::patch('cms/pages/{page}', [CmsPageController::class, 'update'])->middleware('permission:cms.manage');
    Route::delete('cms/pages/{page}', [CmsPageController::class, 'destroy'])->middleware('permission:cms.manage');
    Route::post('partners', [PartnerController::class, 'store'])->middleware('permission:cms.manage');
    Route::patch('partners/{partner}', [PartnerController::class, 'update'])->middleware('permission:cms.manage');
    Route::delete('partners/{partner}', [PartnerController::class, 'destroy'])->middleware('permission:cms.manage');
    Route::post('testimonials', [TestimonialController::class, 'store'])->middleware('permission:cms.manage');
    Route::patch('testimonials/{testimonial}', [TestimonialController::class, 'update'])->middleware('permission:cms.manage');
    Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->middleware('permission:cms.manage');
    Route::post('faqs', [FaqController::class, 'store'])->middleware('permission:cms.manage');
    Route::patch('faqs/{faq}', [FaqController::class, 'update'])->middleware('permission:cms.manage');
    Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])->middleware('permission:cms.manage');

    // Backoffice dashboard
    Route::get('dashboard/kpis', [DashboardController::class, 'kpis'])->middleware('permission:users.view');
    Route::get('dashboard/activity-by-program', [DashboardController::class, 'activityByProgram'])->middleware('permission:users.view');
    Route::get('dashboard/alerts', [DashboardController::class, 'alerts'])->middleware('permission:users.view');
});
