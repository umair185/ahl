<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Order;
use App\Models\ScanOrder;
use App\Models\UserCity;
use App\Models\Template;
use App\Models\VendorWeight;
use App\Helpers\Helper;
use App\Models\User;
use App\Models\OrderAssigned;
use App\Models\City;
use App\Models\ParcelSag;
use App\Models\OrderInSag;
use App\Models\Bilty;
use App\Models\SagInBilty;
use App\Models\Vendor;
use Log;
use PDF;
use Illuminate\Support\Facades\Http;

class BarcodeController extends Controller
{
    public function scanParcelList()
    {
        $breadcrumbs = [
            'name' => 'Scan Parcels', 
        ];

        $vendors = Vendor::where('status', 1)->get();

    	return view('barcode/scan-parcel-list',compact('breadcrumbs','vendors'));
    }

    public function addParcel(Request $request)
    {
    	$validatedData = $request->validate([
            'order_parcel_reference_no' => 'required',
        ]);

        $responseMessage = "";
        $authUser = Auth::user();
        $authMiddleManId = $authUser->id;
        // $userCity = UserCity::where('user_id',$authMiddleManId )->pluck('city_id');

        $orderReferencce = $request->order_parcel_reference_no;
        $parcel = Order::where('order_reference',$orderReferencce)
                ->with([
                    'vendorWeight' => function($query) {
                    $query->with(['ahlWeight','city']);
                    }
                ])->first();

        if($parcel){
            if($parcel->sag_status == 1)
            {
                if($parcel->sagParcel->status == 1)
                {
                    $status = 'SagStatus';
                    $message = 'Please Open your Sag '.$parcel->sagParcel->sag_number.' First';
                    $data = 0;
                    $responseMessage = "";
                }
            }
            else
            {
                if($parcel->order_status == 2){
                
                    $newOrder = $parcel->id;
                    $orderReference = explode("#", $request->order_parcel_reference_no);
                    $number = $parcel->consignee_phone;
                    $name = $parcel->consignee_first_name. ' '. $parcel->consignee_last_name;

                    // $message = Template::find(1);
                    // $body = str_replace('{{MEMBER_NAME}}', $name, $message->message);
                    // $body = str_replace('{{COMPANY_NAME}}', "AHL", $body);
                    // $body = str_replace('{{APP_URL}}',  "https://tracking.ahlogistic.pk/tracking/" . $newOrder, $body);
                    // $body = str_replace('{{ORDER_NUMBER}}', $orderReference[1], $body);
                    // $body = str_replace('<p>', '%20', $body);
                    // $body = str_replace('</p>', '%20', $body);
                    // $body = str_replace(' ', '%20', $body);
                    // $message_data = [
                    //     'number' => $number,
                    //     'message' => $body
                    // ];

                    // Helper::sendMessage($message_data);
                    
                    Order::where('order_reference',$orderReferencce)->update(['order_status' => 3]);

                    $responseMessage = "";
                    $status = 'Success';
                    $message = 'Status Change Successfully';
                    $data = $parcel;

                    $scanOrder = ScanOrder::where('order_id',$parcel->id)->update(['middle_man_id'=>$authMiddleManId,'middle_man_scan_date'=>now()]);

                }elseif($parcel->order_status == 15){

                    Order::where('order_reference',$orderReferencce)->update(['order_status' => 3, 'consignee_city' => $parcel->sagParcel->open_in]);           
                    
                    $status = 'Success';
                    $message = 'Status Change Successfully';
                    $data = $parcel;
                    $responseMessage = "";

                    $scanOrder = ScanOrder::where('order_id',$parcel->id)->update(['middle_man_id'=>$authMiddleManId,'middle_man_scan_date'=>now()]);
                }
                elseif($parcel->order_status < 2){
                    $status = 'Before';
                    $message = 'You can not change status before Picker';
                    $data = 0;
                    $responseMessage = "";
                }elseif($parcel->order_status >= 3){
                    $status = 'After';
                    $message = 'Already Scan';
                    $data = 0;
                    $responseMessage = "";
                }
            }
        }else{
        	$status = 'Invalid';
        	$message = 'Invalid Parcel Reference Number';
        	$data = 0;
        	$responseMessage = "";
        }

        $response = [
        	'status' => $status,
        	'message' => $message,
        	'parcel' => $data,
        	'responseMessage' => $responseMessage
        ];

        return response()->json($response);
    }

    public function enRouteScanParcelList()
    {
        $breadcrumbs = [
            'name' => 'Scan Enroute Parcels', 
        ];

        $getCities = City::all();
        $authUser = Auth::user();
        $userCity = UserCity::where('user_id',$authUser->id )->pluck('city_id');
        $userCities = City::whereIn('id', $userCity)->get();

    	return view('barcode/enroute-scan-parcel-list',compact('breadcrumbs','getCities','userCities'));
    }

