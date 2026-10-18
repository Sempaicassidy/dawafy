<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone', 'Africa/Dar_es_Salaam'));

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $sysSettings = [];
            if (\Illuminate\Support\Facades\Auth::check()) {
                $tenantId = session('masquerade_tenant_id') ?? \Illuminate\Support\Facades\Auth::user()?->tenant_id;
                if ($tenantId) {
                    $tenant = \App\Models\Tenant::find($tenantId);
                    if ($tenant) {
                        $sysSettings = $tenant->toArray();
                        $sysSettings['company_name'] = $tenant->name ?? 'Dawafy Pharmacy';
                        $sysSettings['logo_url'] = !empty($tenant->logo_path) ? asset($tenant->logo_path) : null;
                    }
                }
            }
            $view->with('SYS_SETTINGS', $sysSettings);
            $GLOBALS['SYS_SETTINGS'] = $sysSettings;
        });
    }
}
