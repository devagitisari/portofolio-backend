<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\GitHubActivityController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{slug}', [ProjectController::class, 'show']);

Route::get('/skills', [SkillController::class, 'index']);

Route::get('/experiences', [ExperienceController::class, 'index']);

Route::get('/certificates', [CertificateController::class, 'index']);
Route::get('/certificates/{certificate}', [CertificateController::class, 'show']);
Route::get('/certificates/{certificate}/thumbnail', [CertificateController::class, 'getPdfThumbnail']);

Route::get('/settings', [SettingController::class, 'index']);
Route::get('/resume', [SettingController::class, 'resume']);
Route::get('/github-activity', [GitHubActivityController::class, 'index']);
Route::get('/github-contributions', [GitHubActivityController::class, 'contributions']);

Route::post('/contact', [InquiryController::class, 'store']);

// Analytics tracking (public)
Route::post('/analytics/track', [AnalyticsController::class, 'track']);


// Admin routes (protected by auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('admin/projects', ProjectController::class)->except(['index', 'show']);
    Route::apiResource('admin/skills', SkillController::class);
    Route::apiResource('admin/experiences', ExperienceController::class);
    Route::apiResource('admin/certificates', CertificateController::class);
    Route::apiResource('admin/inquiries', InquiryController::class);
    Route::post('/admin/inquiries/{inquiry}/replies', [ReplyController::class, 'store']);
    Route::get('/admin/inquiries/{inquiry}/replies', [ReplyController::class, 'index']);
    Route::apiResource('admin/settings', SettingController::class);
    Route::get('/admin/sessions', [AuthController::class, 'sessions']);
    Route::delete('/admin/sessions/{token}', [AuthController::class, 'revokeSession']);
    Route::post('/admin/password', [AuthController::class, 'updatePassword']);
    Route::post('/admin/revoke-sessions', [AuthController::class, 'logoutOtherDevices']);
    Route::post('/admin/two-factor', [AuthController::class, 'toggleTwoFactor']);

    // Analytics routes
    Route::get('/admin/analytics/overview', [AnalyticsController::class, 'overview']);

    // Backup routes
    Route::get('/admin/backup/export', [BackupController::class, 'export']);
    Route::post('/admin/backup/import', [BackupController::class, 'import']);
});
