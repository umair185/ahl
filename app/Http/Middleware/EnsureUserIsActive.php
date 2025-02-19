<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::user()->password_status == 0)
        {
            if (Auth::user()->status !== 1) {
                if ($request->wantsJson()) {
                    // return JSON-formatted response
                    $user = Auth::user();
                    $user->device_token = NULL;
                    $user->save();

                    $token = $request->user()->token();
                    $token->revoke();
                    
                    //return ResponseHelper::apiResponse(1,'You have been successfully logged out!',[],'user',[]);
                } else {
                    // return HTML response
                    Auth::logout();
                    return redirect('/login');
                }

            }
        }
        else
        {
            if ($request->wantsJson()) {
                // return JSON-formatted response
                $user = Auth::user();
                $user->device_token = NULL;
                $user->password_status = 0;
                $user->save();

                $token = $request->user()->token();
                $token->revoke();
                
                //return ResponseHelper::apiResponse(1,'You have been successfully logged out!',[],'user',[]);
            } else {
                // return HTML response
                $user = Auth::user();
                $user->password_status = 0;
                $user->save();

                Auth::logout();
                return redirect('/login');
            }
        }

        return $next($request);
    }
}
