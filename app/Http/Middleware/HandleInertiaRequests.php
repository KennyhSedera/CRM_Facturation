<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified_at' => $request->user()->email_verified_at,
                    'user_role' => $request->user()->user_role,
                    'company_id' => $request->user()->company_id,
                    'company' => $request->user()->company ? [
                        'company_id' => $request->user()->company->company_id,
                        'company_name' => $request->user()->company->company_name,
                        'company_email' => $request->user()->company->company_email,
                        'company_logo' => $request->user()->company->company_logo,
                        'company_logo_url' => $request->user()->company->company_logo_url ?? null,
                        'company_phone' => $request->user()->company->company_phone,
                        'company_website' => $request->user()->company->company_website,
                        'company_address' => $request->user()->company->company_address,
                        'company_city' => $request->user()->company->company_city,
                        'company_postal_code' => $request->user()->company->company_postal_code,
                        'company_country' => $request->user()->company->company_country,
                        'company_currency' => $request->user()->company->company_currency,
                        'company_timezone' => $request->user()->company->company_timezone,
                        'plan_status' => $request->user()->company->plan_status,
                        'plan_start_date' => $request->user()->company->plan_start_date,
                        'plan_end_date' => $request->user()->company->plan_end_date,
                        'is_active' => $request->user()->company->is_active,
                    ] : null,
                ] : null,
            ],
            'ziggy' => fn(): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
                'info' => fn() => $request->session()->get('info'),
            ],
        ];
    }
}
