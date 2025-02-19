<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PickupRequest;
use App\Models\AssignRequest;
use App\Models\PickerAssign;
use App\Models\User;
use App\Models\UserCity;
use App\Helpers\NotificationHelper;
use DB;

use Auth;

class AssignRequestController extends Controller {

    public function assignRequest($id) {
        
        $breadcrumbs = [
            'name' => 'Assign Request To Picker', 
        ];

        if(Auth::user()->hasAnyRole('admin')){

            $pickup = PickupRequest::find($id);
            $pickupId = $pickup->id;
            $vendor_id = $pickup->vendor_id;
            $picker_riders = PickerAssign::where('vendor_id', $vendor_id)->pluck('picker_id');

            $riderId = User::whereIn('id', $picker_riders)->where('status',1)->get();
        }
        elseif(Auth::user()->hasAnyRole('first_man'))
        {
            $pickup = PickupRequest::find($id);
            $pickupId = $pickup->id;
            $vendor_id = $pickup->vendor_id;
            $picker_riders = PickerAssign::where('vendor_id', $vendor_id)->pluck('picker_id');

            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

            $riderId = User::whereHas(
                'usercity', function($query) use($usercity){
                    $query->whereIn('city_id',$usercity);
                }
            )->whereIn('id', $picker_riders)->where('status',1)->get();

            //  dd($riderId);
        }

        return view('admin.pickup_request.assign_picker', compact('breadcrumbs','pickup', 'pickupId', 'riderId'));
    }

    public function saveAssignPickerRequest(Request $request) {
        $validatedData = $request->validate([
            //company detail
            'pickup_request_id' => 'required',
            'rider_id' => 'required',
        ]);

        $pickerRiderId = $request->rider_id;
        
        //dd($pickerRiderId);
        $picker = [
            'pickup_request_id' => $request->pickup_request_id,
            'picker_id' => $pickerRiderId,
            'status' => 1,
        ];
        $assignRequest = AssignRequest::create($picker);

        if($assignRequest){
            $userPicker = User::find($pickerRiderId);
            $pickup_request = PickupRequest::find($request->pickup_request_id);
            $pickup_request->status = 2;
            $pickup_request->save();
            //dd($userPicker);
            if($userPicker->device_token){
                $notificationData = [
                    'token' =>  $userPicker->device_token,   
                    'to_user_id' =>  $pickerRiderId,   
                    'user_type' =>  'picker',   
                    'body' =>  [
                        'name' => $userPicker->name,
                        'message' => $pickup_request->vendorName->vendor_name.' New Pickup Request',
                    ],   
                ];
                
                NotificationHelper::sendMultiFCMNotification($notificationData);
            }
        }
        
        return redirect()->route('vendorPickupRequestList');
    }

    public function assignedRequestList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Picker Assign Requests', 
        ];
        if(Auth::user()->hasAnyRole('admin'))
        {
            $assignRequest = AssignRequest::where('status',1)
            ->with([
                'pickerRequest' => function($query){
                    $query->select('id','vendor_id','status','pickup_date','estimated_parcel')->with([
                        'vendorName' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'pickupLocation' => function($query){
                            $query->select('id','address');
                        }
                    ]);
                },
                'pickerName' => function($query){
                    $query->select('id','name');
                }
            ])->orderBy('created_at','DESC')
            ->get();
        }
        elseif(Auth::user()->hasAnyRole('first_man'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');
        
            $assignRequest = AssignRequest::whereHas('pickerName.usercity' ,function($query) use($usercity){
                $query->whereIn('city_id',$usercity);
            })
            ->where('status',1)
            ->with([
                'pickerRequest' => function($query){
                    $query->select('id','vendor_id','status','pickup_date','estimated_parcel')->with([
                        'vendorName' => function($query){
                            $query->select('id','vendor_name');
                    },
                    'pickupLocation' => function($query){
                        $query->select('id','address');
                    }
                    ]);
                },
                'pickerName' => function($query){
                    $query->select('id','name');
                },
            ])->orderBy('created_at','DESC')
            ->get();
        }
        // dd($assignRequest);
        

        return view('admin.pickup_request.assign_request_list',compact('breadcrumbs','assignRequest'));
    }

    public function forceRequestComplete(Request $request)
    {
        $assignId = $request->assign_id;
        // dd($assignId);
        $pickupRequestId = $request->pickup_request_id;
        // dd($pickupRequestId);

        
        $assignRequest = AssignRequest::find($assignId)->update(['status'=>2]);
        $pickupRequest = PickupRequest::find($pickupRequestId)->update(['status'=>3]);
            // return false;

        return back()->with(['success'=>'Request Force Complete Successfully' ]);
    }

    public function BulkorceRequestComplete(Request $request)
    {
        // dd($request->pickupRequests);
        $pickupRequests = $request->pickupRequests;

        foreach($pickupRequests as $pickupRequest)
        {
            $assignRequest = AssignRequest::find($pickupRequest);
            $assignRequest->update(['status'=>2]);

            $pickupRequest_value = PickupRequest::find($assignRequest->pickup_request_id)->update(['status'=>3]);
        }

        return response()->json([
            'status' => 1, 
        ]);
    }

}
