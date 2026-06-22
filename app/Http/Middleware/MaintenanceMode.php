<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceMode
{
    protected $except = [
        'admin/login',
        'admin',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        $isUnderMaintenance = Setting::where('key', 'system_dev-site')->value('value') === '1';

        if (! $isUnderMaintenance) {
            return $next($request);
        }

        // Разрешаем доступ к определённым путям
        foreach ($this->except as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        // Разрешаем доступ авторизованным
        if (Auth::check()) {
            return $next($request);
        }

        return response()->view('on-dev');
    }
}