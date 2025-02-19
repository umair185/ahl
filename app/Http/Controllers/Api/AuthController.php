<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Order;
use App\Models\Template;
use App\Helpers\Helper;
use App\Helpers\ResponseHelper;
use App\Models\AppVersion;

class AuthController extends Controller
{
    public function staffLogin(Request $request)
	{
	    
	    //https://github.com/laravel/passport/issues/1381
	    $validator = Validator::make($request->all(), [
            'email' => 'email|required',
	        'password' => 'required',
	        'app' => 'required',
        ]);

	    if($validator->fails()){
	    	$error = $validator->errors();
	    	return ResponseHelper::apiResponse(0,'The provided password does not match our records.',$error,'user',(object) []);
	    }

		$staff = User::where(['email'=>$request->email,'status'=>1])->first();
		$requestApp = $request->app;
	    
		if(!empty($staff)){
			if($staff->isPicker() && $requestApp == 'picker'){
				$allowLogin = true;
			}elseif($staff->isRider() && $requestApp == 'rider'){
				$allowLogin = true;
			}else{
				return ResponseHelper::apiResponse(0,'You Are Not Authroized For This App.',[],'user',(object) []);
			}

        	if($allowLogin){

			    // do the passwords match?
			    if (!Hash::check($request->password, $staff->password)) {
			        /*return response()->json([
			        	'code' => 401,
			        	'message'=> 'The provided password does not match our records.',
			        	'error' => $error
			        ], 401);*/

			        //422
			        return ResponseHelper::apiResponse(0,'The provided password does not match our records.',[],'user',(object) []);
			    }

			    if ($request->device_token) {
					$staff->device_token = $request->device_token;
					$staff->save();
				}
    
	        	//this line genrate a error if you are added Passport::hashClientSecrets(); in  your authService provider
		        $accessToken = $staff->createToken('staff_access_token')->accessToken;
		        return response()->json([
		        	'status' => 1,
		        	'message'=> 'Login Successfully!',
		        	'error'=> (object) array(),
		        	'access_token' => $accessToken,
		        	'user'=> $staff
		        ]);
        	}
	    }

	    return ResponseHelper::apiResponse(0,'The provided credentials does not match our records.',[],'user',(object) []);
	}

	public function userProfile()
	{
		$userProfile = Auth::user();
		return ResponseHelper::apiResponse(1,'User Profile!',[],'user_profile',$userProfile);
	}

	public function logout(Request $request) {
	    $user = Auth::user();
	    $user->device_token = NULL;
	    $user->save();

	    $token = $request->user()->token();
	    $token->revoke();
	    
	    return ResponseHelper::apiResponse(1,'You have been successfully logged out!',[],'user',[]);
	}

	public function fakeChangeStatus(Request $request)
	{
		$orderId = $request->order_id;
		$status = $request->status;
		Order::find($orderId)->update(['order_status'=>$status]);

		return ResponseHelper::apiResponse(1,'Fake Status Changes!',[],'user',[]);
	}

	public function shopifyVendorAccessToken(Request $request)
	{
		$validator = Validator::make($request->all(), [
            'email' => 'email|required',
	        'password' => 'required',
        ]);

        if($validator->fails()){
	    	$error = $validator->errors();
	    	return ResponseHelper::apiResponse(0,'The provided password does not match our records.',$error,'user',(object) []);
	    }

// 		$staff = User::where(['email'=>$request->email,'status'=>1])->first();
        $staff = User::where(['email'=>$request->email,'status'=>1])
                        ->with([
                            'vendorDetail' => function($query){
                                $query->select('id','vendor_name')
                                        ->with([
                                            'pickupLocation' => function($query){
                                                $query->select('id','vendor_id','address');
                                            },
                                            'vendorWeights' => function($query){
                                                $query->select('id','vendor_id','ahl_weight_id','min_weight','max_weight','city_id')->with([
                                                    'ahlWeight' => function($query){
                                                        $query->select('id','weight');
                                                    }
                                                ]);
                                            }
                                        ]);
                            }
                        ])->first();

		if(!empty($staff)){

		    // do the passwords match?
		    if (!Hash::check($request->password, $staff->password)) {
		        return ResponseHelper::apiResponse(0,'The provided credentials does not match our records.',[],'user',(object) []);
		    }

		    if ($request->device_token) {
				$staff->device_token = $request->device_token;
				$staff->save();
			}

        	//this line genrate a error if you are added Passport::hashClientSecrets(); in  your authService provider
	        $accessToken = $staff->createToken('staff_access_token')->accessToken;
	        return response()->json([
	        	'status' => 1,
	        	'message'=> 'Login Successfully!',
	        	'error'=> (object) array(),
	        	'access_token' => $accessToken,
	        	'user'=> $staff
	        ]);
	    }

	    return ResponseHelper::apiResponse(0,'The provided credentials does not match our records.',[],'user',(object) []);
	}
	
	public function testMessage(Request $request)
	{
	    $parcel = Order::where('id', 67569)->first();
	            $newOrder = $parcel->id;
                $orderReference = explode("#", $request->order_parcel_reference_no);
                $number = $parcel->consignee_phone;
                $orderAmount = $parcel->consignment_cod_price;
                $rider_phone = '03424983850';
                $name = $parcel->consignee_first_name. ' '. $parcel->consignee_last_name;

                $message = Template::find(4);
                $body = str_replace('{{MEMBER_NAME}}', $name, $message->message);
                $body = str_replace('{{COMPANY_NAME}}', "AHL", $body);
                $body = str_replace('{{APP_URL}}',  "https://tracking.ahlogistic.pk/tracking/" . $newOrder, $body);
                $body = str_replace('{{ORDER_NUMBER}}', $orderReference[1], $body);
                $body = str_replace('{{ORDER_AMOUNT}}', $orderAmount, $body);
                $body = str_replace('{{RIDER_PHONE}}', $rider_phone, $body);
                $body = str_replace('<p>', '%20', $body);
                $body = str_replace('</p>', '%20', $body);
                $body = str_replace(' ', '%20', $body);
                $message_data = [
                    'number' => $number,
                    'message' => $body
                ];

                Helper::sendMessage($message_data);
	}

	public function getVersion()
    {
        $get = AppVersion::where('id',1)->first();

        $response = [
            'status' => 1,
            'message' => 'App Version fetched successfully!',
            'rider' => $get->rider,
            'picker' => $get->picker,
            'forceful_status' => $get->forceful_status,
        ];

        return response()->json($response);
    }
}
