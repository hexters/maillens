<?php

namespace Hexters\MailLens\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MailLensGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $password = config('maillens.password');

        // No password set: the inbox is open. This is the local-dev default.
        if (blank($password)) {
            return $next($request);
        }

        // Already unlocked this session.
        if ($request->session()->get('maillens_unlocked') === true) {
            return $next($request);
        }

        // Password submitted from the lock screen.
        if ($request->isMethod('post') && $request->routeIs('maillens.unlock')) {
            if (hash_equals((string) $password, (string) $request->input('password'))) {
                $request->session()->put('maillens_unlocked', true);

                return redirect()->route('maillens.index');
            }

            return response(view('maillens::lock', ['error' => true]), 401);
        }

        return response(view('maillens::lock', ['error' => false]), 401);
    }
}
