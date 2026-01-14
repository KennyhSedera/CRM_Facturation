<?php

use App\Http\Controllers\WebAppController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ============================================
// ROUTES PUBLIQUES
// ============================================

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('/company', function () {
    return Inertia::render('company/formulaire-company-page');
})->name('company.form');

Route::get('/webapp/form/company', [WebAppController::class, 'form_company'])->name('webapp.form.company');
Route::get('/webapp/form/client', [WebAppController::class, 'form_client'])->name('webapp.form.client');
Route::get('/webapp/form/article', [WebAppController::class, 'form_article'])->name('webapp.form.article');

// ============================================
// ROUTES AUTHENTIFIÉES (tous les utilisateurs)
// ============================================

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::prefix('articles')->name('article.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('articles/article-page');
        })->name('page');
    });

    Route::get('/catalogues', function () {
        return Inertia::render('articles/catalogue-page');
    })->name('catalogue.page');

    Route::get('/clients', function () {
        return Inertia::render('client/client-page');
    })->name('client.page');

    Route::get('/quotes', function () {
        return Inertia::render('quotes/quote-page');
    })->name('quote.page');
});

// ============================================
// ROUTES ADMIN DE COMPAGNIE (admin_company OU super_admin)
// ============================================

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/company/profile', function () {
        return Inertia::render('company/company-profile');
    })->name('company.profile');

    Route::get('/mouvements', function () {
        return Inertia::render('articles/mvt-article-page');
    })->name('article.mvt.page');

    Route::get('/users', function () {
        return Inertia::render('users/user-page');
    })->name('users.page');
});

// ============================================
// ROUTES SUPER ADMIN UNIQUEMENT
// ============================================

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/companies', function () {
        return Inertia::render('company/company-page');
    })->name('company.page');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
