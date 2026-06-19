<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminGroupController;
use App\Http\Controllers\AttendanceApprovalController;
use App\Http\Controllers\AttendanceCheckInController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LeaderDashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\SuperadminDashboardController;
use App\Http\Controllers\UstadDashboardController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────
// Public Routes
// ─────────────────────────────────────────────────────────

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─────────────────────────────────────────────────────────
// Authenticated Routes
// ─────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ─── 1. Superadmin Area ──────────────────────────────
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('/superadmin/dashboard', [SuperadminDashboardController::class, 'index'])
            ->name('superadmin.dashboard');

        // User CRUD
        Route::post('/superadmin/users', [SuperadminDashboardController::class, 'storeUser'])->name('superadmin.users.store');
        Route::put('/superadmin/users/{user}', [SuperadminDashboardController::class, 'updateUser'])->name('superadmin.users.update');
        Route::delete('/superadmin/users/{user}', [SuperadminDashboardController::class, 'destroyUser'])->name('superadmin.users.destroy');

        // System Settings
        Route::post('/superadmin/settings', [SuperadminDashboardController::class, 'updateSettings'])->name('superadmin.settings.update');

        // Group CRUD
        Route::post('/superadmin/groups', [SuperadminDashboardController::class, 'storeGroup'])->name('superadmin.groups.store');
        Route::put('/superadmin/groups/{group}', [SuperadminDashboardController::class, 'updateGroup'])->name('superadmin.groups.update');
        Route::delete('/superadmin/groups/{group}', [SuperadminDashboardController::class, 'destroyGroup'])->name('superadmin.groups.destroy');
        Route::post('/superadmin/groups/{group}/members', [SuperadminDashboardController::class, 'attachMembers'])->name('superadmin.groups.attach-members');
        Route::delete('/superadmin/groups/{group}/members/{user}', [SuperadminDashboardController::class, 'detachMember'])->name('superadmin.groups.detach-member');

        // Material CRUD
        Route::post('/superadmin/materials', [SuperadminDashboardController::class, 'storeMaterial'])->name('superadmin.materials.store');
        Route::delete('/superadmin/materials/{material}', [SuperadminDashboardController::class, 'destroyMaterial'])->name('superadmin.materials.destroy');

        // Activity CRUD
        Route::post('/superadmin/activities', [SuperadminDashboardController::class, 'storeActivity'])->name('superadmin.activities.store');
        Route::put('/superadmin/activities/{activity}', [SuperadminDashboardController::class, 'updateActivity'])->name('superadmin.activities.update');
        Route::delete('/superadmin/activities/{activity}', [SuperadminDashboardController::class, 'destroyActivity'])->name('superadmin.activities.destroy');

        // Grade CRUD
        Route::post('/superadmin/grades', [SuperadminDashboardController::class, 'storeGrade'])->name('superadmin.grades.store');
        Route::delete('/superadmin/grades/{grade}', [SuperadminDashboardController::class, 'destroyGrade'])->name('superadmin.grades.destroy');

        // Attendance CRUD
        Route::post('/superadmin/activities/{activity}/attendances/approve', [SuperadminDashboardController::class, 'approveAttendance'])->name('superadmin.attendances.approve');
        Route::delete('/superadmin/attendances/{attendance}', [SuperadminDashboardController::class, 'destroyAttendance'])->name('superadmin.attendances.destroy');
    });

    // ─── 2. Admin Area ───────────────────────────────────
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminGroupController::class, 'dashboard'])
            ->name('admin.dashboard');

        // Group CRUD operations
        Route::post('/admin/groups', [AdminGroupController::class, 'store'])
            ->name('admin.groups.store');
        Route::put('/admin/groups/{group}', [AdminGroupController::class, 'update'])
            ->name('admin.groups.update');
        Route::delete('/admin/groups/{group}', [AdminGroupController::class, 'destroy'])
            ->name('admin.groups.destroy');

        // Member plotting
        Route::post('/admin/groups/{group}/members', [AdminGroupController::class, 'attachMembers'])
            ->name('admin.groups.attach-members');
        Route::delete('/admin/groups/{group}/members/{user}', [AdminGroupController::class, 'detachMember'])
            ->name('admin.groups.detach-member');

        // Reports download
        Route::get('/admin/reports/download', [AdminGroupController::class, 'downloadReport'])
            ->name('admin.reports.download');

        // Material CRUD
        Route::post('/admin/materials', [AdminGroupController::class, 'storeMaterial'])->name('admin.materials.store');
        Route::delete('/admin/materials/{material}', [AdminGroupController::class, 'destroyMaterial'])->name('admin.materials.destroy');

        // Activity CRUD
        Route::post('/admin/activities', [AdminGroupController::class, 'storeActivity'])->name('admin.activities.store');
        Route::put('/admin/activities/{activity}', [AdminGroupController::class, 'updateActivity'])->name('admin.activities.update');
        Route::delete('/admin/activities/{activity}', [AdminGroupController::class, 'destroyActivity'])->name('admin.activities.destroy');

        // Grade CRUD
        Route::post('/admin/grades', [AdminGroupController::class, 'storeGrade'])->name('admin.grades.store');
        Route::delete('/admin/grades/{grade}', [AdminGroupController::class, 'destroyGrade'])->name('admin.grades.destroy');

        // Attendance CRUD
        Route::post('/admin/activities/{activity}/attendances/approve', [AdminGroupController::class, 'approveAttendance'])->name('admin.attendances.approve');
        Route::delete('/admin/attendances/{attendance}', [AdminGroupController::class, 'destroyAttendance'])->name('admin.attendances.destroy');
    });

    // ─── 3. Ustad (Pembina) Area ─────────────────────────
    Route::middleware(['role:ustad'])->group(function () {
        Route::get('/ustad/dashboard', [UstadDashboardController::class, 'index'])
            ->name('ustad.dashboard');

        // Materials Management (Ustad only)
        Route::post('/materials', [MaterialController::class, 'store'])
            ->name('materials.store');
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])
            ->name('materials.destroy');

        // Grades Management (Ustad only)
        Route::post('/grades', [GradeController::class, 'store'])
            ->name('grades.store');
        Route::post('/grades/bulk', [GradeController::class, 'bulkStore'])
            ->name('grades.bulk-store');
        Route::delete('/grades/{grade}', [GradeController::class, 'destroy'])
            ->name('grades.destroy');

        // Activities / Kajian Sessions Management (Ustad only)
        Route::post('/activities', [ActivityController::class, 'store'])
            ->name('activities.store');
        Route::put('/activities/{activity}', [ActivityController::class, 'update'])
            ->name('activities.update');
        Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])
            ->name('activities.destroy');

        // Attendance Management (Ustad only)
        Route::delete('/attendances/{attendance}', [AttendanceApprovalController::class, 'destroy'])
            ->name('attendances.destroy');
    });

    // ─── 4. Ketua Kelompok (Leader) Area ─────────────────
    Route::middleware(['role:leader'])->group(function () {
        Route::get('/leader/dashboard', [LeaderDashboardController::class, 'index'])
            ->name('leader.dashboard');
    });

    // ─── 5. Member (Anggota) Area ────────────────────────
    Route::middleware(['role:member'])->group(function () {
        Route::get('/member/dashboard', [MemberDashboardController::class, 'index'])
            ->name('member.dashboard');
    });

    // ─────────────────────────────────────────────────────
    // Shared Routes (accessible by multiple roles)
    // Authorization is handled inside each controller method
    // ─────────────────────────────────────────────────────

    // Self check-in — Leader OR Member (controller validates group membership)
    Route::post('/attendances/check-in', [AttendanceCheckInController::class, 'checkIn'])
        ->name('attendances.check-in');

    // Self check-out — Member (or Leader)
    Route::post('/attendances/check-out', [AttendanceCheckInController::class, 'checkOut'])
        ->name('attendances.check-out');

    // Attendance approval — Ustad OR delegated Leader (controller checks authorization)
    Route::post('/activities/{activity}/attendances/approve', [AttendanceApprovalController::class, 'approve'])
        ->name('attendances.approve');

    Route::post('/activities/{activity}/attendances/{user}/approve', [AttendanceApprovalController::class, 'approveSingle'])
        ->name('attendances.approve-single');

    Route::post('/activities/{activity}/attendances/{user}/reject', [AttendanceApprovalController::class, 'rejectSingle'])
        ->name('attendances.reject-single');

    // Delegation toggle — Admin OR Ustad (controller checks authorization)
    Route::post('/groups/{group}/delegation/toggle', [AttendanceApprovalController::class, 'toggleDelegation'])
        ->name('groups.delegation.toggle');

    // Material download — all authenticated users
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])
        ->name('materials.download');
});
