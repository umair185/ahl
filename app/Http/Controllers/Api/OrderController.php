<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Order;
use App\Models\OrderAssigned;
use App\Models\ScanOrder;
use App\Models\PickerAssign;
use App\Helpers\ResponseHelper;
use App\Models\PickupRequest;
use App\Models\VendorWeight;

class OrderController extends Controller
{
    public function changeParcelStatusTroughQR(Request $request)
    {
	    $validator = Validator::make($request->all(), [
            'pickup_request_id' => 'required',
            'order_parcel_id' => 'required',
        ]);

	    if($validator->fails()){
	    	$error = $validator->errors();
	    	return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_parcel',(object) []);
	    }

	    $orderId = $request->order_parcel_id;
	    $order = Order::with([
            'vendorWeight' => function($query) {
            	$query->with(['ahlWeight']);
            }])->where('id',$orderId)->first();

	    $authUser = Auth::user();
	    $authUserRoleName = $authUser->roles[0]->name;
	    $authUserId = $authUser->id;
	    $pickupRequestId = $request->pickup_request_id;
	    
	   	$assignVendor = PickerAssign::where(['picker_id' => $authUserId])->get()->pluck('vendor_id')->toArray();

	   	if($assignVendor && $order){
		   	if (!in_array($order->vendor_id, $assignVendor)){
		   		return ResponseHelper::apiResponse(0,' You Are Not Allowed',[],'order_parcel',(object) []);
		   	}
	   	}
	   	
	   	$pickupRequestVendor = PickupRequest::where('id', $pickupRequestId)->first();
            
        if($order->vendor_id != $pickupRequestVendor->vendor_id)
        {
            return ResponseHelper::apiResponse(0,'Sorry! Wrong Vendor Parcel',[],'order_parcel',(object) []);
        }
        
	    if($order){
		    if($order->order_status == 1){
		    	//picker picked the parcel from vendor
			    $order->order_status = 2;
			    $order->updated_at = now();
			    $order->save();

			    if($authUserRoleName == 'picker'){
			    	$scanOrder = [
				    	'pickup_request_id' => $pickupRequestId,
				    	'order_id' => $orderId,
				    	'picker_id' => $authUserId,
			    	];

			    	ScanOrder::create($scanOrder);
			    }

			    $totalScanOrder = ScanOrder::where(['pickup_request_id'=>$pickupRequestId,'picker_id'=>$authUserId])->count();

			    $response = [
			    	'counter' => $totalScanOrder,
			    	'order' => $order,
			    ];

		    	return ResponseHelper::apiResponse(1,'Order Parcel Picked!',[],'order_parcel',$response);
		    }else{

		    	return ResponseHelper::apiResponse(0,' Parcel Already Picked',[],'order_parcel',(object)[]);
		    }
		}else{
	    	return ResponseHelper::apiResponse(0,' Invalid Parcel Reference Number',[],'order_parcel',(object) []);
	    }
    }

    public function changeParcelStatusTroughReference(Request $request)
    {
	    $validator = Validator::make($request->all(), [
            'pickup_request_id' => 'required',
            'parcel_reference_number' => 'required',
        ]);

	    if($validator->fails()){
	    	$error = $validator->errors();
	    	return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'parcel_status',(object) []);
	    }

	    $authUser = Auth::user();
	    $authUserRoleName = $authUser->roles[0]->name;
	    $authUserId = $authUser->id;

	    $pickupRequestId = $request->pickup_request_id;
	    $orderParcelReferenceNumber = $request->parcel_reference_number;
	    
	    $order = Order::with([
            'vendorWeight' => function($query) {
            	$query->with(['ahlWeight']);
            }])->where('order_reference',$orderParcelReferenceNumber)->first();

	    $assignVendor = PickerAssign::where(['picker_id' => $authUserId])->get()->pluck('vendor_id')->toArray();

	   	if($assignVendor && $order){
		   	if (!in_array($order->vendor_id, $assignVendor)){
		   		return ResponseHelper::apiResponse(0,' You Are Not Allowed',[],'order_parcel',(object) []);
		   	}
	   	}
	   	
	   	$pickupRequestVendor = PickupRequest::where('id', $pickupRequestId)->first();
            
        if($order->vendor_id != $pickupRequestVendor->vendor_id)
        {
            return ResponseHelper::apiResponse(0,'Sorry! Wrong Vendor Parcel',[],'order_parcel',(object) []);
        }
            
