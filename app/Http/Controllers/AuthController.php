<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Vendor;
use App\Models\OTPVerify;
use App\Models\Template;
use App\Models\UserDevice;

use Helper;
use Carbon\Carbon;

class AuthController extends Controller {

    protected function getDeviceIdentifier(Request $request)
    {
        return md5($request->userAgent() . $request->ip());
    }

    public function login() {
        return view('login');
    }

    public function authLogin(Request $request) {
        $validatedData = $request->validate([
            //Vendor
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::where(['email'=>$request->email,'status'=>1])->first();
        if (!empty($user)) {
            // do the passwords match?
            if (!Hash::check($request->password, $user->password)) {
                return back()->withErrors([
                    'password' => ['The provided password does not match our records.']
                ]);
            }
            //https://morioh.com/p/6ac297d15e53
            $credentials = $request->only('email', 'password');

            //for local use only
            if (Auth::attempt($credentials)) {

                $deviceIdentifier = $this->getDeviceIdentifier($request);

                $device = UserDevice::where('user_id', $user->id)
                    ->where('device_identifier', $deviceIdentifier)
                    ->first();

                if ($device && $device->otp_expires_at > now()) {
                    $baseUrl = $_SERVER['SERVER_NAME'];
                    if($baseUrl == 'admin.ahlogistic.pk')
                    {
                        if($user->isAdmin() || $user->isFirstMan() || $user->isMiddleMan() || $user->isSupervisor() || $user->isCashier() || $user->isFinancer() || $user->isSales() || $user->isCSR() || $user->isBD() || $user->isBDM() || $user->isHR() || $user->isHubManager()|| $user->isDataAnalyst()){
                            return redirect('/admin-dashboard');
                        }else{
                            return back()->withErrors([
                                'otp' => ['You Are Not Authroized For This Domain.']]);
                        }

                    }

                    if($baseUrl == 'vendor.ahlogistic.pk' || $baseUrl == 'ahlvendor.ahlogistic.pk'){
                        if($user->isVendorAdmin() || $user->isVendorEditor()){
                            return redirect('/dashboard');
                        }else{
                            return back()->withErrors([
                                'otp' => ['You Are Not Authroized For This Domain.']]);
                        }

                    }

                    //for local use only
                    if($user->isVendorAdmin() || $user->isVendorEditor())
                    {
                        return redirect('/dashboard');
                    }else{
                        return redirect('/admin-dashboard');
                    }
                }
                else
                {
                    $user_data = Auth::user();
                    $name = $user_data->name;
                    $code = rand(00000000,99999999);
                    $number = $user_data->phone_number ? $user_data->phone_number : '03004407411';

                    $last_four = substr ($number, -3);

                    $checkOTP = OTPVerify::where('user_id', $user_data->id)->where('status', 1)->get();
                    if(count($checkOTP) > 0)
                    {
                        foreach($checkOTP as $otp)
                        {
                            $otp->update(['status'=>0]);
                        }
                    }

                    $otp_data = [
                        'user_id' => $user_data->id,
                        'code' => $code,
                        'status' => 1,
                    ];

                    $create_otp = OTPVerify::create($otp_data);
                    if($create_otp)
                    {
                        $user_data->update(['otp_status' => 1]);

                        $message = Template::find(5);
                        $body = str_replace('{{MEMBER_NAME}}', $name, $message->message);
                        $body = str_replace('{{OTP_CODE}}', $code, $body);
                        $body = str_replace('<p>', '%20', $body);
                        $body = str_replace('</p>', '%20', $body);
                        $body = str_replace(' ', '%20', $body);
                        $message_data = [
                            'number' => $number,
                            'message' => $body
                        ];

                        Helper::sendMessage($message_data);
                    }

                    $notification_message = "OTP has been sent to your Phone Number ********".$last_four;

                    return redirect()->route('checkOTP')->with('message', $notification_message);
                }
            }
        }

        return back()->withErrors([
            'email' => ['The provided credentials does not match our records.']
        ]);
    }

    public function logout() {
        Auth::logout();
        return redirect()->to(env('APP_WEBSITE'));
    }

    public function vendorStatusChange(Request $request)
    {
        $vendorId = $request->id;

        //change vendor status
        $vendor = Vendor::find($vendorId);

        //this will get only vendor becasue vendor alwasy create first
        $vendorUserObject = User::where('vendor_id',$vendorId)->first();

        if($vendor->status == 1){
            //dump('1 now is going to 0 with all vendor editors');
            $changeStatus = 0;
            User::where(['vendor_id'=>$vendorId])->update(['status'=>$changeStatus]);
        }else{
            //dump('0 now is going to 1 with only vendor not editors');
            $changeStatus = 1;
            $vendorUserObject->update(['status'=>$changeStatus]);
        }
        
        //also change vendor status in vendor table
        $vendor->update(['status'=>$changeStatus]);
        
        return back()->with(['success'=> 'Vendor Status Change Successfully!']);
    }

    public function StaffStatusChange(Request $request)
    {
        //$staffId = $request->id;
        $staffId = Helper::decrypt($request->id);

        $user = User::find($staffId);
        if($user->status == 1){
            $changeStatus = 0;
        }else{
            $changeStatus = 1;
        }

        $user->update(['status'=>$changeStatus]);
        
        return back()->with(['success'=> 'Staff Status Change Successfully!']);
    }

    public function vendorCommissionChange(Request $request)
    {
        $vendorId = $request->id;

        $vendor = Vendor::where('id',$vendorId)->first();

        if($vendor->commision == 1){
            $changeStatus = 0;
            $vendor->update(['commision'=>$changeStatus]);
        }else{
            $changeStatus = 1;
            $vendor->update(['commision'=>$changeStatus]);
        }
        
        
        return back()->with(['success'=> 'Vendor Commission Status Change Successfully!']);
    }

    public function checkOTP() {
        return view('check-otp');
    }

    public function verifyOTP(Request $request) {

        $user = Auth::user();
        $code = $request->otp;
        $userId = $user->id;

        $check_otp = OTPVerify::where('user_id',$userId)->where('code',$code)->where('status', 1)->first();
        if(!empty($check_otp))
        {
            $check_otp->update(['status'=>0, 'used_status'=>1]);
            $user->update(['otp_status'=>0]);

            $deviceIdentifier = $this->getDeviceIdentifier($request);

            UserDevice::updateOrCreate(['user_id' => $userId, 'device_identifier' => $deviceIdentifier],
                ['otp_expires_at' => now()->addDays(15)]
            );

            $baseUrl = $_SERVER['SERVER_NAME'];
            if($baseUrl == 'admin.ahlogistic.pk')
            {
                if($user->isAdmin() || $user->isFirstMan() || $user->isMiddleMan() || $user->isSupervisor() || $user->isCashier() || $user->isFinancer() || $user->isSales() || $user->isCSR() || $user->isBD() || $user->isBDM() || $user->isHR() || $user->isHubManager()|| $user->isDataAnalyst()){
                    return redirect('/admin-dashboard');
                }else{
                    return back()->withErrors([
                        'otp' => ['You Are Not Authroized For This Domain.']]);
                }

            }

            if($baseUrl == 'vendor.ahlogistic.pk' || $baseUrl == 'ahlvendor.ahlogistic.pk'){
                if($user->isVendorAdmin() || $user->isVendorEditor()){
                    return redirect('/dashboard');
                }else{
                    return back()->withErrors([
                        'otp' => ['You Are Not Authroized For This Domain.']]);
                }

            }

            //for local use only
            if($user->isVendorAdmin() || $user->isVendorEditor())
            {
                return redirect('/dashboard');
            }else{
                return redirect('/admin-dashboard');
            }
        }
        else
        {
            return redirect()->back()->with('error', 'Incorrect OTP');
        }
    }

    public function resendOTP() {

        $user_data = Auth::user();
        $name = $user_data->name;
        $code = rand(00000000,99999999);
        $number = $user_data->phone_number ? $user_data->phone_number : '03235081556';

        $last_four = substr ($number, -3);

        $checkOTP = OTPVerify::where('user_id', $user_data->id)->where('status', 1)->get();
        if(count($checkOTP) > 0)
        {
            foreach($checkOTP as $otp)
            {
                $otp->update(['status'=>0]);
            }
        }

        $otp_data = [
            'user_id' => $user_data->id,
            'code' => $code,
            'status' => 1,
        ];

        $create_otp = OTPVerify::create($otp_data);
        if($create_otp)
        {
            $user_data->update(['otp_status' => 1]);

            $message = Template::find(5);
            $body = str_replace('{{MEMBER_NAME}}', $name, $message->message);
            $body = str_replace('{{OTP_CODE}}', $code, $body);
            $body = str_replace('<p>', '%20', $body);
            $body = str_replace('</p>', '%20', $body);
            $body = str_replace(' ', '%20', $body);
            $message_data = [
                'number' => $number,
                'message' => $body
            ];

            Helper::sendMessage($message_data);
        }

        $notification_message = "OTP has been sent to your Phone Number ********".$last_four;

        return redirect()->back()->with('message', $notification_message);
    }

}
