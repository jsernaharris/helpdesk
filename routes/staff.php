<?php

use App\Http\Controllers\Staff\ChangeRequestController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\KnowledgeBaseController;
use App\Http\Controllers\Staff\OrganizationController;
use App\Http\Controllers\Staff\ProblemController;
use App\Http\Controllers\Staff\ReportController;
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

    // Problem Management
    Route::resource('problems', ProblemController::class)->except(['destroy', 'edit']);
    Route::post('/problems/{problem}/link-incident', [ProblemController::class, 'linkIncident'])->name('problems.link-incident');

    // Change Management
    Route::resource('changes', ChangeRequestController::class)->except(['destroy', 'edit', 'update']);
    Route::patch('/changes/{change}/approve', [ChangeRequestController::class, 'approve'])->name('changes.approve');
    Route::patch('/changes/{change}/reject', [ChangeRequestController::class, 'reject'])->name('changes.reject');

    // Knowledge Base
    Route::resource('kb', KnowledgeBaseController::class)->except(['show']);

    // Organizations
    Route::resource('organizations', OrganizationController::class);

    // Users
    Route::resource('users', UserController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sla-compliance', [ReportController::class, 'slaCompliance'])->name('reports.sla-compliance');
    Route::get('/reports/ticket-volume', [ReportController::class, 'ticketVolume'])->name('reports.ticket-volume');
    Route::get('/reports/technician-performance', [ReportController::class, 'technicianPerformance'])->name('reports.technician-performance');
});
