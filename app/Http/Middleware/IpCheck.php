<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class IpCheck
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
        
        $ip = $request->ip();
       
        //   if($ip == '192.168.242.238' || $ip =='192.168.1.38' )
        //     {
                return $next($request);
            // }
            // else
            // {
            //     dd("You are not Allowed to use this website");
            // }
    }
}
