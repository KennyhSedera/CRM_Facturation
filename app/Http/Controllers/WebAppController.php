<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class WebAppController extends Controller
{
    public function form_company(Request $request)
    {
        return Inertia::render('Telegram/CompanyFormTelegram', [
            'telegram_id' => $request->get('user_id'),
        ]);
    }

    public function form_client(Request $request)
    {
        return Inertia::render('Telegram/ClientFormTelegram', [
            'telegram_id' => $request->get('user_id'),
        ]);
    }

    public function form_article(Request $request)
    {
        return Inertia::render('Telegram/ArticleFormTelegram', [
            'telegram_id' => $request->get('user_id'),
        ]);
    }
}
