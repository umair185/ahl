<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Order;
use App\Models\OrderAssigned;
//use App\Models\Reason;
use App\Models\OrderDelivered;
use App\Models\ConsigneeRelation;
use App\Models\RiderCashCollection;
use App\Models\OrderDecline;
use App\Models\OrderDeclineStatus;
use App\Models\OrderDeclineReason;

use App\Helpers\ImageHelper;
use Helper;
use Illuminate\Support\Facades\Http;

class RiderController extends Controller
{
    public function checkOrderStart()
    {
        $authUser = Auth::user();
        $authUserId = Auth::user()->id;

        $orderAssigned = OrderAssigned::whereIn('trip_status_id',[2,3])
        ->with([
            'order' => function($query){
                $query->select('id','order_reference','consignee_first_name','consignee_last_name','consignee_address','consignee_email','consignee_phone','consignment_cod_price');
            },
        ])
        ->where('status',1)
        ->where('rider_id',$authUserId)
        ->first();

        if($orderAssigned){
            return ResponseHelper::apiResponse(2,'One Order Already Start !',[],'order_delivery',$orderAssigned);
        }
        
        return ResponseHelper::apiResponse(0,'No Order Start!',[],'order_delivery',$orderAssigned);
        
    }

    public function orderStatuses()
    {
    	$authUser = Auth::user();
    	$authUserId = $authUser->id;

    	//$totalOrder = OrderAssigned::where('rider_id',$authUserId)->whereDate('created_at', now())->count();
        //status 1 is active
        //status 0 is not active | undelivered
    	$todayOrder = OrderAssigned::where(['rider_id'=>$authUserId])->where('force_status', '!=', 0)->whereDate('created_at', now())->count();
    	$pendingOrder = OrderAssigned::where(['rider_id'=>$authUserId,'status'=>1])->whereIn('trip_status_id',[1,2,3])->whereDate('created_at', now())->count();
    	$deliverOrder = OrderAssigned::where(['rider_id'=>$authUserId,'trip_status_id'=> 4,'status'=>1])->whereDate('created_at', now())->count();
    	$returnOrder = OrderAssigned::where(['rider_id'=>$authUserId,'status'=>0])->whereIn('trip_status_id', [5,6])->whereDate('created_at', now())->count();

		$orderStatus = [
			//'totalOrder' => $totalOrder,
			'todayOrder' => $todayOrder,
			'pendingOrder' => $pendingOrder,
			'deliverOrder' => $deliverOrder,
			'returnOrder' => $returnOrder,
		];
	
		return ResponseHelper::apiResponse(1,'Rider Order Statuses!',[],'rider_report',$orderStatus);
    }

    public function orders(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'order_status' => 'required',
            'order_filter' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_status',(object) []);
        }

        $authUser = Auth::user();
    	$authUserId = $authUser->id;
    	$orderStatus = $request->order_status;
        $orderFilter = $request->order_filter;

    	if($orderStatus == 1){
    		$status = [1,2,3];//pending
            $whereActiveStatus = 1;
    	}

    	if($orderStatus == 4){
    		$status = [4];//deliver
            $whereActiveStatus = 1;
    	}

    	if($orderStatus == 5){
    		$status = [5];//return
            $whereActiveStatus = 0;
    	}
        

        if($orderFilter == 1){
            $orderTasks = OrderAssigned::where(['rider_id'=>$authUserId])->with([
                'order' => function($query){
                    $query->select('id','order_reference','consignee_first_name','consignee_last_name','consignee_address','consignee_email','consignee_phone','consignment_cod_price');
                },
            ])
            ->whereIn('trip_status_id',$status)
            ->whereDate('created_at', now())
            ->where('status',$whereActiveStatus)
            ->get();    
        }else{
        	$orderTasks = OrderAssigned::where(['rider_id'=>$authUserId])->with([
    			'order' => function($query){
    				$query->select('id','order_reference','consignee_first_name','consignee_last_name','consignee_address','consignee_email','consignee_phone','consignment_cod_price');
    			},
    		])
    		->whereIn('trip_status_id',$status)
            ->where('status',$whereActiveStatus)
    		->get();
        }

