<?php

use App\Http\Controllers\StartBotController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

Route::get('/test', [StartBotController::class, 'sendMessage']);

Route::get('/company', function () {
    return Inertia::render('company/formulaire-company-page');
})->name('company.form');

Route::get('/companies', function () {
    return Inertia::render('company/company-page');
})->name('company.page');

Route::get('/company/profile', function () {
    return Inertia::render('company/company-profile');
})->name('company.profile');

Route::get('/articles', function () {
    return Inertia::render('articles/article-page');
})->name('article.page');

Route::get('/catalogues', function () {
    return Inertia::render('articles/catalogue-page');
})->name('catalogue.page');

Route::get('/mvt-article', function () {
    return Inertia::render('articles/mvt-article-page');
})->name('mvt.article.page');

Route::get('/clients', function () {
    return Inertia::render('client/client-page');
})->name('client.page');

Route::get('/quotes', function () {
    return Inertia::render('quotes/quote-page');
})->name('quote.page');

Route::get('/users', function () {
    return Inertia::render('users/user-page');
})->name('users.page');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
