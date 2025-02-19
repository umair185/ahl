<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserDevice;

class OtpCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $deviceIdentifier = $this->getDeviceIdentifier($request);

        // Check if the device is registered and has a valid OTP
        $device = UserDevice::where('user_id', $user->id)
            ->where('device_identifier', $deviceIdentifier)
            ->first();

        if ($device && $device->otp_expires_at > now()) {
            // Device is valid and OTP is still active
            return $next($request);
        }

        // Redirect to OTP input page if not valid
        return redirect()->route('checkOTP');
    }

    // Generate a unique identifier for the device
    protected function getDeviceIdentifier($request)
    {
        return md5($request->userAgent() . $request->ip());
    }

}
