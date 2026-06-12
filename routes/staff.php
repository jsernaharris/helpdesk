<?php

use App\Http\Controllers\Staff\ChangePolicyController;
use App\Http\Controllers\Staff\ChangeRequestController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\FormTemplateController;
use App\Http\Controllers\Staff\KnowledgeBaseController;
use App\Http\Controllers\Staff\MailboxController;
use App\Http\Controllers\Staff\OrganizationController;
use App\Http\Controllers\Staff\ProblemController;
use App\Http\Controllers\Staff\ProjectController;
use App\Http\Controllers\Staff\ProjectTimeController;
use App\Http\Controllers\Staff\QueueController;
use App\Http\Controllers\Staff\ReportController;
use App\Http\Controllers\Staff\RoleController;
use App\Http\Controllers\Staff\TicketAiController;
use App\Http\Controllers\Staff\TicketController;
use App\Http\Controllers\Staff\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'msp_staff'])->prefix('staff')->name('staff.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tickets
    Route::resource('tickets', TicketController::class)->except(['destroy']);
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::post('/tickets/{ticket}/note', [TicketController::class, 'addNote'])->name('tickets.note');
    Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::patch('/tickets/{ticket}/escalate', [TicketController::class, 'escalate'])->name('tickets.escalate');
    Route::post('/tickets/{ticket}/merge', [TicketController::class, 'merge'])->name('tickets.merge');
    Route::post('/tickets/{ticket}/ai/suggest-reply', [TicketAiController::class, 'suggestReply'])->name('tickets.ai.suggest-reply');
    Route::post('/tickets/{ticket}/ai/triage', [TicketAiController::class, 'triage'])->name('tickets.ai.triage');

    // Problem Management
    Route::resource('problems', ProblemController::class)->except(['destroy', 'edit']);
    Route::post('/problems/{problem}/link-incident', [ProblemController::class, 'linkIncident'])->name('problems.link-incident');

    // Change Management
    Route::resource('changes', ChangeRequestController::class)->except(['destroy']);
    Route::post('/changes/{change}/submit', [ChangeRequestController::class, 'submit'])->name('changes.submit');
    Route::patch('/changes/{change}/approve', [ChangeRequestController::class, 'approve'])->name('changes.approve');
    Route::patch('/changes/{change}/reject', [ChangeRequestController::class, 'reject'])->name('changes.reject');
    Route::patch('/changes/{change}/start-implementation', [ChangeRequestController::class, 'startImplementation'])->name('changes.start-implementation');
    Route::patch('/changes/{change}/complete-implementation', [ChangeRequestController::class, 'completeImplementation'])->name('changes.complete-implementation');
    Route::post('/changes/{change}/review', [ChangeRequestController::class, 'storeReview'])->name('changes.review');
    Route::get('/change-calendar', [ChangeRequestController::class, 'calendar'])->name('changes.calendar');

    // Per-Organization Change Policies
    Route::get('/organizations/{organization}/change-policy', [ChangePolicyController::class, 'show'])->name('changes.policy');
    Route::put('/organizations/{organization}/change-policy', [ChangePolicyController::class, 'updatePolicy'])->name('changes.policy.update');
    Route::post('/organizations/{organization}/change-categories', [ChangePolicyController::class, 'storeCategory'])->name('changes.categories.store');
    Route::delete('/organizations/{organization}/change-categories/{category}', [ChangePolicyController::class, 'destroyCategory'])->name('changes.categories.destroy');
    Route::post('/organizations/{organization}/cab-members', [ChangePolicyController::class, 'storeCabMember'])->name('changes.cab.store');
    Route::delete('/organizations/{organization}/cab-members/{member}', [ChangePolicyController::class, 'destroyCabMember'])->name('changes.cab.destroy');
    Route::post('/organizations/{organization}/blackouts', [ChangePolicyController::class, 'storeBlackout'])->name('changes.blackouts.store');
    Route::delete('/organizations/{organization}/blackouts/{blackout}', [ChangePolicyController::class, 'destroyBlackout'])->name('changes.blackouts.destroy');

    // Form Templates
    Route::resource('form-templates', FormTemplateController::class);

    // Knowledge Base
    Route::resource('kb', KnowledgeBaseController::class);
    Route::post('/kb/upload-image', [KnowledgeBaseController::class, 'uploadImage'])->name('kb.upload-image');

    // Projects & technician time tracking
    Route::get('/projects-time/export', [ProjectTimeController::class, 'export'])->name('projects.time.export');
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.members.store');
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.members.destroy');
    Route::post('/projects/{project}/time', [ProjectTimeController::class, 'store'])->name('projects.time.store');
    Route::delete('/projects/{project}/time/{entry}', [ProjectTimeController::class, 'destroy'])->name('projects.time.destroy');

    // Organizations
    Route::resource('organizations', OrganizationController::class);

    // Per-Organization ticket queues (service lines: Cybersecurity, AI, etc.)
    Route::post('/organizations/{organization}/queues', [QueueController::class, 'store'])->name('queues.store');
    Route::delete('/organizations/{organization}/queues/{queue}', [QueueController::class, 'destroy'])->name('queues.destroy');

    // Users
    Route::resource('users', UserController::class);

    // Roles & Permissions
    Route::resource('roles', RoleController::class)->except(['show'])->middleware('can:settings.manage');

    // Email Mailboxes (inbound listeners + outbound senders)
    Route::middleware('can:settings.manage')->group(function () {
        Route::resource('mailboxes', MailboxController::class);
        Route::post('/mailboxes/{mailbox}/test', [MailboxController::class, 'test'])->name('mailboxes.test');
    });

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sla-compliance', [ReportController::class, 'slaCompliance'])->name('reports.sla-compliance');
    Route::get('/reports/ticket-volume', [ReportController::class, 'ticketVolume'])->name('reports.ticket-volume');
    Route::get('/reports/technician-performance', [ReportController::class, 'technicianPerformance'])->name('reports.technician-performance');
});