		return ResponseHelper::apiResponse(1,'Rider Order Tasks!',[],'rider_order',$orderTasks);
    }

    public function orderDelivery(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'order_assigned_id' => 'required',
            'order_status' => 'required',

            //status 3 delivered
            'amount' => 'required_if:order_status,3',
            'consignee_relation_id' => 'required_if:order_status,3',
            //consignee relation id 14 is other
            'other_relation' => 'required_if:consignee_relation_id,14',
            'receiver_name' => 'required_if:order_status,3',
            //'cnic' => 'required_if:order_status,3',
            //'comment' => 'required_if:order_status,3',
            'cnic' => 'nullable',
            'comment' => 'nullable',
            'signature' => 'required_if:order_status,3',
            'location_picture' => 'required_if:order_status,3',

            //status 5 undelivered
            //'reason' => 'required_if:order_status,5',
            //'reason_id' => 'required_if:order_status,5',
            //'sub_reason_id' => 'required_if:order_status,5',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_delivery',(object) []);
        }
        
        $authUser = Auth::user();
    	$authUserId = $authUser->id;
    	$orderAssignedId = $request->order_assigned_id;
    	$orderStatus = $request->order_status;
        //$orderReasonId = $request->reason_id;
        //$orderSubReasonId = $request->sub_reason_id;

        $orderAssigned = OrderAssigned::find($orderAssignedId);
        $orderId = $orderAssigned->order_id;

        $alreadyStart = OrderAssigned::whereIn('trip_status_id',[2,3])
        ->with([
            'order' => function($query){
                $query->select('id','order_reference','consignee_first_name','consignee_last_name','consignee_address','consignee_email','consignee_phone','consignment_cod_price');
            },
        ])
        ->where('status',1)
        ->whereNotIn('id',[$orderAssignedId])
        ->where('rider_id',$authUserId)
        ->first();

        if($alreadyStart){
            return ResponseHelper::apiResponse(2,'One Order Already Start',[],'order_delivery',$alreadyStart);
        }

        try {

            //Transaction
            DB::beginTransaction();

            if($orderStatus == 3){

                $signature = $request->file('signature');
                $locationPicture = $request->file('location_picture');

                $signature = [
                    'requestFileImage'=> $signature,
                    'databaseAttrName'=> 'signature',
                    'desPath'=> 'uploads/order_delivered',
                    //'desPath'=> 'public/profile',
                    'modelName'=> 'App\Models\OrderDelivered',
                    //'id'=> $empId,
                    'order_id'=> $orderId,
                ];

                $locationPicture = [
                    'requestFileImage'=> $locationPicture,
                    'databaseAttrName'=> 'location_picture',
                    'desPath'=> 'uploads/order_delivered',
                    //'desPath'=> 'public/profile',
                    'modelName'=> 'App\Models\OrderDelivered',
                    //'id'=> $empId,
                    'order_id'=> $orderId,
                ];

                $signatureImage = ImageHelper::publicImage($signature);
                $locationPictureImage = ImageHelper::publicImage($locationPicture);

                $order = OrderAssigned::where('order_id',$orderId)->first();

                $delivered = [
                    'order_assigned_id' => $orderAssignedId,
                    'amount' => $request->amount,
                    'consignee_relation_id' => $request->consignee_relation_id,
                    //consignee relation id 14 is other
                    'other_relation' => $request->other_relation,
                    'receiver_name' => $request->receiver_name,
                    'cnic' => $request->cnic,
                    'comment' => $request->comment,
                    'signature' => $signatureImage,
                    'location_picture' => $locationPictureImage,
                ];
                OrderDelivered::create($delivered);
            }

            /*if($orderStatus == 5){
                $requestReason = $request->reason;
                //$requestReason = json_decode($request->reason, true);
                $orderDelivery = OrderAssigned::where(['order_id'=>$orderId,'rider_id'=>$authUserId])
                ->update(['cancel_reason' => $requestReason,'status'=>0]);
            }*/

            switch ($orderStatus) {
                case 2:
                    $message = 'Order Start';
                    break;

                case 3:
                    $message = 'Cash Collect';//message change to order delivered to Cash Collect
                    break;
                case 4:
                    $message = 'Order Delivered';//message change to Cash Collect to order delivered 
                    //order status is delivered
                    Order::where('id',$orderId)->update(['order_status' => 6]);
                    break;
                /*case 5:
                    $message = 'Order Cancelled';
                    //status 7 is request for reattempte
                    Order::where('id',$orderId)->update(['order_status'=>7]);
                    break;*/
                

                default:
                    # code...
                    break;
            }

            $orderDelivery = OrderAssigned::where(['order_id'=>$orderId,'rider_id'=>$authUserId])
                ->update(['trip_status_id' => $orderStatus]);

            DB::commit();

            return ResponseHelper::apiResponse(1,$message,[],'order_delivery',(object) []);
            // all good

        } catch (Throwable $e) {
            DB::rollback();
            return ResponseHelper::apiResponse(1,'Transaction Roll Back, Beacuse of Some issue',[],'order_delivery',(object) []);
            report($e);
            //return false;
        }


    }

    public function orderDecline(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'order_assigned_id' => 'required',
            'order_decline_status_id' => 'required',
            'order_decline_reason_id' => 'required',
            'additional_note' => 'required',
            'image' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'order_delivery',(object) []);
        }
        
        //dump($request->all());
        //Log::info($request->all());
        
        $orderId = $request->order_id;
        $orderAssignedId = $request->order_assigned_id;
        $orderDeclineStatusId = $request->order_decline_status_id;
        $orderDeclineReasonId = $request->order_decline_reason_id;
        $orderDeclineNote = $request->additional_note;

        $authUser = Auth::user();
        $authUserId = $authUser->id;
        
        $image = $request->file('image');
        $locationPicture = [
            'requestFileImage'=> $image,
            'databaseAttrName'=> 'image',
            'desPath'=> 'uploads/order_decline',
            //'desPath'=> 'public/profile',
            'modelName'=> 'App\Models\OrderDecline',
            //'id'=> $empId,
            'order_id'=> $orderAssignedId,
        ];

        $locationPicture = ImageHelper::publicImage($locationPicture);

        $orderDecline = [
            'order_assigned_id' => $orderAssignedId,
            'order_decline_status_id' => $orderDeclineStatusId,
            'order_decline_reason_id' => $orderDeclineReasonId,
            'additional_note' => $orderDeclineNote,
            'image' => $locationPicture,
        ];
        
        //Log::info($orderDecline);
        //dd();
        $get_order_number = Order::where('id', $orderId)->first();
        $number = $get_order_number->consignee_phone;

        $check_order_status = OrderAssigned::where('id', $orderAssignedId)->where('rider_id', $authUserId)->whereIn('trip_status_id', [2,3])->where('status',1)->first();
        if(!empty($check_order_status))
        {
            try
            {            
                //Transaction
                DB::beginTransaction();
            
                //order assigned
                // $orderDelivery = OrderAssigned::where(['id'=>$orderAssignedId,'rider_id'=>$authUserId])
                //     ->update(['trip_status_id'=>5,'status'=>0]);
                if($orderDeclineStatusId == 1){
                    $last_four = substr ($number, -10);
                    $response = Http::get('https://voicegateway.its.com.pk/api?ApiKey=382D4D8914FCDA04C5912C6D11D0682E&Recipient='.$last_four.'&CampId=480&UniqueId=123456789');

                    $data = $response->json();
                    $a = response()->json($data);
                    // if($a->getData()->ErrorCode == "401")
                    // {
                    //     $ss = Null;
                    // }
                    // else
                    // {
                        $ss = $a->getData()->CdrID;
                    // }

                    $orderDelivery = OrderAssigned::where(['id'=>$orderAssignedId,'rider_id'=>$authUserId])->orderBy('id', 'DESC')->update(['trip_status_id'=>5,'status'=>0, 'cdrid' => $ss, 'ivr_value' => 480]);
                }  else {
                    $last_four = substr ($number, -10);
                    $response = Http::get('https://voicegateway.its.com.pk/api?ApiKey=382D4D8914FCDA04C5912C6D11D0682E&Recipient='.$last_four.'&CampId=479&UniqueId=123456789');

                    $data = $response->json();
                    $a = response()->json($data);
                    // if($a->getData()->ErrorCode == "401")
                    // {
                    //     $ss = Null;
                    // }
                    // else
                    // {
                        $ss = $a->getData()->CdrID;
                    // }

                    $orderDelivery = OrderAssigned::where(['id'=>$orderAssignedId,'rider_id'=>$authUserId])->orderBy('id', 'DESC')->update(['trip_status_id'=>6,'status'=>0, 'cdrid' => $ss, 'ivr_value' => 479]);
                }

                if($orderDeclineStatusId == 1){
                    //cancelled
                    //status 17 is cancelled by rider
                    //add status 17 in Live Database
                    $orderStatus = 17;
                }else{
                    //reattempt
                    //status 7 is request for reattempte
                    //status 16 is reattempt by rider
                    $orderStatus = 16;
                }

                Order::where('id',$orderId)->update(['order_status'=>$orderStatus]);

                //order create
                OrderDecline::create($orderDecline);
                //dd('clear');

                DB::commit();
                // all good
            } catch (\Exception $e) {
                DB::rollback();
                return ResponseHelper::apiResponse(1,'Some Error Try Again',[],'order_decline',(object) []);
                // something went wrong
            }

            return ResponseHelper::apiResponse(1,'Order Decline',[],'order_decline',(object) []);
        }
        else
        {
            return ResponseHelper::apiResponse(1,'Order Already Changed, Please restart your App',[],'order_decline',(object) []);
        }
    }

    public function riderCommission()
    {
        $authRider = Auth::user(); 
        $riderCommission = Helper::staffCommission($authRider);

        return ResponseHelper::apiResponse(1,'Rider Commissino',[],'rider_commission',$riderCommission);

    }

    /*public function undeliveredReasons()
    {
        $reasons = Reason::with([
            'subReason' => function($query){
                $query->select('id','reason_id','name');               
            }
        ])->get();

        return ResponseHelper::apiResponse(1,'Undelivered Reasons',[],'undelivered_reason',$reasons);
    }*/

    public function orderDeclineStatuses()
    {
        $statuses = OrderDeclineStatus::all();

        return ResponseHelper::apiResponse(1,'Order Decline Status',[],'order_decline_statuses',$statuses);
    }

    public function orderDeclineReasons()
    {
        $reasons = OrderDeclineReason::all();

        return ResponseHelper::apiResponse(1,'Order Decline Reasons',[],'order_decline_reasons',$reasons);
    }

    public function consigneeRelation()
    {
        $relations = ConsigneeRelation::all();

        return ResponseHelper::apiResponse(1,'Consignee Relation',[],'consignee_relation',$relations);
    }

    public function history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors();
            return ResponseHelper::apiResponse(0,'Invalid Request.',$error,'rider_history',(object) []);
        }

        $date = $request->date;
        $authUser = Auth::user();
        $authUserId = $authUser->id;

        $orderAssignedHistory = OrderAssigned::where('rider_id',$authUserId)
        ->with([
            'order'=> function($query){
                $query->select('id','order_reference','consignee_first_name','consignee_last_name','consignee_address','consignee_email','consignee_phone','consignment_cod_price');
            },
            'riderVendor'=> function($query){
                $query->select('id','vendor_name');
            },
            'tripStatus'=> function($query){
                $query->select('id','description');
            },
            'orderDelivery' => function($query){
                $query->select('id','order_assigned_id','amount','consignee_relation_id','other_relation','receiver_name','cnic','comment','signature','location_picture','created_at');
            },
        ])
        ->whereDate('created_at', $date)
        ->whereIn('trip_status_id',[3,4,5])
        ->get();

        return ResponseHelper::apiResponse(1,'Rider History',[],'rider_history',$orderAssignedHistory);
    }

    public function wallet(Request $request)
    {
        $authUser = Auth::user();
        $authUserId = $authUser->id;

        $riderWallet = Helper::riderCashCollection($authUser);

        return ResponseHelper::apiResponse(1,'Rider Wallet',[],'rider_wallet',$riderWallet);
    }
    
    public function walletNew(Request $request)
    {
        $authUser = Auth::user();
        $authUserId = $authUser->id;

        $todayOrder = OrderAssigned::where(['rider_id'=>$authUserId])->whereDate('created_at', now())->count();
        $remaingOrderremaingOrder = OrderAssigned::where(['rider_id'=>$authUserId])->whereIn('trip_status_id',[1,2])->where('status',1)->count();
        $orders = OrderAssigned::where('rider_id', $authUserId)->where('trip_status_id',4)->where('status',1)->pluck('order_id');
        $initial_sum = 0;
        foreach($orders as $order)
        {
            $order_details_arr = Order::where('id', $order)->where('order_status', 6)->where('consignment_order_type', 1)->sum('consignment_cod_price');
            $initial_sum = $initial_sum + $order_details_arr;
        }
        $final_initial_sum = round($initial_sum);

        $totalCollectCashFromRider = RiderCashCollection::where('rider_id',$authUserId)->sum('amount');
        
        $remainingCash = $final_initial_sum-$totalCollectCashFromRider;
        $final_remainingCash = round($remainingCash);
        
        $data = [
            'todayOrder' => $todayOrder,
            'remaingOrder' => $remaingOrderremaingOrder,
            'totalCashByRider' => $final_initial_sum,
            'totalCollectCashFromRider' => $totalCollectCashFromRider,
            'remainingCash' => $final_remainingCash,
        ];
        
        $response = [
            'status' => 1,
            'method' => $request->route()->getActionMethod(),
            'message' => 'amount Fetched',
            'rider_wallet' => $data,
            ];
        
        return response()->json($response);
    }
}
