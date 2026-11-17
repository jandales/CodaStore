<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Route middleware: permission:products, permission:orders, ... (administrators pass every check). */
class AdminPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $admin = auth('admin')->user();

        if (!$admin || !$admin->hasPermission($permission)) {
            if ($request->expectsJson()) {
                abort(403, "You don't have access to this area.");
            }

            return response()->view('admin.forbidden', ['permission' => $permission], 403);
        }

        return $next($request);
    }
}
