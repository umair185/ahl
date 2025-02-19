<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Maatwebsite\Excel\Facades\Excel;

use App\Models\Order;
use App\Models\OrderAssigned;
use App\Models\ScanOrder;
use App\Models\User;
use App\Models\UserCity;
use App\Models\City;
use App\Models\UserDetail;
use App\Models\Template;
use App\Models\ParcelLimit;
use App\Helpers\Helper;
use AHLHelper;
use Illuminate\Support\Facades\DB;

use App\Exports\RiderDispatchExport;
use App\Exports\SpecificSupervisorScanHistory;
use App\Exports\AllSupervisorScanHistory;
use PDF;
use Log;

class SupervisorController extends Controller
{
    public function scanParcelList()
    {
        $breadcrumbs = [
            'name' => 'Scan Parcels', 
        ];

        $authUser = Auth::user();
        $authUserId = $authUser->id;
        $usercity = UserCity::where('user_id',$authUserId)->pluck('city_id');

        $riders = User::whereHas(
            'roles', function($q){
                $q->where('name', 'rider');
            })->whereHas(
                'usercity', function($query) use($usercity){
                    $query->whereIn('city_id',$usercity);
                }
            )
        ->with([
            'userDetail' => function($query){
                //$query->select('id','created_by','user_id','cnic');
                $query->select('id','created_by','user_id','cnic');
            },
        ])
        ->where('status',1)
        ->get();

        $scanParcels = ScanOrder::where('supervisor_id', $authUserId)->pluck('order_id');
        $parcels = Order::whereIn('id',$scanParcels)->where('order_status', 4)->with([
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
                $query->select('id','name');
            },
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'scanOrder' => function($query){
                $query->select('id','order_id','created_at');
            },
            'vendorWeight' => function($query){
                $query->select('id','ahl_weight_id','city_id')->with([
                    'ahlWeight' => function($query){
                        $query->select('id','weight');
                    },
                    'city' => function($query){
                        $query->select('id','name');
                    },
                ]);
            },
        ])
        ->get();
        
