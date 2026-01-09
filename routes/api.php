<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MvtArticlesController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuoteItemController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramController::class, 'handle']);

// routes companies
Route::apiResource('companies', CompanyController::class);
Route::prefix('companies')->group(function () {
    Route::post('/{id}/toggle-status', [CompanyController::class, 'toggleStatus']);
    Route::get('/{id}/users', [CompanyController::class, 'getUsers']);
});

// Route::middleware('auth:sanctum')->group(function () {
// routes clients
Route::apiResource('clients', ClientController::class);
Route::post('clients/{id}/toggle-status', [ClientController::class, 'toggleStatus']);
Route::get('clients/{id}/statistics', [ClientController::class, 'statistics']);

// routes articles
Route::apiResource('articles', ArticleController::class);

// routes mvt article
Route::apiResource('mvt_articles', MvtArticlesController::class);

// routes quote
Route::apiResource('quotes', QuoteController::class);

// routes quote item
Route::apiResource('quote_items', QuoteItemController::class);
// });

Route::get('/pdf', [PdfController::class, 'generate']);
