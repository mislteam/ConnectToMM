<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return $next($request);
        }

        $overrides = config('auto_permission.overrides', []);

        if (isset($overrides[$routeName])) {
            return $this->checkPermission($overrides[$routeName], $request, $next);
        }

        $parts = explode('.', $routeName);

        if (count($parts) < 2) {
            return $next($request);
        }

        $routeAction = array_pop($parts);
        $module = implode('.', $parts);
        $actionMap = [
            'index' => 'menu',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'delete' => 'delete',
        ];

        if (!array_key_exists($routeAction, $actionMap)) {
            return $next($request);
        }

        $permission = "{$module}.{$actionMap[$routeAction]}";

        if (!auth()->check()) {
            abort(403);
        }

        abort_unless(auth()->user()->can($permission), 403);

        return $next($request);
    }

    private function checkPermission(string $permission, Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403);
        }
        abort_unless(auth()->user()->can($permission), 403);
        return $next($request);
    }
}
