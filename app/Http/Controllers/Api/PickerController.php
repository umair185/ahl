<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\PickerAssign;
use App\Models\PickupRequest;
use App\Models\AssignRequest;
use App\Models\ScanOrder;
use App\Models\StaffFinancial;

use App\Helpers\ResponseHelper;

use Helper;

class PickerController extends Controller
{
    public function vendorList(Request $request)
    {
    	$authUserId = Auth::user()->id;
    	$pickerVendorList = PickerAssign::with([
            'vendor' => function($query){
                $query->select('id','vendor_name','focal_person_phone');
            }
        ])
        ->where('picker_id',$authUserId)
    	->get();

        
    	return ResponseHelper::apiResponse(1,'Picker Vendor List!',[],'picker_vendor_list',$pickerVendorList);
    }

    public function assignRequest(Request $request)
    {
    	$authUserId = Auth::user()->id;
    	//$vendorId = $request->vendor_id; 
    	//$vendorId = 3; 
    	$pickerAssignRequest = AssignRequest::with([
            /*'pickerRequest' => function($query){
            	$query->select('id','vendor_id','vendor_time_id','warehouse_location_id','pickup_date','estimated_parcel')
            	->with([
            		'pickupTiming',
            	]);
            }*/
            /*'pickerRequest.requestTiming.vendorTiming' => function($query){
            	$query->select(['id','timings']);
            },*/
            'pickerRequest' => function($query){
                $query->select('id','vendor_id','vendor_time_id','vendor_time_id','warehouse_location_id','pickup_date','estimated_parcel','status')->with([
                    'requestTiming' => function($query){
                        $query->select('id','timing_slot_id')->with([
                            'vendorTiming' => function($query){
                                $query->select('id','timings');
                            }
                        ]);
                    }
                ]);
            },
            'pickerRequest.pickupLocation' => function($query){
            	$query->select(['id','address']);
            },
            'pickerRequest.vendorName'=> function($query){
            	$query->select(['id','vendor_name']);
            },

        ])
        ->where('picker_id',$authUserId)
        ->where('status',1)
        ->get();

        return ResponseHelper::apiResponse(1,'Picker Request List!',[],'picker_request_list',$pickerAssignRequest);
    }

    public function pickerCompleteRequestList()
    {
        $authUser = Auth::user();
        $authUserId = $authUser->id;

        /*$pickerCompleteRequest = AssignRequest::select('id','pickup_request_id','picker_id','total_picked_parcel')
        ->with([
            'pickerRequest' => function($query){
                $query->select('id','vendor_id','status')->with([
                    'vendorName' => function($query){
                        $query->select('id','vendor_name','status');
                    }
                ])
                ->where(['status' => 2]);
            }
        ])
        ->where(['picker_id'=>$authUserId])
        ->get();

        $pickerRequestIds = AssignRequest::select('id','pickup_request_id','picker_id','total_picked_parcel')
        ->where(['picker_id' => $authUserId])
        ->get();*/

        $pickerCompleteRequest = DB::table('assign_requests')

            ->rightJoin('pickup_requests', 'assign_requests.pickup_request_id', '=', 'pickup_requests.id')
            ->rightJoin('vendors', 'pickup_requests.vendor_id', '=', 'vendors.id')
            

            ->where('assign_requests.picker_id',$authUserId)
            ->where('pickup_requests.status','=',3)//complete
            ->where('assign_requests.status','=',2)//complete

            ->select(
                'assign_requests.id as assign_request_id',
                'pickup_requests.vendor_id as pickup_request_vendor_id',
                'pickup_request_id',
                'vendors.id as vendor_id',
                'picker_id',
                'pickup_requests.status',
                'vendors.vendor_name',
                'total_picked_parcel',
                'assign_requests.status'
            )
            //->select('users.*', 'contacts.phone', 'orders.price')
            ->get();


        return ResponseHelper::apiResponse(1,'Picker Complete Request List!',[],'picker_complete_request_list',$pickerCompleteRequest);

    }

    public function pickerRequestScanParcelList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_request_id' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_parcel',(object) []);
        }

        $authUser = Auth::user();
        $authPickerId = $authUser->id;//picker id
        $pickupRequestId = $request->pickup_request_id;//picker id
        
        $scanOrder = ScanOrder::select('id','pickup_request_id','picker_id','order_id','created_at','updated_at')
        ->with([
            'orderDetail' =>function($query){
                $query->select('id','order_reference');
            }
        ])
        ->where(['pickup_request_id'=>$pickupRequestId,'picker_id'=>$authPickerId])
        ->get();
        return ResponseHelper::apiResponse(1,'Complete Scan Parcel List!',[],'complete_scan_parcel_list',$scanOrder);
    }

    public function financial(Request $request)
    {
        $authUser = Auth::user();
        $pickerFinancial = Helper::staffCommission($authUser);

        return ResponseHelper::apiResponse(1,'Picker Financial!',[],'picker_financial',$pickerFinancial);
    }

    public function financialReport(Request $request)
    {
        $authUserId = Auth::user()->id;

        $financialReport = StaffFinancial::where('staff_id',$authUserId)
        ->with([
            'staffName' => function($query){
                $query->select('id','name');
            },
            'cashierName' => function($query){
                $query->select('id','name');
            },
        ])
        ->orderBY('id','desc')
        ->get();
        
        return ResponseHelper::apiResponse(1,'Picker Financial Report!',[],'financial_report',$financialReport);
    }
}
