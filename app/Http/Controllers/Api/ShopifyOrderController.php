<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Vendor;
use App\Models\Order;
use App\Models\VendorWeight;
use App\Models\WarehouseLocation;
use App\Models\City;
use App\Models\ScanOrder;
use App\Models\OrderAssigned;
use App\Models\Status;
use App\Models\ParcelLimit;
use App\Models\ShiperAdviser;

use App\Helpers\Helper;
use App\Helpers\AHLHelper;
use App\Helpers\ResponseHelper;
use Log;

class ShopifyOrderController extends Controller
{
    public function order(Request $request)
    {

        $requestData = $request->all();

        if(empty($requestData['vendor_id']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty Vendor ID",
            ]);
        }
        if(empty($requestData['consignee_first_name']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignee_first_name",
            ]);
        }
        if(empty($requestData['consignee_address']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignee_address",
            ]);
        }
        if(empty($requestData['consignee_country']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignee_country",
            ]);
        }
        if(empty($requestData['consignee_state']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignee_state",
            ]);
        }
        if(empty($requestData['consignee_city']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignee_city",
            ]);
        }
        if(empty($requestData['consignment_order_id']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignment_order_id",
            ]);
        }
        if(empty($requestData['consignment_order_type']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignment_order_type",
            ]);
        }
        if(empty($requestData['vendor_weight_id']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty vendor_weight_id",
            ]);
        }
        if(empty($requestData['consignment_packaging']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignment_packaging",
            ]);
        }
        if(empty($requestData['consignment_pieces']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignment_pieces",
            ]);
        }
        if(empty($requestData['consignment_pickup_location']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignment_pickup_location",
            ]);
        }
        if(empty($requestData['consignment_origin_city']))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Empty consignment_origin_city",
            ]);
        }
        
        $check_city = City::where('id', $requestData['consignee_city'])->first();
        if(!empty($check_city))
        {
            $city_code = $check_city->code;
        }
        else
        {
            $city_code = 'AHL';
        }
        $warehouse = WarehouseLocation::where('id',$requestData['consignment_pickup_location'])->first();
        if(empty($warehouse))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Invalid Pickup Location ID, not exists for this vendor",
            ]);
        }
        $weight_price = VendorWeight::where('id',$requestData['vendor_weight_id'])->first();
        if(!empty($weight_price))
        {
            if($weight_price->vendor_id == $requestData['vendor_id'])
            {
                $vendor_weight_price = $weight_price->price;
                //fuel
                $authVendorId = $requestData['vendor_id'];
                $payee = Vendor::where('id',$authVendorId)->first();
                $fuel_adjustment = ($weight_price->price * $payee->fuel)/100;
                $round_fuel_adjustment = round($fuel_adjustment);
                //GST
                $gst_adjustment = ($weight_price->price * $payee->gst)/100;
                $round_gst_adjustment = round($gst_adjustment);
            }
            else
            {
                return response()->json([
                    'status' => 0,
                    'message'=> "Invalid Request.",
                    'error'=> "Invalid Weight ID, not exists for this vendor",
                ]);
            }
        }
        else
        {
            $vendor_weight_price = 0;
            $round_fuel_adjustment = 0;
            $round_gst_adjustment = 0;
        }

        $fetch_limit = ParcelLimit::where('city_id', $requestData['consignee_city'])->first();
        if(!empty($fetch_limit))
        {
            $parcel_limit = $fetch_limit->limit;
        }
        else
        {
            $parcel_limit = 1;
        }

        $genrateMT = Helper::genrateOrderReference();
        sleep(1);
        $latestOrder = Order::orderBy('created_at','DESC')->first();
        $orderReferenceNumber = $latestOrder->id + $genrateMT;

        $check_order_cn = Order::where('order_reference', $orderReferenceNumber)->first();
        if(!empty($check_order_cn))
        {
            return response()->json([
                'status' => 0,
                'message'=> "Invalid Request.",
                'error'=> "Try Again, CN Already exists",
            ]);
        }
        else
        {
            
        }

        //foreach ($requestData as $key => $requestData) {
            
            $orders = [
                'vendor_id'=> $requestData['vendor_id'],
                'consignee_first_name'=> $requestData['consignee_first_name'],
                'consignee_last_name'=> $requestData['consignee_last_name'],
                'consignee_email'=> $requestData['consignee_email'],
                'consignee_address'=> $requestData['consignee_address'],
                'consignee_phone'=> $requestData['consignee_phone'],
                'consignee_country'=> $requestData['consignee_country'],
                'consignee_state'=> $requestData['consignee_state'],
                'consignee_city'=> $requestData['consignee_city'],
                'consignment_order_id'=> $requestData['consignment_order_id'],
                'consignment_order_type'=> $requestData['consignment_order_type'],
                'consignment_cod_price'=> $requestData['consignment_cod_price'],
                'consignment_weight'=> 0,//force fully remove when ahl weight id done every where 
                'vendor_weight_id'=> $requestData['vendor_weight_id'],
                'consignment_packaging'=> $requestData['consignment_packaging'],
                'consignment_pieces'=> $requestData['consignment_pieces'],
                'consignment_description'=> $requestData['consignment_description'],
                'pickup_location'=> $requestData['consignment_pickup_location'],
                'consignment_origin_city'=> $requestData['consignment_origin_city'],
                'additional_services_type'=> null,
                'order_reference'=> '#'.$city_code.$orderReferenceNumber,
                'vendor_weight_price' => $vendor_weight_price,
                'vendor_fuel_price' => $round_fuel_adjustment,
                'vendor_tax_price' => $round_gst_adjustment,
                'order_status'=> 1,
                'parcel_limit' => $parcel_limit,
                'created_at'=> now(),
            ];
        //}

        //dd($order);
        //Transaction
        DB::beginTransaction();
        
        try {

            DB::commit();
            $order = Order::create($orders);
            return ResponseHelper::apiResponse(1,'Your Order Has been Placed.',[],'order_parcel',$order);

        } catch (Throwable $e) {
            
            DB::rollback();
            report($e);
            return ResponseHelper::apiResponse(0,'Error to save vendor order.',[],'order_parcel',(object) []);
        }

        //$order = Order::create($order);
    }

    public function multipleOrders(Request $request)
    {

        
        $requestData = $request->all();

        $totalRequestOrder = count($requestData);
        $collection = collect($requestData);

        $requestVendorIds = $collection->pluck('vendor_id');
        $countRequestVendorIds = count($requestVendorIds);
        
        var_dump($countRequestVendorIds < $totalRequestOrder);exit;

        if($countRequestVendorIds < $totalRequestOrder){
            $rules = [
                '*.vendor_id'=> 'required|numeric',
            ];            
        }else{
            $vendorsId = Vendor::select('id')->get()->pluck('id');

            $countryIds = Helper::getCountry()->pluck('id');
            $statesIds =  Helper::getStates()->pluck('id');
            $citiesIds = Helper::getCities()->pluck('id');

            $orderTypesIds = AHLHelper::orderType()->pluck('id');
            $packagingIds = AHLHelper::packaging()->pluck('id');

            dump($requestVendorIds);
            $vendorWeightIds = VendorWeight::whereIn('vendor_id',$requestVendorIds)->get()->pluck('id');
            
            dump($vendorWeightIds);
            $vendorWarehouseLocationsIds = WarehouseLocation::whereIn('vendor_id',$requestVendorIds)->get()->pluck('id');
            dd($vendorWarehouseLocationsIds);
            
            foreach ($variable as $key => $value) {
                // code...
            }

            $rules = [
                //order
                '*.vendor_id' => [
                    'required',
                    'numeric',
                    Rule::in($vendorsId),
                ],
                '*.consignee_phone'=> 'required|numeric',
                '*.consignee_first_name'=> 'required',
                '*.consignee_last_name'=> 'required',
                '*.consignee_email'=> 'required|email',
                '*.consignee_address'=> 'required',
                '*.consignee_country'=> [
                    'required',
                    'numeric',
                    Rule::in($countryIds),
                ],
                '*.consignee_state'=> [
                    'required',
                    'numeric',
                    Rule::in($statesIds),
                ],
                '*.consignee_city'=> [
                    'required',
                    'numeric',
                    Rule::in($citiesIds),
                ],
                '*.consignment_order_id'=> 'required',
                '*.consignment_order_type'=> [
                    'required',
                    'numeric',
                    Rule::in($orderTypesIds),
                ],
                '*.consignment_cod_price'=> 'required|numeric',
                //'consignment_weight'=> 'required',
                '*.vendor_weight_id'=> [
                    'required',
                    'numeric',
                    Rule::in($vendorWeightIds),
                ],
                '*.consignment_packaging'=> [
                    'required',
                    'numeric',
                    Rule::in($packagingIds),
                ],
                '*.consignment_pieces'=> 'required|numeric',
                '*.consignment_pickup_location'=> [
                    'required',
                    'numeric',
                    Rule::in($vendorWarehouseLocationsIds),
                ],
                '*.consignment_origin_city'=> [
                    'required',
                    'numeric',
                    Rule::in($citiesIds),
                ],
                '*.consignment_description'=> 'nullable',
            ];

        }

        

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_parcel',(object) []);
        }

        foreach ($requestData as $key => $shopifyData) {
            
            $orders[] = [
                'vendor_id'=> $shopifyData['vendor_id'],
                'consignee_first_name'=> $shopifyData['consignee_first_name'],
                'consignee_last_name'=> $shopifyData['consignee_last_name'],
                'consignee_email'=> $shopifyData['consignee_email'],
                'consignee_address'=> $shopifyData['consignee_address'],
                'consignee_phone'=> $shopifyData['consignee_phone'],
                'consignee_country'=> $shopifyData['consignee_country'],
                'consignee_state'=> $shopifyData['consignee_state'],
                'consignee_city'=> $shopifyData['consignee_city'],
                'consignment_order_id'=> $shopifyData['consignment_order_id'],
                'consignment_order_type'=> $shopifyData['consignment_order_type'],
                'consignment_cod_price'=> $shopifyData['consignment_cod_price'],
                'consignment_weight'=> 0,//force fully remove when ahl weight id done every where 
                'vendor_weight_id'=> $shopifyData['vendor_weight_id'],
                'consignment_packaging'=> $shopifyData['consignment_packaging'],
                'consignment_pieces'=> $shopifyData['consignment_pieces'],
                'consignment_description'=> $shopifyData['consignment_description'],
                'pickup_location'=> $shopifyData['consignment_pickup_location'],
                'consignment_origin_city'=> $shopifyData['consignment_origin_city'],
                'additional_services_type'=> null,
                'order_reference'=> Helper::genrateOrderReference(),
                'order_status'=> 1,
                'created_at'=> now(),
            ];
        }

        //dd($order);
        //Transaction
        DB::beginTransaction();
        
        try {

            DB::commit();
            $order = Order::insert($orders);
            return ResponseHelper::apiResponse(1,'Your Order Has been Placed.',[],'order_parcel',(object) []);

        } catch (Throwable $e) {
            
            DB::rollback();
            report($e);
            return ResponseHelper::apiResponse(0,'Error to save vendor order.',[],'order_parcel',(object) []);
        }

        //$order = Order::create($order);
    }

    public function orderTrack(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_reference_no' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_track',(object) []);
        }

        $orderReferencce = $request->order_reference_no;

        $order = Order::select('id','order_reference','order_status','updated_at')->with([
            'orderStatus' => function($query){
                $query->select('id','name');
            }
        ])
        ->where('order_reference',$orderReferencce)
        ->first();
        
        
        if($order){
            return ResponseHelper::apiResponse(1,'Order Track',[],'order_track',$order);
        }else{
            return ResponseHelper::apiResponse(0,'No Order Found',[],'order_track',(object) []);
        }
    }

    public function orderCancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_reference_no' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_decline',(object) []);
        }

        $orderReferencce = $request->order_reference_no;

        Order::where('order_reference',$orderReferencce)->update(['order_status'=>12]);

        return ResponseHelper::apiResponse(1,'Order Cancel Successfully',[],'order_decline',(object) []);
    }

    public function orderCities()
    {
        // $cityIds = [31456];
        $city = City::select('id','name','code','state_id')->with([
            'state' => function($query){
                $query->select('id','country_id','name')->with([
                    'country' => function($query){
                        $query->select('id','name');
                    }
                ]);
            }
        ])->get();

        return ResponseHelper::apiResponse(1,'Order City',[],'order_city',(object) $city);
    }

    public function vendorDetail()
    {
        
        $vendor = Vendor::select('id','vendor_name')->where('status',1)
        ->with([
            'pickupLocation' => function($query){
                $query->select('id','vendor_id','address');
            },
            'vendorWeights' => function($query){
                $query->select('id','vendor_id','ahl_weight_id')->with([
                    'ahlWeight' => function($query){
                        $query->select('id','weight');
                    }
                ]);
            }
        ])
        ->get();

        return ResponseHelper::apiResponse(1,'Vendor Details',[],'vendors_detail',$vendor);
    }
    
    public function orderInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_reference_no' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_invoice',(object) []);
        }

        $orderReferencce = $request->order_reference_no;

        $order = Order::where('order_reference',$orderReferencce)->first();
        $url = 'https://vendor.ahlogistic.pk/api/change-parcel-status-qr?'.$order->id;
        
        $response = [
            'status' => 1,
            'message' => 'Invoice Link Get Successfully',
            'data' => $url,
        ];

        return response()->json($response);

    }

    public function orderSMSLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_reference_no' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_invoice',(object) []);
        }

        $orderReferencce = $request->order_reference_no;

        $order = Order::where('order_reference',$orderReferencce)->first();
        $url = 'https://tracking.ahlogistic.pk/tracking/'.$order->id;
        $parcel_qr = 'https://vendor.ahlogistic.pk/parcels-qr';
        
        $response = [
            'status' => 1,
            'message' => 'SMS Link Get Successfully',
            'data' => $url,
            'parcelsqr' => $parcel_qr,
        ];

        return response()->json($response);

    }

    public function parcelQR(Request $request)
    {
        $parcels = $request->order_reference_no;
        $authVendorId = $request->vendor_id;
        $orders = Order::where('order_reference',$parcels)
        ->with([
            'customerCity' => function($query){
                $query->select('id','name');
            },
            'orderType' => function($query){
                $query->select('id','name');
            },
            'vendorWeight' => function($query){
                $query->with([
                    'ahlWeight' => function($query){
                        $query->select('id','weight');
                    }
                ]);
            }
        ])
        ->get();
        $vendor = Vendor::whereId($authVendorId)->with([
            'vendorCity' => function($query){
                $query->select('id','name');
            }
        ])->first();
        
        return view('vendor/api_parcel_qr',compact('orders','vendor'));
    }

    public function trackingApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_reference_no' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_invoice',(object) []);
        }

        $orderReferencce = $request->order_reference_no;

        $orderDetail =  Order::where('parcel_nature',1)->where('order_reference',$orderReferencce)->first();

        $delivered_date = '';

        if($orderDetail->status == 6)
        {
            $delivered_date = $orderDetail->updated_at;
        }

        $scanOrder = ScanOrder::where('order_id',$orderDetail->id)->first();

        $data = [
            'order_cn' => $orderReferencce,
            'booking_date' => $orderDetail->created_at,
            'picked_up' => $scanOrder ? $scanOrder->created_at : '',
            'at_warehouse' => $scanOrder ? $scanOrder->middle_man_scan_date : '',
            'dispatched' => $scanOrder ? $scanOrder->supervisor_scan_date : '',
            'out_for_delivery' => $scanOrder ? $scanOrder->supervisor_scan_date : '',
            'delivered' => $delivered_date,
        ];
        
        $response = [
            'status' => 1,
            'message' => 'Invoice Link Get Successfully',
            'data' => $data,
        ];

        return response()->json($response);

    }

    public function statusList()
    {
        $statuses = Status::select('id','name')->get();

        return ResponseHelper::apiResponse(1,'Status List',[],'status_list',(object) $statuses);
    }

    public function orderAmountDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_reference_no' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_invoice',(object) []);
        }

        $orderReferencce = $request->order_reference_no;

        $order = Order::where('order_reference',$orderReferencce)->first();

        $parcel_amount = $order->consignment_cod_price;
        $parcel_order_type = $order->orderType->name;
        $weight_charges = $order->vendor_weight_price;
        $parcel_weight = $order->vendorWeight->ahlWeight->weight;
        $parcel_tax = $order->vendor_tax_price;
        $parcel_fuel = $order->vendor_fuel_price;

        $data = [
            'parcel_amount' => $parcel_amount,
            'parcel_order_type' => $parcel_order_type,
            'weight_charges' => $weight_charges,
            'parcel_weight' => $parcel_weight,
            'parcel_tax' => $parcel_tax,
            'parcel_fuel' => $parcel_fuel,
        ];
        
        $response = [
            'status' => 1,
            'message' => 'Order Amount Successfully',
            'data' => $data,
        ];

        return response()->json($response);

    }
    
    public function getCallingResponse(Request $request)
    {
        $call_response = $request->call_response;
        $call_input = $request->call_input;
        $cdrid = OrderAssigned::where('cdrid', $request->cdrid)->first();
        if(!empty($cdrid))
        {
            $cdrid->update(['call_response' => $call_response, 'call_input' => $call_input]);
            $response = [
                'message' => 'success',
            ];
        }
        else
        {
            $response = [
                'message' => 'no cdrid found!',
            ];
        }

        return response()->json($response);
    }

    public function detailTrackingApiOld(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_reference_no' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_invoice',(object) []);
        }

        $orderReferencce = $request->order_reference_no;

        $model =  Order::where('parcel_nature',1)->where('order_reference',$orderReferencce)->first();
        if(!empty($model))
        {
            $tracking_history = [];
            $booking = [
                'date_time' => date('d/m/Y h:i a', strtoTime($model->created_at)),
                'status' => 'PARCEL CREATED AT',
                'status_reason' => null,
            ];

            if(!empty($model->scanOrder->created_at))
            {
                $vendor_warehouse = [
                    'date_time' => date('d/m/Y h:i a', strtoTime($model->scanOrder->created_at)),
                    'status' => 'PARCEL PICKED FROM '.$model->vendor->vendor_name.' WAREHOUSE',
                    'status_reason' => null,
                ];
            }

            if(!empty($model->scanOrder->middle_man_scan_date))
            {
                $ahl_warehouse = [
                    'date_time' => date('d/m/Y h:i a', strtoTime($model->scanOrder->middle_man_scan_date)),
                    'status' => 'PARCEL ENTERED AT AHL WAREHOUSE',
                    'status_reason' => null,
                ];
            }

            $dispatch_history = [];
            if(!empty($model->scanOrder->supervisor_scan_date))
            {
                foreach($model->orderReAssigned as $key => $order_assigned)
                {
                    $dispatch_from_warehouse = [
                        'date_time' => date('d/m/Y h:i a', strtoTime($order_assigned->created_at)),
                        'status' => 'PARCEL DISPATCHED FROM AHL WAREHOUSE',
                        'status_reason' => null,
                    ];
                    
                    $rider_cancel = [
                        'date_time' => date('d/m/Y h:i a', strtoTime($order_assigned->updated_at)),
                        'status' => 'PARCEL CANCELLED FROM RIDER END',
                        'status_reason' => null,
                    ];
                    
                    $rider_reattempt = [
                        'date_time' => date('d/m/Y h:i a', strtoTime($order_assigned->updated_at)),
                        'status' => 'PARCEL REATTEMPT FROM RIDER END',
                        'status_reason' => null,
                    ];

                    $dispatch_history[] = $dispatch_from_warehouse;
                    if(($order_assigned->trip_status_id) == 5)
                    {
                        $dispatch_history[] = $rider_cancel;
                    }
                    if(($order_assigned->trip_status_id) == 6)
                    {
                        $dispatch_history[] = $rider_reattempt;
                    }
                }
            }

            if(!empty($model->orderAssigned->trip_status_id))
            {
                if(($model->orderAssigned->trip_status_id) == 4)
                {
                    $delivered_at = [
                        'date_time' => date('d/m/Y h:i a', strtoTime($model->orderAssigned->updated_at)),
                        'status' => 'PARCEL DELIVERED AT',
                        'status_reason' => null,
                    ];
                }
            }

            $cancel_at = [
                'date_time' => date('d/m/Y h:i a', strtoTime($model->updated_at)),
                'status' => 'PARCEL CANCELLED AT',
                'status_reason' => null,
            ];

            $reattempt_at = [
                'date_time' => date('d/m/Y h:i a', strtoTime($model->updated_at)),
                'status' => 'PARCEL RE-ATTEMPT AT',
                'status_reason' => null,
            ];

            $return_in_progress = [
                'date_time' => date('d/m/Y h:i a', strtoTime($model->updated_at)),
                'status' => 'PARCEL RETURN TO VENDOR IN-PROGRESS AT',
                'status_reason' => null,
            ];

            $return_to_vendor = [
                'date_time' => date('d/m/Y h:i a', strtoTime($model->updated_at)),
                'status' => 'PARCEL RETURN TO VENDOR AT',
                'status_reason' => null,
            ];

            $tracking_history[] = $booking;
            if(!empty($model->scanOrder->created_at))
            {
                $tracking_history[] = $vendor_warehouse;
            }
            if(!empty($model->scanOrder->middle_man_scan_date))
            {
                $tracking_history[] = $ahl_warehouse;
            }
            if(!empty($model->scanOrder->supervisor_scan_date))
            {
                $tracking_history[] = $dispatch_history;
            }
            if(!empty($model->orderAssigned->trip_status_id))
            {
                if(($model->orderAssigned->trip_status_id) == 4)
                {
                    $tracking_history[] = $delivered_at;
                }
            }
            if(($model->order_status == 9) || ($model->order_status == 17) || ($model->order_status == 18))
            {
                $tracking_history[] = $cancel_at;
            }
            if(($model->order_status == 7) || ($model->order_status == 8) || ($model->order_status == 16))
            {
                $tracking_history[] = $reattempt_at;
            }
            if(($model->order_status == 19))
            {
                $tracking_history[] = $return_in_progress;
            }
            if(($model->order_status == 10))
            {
                $tracking_history[] = $return_to_vendor;
            }
            
            $response = [
                'status' => 1,
                'message' => 'Detail Tracking API',
                'tracking_history' => $tracking_history,
            ];

            return response()->json($response);
        }
        else
        {            
            $response = [
                'status' => 0,
                'message' => 'InValid Order Reference',
                'tracking_history' => [],
            ];

            return response()->json($response);
        }

    }

    public function detailTrackingApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_reference_no' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0, 'Invalid Request.', $error, 'order_invoice', (object) []);
        }

        $orderReference = $request->order_reference_no;

        $model =  Order::where('parcel_nature', 1)->where('order_reference', $orderReference)->first();
        if (!empty($model)) {
            $tracking_history = [];

            // Add booking status
            $booking = [
                'date_time' => date('d/m/Y h:i a', strtotime($model->created_at)),
                'status' => 'PARCEL CREATED AT',
                'status_reason' => null,
            ];
            $tracking_history[] = $booking;

            // Add vendor warehouse status if available
            if (!empty($model->scanOrder->created_at)) {
                $vendor_warehouse = [
                    'date_time' => date('d/m/Y h:i a', strtotime($model->scanOrder->created_at)),
                    'status' => 'PARCEL PICKED FROM ' . $model->vendor->vendor_name . ' WAREHOUSE',
                    'status_reason' => null,
                ];
                $tracking_history[] = $vendor_warehouse;
            }

            // Add AHL warehouse status if available
            if (!empty($model->scanOrder->middle_man_scan_date)) {
                $ahl_warehouse = [
                    'date_time' => date('d/m/Y h:i a', strtotime($model->scanOrder->middle_man_scan_date)),
                    'status' => 'PARCEL ENTERED AT AHL WAREHOUSE',
                    'status_reason' => null,
                ];
                $tracking_history[] = $ahl_warehouse;
            }

            // Add dispatch history
            if (!empty($model->scanOrder->supervisor_scan_date)) {
                $dispatch_history = [];
                foreach ($model->orderReAssigned as $key => $order_assigned) {
                    $dispatch_from_warehouse = [
                        'date_time' => date('d/m/Y h:i a', strtotime($order_assigned->created_at)),
                        'status' => 'PARCEL DISPATCHED FROM AHL WAREHOUSE',
                        'status_reason' => null,
                    ];
                    
                    $rider_cancel = [
                        'date_time' => date('d/m/Y h:i a', strtotime($order_assigned->updated_at)),
                        'status' => 'PARCEL CANCELLED FROM RIDER END',
                        'status_reason' => null,
                    ];
                    
                    $rider_reattempt = [
                        'date_time' => date('d/m/Y h:i a', strtotime($order_assigned->updated_at)),
                        'status' => 'PARCEL REATTEMPT FROM RIDER END',
                        'status_reason' => null,
                    ];

                    $dispatch_history[] = $dispatch_from_warehouse;
                    if (($order_assigned->trip_status_id) == 5) {
                        $dispatch_history[] = $rider_cancel;
                    }
                    if (($order_assigned->trip_status_id) == 6) {
                        $dispatch_history[] = $rider_reattempt;
                    }
                }

                // Add dispatch history at the desired position
                $insert_index = count($tracking_history); // Add after AHL warehouse status
                array_splice($tracking_history, $insert_index, 0, $dispatch_history);
            }

            // Add delivered status if available
            if (!empty($model->orderAssigned->trip_status_id) && $model->orderAssigned->trip_status_id == 4) {
                $delivered_at = [
                    'date_time' => date('d/m/Y h:i a', strtotime($model->orderAssigned->updated_at)),
                    'status' => 'PARCEL DELIVERED AT',
                    'status_reason' => null,
                ];
                $tracking_history[] = $delivered_at;
            }

            // Add cancel status if applicable
            if (in_array($model->order_status, [9, 17, 18])) {
                $cancel_at = [
                    'date_time' => date('d/m/Y h:i a', strtotime($model->updated_at)),
                    'status' => 'PARCEL CANCELLED AT',
                    'status_reason' => null,
                ];
                $tracking_history[] = $cancel_at;
            }

            // Add reattempt status if applicable
            if (in_array($model->order_status, [7, 8, 16])) {
                $reattempt_at = [
                    'date_time' => date('d/m/Y h:i a', strtotime($model->updated_at)),
                    'status' => 'PARCEL RE-ATTEMPT AT',
                    'status_reason' => null,
                ];
                $tracking_history[] = $reattempt_at;
            }

            // Add return in progress status if applicable
            if ($model->order_status == 19) {
                $return_in_progress = [
                    'date_time' => date('d/m/Y h:i a', strtotime($model->updated_at)),
                    'status' => 'PARCEL RETURN TO VENDOR IN-PROGRESS AT',
                    'status_reason' => null,
                ];
                $tracking_history[] = $return_in_progress;
            }

            // Add return to vendor status if applicable
            if ($model->order_status == 10) {
                $return_to_vendor = [
                    'date_time' => date('d/m/Y h:i a', strtotime($model->updated_at)),
                    'status' => 'PARCEL RETURN TO VENDOR AT',
                    'status_reason' => null,
                ];
                $tracking_history[] = $return_to_vendor;
            }

            // Prepare response
            $response = [
                'status' => 1,
                'message' => 'Detail Tracking API',
                'tracking_history' => $tracking_history,
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 0,
                'message' => 'Invalid Order Reference',
                'tracking_history' => [],
            ];

            return response()->json($response);
        }
    }

    public function shiperParcelAdvice(Request $request)
    {
        $order_reference_no = $request->order_reference_no;
        $order = Order::where('order_reference', $order_reference_no)->first();

        if(!empty($order))
        {
            $orderId = $order->id;
            $advise = $request->advise;
            $data = [
                'order_id' => $orderId,
                'advise' => $advise,
                'status' => 1,
            ];

            $addAdvise = ShiperAdviser::create($data);

            $response = [
                'status' => 1,
                'message' => 'Shipper Advise Successfully!',
            ];

        }
        else
        {
            $response = [
                'status' => 0,
                'message' => 'Order Not Found!',
            ];
        }

        return response()->json($response);
    }

}