    public function  addEnRouteParcel(Request $request){

        $validateData = $request->validate([
            'order_parcel_reference_no' => 'required',
        ]);

        if(empty($request->fromCity))
        {
            $status = 'fromCity';
            $message = 'Please Select From City First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
            ];

            return response()->json($response);
        }

        if(empty($request->toCity))
        {
            $status = 'toCity';
            $message = 'Please Select To City First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
            ];

            return response()->json($response);
        }

        if(empty($request->parcel_sag))
        {
            $status = 'parcelSag';
            $message = 'Please Create a Sag First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
            ];

            return response()->json($response);
        }

        $middleManId = Auth::user()->id;
        $orderReferance = $request->order_parcel_reference_no;

        $parcel = Order::where('order_reference',$orderReferance)
        ->with([
            'vendorWeight' => function($query) {
                $query->with(['ahlWeight','city']);
                }
        ])
        ->first();

        if($parcel){
            if($parcel->order_status == 3 || $parcel->order_status == 9){

                $get_sag = ParcelSag::where('sag_number', $request->parcel_sag)->first();

                Order::where('order_reference',$orderReferance)->update(['previous_order_value' => $parcel->order_status, 'order_status' => 15, 'sag_status' => 1, 'sag_id' => $get_sag->id]);

                $sag_order = [
                    'order_id' => $parcel->id,
                    'sag_id' => $get_sag->id,
                    'status' => 0,
                    'from' => $request->fromCity,
                    'to' => $request->toCity,
                ];

                OrderInSag::create($sag_order);

                $status = 'Success';
	        	$message = 'Status Change Successfully';
	        	$data = $parcel;
            }
            elseif($parcel->order_status == 15){
	        	$status = 'Scanned';
	        	$message = 'Already Scan';
	        	$data = 0;
	        	
	        }
            elseif($parcel->order_status < 3){
	        	$status = 'Before';
	        	$message = 'You can not change status';
	        	$data = 0;
	        }
            elseif($parcel->order_status > 3){
	        	$status = 'After';
	        	$message = 'Invalid Parcel Reference';
	        	$data = 0;
	        	
	        }
        }else{
        	$status = 'Invalid';
        	$message = 'Invalid Parcel Reference Number';
        	$data = 0;
        }

        $response = [
        	'status' => $status,
        	'message' => $message,
        	'parcel' => $data,
        ];

        return response()->json($response);
    }

    public function changeWeight($id)
    {
//        dd($id);
        $find_parcel = Order::find($id);
        $vendor_id = $find_parcel->vendor_id;
        $vendor_weights = \App\Models\VendorWeight::where('vendor_id', $vendor_id)->where('city_id', $find_parcel->consignee_city)->get();
        
        return view('barcode/change-weight', compact('find_parcel','vendor_weights'));
    }
    
    public function saveChangeWeight(Request $request)
    {
        $validateData = $request->validate([
            'parcel_id' => 'required',
            'vendor_weight_id' => 'required',
        ]);

        $weight_price = VendorWeight::where('id',$request->vendor_weight_id)->first();

        $id = $request->parcel_id;
        $order = Order::find($id);
        $order->vendor_weight_id = $request->vendor_weight_id;
        $order->vendor_weight_price = $weight_price->price;
        $order->save();
        
        return redirect()->back()->with('success', 'Weight has been changed Successfully.');
    }

    public function scanCancelledParcelList()
    {
        $breadcrumbs = [
            'name' => 'Scan Cancelled Parcels', 
        ];

        return view('barcode/scan-cancel-parcel-list',compact('breadcrumbs'));
    }

    public function addCancelledParcel(Request $request)
    {
        $validatedData = $request->validate([
            'order_parcel_reference_no' => 'required',
        ]);

        $authUser = Auth::user();
        $authMiddleManId = $authUser->id;
        // $userCity = UserCity::where('user_id',$authMiddleManId )->pluck('city_id');

        $orderReferencce = $request->order_parcel_reference_no;
        $parcel = Order::where('order_reference',$orderReferencce)->with('vendor')
                ->with([
                    'vendorWeight' => function($query) {
                    $query->with(['ahlWeight','city']);
                    }
                ])->first();

        if($parcel){
            if($parcel->order_status == 9){
                
                Order::where('order_reference',$orderReferencce)->update(['order_status' => 19]);

                $status = 'Success';
                $message = 'Status Change Successfully';
                $data = $parcel;

            }elseif($parcel->order_status != 9){

                $status = 'Error';
                $message = 'You cannot change status before cancelling this parcel';
                $data = $parcel;
            }
        }else{
            $status = 'Invalid';
            $message = 'Invalid Parcel Reference Number';
            $data = 0;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'parcel' => $data,
            'user_name' => $authUser->name,
        ];

        return response()->json($response);
    }

    public function reattemptParcelList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Scan Reattempt Parcels', 
        ];

        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $staffList = User::whereHas(
            'roles', function($q){
                $q->whereIn('id', [5]);
            }
        )->whereHas('usercity',function($query) use($usercity){
            $query->whereIn('city_id',$usercity);
        })
        ->with('userDetail')
        ->where('status',1)
        ->get();

        if($request->staff_id && $request->date)
        {
            $orderAssignedIds = OrderAssigned::select('id','order_id','created_at','rider_id','status','reattempt_by')
            ->whereDate('created_at',$request->date)
            ->where(['reattempt_by'=>$request->staff_id])
            ->get()
            ->pluck('order_id');
            $orders = Order::whereIn('id', $orderAssignedIds)->where('order_status', 7)->get();
        }
        else
        {
            $orders = [];
        }

        return view('middle_man/request-for-reattempt',compact('breadcrumbs','staffList','orders'));
    }

    public function reattemptParcel(Request $request)
    {
        $validatedData = $request->validate([
            'order_parcel_reference_no' => 'required',
        ]);

        $authUser = Auth::user();
        $authMiddleManId = $authUser->id;
        // $userCity = UserCity::where('user_id',$authMiddleManId )->pluck('city_id');

        $orderReferencce = $request->order_parcel_reference_no;
        $parcel = Order::where('order_reference',$orderReferencce)
                ->with([
                    'vendorWeight' => function($query) {
                    $query->with(['ahlWeight','city']);
                    }
                ])->first();

        if($parcel){
            if($parcel->order_status == 7){
                
                Order::where('order_reference',$orderReferencce)->update(['order_status' => 8]);

                $orderAssignedIds = OrderAssigned::select('id','order_id','created_at','rider_id','status','reattempt_by')
                ->whereDate('created_at',$request->requestedDate)
                ->where(['reattempt_by'=>$request->staffId])
                ->get()
                ->pluck('order_id');
                $orders = Order::whereIn('id', $orderAssignedIds)->where('order_status', 7)->get();
                if(!empty($orders))
                {
                    $html = '<thead>';
                    $html .= '<tr><th>#</th><th>Customer Ref #</th><th>Amount</th><th>Qty</th><th>Weight</th><th>Order ID</th><th>Description</th></thead>';
                    $html .= '<tbody>';
                    foreach ($orders as $key => $order) {
                    $html .= '<tr>';
                        $html .= '<td>' . $key++ . '</td>
                            <td>' . $order->order_reference . '</td>
                            <td>' . $order->consignment_cod_price . '</td>
                            <td>' . $order->consignment_pieces . '</td>
                            <td>' . $order->vendorWeight->ahlWeight->weight . '</td>
                            <td>' . $order->consignment_order_id . '</td>
                            <td>' . $order->consignment_description . '</td>
                        </tr>';
                    }
                    $html .= '</tbody>';
                }
                else
                {
                    $html = '';
                }

                $status = 'Success';
                $message = 'Status Change Successfully';
                $data = $parcel;
                $html = $html;

            }elseif($parcel->order_status == 8){

                $status = 'Scanned';
                $message = 'Pacel Already Scan';
                $data = 0;
                $html = 0;

            }
            elseif($parcel->order_status != 7){
                $status = 'Before';
                $message = 'You can not change status before Making this Parcel Request for Re-attempt';
                $data = 0;
                $html = 0;
            }
        }else{
            $status = 'Invalid';
            $message = 'Invalid Parcel Reference Number';
            $data = 0;
            $html = 0;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'parcel' => $data,
            'html_data' => $html,
        ];

        return response()->json($response);
    }

    public function cancelByRiderParcelList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Scan Cancel Parcels By Rider', 
        ];

        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $staffList = User::whereHas(
            'roles', function($q){
                $q->whereIn('id', [5]);
            }
        )->whereHas('usercity',function($query) use($usercity){
            $query->whereIn('city_id',$usercity);
        })
        ->with('userDetail')
        ->where('status',1)
        ->get();

        if($request->staff_id && $request->date)
        {
            $orderAssignedIds = OrderAssigned::select('id','order_id','created_at','rider_id','status','reattempt_by')
            ->whereDate('created_at',$request->date)
            ->where(['reattempt_by'=>$request->staff_id])
            ->get()
            ->pluck('order_id');
            $orders = Order::whereIn('id', $orderAssignedIds)->where('order_status', 18)->get();
        }
        else
        {
            $orders = [];
        }

        return view('middle_man/cancel-parcel-scan',compact('breadcrumbs','staffList','orders'));
    }

    public function cancelScanParcel(Request $request)
    {
        $validatedData = $request->validate([
            'order_parcel_reference_no' => 'required',
        ]);

        $authUser = Auth::user();
        $authMiddleManId = $authUser->id;
        // $userCity = UserCity::where('user_id',$authMiddleManId )->pluck('city_id');

        $orderReferencce = $request->order_parcel_reference_no;
        $parcel = Order::where('order_reference',$orderReferencce)
                ->with([
                    'vendorWeight' => function($query) {
                    $query->with(['ahlWeight','city']);
                    }
                ])->first();

        if($parcel){
            if($parcel->order_status == 18){
                
                Order::where('order_reference',$orderReferencce)->update(['order_status' => 9]);

                $orderAssignedIds = OrderAssigned::select('id','order_id','created_at','rider_id','status','reattempt_by')
                ->whereDate('created_at',$request->requestedDate)
                ->where(['reattempt_by'=>$request->staffId])
                ->get()
                ->pluck('order_id');
                $orders = Order::whereIn('id', $orderAssignedIds)->where('order_status', 18)->get();
                if(!empty($orders))
                {
                    $html = '<thead>';
                    $html .= '<tr><th>#</th><th>Customer Ref #</th><th>Amount</th><th>Qty</th><th>Weight</th><th>Order ID</th><th>Description</th></thead>';
                    $html .= '<tbody>';
                    foreach ($orders as $key => $order) {
                    $html .= '<tr>';
                        $html .= '<td>' . $key++ . '</td>
                            <td>' . $order->order_reference . '</td>
                            <td>' . $order->consignment_cod_price . '</td>
                            <td>' . $order->consignment_pieces . '</td>
                            <td>' . $order->vendorWeight->ahlWeight->weight . '</td>
                            <td>' . $order->consignment_order_id . '</td>
                            <td>' . $order->consignment_description . '</td>
                        </tr>';
                    }
                    $html .= '</tbody>';
                }
                else
                {
                    $html = '';
                }

                $status = 'Success';
                $message = 'Status Change Successfully';
                $data = $parcel;
                $html = $html;

            }elseif($parcel->order_status == 9){

                $status = 'Scanned';
                $message = 'Pacel Already Scan';
                $data = 0;
                $html = 0;

            }
            elseif($parcel->order_status != 18){
                $status = 'Before';
                $message = 'You can not change status before Making this Parcel Request for Re-attempt';
                $data = 0;
                $html = 0;
            }
        }else{
            $status = 'Invalid';
            $message = 'Invalid Parcel Reference Number';
            $data = 0;
            $html = 0;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'parcel' => $data,
            'html_data' => $html,
        ];

        return response()->json($response);
    }

    public function scanReverseParcelList()
    {
        $breadcrumbs = [
            'name' => 'Scan Reverse Pickup Parcels', 
        ];

        return view('admin/reverse_pickup/scan-reverse-parcel-list',compact('breadcrumbs'));
    }

    public function addReverseParcel(Request $request)
    {
        $validatedData = $request->validate([
            'order_parcel_reference_no' => 'required',
        ]);

        $responseMessage = "";
        $authUser = Auth::user();
        $authMiddleManId = $authUser->id;
        $datetime = now();

        $orderReferencce = $request->order_parcel_reference_no;
        $parcel = Order::where('order_reference',$orderReferencce)->where('parcel_nature',2)->with('vendor','orderStatus')
                ->with([
                    'vendorWeight' => function($query) {
                    $query->with(['ahlWeight','city']);
                    }
                ])->first();

        if($parcel){
            if($parcel->order_status == 1){
                
                Order::where('order_reference',$orderReferencce)->update(['order_status' => 3]);

                $status = 'Success';
                $message = 'Status Change Successfully';
                $data = $parcel;
                $responseMessage = "";

                $find_scanOrder = ScanOrder::where('order_id',$parcel->id)->first();
                if(!empty($find_scanOrder))
                {
                    $scanOrder = [
                        'pickup_request_id' => 1,
                        'order_id' => $parcel->id,
                        'picker_id' => $authMiddleManId,
                        'middle_man_id' => $authMiddleManId,
                        'middle_man_scan_date' => $datetime,
                    ];
                    $find_scanOrder->update($scanOrder);
                }
                else
                {
                    $scanOrder = [
                        'pickup_request_id' => 1,
                        'order_id' => $parcel->id,
                        'picker_id' => $authMiddleManId,
                        'middle_man_id' => $authMiddleManId,
                        'middle_man_scan_date' => $datetime,
                    ];

                    ScanOrder::create($scanOrder);
                }

            }elseif($parcel->order_status == 2){

                Order::where('order_reference',$orderReferencce)->update(['order_status' => 3]);           
                
                $status = 'Scanned';
                $message = 'Pacel Already Scan';
                $data = $parcel;
                $responseMessage = "";

                $find_scanOrder = ScanOrder::where('order_id',$parcel->id)->first();
                if(!empty($find_scanOrder))
                {
                    $scanOrder = [
                        'pickup_request_id' => 1,
                        'order_id' => $parcel->id,
                        'picker_id' => $authMiddleManId,
                        'middle_man_id' => $authMiddleManId,
                        'middle_man_scan_date' => $datetime,
                    ];
                    $find_scanOrder->update($scanOrder);
                }
                else
                {
                    $scanOrder = [
                        'pickup_request_id' => 1,
                        'order_id' => $parcel->id,
                        'picker_id' => $authMiddleManId,
                        'middle_man_id' => $authMiddleManId,
                        'middle_man_scan_date' => $datetime,
                    ];

                    ScanOrder::create($scanOrder);
                }
            }
            elseif($parcel->order_status >= 3){
                $status = 'After';
                $message = 'Already Scan';
                $data = 0;
                $responseMessage = "";
            }
        }else{
            $status = 'Invalid';
            $message = 'Invalid Parcel Reference Number';
            $data = 0;
            $responseMessage = "";
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'parcel' => $data,
            'responseMessage' => $responseMessage
        ];

        return response()->json($response);
    }

    public function  getSagNumber(Request $request)
    {
        if(empty($request->fromCity))
        {
            $status = 'fromCity';
            $message = 'Please Select From City First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
            ];

            return response()->json($response);
        }

        if(empty($request->toCity))
        {
            $status = 'toCity';
            $message = 'Please Select To City First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
            ];

            return response()->json($response);
        }

        if(empty($request->seal_number))
        {
            $status = 'SealNumber';
            $message = 'Please Enter Seal Number First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
            ];

            return response()->json($response);
        }

        $check_number = ParcelSag::where('manual_seal_number', $request->seal_number)->first();
        if(!empty($check_number))
        {
            $status = 'Duplicate';
            $message = 'Seal Number has been already used, Enter New!';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
            ];

            return response()->json($response);
        }

        $sag_number = 0;

        $fetch_sag = ParcelSag::orderBy('id','DESC')->first();
        if(!empty($fetch_sag))
        {
            $sag_number = $fetch_sag->sag_count + 1;
        }
        else
        {
            $sag_number = 1;
        }

        $fromCity = City::where('id', $request->fromCity)->first();
        $toCity = City::where('id', $request->toCity)->first();

        $sag_value = $fromCity->code .'-'. $toCity->code .'-'. $sag_number;

        $sag_data = [
            'sag_number' => $sag_value,
            'manual_seal_number' => $request->seal_number,
            'sag_count' => $sag_number,
            'status' => 0,
            'created_by' => Auth::user()->id,
        ];

        ParcelSag::create($sag_data);

        $status = 'Success';
        $message = 'Sag Number Created';
        $data = $sag_value;

        $response = [
            'status' => $status,
            'message' => $message,
            'parcel' => $data,
        ];

        return response()->json($response);
    }

    public function  closeSagNumber(Request $request)
    {
        $authUser = Auth::user()->id;
        $fetch_sag = ParcelSag::where('sag_number', $request->parcel_sag)->first();
        if(!empty($fetch_sag))
        {
            $status = 'Success';
            $message = 'Sag Closed Successfully!';

            $fetch_sag->update(['status' => 1, 'close_by' => $authUser, 'close_in' => $request->fromCity]);

            $get_sag_orders = OrderInSag::where('sag_id', $fetch_sag->id)->get();
            if(count($get_sag_orders) > 0)
            {
                foreach($get_sag_orders as $order)
                {
                    $order->update(['status'=>1]);
                }
            }
        }
        else
        {
            $status = 'Invalid';
            $message = 'No Sag Found!';
        }

        $response = [
            'status' => $status,
            'message' => $message,
        ];

        return response()->json($response);
    }

    public function checkSag()
    {
        $breadcrumbs = [
            'name' => 'Check Received Sag', 
        ];

        $authUser = Auth::user();
        $userCity = UserCity::where('user_id',$authUser->id )->pluck('city_id');
        $userCities = City::whereIn('id', $userCity)->get();

        return view('barcode/check-sag',compact('breadcrumbs','userCities'));
    }

    public function  openSagNumber(Request $request)
    {
        if(empty($request->fromCity))
        {
            $status = 'fromCity';
            $message = 'Please Select City First';

            $response = [
                'status' => $status,
                'message' => $message,
            ];

            return response()->json($response);
        }
        $authUser = Auth::user()->id;
        $fetch_sag = ParcelSag::where('sag_number', $request->parcel_sag)->first();
        if(!empty($fetch_sag))
        {
            $check_bilty = SagInBilty::where('sag_id', $fetch_sag->id)->first();
            if(!empty($check_bilty))
            {
                if($check_bilty->status == 1)
                {
                    if($fetch_sag->status == 1)
                    {
                        $status = 'Success';
                        $message = 'Sag Opened Successfully!';

                        $fetch_sag->update(['status' => 2, 'open_by' => $authUser, 'open_in' => $request->fromCity]);
                    }
                    else
                    {
                        $status = 'Already';
                        $message = 'Sag Already Opened!';
                    }
                }
                else
                {
                    $status = 'CloseBilty';
                    $message = 'First Open Bilty!';
                }
            }
            else
            {
                $status = 'EmptyBilty';
                $message = 'No Bilty was Assigned! First Assign Bilty';
            }
        }
        else
        {
            $status = 'Invalid';
            $message = 'No Sag Found!';
        }

        $response = [
            'status' => $status,
            'message' => $message,
        ];

        return response()->json($response);
    }

    public function  checkSagParcels(Request $request){

        $validateData = $request->validate([
            'order_parcel_reference_no' => 'required',
        ]);

        $orderReferance = $request->order_parcel_reference_no;

        $parcel = Order::where('order_reference',$orderReferance)
        ->with([
            'vendorWeight' => function($query) {
                $query->with(['ahlWeight','city']);
                }
        ])
        ->first();

        if($parcel){

            $parcel->update(['order_status' => $parcel->previous_order_value]);

            $get_sag = ParcelSag::where('sag_number', $request->parcel_sag)->first();
            $get_order_in_sag = OrderInSag::where('order_id',$parcel->id)->where('status', 1)->first();

            if($get_sag->id == $get_order_in_sag->sag_id)
            {
                $get_order_in_sag->update(['status' => 2]);
                $parcel->update(['sag_status' => 0]);

                $status = 'Success';
                $message = 'Status Change Successfully';
                $data = $parcel;
            }
            else
            {
                $status = 'Error';
                $message = 'InValid Parcel in Sag!';
                $data = 0;
            }
        }else{
            $status = 'Invalid';
            $message = 'Invalid Parcel Reference Number';
            $data = 0;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'parcel' => $data,
        ];

        return response()->json($response);
    }

    public function createBilty()
    {
        $breadcrumbs = [
            'name' => 'Create New Bilty', 
        ];

        $getCities = City::all();
        $authUser = Auth::user();
        $userCity = UserCity::where('user_id',$authUser->id )->pluck('city_id');
        $userCities = City::whereIn('id', $userCity)->get();

        return view('barcode/bilty/create-bilty',compact('breadcrumbs','getCities','userCities'));
    }

    public function  getBiltyNumber(Request $request)
    {
        if(empty($request->fromCity))
        {
            $status = 'fromCity';
            $message = 'Please Select From City First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
                'sags' => 0,
            ];

            return response()->json($response);
        }

        if(empty($request->toCity))
        {
            $status = 'toCity';
            $message = 'Please Select To City First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
                'sags' => 0,
            ];

            return response()->json($response);
        }

        if(empty($request->manual_bilty))
        {
            $status = 'ManualBilty';
            $message = 'Please Enter To Manual Bilty First';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
                'sags' => 0,
            ];

            return response()->json($response);
        }

        $check_bilty = Bilty::where('manual_bilty_number', $request->manual_bilty)->first();
        if(!empty($check_bilty))
        {
            $status = 'Duplicate';
            $message = 'This Manual Bilty Number has already been used. Please use a new one!';
            $data = 0;

            $response = [
                'status' => $status,
                'message' => $message,
                'parcel' => $data,
                'sags' => 0,
            ];

            return response()->json($response);
        }

        $bilty_number = 0;

        $fetch_bilty = Bilty::orderBy('id','DESC')->first();
        if(!empty($fetch_bilty))
        {
            $bilty_number = $fetch_bilty->id + 1;
        }
        else
        {
            $bilty_number = 1;
        }

        $fromCity = City::where('id', $request->fromCity)->first();
        $toCity = City::where('id', $request->toCity)->first();

        $bilty_value = $fromCity->code .'-'. $toCity->code .'-'. $bilty_number;

        $bilty_data = [
            'bilty_number' => $bilty_value,
            'manual_bilty_number' => $request->manual_bilty,
            'from' => $fromCity->id,
            'to' => $toCity->id,
            'created_by' => Auth::user()->id,
            'create_in' => $toCity->id,
            'status' => 0,
        ];

        Bilty::create($bilty_data);

        $get_sags = ParcelSag::where('close_in', $fromCity->id)->where('bilty_status', 0)->where('status',1)->get();

        $html = '';
        foreach ($get_sags as $key => $get_sag) {
            $get_order = OrderInSag::where('sag_id', $get_sag->id)->count();
            $html .= '<tr>';
            $html .= '<td align="center">';
            $html .= '<input type="checkbox" class="case" name="case" value="'. $get_sag->id .'"></td>';
            $html .= '<th scope="row">'. ++$key .'</th>';
            $html .= '<td>'.$get_sag->sag_number.'</td>';
            $html .= '<td>'.$get_order.'</td>';
            $html .= '</tr>';
        }

        $status = 'Success';
        $message = 'Bilty Number Created';
        $data = $bilty_value;
        $sags = $html;

        $response = [
            'status' => $status,
            'message' => $message,
            'parcel' => $data,
            'sags' => $sags,
        ];

        return response()->json($response);
    }

    public function  closeBiltyNumber(Request $request)
    {
        $authUser = Auth::user()->id;
        $sags = $request->sags;
        $fetch_bilty = Bilty::where('bilty_number', $request->bilty_number)->first();
        if(!empty($fetch_bilty))
        {
            $status = 'Success';
            $message = 'Bilty Closed Successfully!';

            foreach($sags as $sag)
            {
                $get_sag = ParcelSag::where('id', $sag)->first();
                $get_sag->update(['bilty_status' => 1]);

                $bilty_data = [
                    'bilty_id' => $fetch_bilty->id,
                    'sag_id' => $sag,
                    'status' => 0,
                ];

                SagInBilty::create($bilty_data);
            }
        }
        else
        {
            $status = 'Invalid';
            $message = 'No Bilty Found!';
        }

        $response = [
            'status' => $status,
            'message' => $message,
        ];

        return response()->json($response);
    }

    public function checkBilty()
    {
        $breadcrumbs = [
            'name' => 'Check Received Bilty', 
        ];

        $authUser = Auth::user();
        $userCity = UserCity::where('user_id',$authUser->id )->pluck('city_id');
        $userCities = City::whereIn('id', $userCity)->get();

        return view('barcode/bilty/open-bilty',compact('breadcrumbs','userCities'));
    }

    public function  openBiltyNumber(Request $request)
    {
        if(empty($request->fromCity))
        {
            $status = 'fromCity';
            $message = 'Please Select City First';

            $response = [
                'status' => $status,
                'message' => $message,
                'sags' => 0,
            ];

            return response()->json($response);
        }
        $fetch_bilty = Bilty::where('bilty_number', $request->bilty_number)->first();
        if(!empty($fetch_bilty))
        {
            $get_sags = SagInBilty::where('bilty_id', $fetch_bilty->id)->where('status', 0)->get();

            $html = '';
            foreach ($get_sags as $key => $get_sag) {
                $html .= '<tr>';
                $html .= '<td align="center">';
                $html .= '<input type="checkbox" class="case" name="case" value="'. $get_sag->sag_id .'"></td>';
                $html .= '<th scope="row">'. ++$key .'</th>';
                $html .= '<td>'.$get_sag->SagDetail->sag_number.'</td>';
                $html .= '</tr>';
            }

            if($fetch_bilty->status == 0)
            {
                $status = 'Success';
                $message = 'Bilty Found Successfully!';
                $sags = $html;
            }
            else
            {
                $status = 'Already';
                $message = 'Bilty Already Opened!';
                $sags = 0;
            }
        }
        else
        {
            $status = 'Invalid';
            $message = 'No Bilty Found!';
            $sags = 0;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'sags' => $sags,
        ];

        return response()->json($response);
    }

    public function  receivedBiltyNumber(Request $request)
    {
        $authUser = Auth::user()->id;
        $sags = $request->sags;
        $fetch_bilty = Bilty::where('bilty_number', $request->bilty_number)->first();
        if(!empty($fetch_bilty))
        {
            $status = 'Success';
            $message = 'Bilty Closed Successfully!';

            $fetch_bilty->update(['status' => 1, 'open_by' => $authUser, 'open_in' => $request->fromCity]);

            foreach($sags as $sag)
            {
                $get_sag = ParcelSag::where('id', $sag)->first();
                $get_sag->update(['bilty_status' => 0]);

                $get_sag_in_bilty = SagInBilty::where('sag_id', $sag)->first();
                $get_sag_in_bilty->update(['status' => 1]);
            }
        }
        else
        {
            $status = 'Invalid';
            $message = 'No Bilty Found!';
        }

        $response = [
            'status' => $status,
            'message' => $message,
        ];

        return response()->json($response);
    }

    //enroute pdf

    public function enroutePDF(Request $request) {

        $sag = $request->sag;
        $find_sag = ParcelSag::where('sag_number', $sag)->first();
        $title = $sag.' '.'Enroute Parcel List';
        $seal_number = $find_sag->manual_seal_number;

        $count_orders = OrderInSag::where('sag_id', $find_sag->id)->count();
        $get_orders = OrderInSag::where('sag_id', $find_sag->id)->pluck('order_id');
        $parcelPdfData = Order::whereIn('id', $get_orders)->where('order_status', 15)->get();

        $fileName = $sag.'-enroute-parcels';
        $date = date('d-M-Y');

        $pdf = PDF::loadView('barcode.enroute-pdf', compact('parcelPdfData','title','date','sag','count_orders','seal_number'))->setPaper('a4', 'landscape');
        return $pdf->download($fileName.'.pdf');
    }

    //bilty pdf

    public function biltyPDF(Request $request) {

        $bilty = $request->bilty;
        $find_bilty = Bilty::where('bilty_number', $bilty)->first();
        $title = 'Bilty Receipt';
        $manual_number = $find_bilty->manual_bilty_number;

        $count_sags = SagInBilty::where('bilty_id', $find_bilty->id)->count();
        $get_sags = SagInBilty::where('bilty_id', $find_bilty->id)->pluck('sag_id');
        $parcelPdfData = ParcelSag::whereIn('id', $get_sags)->get();

        $fileName = $bilty.'-bilty-receipt';

        $pdf = PDF::loadView('barcode.bilty.bilty-pdf', compact('parcelPdfData','title','bilty','count_sags','manual_number'));
        return $pdf->download($fileName.'.pdf');
    }

    public function inProgressSag()
    {
        $breadcrumbs = [
            'name' => 'In-Progress Sags List', 
        ];

        $sags = ParcelSag::where('status', 1)->get();
        
        return view('barcode/in-progress-sag',compact('sags','breadcrumbs'));
    }

    public function inProgressSagParcelList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'In-Progress Sag Parcels List', 
        ];

        $sag_id = $request->id;

        $orders = OrderInSag::where('sag_id',$sag_id)->with('orderDetail')->get();

        return view('barcode/in-progress-sag-parcel-list',compact('orders','breadcrumbs'));
    }

    public function closedSag()
    {
        $breadcrumbs = [
            'name' => 'Closed Sags List', 
        ];

        $sags = ParcelSag::where('status', 2)->get();
        
        return view('barcode/closed-sag',compact('sags','breadcrumbs'));
    }

    //bilty sag

    public function inProgressBilty()
    {
        $breadcrumbs = [
            'name' => 'In-Progress Biltys List', 
        ];

        $bilties = Bilty::where('status', 0)->get();
        
        return view('barcode/bilty/in-progress-bilty',compact('bilties','breadcrumbs'));
    }

    public function biltySagList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Bilty Sag List', 
        ];

        $bilty_id = $request->id;

        $sags = SagInBilty::where('bilty_id',$bilty_id)->get();

        return view('barcode/bilty/sag-list',compact('sags','breadcrumbs'));
    }

    public function closedBilty()
    {
        $breadcrumbs = [
            'name' => 'Closed Biltys List', 
        ];

        $bilties = Bilty::where('status', 1)->get();
        
        return view('barcode/bilty/closed-bilty',compact('bilties','breadcrumbs'));
    }

    public function getVendorWeight(Request $request)
    {
        $vendor_id = $request->vendor_id;

        $weights = VendorWeight::where('vendor_id', $vendor_id)->get();
        // dd($weights);
        $html = '<option disabled= "">Select Parcel Weight</option>';
        foreach($weights as $weight)
        {
            $html .='<option value=' . $weight->id . '>' . $weight->ahlWeight->weight . ' (' . $weight->city->first()->name . ') </option>';
        }

        $data = [
            'status' => 'success',
            'html_data' => $html,
        ];

        return response()->json($data);
    }

    public function bulkVendorWeight(Request $request)
    {
        $weight_price = VendorWeight::where('id',$request->vendor_weight_id)->first();
        $parcles = $request->parcels;

        foreach($parcles as $key => $parcel)
        {
            $id = $parcel;
            $order = Order::find($id);
            $order->vendor_weight_id = $request->vendor_weight_id;
            $order->vendor_weight_price = $weight_price->price;
            $order->save();
        }

        $status = 'Success';
        $message = 'Weight has been Changed Successfully!';

        $response = [
            'status' => $status,
            'message' => $message,
        ];

        return response()->json($response);
    }
    
    public function getCall()
    {
        $response = Http::get('https://voicegateway.its.com.pk/api?ApiKey=382D4D8914FCDA04C5912C6D11D0682E&Recipient=3235081556&CampId=480&UniqueId=123456789');

        $data = $response->json();
        $a = response()->json($data);
        // if($a->getData()->ErrorCode == "401")
        // {
        //     $ss = "Null";
        // }
        // else
        // {
            $ss = $a->getData()->CdrID;
        // }
        
        return $ss;
    }
}