	    if($order){
	    	$orderId = $order->id;
	    	if($order->order_status == 1){
	    	//picker picked the parcel from vendor
		    $order->order_status = 2;
		    $order->updated_at = now();
		    $order->save();

		    if($authUserRoleName == 'picker'){
		    	$scanOrder = [
			    	'pickup_request_id' => $pickupRequestId,
			    	'order_id' => $orderId,
			    	'picker_id' => $authUserId,
		    	];

		    	ScanOrder::create($scanOrder);
		    }

		    $totalScanOrder = ScanOrder::where(['pickup_request_id'=>$pickupRequestId,'picker_id'=>$authUserId])->count();

		    $response = [
		    	'counter' => $totalScanOrder,
		    	'order' => $order,
		    ];

	    	return ResponseHelper::apiResponse(1,'Order Parcel Picked!',[],'order_parcel',$response);
		    }else{
		    	return ResponseHelper::apiResponse(0,' Parcel Already Picked',[],'order_parcel',(object) []);
		    }
	    }else{
	    	return ResponseHelper::apiResponse(0,' Invalid Parcel Reference Number',[],'order_parcel',(object) []);
	    }
	    
    }

    public function changeParcelStatusTroughBarcode(Request $request)
    {
	    $validator = Validator::make($request->all(), [
            'pickup_request_id' => 'required',
            'parcel_reference_number' => 'required',
        ]);

	    if($validator->fails()){
	    	$error = $validator->errors();
	    	return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'parcel_status',(object) []);
	    }

	    $authUser = Auth::user();
	    $authUserRoleName = $authUser->roles[0]->name;
	    $authUserId = $authUser->id;

	    $pickupRequestId = $request->pickup_request_id;
	    $orderParcelReferenceNumber = $request->parcel_reference_number;
	    
	    $order = Order::with([
            'vendorWeight' => function($query) {
            	$query->with(['ahlWeight']);
            }])->where('order_reference',$orderParcelReferenceNumber)->first();
            
	    if($order){

	    	$assignVendor = PickerAssign::where(['picker_id' => $authUserId])->get()->pluck('vendor_id')->toArray();

		   	if($assignVendor && $order){
			   	if (!in_array($order->vendor_id, $assignVendor)){
			   		return ResponseHelper::apiResponse(0,' You Are Not Allowed',[],'order_parcel',(object) []);
			   	}
		   	}
		   	
		   	$pickupRequestVendor = PickupRequest::where('id', $pickupRequestId)->first();
	            
	        if($order->vendor_id != $pickupRequestVendor->vendor_id)
	        {
	            return ResponseHelper::apiResponse(0,'Sorry! Wrong Vendor Parcel',[],'order_parcel',(object) []);
	        }
        
	    	$orderId = $order->id;
	    	if($order->order_status == 1){
		    $order->order_status = 2;
		    $order->updated_at = now();
		    $order->save();

		    if($authUserRoleName == 'picker'){
		    	$scanOrder = [
			    	'pickup_request_id' => $pickupRequestId,
			    	'order_id' => $orderId,
			    	'picker_id' => $authUserId,
		    	];

		    	ScanOrder::create($scanOrder);
		    }

		    $totalScanOrder = ScanOrder::where(['pickup_request_id'=>$pickupRequestId,'picker_id'=>$authUserId])->count();

		    $response = [
		    	'counter' => $totalScanOrder,
		    	'order' => $order,
		    ];

	    	return ResponseHelper::apiResponse(1,'Order Parcel Picked!',[],'order_parcel',$response);
		    }else{
		    	return ResponseHelper::apiResponse(0,' Parcel Already Picked',[],'order_parcel',(object) []);
		    }
	    }else{
	    	return ResponseHelper::apiResponse(0,' Invalid Parcel Reference Number',[],'order_parcel',(object) []);
	    }
	    
    }

    public function pickerRequestScanParcelCounter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_request_id' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_parcel',(object) []);
        }
        
        $authUser = Auth::user();
	    $authUserRoleName = $authUser->roles[0]->name;
	    $authUserId = $authUser->id;

	    $pickupRequestId = $request->pickup_request_id;

        $totalScanOrder = ScanOrder::where(['pickup_request_id'=>$pickupRequestId,'picker_id'=>$authUserId])->count();

	    $response = [
	    	'counter' => $totalScanOrder,
	    ];

        return ResponseHelper::apiResponse(1,'Picker Request Scan Parcel Counter!',[],'picker_request_scan_parcel_counter',$response);
    }

    public function changeWeight(Request $request)
    {
    	$orderId = $request->order_parcel_id;
	    $order = Order::where('id',$orderId)->first();
	    $vendor_id = $order->vendor_id;
        $vendor_weights = VendorWeight::where('vendor_id', $vendor_id)->where('city_id', $order->consignee_city)->with('ahlWeight')->get();

        $response = [
        	'status' => 1,
        	'weights' => $vendor_weights,
        	'orderId' => $orderId,
        ];

        return response()->json($response);
    }

    public function saveChangeWeight(Request $request)
    {
    	$orderId = $request->order_id;
    	$weight_price = VendorWeight::where('id',$request->vendor_weight_id)->first();

    	$order = Order::find($orderId);
        $order->vendor_weight_id = $request->vendor_weight_id;
        $order->vendor_weight_price = $weight_price->price;
        $order->save();

        $response = [
        	'status' => 1,
        	'order' => $order,
        	'message' => 'Weight has been changed Successfully',
        ];

        return response()->json($response);
    }

    public function pickerRecord()
    {
    	$week = \Carbon\Carbon::today()->subDays(7);
    	$month = \Carbon\Carbon::today()->subDays(30);

    	$authUser = Auth::user();
	    $authUserRoleName = $authUser->roles[0]->name;
	    $authUserId = $authUser->id;

	    $weekScanOrder = ScanOrder::where('picker_id' , $authUserId)->whereDate('created_at', '>=' , $week)->count();
	    $totalScanOrder = ScanOrder::where('picker_id' , $authUserId)->whereDate('created_at', '>=' , $month)->count();

    	$response = [
        	'status' => 1,
        	'week' => $weekScanOrder,
        	'month' => $totalScanOrder,
        	'message' => 'Picker Record Fetched',
        ];

        return response()->json($response);
    }

    public function riderDeliveryRecord()
    {
    	$today = \Carbon\Carbon::today();
    	$week = \Carbon\Carbon::today()->subDays(7);
    	$month = \Carbon\Carbon::today()->subDays(30);

    	$authUser = Auth::user();
	    $authUserRoleName = $authUser->roles[0]->name;
	    $authUserId = $authUser->id;

	    //Today Ratio
	    $todayOrderAssignedIds = OrderAssigned::whereDate('created_at',$today)->where(['rider_id'=>$authUserId])->get()->pluck('order_id');
	    $today_total_parcels = Order::whereIn('id', $todayOrderAssignedIds)->count();
        $today_confirm_delivered = OrderAssigned::where('rider_id', $authUserId)->whereDate('created_at',$today)->where('trip_status_id', 4)->where('status',1)->count();

        if($today_total_parcels > 0)
		{
            $round_today_wining_ratio = ($today_confirm_delivered/$today_total_parcels)*100;
            $today_wining_ratio = round($round_today_wining_ratio);
        }
        else
        {
        	$today_wining_ratio = 0;
        }

        //Weekly Ratio
	    $weeklyOrderAssignedIds = OrderAssigned::whereDate('created_at', '>=', $week)->where(['rider_id'=>$authUserId])->get()->pluck('order_id');
	    $weekly_total_parcels = Order::whereIn('id', $weeklyOrderAssignedIds)->count();
        $weekly_confirm_delivered = OrderAssigned::where('rider_id', $authUserId)->whereDate('created_at', '>=', $week)->where('trip_status_id', 4)->where('status',1)->count();

        if($weekly_total_parcels > 0)
		{
            $round_weekly_wining_ratio = ($weekly_confirm_delivered/$weekly_total_parcels)*100;
            $weekly_wining_ratio = round($round_weekly_wining_ratio);
        }
        else
        {
        	$weekly_wining_ratio = 0;
        }

        //Monthly Ratio
	    $monthlyOrderAssignedIds = OrderAssigned::whereDate('created_at', '>=', $month)->where(['rider_id'=>$authUserId])->get()->pluck('order_id');
	    $monthly_total_parcels = Order::whereIn('id', $monthlyOrderAssignedIds)->count();
        $monthly_confirm_delivered = OrderAssigned::where('rider_id', $authUserId)->whereDate('created_at', '>=', $month)->where('trip_status_id', 4)->where('status',1)->count();

        if($monthly_total_parcels > 0)
		{
            $round_monthly_wining_ratio = ($monthly_confirm_delivered/$monthly_total_parcels)*100;
            $monthly_wining_ratio = round($round_monthly_wining_ratio);
        }
        else
        {
        	$monthly_wining_ratio = 0;
        }

    	$response = [
        	'status' => 1,
        	'today_ratio' => $today_wining_ratio,
        	'week_ratio' => $weekly_wining_ratio,
        	'month_ratio' => $monthly_wining_ratio,
        	'message' => 'Rider Delivery Ratio Record Fetched',
        ];

        return response()->json($response);
    }
}
