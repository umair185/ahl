<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\AssignRequest;
use App\Models\PickupRequest;

use App\Helpers\ResponseHelper;


class AssignRequestController extends Controller
{
    public function complete(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'pickup_request_id' => 'required',
        ]);

	    if($validator->fails()){
	    	$error = $validator->errors();
	    	return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'assign_request',(object) []);
	    }

	    $user = Auth::user();
	    $userId = $user->id;
	    $pickupRequestId = $request->pickup_request_id;
	    $totalPickedParcels = $request->total_picked_parcel;

	    //1 pending, 2 assign request , 3 complete request status
	    $pickupRequest = PickupRequest::where(['id'=>$pickupRequestId])->update(['status'=> 3]);

	    //1 assign request pending, 2 complete request
	    $assignPickupRequest = AssignRequest::where(['pickup_request_id'=>$pickupRequestId,'picker_id'=>$userId])->update(['total_picked_parcel'=>$totalPickedParcels,'status' => 2]);

	    //dd($assignPickupRequest);
	    
	    return ResponseHelper::apiResponse(1,'Assign Request Complete!',[],'assign_request',[]);
    }
}
