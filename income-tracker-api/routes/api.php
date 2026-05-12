<?php

use App\Http\Controllers\Api\AccountsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CloseoutsController;
use App\Http\Controllers\Api\ExpenseCategoriesController;
use App\Http\Controllers\Api\ExpensesController;
use App\Http\Controllers\Api\PlatformsController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SystemUsersController;
use App\Http\Controllers\Api\UsageRightsController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/settings/bootstrap', [SettingsController::class, 'bootstrap']);
    Route::put('/settings', [SettingsController::class, 'update']);

    Route::get('/system-users', [SystemUsersController::class, 'index']);
    Route::post('/system-users', [SystemUsersController::class, 'store']);
    Route::patch('/system-users/{user}', [SystemUsersController::class, 'update']);
    Route::delete('/system-users/{user}', [SystemUsersController::class, 'destroy']);

    Route::post('/platforms', [PlatformsController::class, 'store']);
    Route::patch('/platforms', [PlatformsController::class, 'update']);
    Route::delete('/platforms', [PlatformsController::class, 'destroy']);

    Route::post('/accounts', [AccountsController::class, 'store']);
    Route::patch('/accounts', [AccountsController::class, 'update']);
    Route::delete('/accounts', [AccountsController::class, 'destroy']);

    Route::post('/expense-categories', [ExpenseCategoriesController::class, 'store']);
    Route::patch('/expense-categories', [ExpenseCategoriesController::class, 'update']);
    Route::delete('/expense-categories', [ExpenseCategoriesController::class, 'destroy']);

    Route::post('/usage-rights', [UsageRightsController::class, 'store']);
    Route::patch('/usage-rights', [UsageRightsController::class, 'update']);
    Route::delete('/usage-rights', [UsageRightsController::class, 'destroy']);

    Route::get('/monthly-totals', [\App\Http\Controllers\Api\MonthlyTotalsController::class, 'index']);
    Route::post('/monthly-totals', [\App\Http\Controllers\Api\MonthlyTotalsController::class, 'store']);
    Route::delete('/monthly-totals', [\App\Http\Controllers\Api\MonthlyTotalsController::class, 'destroy']);

    Route::get('/daily-entries', [\App\Http\Controllers\Api\DailyEntriesController::class, 'index']);
    Route::post('/daily-entries', [\App\Http\Controllers\Api\DailyEntriesController::class, 'store']);
    Route::delete('/daily-entries', [\App\Http\Controllers\Api\DailyEntriesController::class, 'destroy']);

    Route::get('/expenses', [ExpensesController::class, 'index']);
    Route::post('/expenses', [ExpensesController::class, 'store']);
    Route::delete('/expenses', [ExpensesController::class, 'destroy']);
    Route::get('/expenses/{clientId}/receipt', [ExpensesController::class, 'receipt']);

    Route::get('/external-income', [\App\Http\Controllers\Api\ExternalIncomeController::class, 'index']);
    Route::post('/external-income', [\App\Http\Controllers\Api\ExternalIncomeController::class, 'store']);
    Route::delete('/external-income', [\App\Http\Controllers\Api\ExternalIncomeController::class, 'destroy']);

    Route::get('/brand-deals', [\App\Http\Controllers\Api\BrandDealsController::class, 'index']);
    Route::post('/brand-deals', [\App\Http\Controllers\Api\BrandDealsController::class, 'store']);
    Route::delete('/brand-deals', [\App\Http\Controllers\Api\BrandDealsController::class, 'destroy']);

    Route::get('/closeouts', [CloseoutsController::class, 'index']);
    Route::post('/closeouts', [CloseoutsController::class, 'store']);
    Route::delete('/closeouts', [CloseoutsController::class, 'destroy']);
});
