<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Vendor;

use Helper;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();
                if($user->isAdmin() || $user->isFirstMan() || $user->isMiddleMan() || $user->isSupervisor() || $user->isCashier() || $user->isFinancer() || $user->isSales() || $user->isCSR() || $user->isBD() || $user->isBDM() || $user->isDataAnalyst()){
                    return redirect(RouteServiceProvider::ADMINHOME);
                }
                else
                {
                    return redirect(RouteServiceProvider::HOME);
                }
            }
        }

        return $next($request);
    }
}