    	return view('supervisor/scan-parcel-list',compact('riders','parcels','breadcrumbs'));
    }

    public function checkBySupervisor(Request $request)
    {
    	$validatedData = $request->validate([
            'order_parcel_reference_no' => 'required',
        ]);

        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $responseMessage = "";
        $orderReferencce = $request->order_parcel_reference_no;
        
        $parcel = Order::where('order_reference',$orderReferencce)->whereIn('consignee_city',$usercity)->with([
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
                $query->select('id','name');
            },
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'scanOrder' => function($query){
                $query->select('id','order_id','created_at');
            },
            'vendorWeight' => function($query){
                $query->select('id','ahl_weight_id','city_id')->with([
                    'ahlWeight' => function($query){
                        $query->select('id','weight');
                    },
                    'city' => function($query){
                        $query->select('id','name');
                    },
                ]);
            },
        ])
        ->first();
        

        $authUser = Auth::user();
        $authSupervisorId = $authUser->id;

        //$request->session()->flush();
        $limit_count = 0;
        
        if($parcel){
            if($parcel->hold_status == 0)
            {
                if($parcel->parcel_limit > $parcel->parcel_attempts)
                {
                    if($parcel->order_status == 3 || $parcel->order_status == 8){

                        $limit_count = $parcel->parcel_attempts + 1;
                    
                        Order::where('order_reference',$orderReferencce)->update(['order_status' => 4, 'parcel_attempts' => $limit_count]);

                        $responseMessage = "";
                        $status = 'Success';
                        $message = 'Status Change Successfully';
                        $data = $parcel;

                        $scanOrder = ScanOrder::where('order_id',$parcel->id)->update(['supervisor_id'=>$authSupervisorId,'supervisor_scan_date'=>now()]);                    

                    }elseif($parcel->order_status < 3){
                        $status = 'Before';
                        $message = 'You can not change status before Middle Man';
                        $data = 0;
                        $responseMessage = "";
                    }elseif($parcel->order_status >= 4){
                        $status = 'After';
                        $message = 'Already Scan';
                        $data = 0;
                        $responseMessage = "";
                    }
                }
                else
                {
                    $status = 'Invalid';
                    $message = 'Parcel Limit Reached, Contact Hub Manager!';
                    $data = 0;
                    $responseMessage = "";
                }
            }
            else
            {
                $status = 'Invalid';
                $message = 'This Parcel is on Hold';
                $data = 0;
                $responseMessage = "";
            }
        }else{
        	$status = 'Invalid';
        	$message = 'Invalid Parcel Reference Number or other city parcel';
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

    public function dispatchToRider(Request $request)
    {

        $validatedData = $request->validate([
            'paracels' => 'required',
            'rider_id' => 'required',
        ]);

        $parcelIds = $request->paracels;
        $riderIdNew = $request->rider_id;
        $find_rider = User::where('user_id', $riderIdNew)->first();
        if(!empty($find_rider))
        {
            $riderId = $find_rider->id;
        }

        foreach ($parcelIds as $key => $parcelId) {
            $temp_order_assigned = OrderAssigned::where('order_id', $parcelId)->whereDate('created_at' , now())->get();
            if(!empty($temp_order_assigned))
            {
                foreach($temp_order_assigned as $temp)
                {
                    $temp->update(['status' => 0, 'force_status' => 0]);
                }
            }
            $temp_parcel = Order::where('id', $parcelId)->first();
            $orderAssignData = [
                'vendor_id' => $temp_parcel->vendor_id,
                'order_id' => $temp_parcel->id,
                'rider_id' => $riderId,
                'drop_off_location' => 'NK Hair Saloon, Sector T DHA Phase 2, Lahore, Pakistan',
                'latitude' => '31.47362169999999',
                'longitude' => '74.4021149',
                'trip_status_id' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            Order::find($parcelId)->update(['order_status' => 5,'dispatch_date'=>now()]);
            
            $orderAssign = OrderAssigned::create($orderAssignData);

            $orderReference = explode("#", $temp_parcel->order_reference);
            $number = $temp_parcel->consignee_phone;
            $orderAmount = $temp_parcel->consignment_cod_price;
            $name = $temp_parcel->consignee_first_name. ' '. $temp_parcel->consignee_last_name;
            $rider = User::where('id', $riderId)->first();
            $rider_name = $rider->name;
            $rider_phone = $rider->userDetail ? $rider->userDetail->phone : '03424983850';

            $message = Template::find(2);
            $body = str_replace('{{MEMBER_NAME}}', $name, $message->message);
            $body = str_replace('{{COMPANY_NAME}}', "AHL", $body);
            $body = str_replace('{{ORDER_NUMBER}}', $orderReference[1], $body);
            $body = str_replace('{{ORDER_AMOUNT}}', $orderAmount, $body);
            $body = str_replace('{{RIDER_NAME}}', $rider_name, $body);
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

        $response = [
            'status' => 'success',
            'message' => 'Parcel Assign To Rider',
        ];
        
        return response()->json($response);    
    }

    // Generate PDF
    public function generateDispatchPDF(Request $request) {

        $riderIdNew = $request->rider;
        $find_rider = User::where('user_id', $riderIdNew)->first();
        if(!empty($find_rider))
        {
            $riderId = $find_rider->id;
        }

        $rider = User::select('id','name','user_id')->whereId($riderId)->get()->toArray();
        $riderName = $rider[0]['name'].'-'.$rider[0]['user_id'];
        $title = $riderName.'-'.'Dispatch Parcel List';

        $parcels = explode(',',$request->parcels);
        $parcelIds = $parcels;
        $parcelPdfData = Order::whereIn('id', $parcelIds)->where('order_status', 5)->get();

        $fileName = date('m-d-y').'-'.$riderName.'-dispatch-parcels';
        $date = date('m-d-y');

        $pdf = PDF::loadView('supervisor.rider-dispatch-parcel-pdf', compact('parcelPdfData','title','date','riderName'))->setPaper('a4', 'landscape');
        return $pdf->download($fileName.'.pdf');
    }

    public function requestReattempt()
    {
        $breadcrumbs = [
            'name' => 'Scan Parcels', 
        ];

        $authUser = Auth::user();
        $authUserId = $authUser->id;
        
        $supervisorRiderList = UserDetail::Select('id')->where('created_by',$authUserId)->get();
        
        //pluck only user id
        $riders = User::whereHas(
            'roles', function($q){
                $q->where('name', 'rider');
            })
        //->whereIn('user_detail_id',$supervisorRiderList)
        ->get();

        $riderIds = $riders->pluck('id');
        
        //order status 7 is request for reattempt
        /*$orderReattempts = Order::where(['order_status'=>7])->with([
            'orderAssigned' => function($query){
                $query->select('id','rider_id','vendor_id','order_id','cancel_reason','created_at','updated_at')
                ->with([
                    'rider'=>function($query){
                        $query->select('id','name');
                    }
                ]);
            },
        ])->get();*/

        $assignedOrderReattempts = OrderAssigned::whereIn('rider_id',$riderIds)
        ->with([
            'order',
            'riderVendor' => function($query){
                $query->select('id','vendor_name');
            },
            'rider' => function($query){
                $query->select('id','name');
            },
        ])
        ->where('trip_status_id',5)
        ->where('status',1)
        ->get();

        //dd($orderReattempts);
        return view('supervisor.request-reattempt',compact('breadcrumbs','assignedOrderReattempts','riders')); 
    }

    public function reattempt(Request $request)
    {
        $assignedIds = $request->paracel_assigned_id;
        
        if(is_array($assignedIds)){
            $parcelsAssignedRiderId = $request->parcels_assigned_rider_id;
            $parcelOrderIds = $request->parcel_order_ids;
            //when assigned multiple parcels to rider
            //$orderAssigned = OrderAssigned::whereIn('id',$assignedIds)->update(['status'=>5,'rider_id'=>$parcelsAssignedRiderId,'cancel_reason'=>Null]);
            $orderAssigned = OrderAssigned::whereIn('id',$assignedIds)->update(['status'=>0]);

            //order status 5 is dispatched
            $order = Order::whereIn('id',$parcelOrderIds)->update(['order_status'=>5]);
            $assignedOrderVendorIds = OrderAssigned::whereIn('id',$assignedIds)->get()->pluck('vendor_id');
            
            foreach ($assignedIds as $key => $assigneId) {
                $newAssignData[] = [
                    'vendor_id' => $assignedOrderVendorIds[$key],
                    'order_id' => $parcelOrderIds[$key],
                    'rider_id' => $parcelsAssignedRiderId,
                    'drop_off_location' => 'NK Hair Saloon, Sector T DHA Phase 2, Lahore, Pakistan',
                    'latitude' => '31.47362169999999',
                    'longitude' => '74.4021149',
                    'trip_status_id' => 1,
                    'status' => 1,
                ];
            }

            OrderAssigned::insert($newAssignData);

        }else{

            $orderAssigned = OrderAssigned::find($assignedIds);
            //when parcel again assigned to same rider
            $orderAssigned->status = 0;
            $orderAssigned->save();
            //order status 5 is dispatched
            Order::where('id',$orderAssigned->order_id)->update(['order_status'=>5]);

            $newAssignData = [
                'vendor_id' => $orderAssigned->vendor_id,
                'order_id' => $orderAssigned->order_id,
                'rider_id' => $orderAssigned->rider_id,
                'drop_off_location' => 'NK Hair Saloon, Sector T DHA Phase 2, Lahore, Pakistan',
                'latitude' => '31.47362169999999',
                'longitude' => '74.4021149',
                'trip_status_id' => 1,
                'status' => 1,
            ];

            OrderAssigned::create($newAssignData);

        }        

        $response = [
            'status' => 1,
            'message' => 'Order Assigned For Reattempt',
        ];

        return response()->json($response);  

    }

    public function cancelledParcel()
    {
        $breadcrumbs = [
            'name' => 'Cancelled Parcels', 
        ];

        $authUser = Auth::user();
        $authUserId = $authUser->id;
        
        $cancelledOrder = Order::with([
            'vendor',
            'orderAssigned' => function($query){
                $query->with([
                    'orderDecline' => function($query){
                        $query->select('id','order_assigned_id','order_decline_status_id','order_decline_reason_id','additional_note','image')->with([
                            'orderDeclineReason' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ]);
            },
        ])
        ->where('order_status',9)
        ->get();

        //dd($cancelledOrder);

        return view('supervisor.cancelled-parcel',compact('breadcrumbs','cancelledOrder')); 
    }

    public function cancelledParcelReattempt(Request $request)
    {
        $parcelIds = $request->paracel_id;
        
        if(is_array($parcelIds)){
            //order status 2 is pickup
            $order = Order::whereIn('id',$parcelIds)->update(['order_status'=>2]);

        }else{
            //order status 2 is pickup
            Order::where('id',$parcelIds)->update(['order_status'=>2]);
        }        

        $response = [
            'status' => 1,
            'message' => 'Order Send For Reattempt',
        ];

        return response()->json($response);
    }

    public function forceStatusChange(Request $request)
    {
        $orderReferencce = $request->order_reference;
        $statusId = $request->status_id;

        $find_order_check = Order::where('order_reference', $orderReferencce)->first();
        $assigned_order_check = OrderAssigned::where('order_id', $find_order_check->id)->orderBy('id','DESC')->first();
        if(($assigned_order_check->trip_status_id == 1 || $assigned_order_check->trip_status_id == 2 || $assigned_order_check->trip_status_id == 3 || $assigned_order_check->trip_status_id == 7) && $assigned_order_check->status == 1)
        {
            $status = 'danger';
            $message = 'You can not change parcel status before closing it from Application First';
            $data = $assigned_order_check;
            
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ];

            return response()->json($response);
        }

        if(empty($statusId))
        {
            $status = 'danger';
            $message = 'Please Select Order Status First';
            $data = $assigned_order_check;

            $response = [
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ];

            return response()->json($response);
        }

        $order = Order::where('order_reference',$orderReferencce)->update(['order_status'=>$statusId]);
        
        $find_order = Order::where('order_reference', $orderReferencce)->first();
        $find_assigned_order = OrderAssigned::where('order_id', $find_order->id)->get();

        if(!empty($find_assigned_order))
        {
            foreach($find_assigned_order as $find)
            {
                $find->update(['status' => 0]);
            }
        }

        $assigned_order = OrderAssigned::where('order_id', $find_order->id)->orderBy('id','DESC')->first();
        if(!empty($assigned_order))
        {   
            if($find_order->order_status == 6)
            {
                $assigned_order->update(['status' => 1,'trip_status_id' => 4,'force_status' => 1]);
                
            }
            elseif($find_order->order_status == 7)
            {
                if($find_order->parcel_attempts == $find_order->parcel_limit)
                {
                    $status = 'danger';
                    $message = 'Parcel Limit Reached, Now You can just change the status to Cancel, Delivered or Contact Hub Manager';
                    $data = $assigned_order;
                    
                    $response = [
                        'status' => $status,
                        'message' => $message,
                        'data' => $data,
                    ];

                    return response()->json($response);
                }
                
                $assigned_order->update(['status' => 0,'trip_status_id' => 6,'force_status' => 1]);
                $assigned_order->update(['reattempt_by' => Auth::user()->id]);
            }
            elseif($find_order->order_status == 2)
            {
                $assigned_order->update(['status' => 0,'trip_status_id' => 7,'force_status' => 1]);
                //add 7 status in Live Database
            }
            elseif($find_order->order_status == 18)
            {
                $assigned_order->update(['status' => 0,'trip_status_id' => 5,'force_status' => 1]);
                $assigned_order->update(['reattempt_by' => Auth::user()->id]);
            }
            else
            {
                $assigned_order->update(['status' => 0,'trip_status_id' => 5,'force_status' => 1]);
            }
            
            $status = 'success';
            $message = 'Force Status Change Successfully!';
            $data = $assigned_order;
        }


        $response = [
        	'status' => $status,
        	'message' => $message,
        	'data' => $data,
        ];

        return response()->json($response);
    }

    public function riderParcels(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Rider Parcels List', 
        ];

        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $staffList = User::whereHas(
            'roles', function($q){
                $q->whereIn('id', [7]);
            }
        )->whereHas(
            'usercity', function($query) use($usercity){
                $query->whereIn('city_id',$usercity);
            }
        )
        ->with('userDetail')
        ->where('status',1)
        ->get();

        if($request->staff_id && $request->date){
            //supervisor and cashier comman
            $staffId = $request->staff_id;
            $requestDate = $request->date;

            $orderAssigned = OrderAssigned::whereHas('order',function($query) use($usercity){
                $query->whereIn('consignee_city',$usercity);
            })->select('id','vendor_id','order_id','created_at','rider_id','status')
            ->whereDate('created_at',$requestDate)
            ->where(['rider_id'=>$staffId])
            ->with([
                'order' => function($query){
                    $query->select('id','order_reference');
                   
                },
                'riderVendor' => function($query){
                    $query->select('id','vendor_name');
                }
            ])
            ->get();

        }else{
            $orderAssigned = [];
        }

        return view('supervisor.rider-parcel-list',compact('staffList','orderAssigned','breadcrumbs'));
    }
    
    public function supervisorScanHistory(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        if(Auth::user()->hasAnyRole('supervisor'))
        {
            if($request->to && $request->from)
            {
                $scan_orders = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at','<=',$to)->whereHas('orderDetail',function($query) use($usercity){
                        $query->where('consignee_city',$usercity);
                    })->whereHas('scanOrder', function($q) use($userId){
                        $q->where('supervisor_id', $userId);
                    })->with('orderDetail')->get();
            }
            else
            {
                $scan_orders = OrderAssigned::whereHas('orderDetail',function($query) use($usercity){
                    $query->whereIn('consignee_city',$usercity);
                }
                )->whereHas('scanOrder', function($q) use($userId){
                    $q->where('supervisor_id', $userId);
                })->with('orderDetail')->get();
            }
        }
        else
        {
            if($request->to && $request->from)
            {
                $scan_orders = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at','<=',$to)->whereHas('orderDetail',function($query) use($usercity){
                        $query->where('consignee_city',$usercity);
                    })->with('orderDetail')->get();
            }
            else
            {
                $scan_orders = OrderAssigned::whereHas('orderDetail',function($query) use($usercity){
                    $query->whereIn('consignee_city',$usercity);
                }
                )->with('orderDetail')->get();
            }
        }
        
        return view('supervisor.scan-history',compact('scan_orders'));
    }

    public function supervisorScanHistoryDownload(Request $request) 
    {
        if (isset($request->from) && isset($request->to)) {
            $file_name = "supervisor_scan_history_" . date("Y_m_d_h_i_s") . ".xlsx";
            return Excel::download(new SpecificSupervisorScanHistory($request->from, $request->to), $file_name);
        } else {
            $file_name = "supervisor_scan_history_till_" . date("Y_m_d_h_i_s") . ".xlsx";
            return Excel::download(new AllSupervisorScanHistory, $file_name);
        }
    }
    
    public function riderParcelsReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Rider Parcels Report', 
        ];
        
        if($request->date && $request->to)
        {
            $requestDate = $request->date;
            $requestTo = $request->to;
        }
        else
        {
            $requestDate = \Carbon\Carbon::now();
            $requestTo = \Carbon\Carbon::now();
        }

        if(Auth::user()->hasAnyRole('admin'))
        {
            $userId = Auth::user()->id;

            $staffList = User::whereHas(
                'roles', function($q){
                    $q->whereIn('id', [7]);
                }
            )
            ->with('userDetail')
            ->with([
                'totalOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1);
                },
                'deliveredOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->where('status',1)->where('force_status',1);
                },
                'cancelOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->where('status',0)->where('force_status',1);
                },
                'returnOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->where('status',0)->where('force_status',1);
                },
                'forceFulOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->where('status',1)->whereIn('trip_status_id', [1,2,3]);
                },
                'totalOrdersSum' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)
                    ->distinct()
                    ->count(DB::raw('DATE(created_at)'));
                }
            ])
            ->get();
        }
        else
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

            $staffList = User::whereHas(
                'roles', function($q){
                    $q->whereIn('id', [7]);
                }
            )->whereHas('usercity',function($query) use($usercity){
                $query->whereIn('city_id',$usercity);
            })
            ->with('userDetail')
            ->with([
                'totalOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1);
                },
                'deliveredOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->where('status',1)->where('force_status',1);
                },
                'cancelOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->where('status',0)->where('force_status',1);
                },
                'returnOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->where('status',0)->where('force_status',1);
                },
                'forceFulOrders' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->where('status',1)->whereIn('trip_status_id', [1,2,3]);
                },
                'totalOrdersSum' => function($query) use($requestDate, $requestTo){
                    $query->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)
                    ->distinct()
                    ->count(DB::raw('DATE(created_at)'));
                }
            ])
            ->get();

            // $workingDays = OrderAssigned::where('rider_id', 840)
            // ->whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)
            // ->distinct()
            // ->count(DB::raw('DATE(created_at)'));
        }

        return view('supervisor.rider-detail',compact('staffList','breadcrumbs','requestDate','requestTo'));
    }

    public function riderLoadSheet(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Rider Load Sheet', 
        ];

        $requestDate = $request->from;
        $requestTo = $request->to;
        $rider_id = $request->rider_id;

        $dispatch_orders = OrderAssigned::whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->where('rider_id', $rider_id)->get();

        $pickup_orders = OrderAssigned::whereDate('created_at','>=', $requestDate)->whereDate('created_at','<=',$requestTo)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->where('rider_id', $rider_id)->pluck('order_id');

        $order_details = Order::whereIn('id', $pickup_orders)->sum('consignment_cod_price');

        return view('supervisor/load-sheet',compact('dispatch_orders','breadcrumbs', 'order_details'));
    }

    public function bulkStatusView(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Rider Parcels List', 
        ];

        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $staffList = User::whereHas(
            'roles', function($q){
                $q->whereIn('id', [7]);
            }
        )->whereHas('usercity', function($query) use($usercity){
            $query->whereIn('city_id',$usercity);
        })
        ->with('userDetail')
        ->where('status',1)->get();

        if($request->date && $request->staff_id)
        {
            $staffId = $request->staff_id;
            $requestDate = $request->date;

            $orderAssignedIds = OrderAssigned::select('id','order_id','created_at','rider_id','status')
            ->whereDate('created_at',$requestDate)
            ->where(['rider_id'=>$staffId])
            ->get()
            ->pluck('order_id');
            $defaultOrders = Order::whereIn('id', $orderAssignedIds)->whereIn('consignee_city',$usercity)->get();
        }
        else
        {
            $defaultOrders = [];
        }

        return view('supervisor.change-bulk-status', compact('staffList','defaultOrders','breadcrumbs'));
    }

    public function markDelivered(Request $request)
    {
        $parcels = $request->paracels;
        foreach($parcels as $parcel)
        {
            $order = Order::where('id', $parcel)->update(['order_status'=> 6]);
            $find_order = Order::where('id', $parcel)->first();
            $find_assigned_order = OrderAssigned::where('order_id', $find_order->id)->get();
            if(!empty($find_assigned_order))
            {
                foreach($find_assigned_order as $find)
                {
                    $find->update(['status' => 0]);
                }
            }
            $assigned_order = OrderAssigned::where('order_id', $find_order->id)->orderBy('id','DESC')->first();
            if(!empty($assigned_order))
            {
                $assigned_order->update(['status' => 1,'trip_status_id' => 4, 'force_status' => 1]);
            }
        }
    }

    public function holdStatusForm($id){

        $order = Order::with('vendor')->find($id);
        return view('reports.hold-status',compact('order'));
    }

    public function updateholdStatus(Request $request){
        $id = $request->order_id;
        $status = $request->select_hold;
        $reason = $request->reason;

        $checkStatus = Order::where('id',$id)->update(['hold_status'=>$status,'hold_reason' => $reason]);

        if($checkStatus){
            return redirect()->back();
        }

    }

    public function scanReverseParcelList()
    {
        $breadcrumbs = [
            'name' => 'Scan Reverse Pickup Parcels', 
        ];

        $authUser = Auth::user();
        $authUserId = $authUser->id;
        $usercity = UserCity::where('user_id',$authUserId)->pluck('city_id');

        $riders = User::whereHas(
            'roles', function($q){
                $q->where('name', 'rider');
            })->whereHas(
                'usercity', function($query) use($usercity){
                    $query->whereIn('city_id',$usercity);
                }
            )
        ->with([
            'userDetail' => function($query){
                $query->select('id','created_by','user_id','cnic');
            },
        ])
        ->where('status',1)
        ->get();

        $scanParcels = ScanOrder::where('supervisor_id', $authUserId)->pluck('order_id');
        $parcels = Order::whereIn('id',$scanParcels)->where('order_status', 4)->with([
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
                $query->select('id','name');
            },
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'scanOrder' => function($query){
                $query->select('id','order_id','created_at');
            },
            'vendorWeight' => function($query){
                $query->select('id','ahl_weight_id','city_id')->with([
                    'ahlWeight' => function($query){
                        $query->select('id','weight');
                    },
                    'city' => function($query){
                        $query->select('id','name');
                    },
                ]);
            },
        ])
        ->get();

        
        return view('admin/reverse_pickup/scan-dispatch-reverse-parcel-list',compact('riders','parcels','breadcrumbs'));
    }

    public function checkByFirstman(Request $request)
    {
        $validatedData = $request->validate([
            'order_parcel_reference_no' => 'required',
        ]);

        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $responseMessage = "";
        $orderReferencce = $request->order_parcel_reference_no;
        
        $parcel = Order::where('order_reference',$orderReferencce)->with([
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
                $query->select('id','name');
            },
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'scanOrder' => function($query){
                $query->select('id','order_id','created_at');
            },
            'vendorWeight' => function($query){
                $query->select('id','ahl_weight_id','city_id')->with([
                    'ahlWeight' => function($query){
                        $query->select('id','weight');
                    },
                    'city' => function($query){
                        $query->select('id','name');
                    },
                ]);
            },
        ])
        ->first();
        

        $authUser = Auth::user();
        $authSupervisorId = $authUser->id;
        
        if($parcel){
            if($parcel->hold_status == 0)
            {
                if($parcel->order_status == 3 || $parcel->order_status == 8){
                    
                    Order::where('order_reference',$orderReferencce)->update(['order_status' => 4]);

                    $responseMessage = "";
                    $status = 'Success';
                    $message = 'Status Change Successfully';
                    $data = $parcel;

                    $scanOrder = ScanOrder::where('order_id',$parcel->id)->update(['supervisor_id'=>$authSupervisorId,'supervisor_scan_date'=>now()]);

                }elseif($parcel->order_status < 3){
                    $status = 'Before';
                    $message = 'You can not change status before Middle Man';
                    $data = 0;
                    $responseMessage = "";
                }elseif($parcel->order_status >= 4){
                    $status = 'After';
                    $message = 'Already Scan';
                    $data = 0;
                    $responseMessage = "";
                }
            }
            else
            {
                $status = 'Invalid';
                $message = 'This Parcel is on Hold';
                $data = 0;
                $responseMessage = "";
            }
        }else{
            $status = 'Invalid';
            $message = 'Invalid Parcel Reference Number or other city parcel';
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

    public function reverseDispatchToRider(Request $request)
    {

        $validatedData = $request->validate([
            'paracels' => 'required',
            'rider_id' => 'required',
        ]);

        $parcelIds = $request->paracels;
        $riderId = $request->rider_id;

        foreach ($parcelIds as $key => $parcelId) {
            $temp_order_assigned = OrderAssigned::where('order_id', $parcelId)->whereDate('created_at' , now())->get();
            if(!empty($temp_order_assigned))
            {
                foreach($temp_order_assigned as $temp)
                {
                    $temp->update(['force_status' => 0, 'status' => 0]);
                }
            }
            $temp_parcel = Order::where('id', $parcelId)->first();
            $orderAssignData = [
                'vendor_id' => $temp_parcel->vendor_id,
                'order_id' => $temp_parcel->id,
                'rider_id' => $riderId,
                'drop_off_location' => 'NK Hair Saloon, Sector T DHA Phase 2, Lahore, Pakistan',
                'latitude' => '31.47362169999999',
                'longitude' => '74.4021149',
                'trip_status_id' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            Order::find($parcelId)->update(['order_status' => 5,'dispatch_date'=>now()]);
            
            $orderAssign = OrderAssigned::create($orderAssignData);
            
        }

        $response = [
            'status' => 'success',
            'message' => 'Parcel Assign To Rider',
        ];
        
        return response()->json($response);    
    }

    // Generate PDF
    public function generateReverseDispatchPDF(Request $request) {

        $riderId = $request->rider;
        $rider = User::select('id','name','user_id')->whereId($riderId)->get()->toArray();
        $riderName = $rider[0]['name'].'-'.$rider[0]['user_id'];
        $title = $riderName.'-'.'Reverse Dispatch Parcel List';
        
        $parcels = explode(',',$request->parcels);
        $parcelIds = $parcels;
        $parcelPdfData = Order::whereIn('id', $parcelIds)->where('order_status', 5)->where('parcel_nature',2)->get();

        $fileName = date('m-d-y').'-'.$riderName.'-dispatch-parcels';
        $date = date('m-d-y');

        $pdf = PDF::loadView('admin/reverse_pickup/reverse-rider-pdf', compact('parcelPdfData','title','date','riderName'))->setPaper('a4', 'landscape');
        return $pdf->download($fileName.'.pdf');
    }
}
