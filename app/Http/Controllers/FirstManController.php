<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Order;
use App\Models\WarehouseLocation;
use App\Models\VendorWeight;
use App\Models\AhlWeight;
use App\Models\PickupRequest;
use App\Models\Packing;
use App\Models\OrderType;
use App\Models\ShiperAdviser;
use App\Models\Vendor;
use App\Models\UserCity;
use App\Models\VendorTiming;
use App\Models\City;
use App\Models\ParcelLimit;
use Log;
//use App\Models\AssignRequest;

class FirstManController extends Controller
{
    public function returnToVendorList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Return To Vendor', 
        ];

        $vendors = Vendor::where('status',1)->get();
        
        if(Auth::user()->hasAnyRole('admin')){
            if($request->to && $request->from && $request->vendor <> 'any')
            {
                $vendorRequest = $request->vendor;
                $orders = Order::whereDate('updated_at', '>=', $request->from)->whereDate('updated_at', '<=', $request->to)->where('vendor_id', $request->vendor)->where('order_status',10)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
            elseif($request->to && $request->from && $request->vendor == 'any')
            {
                $vendorRequest = 'any';
                $orders = Order::whereDate('updated_at', '>=', $request->from)->whereDate('updated_at', '<=', $request->to)->where('order_status',10)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
            else
            {
                $vendorRequest = 'any';
                $orders = Order::where('order_status',10)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
        }

        if(Auth::user()->hasAnyRole('first_man'))
        {
            $userId = Auth::user()->id;
            $userCity = UserCity::where('user_id',$userId)->pluck('city_id');

            if($request->to && $request->from && $request->vendor <> 'any')
            {
                $vendorRequest = $request->vendor;
                $orders = Order::whereDate('updated_at', '>=', $request->from)->whereDate('updated_at', '<=', $request->to)->where('vendor_id', $request->vendor)->where('order_status',10)->whereIn('consignment_origin_city',$userCity)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
            elseif($request->to && $request->from && $request->vendor == 'any')
            {
                $vendorRequest = 'any';
                $orders = Order::whereDate('updated_at', '>=', $request->from)->whereDate('updated_at', '<=', $request->to)->where('order_status',10)->whereIn('consignment_origin_city',$userCity)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
            else
            {
                $vendorRequest = 'any';
                $orders = Order::where('order_status',10)->whereIn('consignment_origin_city',$userCity)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
        }
        return view('first_man.return_to_vendor_parcel_list',compact('breadcrumbs','orders','vendors','vendorRequest'));
    }

    public function returnToVendor(Request $request)
    {
        $requestOrderId  = $request->order_id;
        $orderUpdate = Order::whereId($requestOrderId)->update(['order_status'=>10]);

        if($orderUpdate){
            return back()->with(['status'=>'Parcel Return To Vendor']);
        }
        
        return back()->with(['status'=>'Parcel Not Return To Vendor']);

    }

    public function updateVendorLocation(Request $request)
    {
        $dbLocationId  = $request->db_location_id;
        $vendorId  = $request->vendor_id;
        $locationAddress  = $request->location_address;
        //dd($request->all());
        $locationUpdate = WarehouseLocation::whereId($dbLocationId)->update(['address'=>$locationAddress]);

        if($locationUpdate){
            $response = [
                'status' => 1,
                'message' => 'Location Update',
            ];
            return response()->json($response);  
        }

    }

    public function deleteVendorLocation(Request $request)
    {
        $dbLocationId  = $request->db_location_id;
        $vendorId  = $request->vendor_id;
        
        $VendorLocation = WarehouseLocation::whereVendorId($vendorId)->get();
        if(count($VendorLocation) > 1){
            //$locationDelete = WarehouseLocation::whereId($dbLocationId)->delete();
            $locationDelete = WarehouseLocation::whereId($dbLocationId)->update([
                'status' => 0
            ]);
            $response = [
                'status' => 1,
                'message' => 'Vendor Location Delete',
            ];
        }else{
            $response = [
                'status' => 2,
                'message' => 'Vendor have to at least one location',
            ];
        }
            
        return response()->json($response);
    }

    public function insertVendorLocation(Request $request)
    {
        $vendorId  = $request->vendor_id;
        $locationAddress  = $request->location_address;
        
        $data = [
            'vendor_id' => $vendorId,
            'address' => $locationAddress,
            'status' => 1,
        ];

        $locationInsert = WarehouseLocation::create($data);
        $response = [
            'status' => 1,
            'message' => 'Vendor Location Add',
        ];
            
        return response()->json($response);
    }

    public function updateVendorWeight(Request $request)
    {
        $vendorId  = $request->vendor_id;

        $ahlWeightId  = $request->ahl_weight_id;
        $weightTitle  = $request->weight_title;
        
        $dbWeightId  = $request->weight_id;
        $weightPrice  = $request->weight_price;
        $weightMin= $request->min_weight;
        $weightMax= $request->max_weight;
        $weightCity= $request->city_id;

        $locationUpdate = VendorWeight::where(['id'=>$dbWeightId,'vendor_id'=>$vendorId])->update([
            'price'=>$weightPrice,
            'min_weight'=>$weightMin,
            'max_weight'=>$weightMax,
            'city_id' => $weightCity,
        ]);

        $updateAhlWeight = AhlWeight::whereId($ahlWeightId)->update([
            'weight' => $weightTitle
        ]);

        if($locationUpdate){
            $response = [
                'status' => 1,
                'message' => 'Weight Update',
            ];
            return response()->json($response);  
        }

    }

    public function deleteVendorWeight(Request $request)
    {
        $dbWeightId  = $request->weight_id;
        $vendorId  = $request->vendor_id;
        
        $VendorLocation = VendorWeight::whereVendorId($vendorId)->get();
        if(count($VendorLocation) > 1){
            //$weightDelete = VendorWeight::whereId($dbWeightId)->delete();
            $weightDelete = VendorWeight::whereId($dbWeightId)->update([
                'status' => 0
            ]);
            $response = [
                'status' => 1,
                'message' => 'Vendor Weight Delete',
            ];
        }else{
            $response = [
                'status' => 2,
                'message' => 'Vendor have to at least one Weight',
            ];
        }
            
        return response()->json($response);
    }

    public function insertVendorWeight(Request $request)
    {
        $vendorId  = $request->vendor_id;
        $weightTitle  = $request->weight_title;
        $weightPrice  = $request->weight_price;
        $weightMin= $request->min_weight;
        $weightMax= $request->max_weight;
        $weightCity= $request->city_id;

        try {

            //Transaction
            DB::beginTransaction();

            $addAhlWeight = [
                'weight' => $weightTitle,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            //dump($addAhlWeight);
            $ahlWeight = AhlWeight::create($addAhlWeight);
            $ahlWeightId = $ahlWeight->id;

            $addVendorWeight = [
                'vendor_id' => $vendorId,
                'ahl_weight_id' => $ahlWeightId,
                'price' => $weightPrice,
                'min_weight'=>$weightMin,
                'max_weight'=>$weightMax,
                'city_id' => $weightCity,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            //dump($addVendorWeight);      
            $vendorWeight = VendorWeight::create($addVendorWeight);

            $response = [
                'status' => 1,
                'message' => 'Vendor Weight Add',
            ];

            DB::commit();
            // all good

        } catch (Throwable $e) {
            DB::rollback();
            $response = [
                'status' => 2,
                'message' => 'Error to save vendor weight',
            ];
            report($e);
            //return false;
        }

        return response()->json($response);
    }

    public function forceRequestDelete(Request $request)
    {
        
        $pickupRequestId = $request->id;
        //$totalPickedParcels = 0;

        //1 pending, 2 assign request , 3 complete request status
        $pickupRequest = PickupRequest::where(['id'=>$pickupRequestId])->delete();

        /*$getAssignPickupRequest = AssignRequest::where(['pickup_request_id'=>$pickupRequestId])->get();
        if($getAssignPickupRequest){
            //1 assign request pending, 2 complete request
            $assignPickupRequest = AssignRequest::where(['pickup_request_id'=>$pickupRequestId])->update(['total_picked_parcel'=>$totalPickedParcels,'status' => 2]);
        }*/

        return back()->with(['success'=>'Request Delete Forcefully']);
    }

    public function editParcel(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Edit Order Parcel', 
        ];

        $orderParcelId = $request->id;

        $order = Order::where('id',$orderParcelId)->with([
            'vendor' => function($query){
                $query->select('id','vendor_name','vendor_email','vendor_address');
            }
        ])
        ->first();

        $vendorId = $order->vendor_id;

        $packing = Packing::all();
        $order_type = OrderType::all();
        $vendorWeights = VendorWeight::with('city')->where('vendor_id',$vendorId)->get();
        $pickup_location = WarehouseLocation::where('vendor_id',$vendorId)->get();
        $fetch_limit = ParcelLimit::where('city_id', $order->consignee_city)->first();

        return view('vendor.edit-order',compact('breadcrumbs','order','packing','order_type','vendorWeights','pickup_location','fetch_limit'));
    }

    public function updateParcel(Request $request)
    {
        
        $orderId = $request->order_id;
        $order = Order::find($orderId);
        $authUser = Auth::user();
        $weight_price = VendorWeight::where('id',$request->vendor_weight_id)->first();

        // dd($request->all());
        if($authUser->isAdmin())
        {

            if(($request->consignment_order_type != $order->consignment_order_type) || ($request->consignment_cod_price != $order->consignment_cod_price))
            {
                $updateData = [
                    'consignee_phone' => $request->consignee_phone,
                    'consignee_address' => $request->consignee_address,
                    'consignment_order_type' => $request->consignment_order_type,
                    'consignment_cod_price' => $request->consignment_cod_price,
                    'vendor_weight_id' => $request->vendor_weight_id,
                    'consignment_packaging' => $request->consignment_packaging,
                    'consignment_description' => $request->consignment_description,
                    'consignment_pieces' => $request->consignment_pieces,
                    'vendor_weight_price' =>  $weight_price->price,
                    'additional_services_type' =>  $weight_price->additional_services_type,
                    'update_by' =>  $authUser->id,
                    'previous_value' => $order->consignment_cod_price,
                    'parcel_limit' => $request->parcel_limit,
                ];
            }
            else
            {
                $updateData = [
                    'consignee_phone' => $request->consignee_phone,
                    'consignee_address' => $request->consignee_address,
                    'consignment_order_type' => $request->consignment_order_type,
                    'consignment_cod_price' => $request->consignment_cod_price,
                    'vendor_weight_id' => $request->vendor_weight_id,
                    'consignment_packaging' => $request->consignment_packaging,
                    'consignment_description' => $request->consignment_description,
                    'consignment_pieces' => $request->consignment_pieces,
                    'vendor_weight_price' =>  $weight_price->price,
                    'additional_services_type' =>  $weight_price->additional_services_type,
                    'update_by' =>  $authUser->id,
                    'parcel_limit' => $request->parcel_limit,
                ];
            }

            $update = $order->update($updateData);
            if($update){
                return redirect()->route('adminDashboard')->with(['success' => 'Parcel Order Updated Successfully']);
            }else{
                return back()->with(['success' => 'Not Save Something went wrong!']);
            }

        }
        elseif($authUser->isFirstMan())
        {
            $updateData = [
                'vendor_weight_id' => $request->vendor_weight_id,
                'consignment_packaging' => $request->consignment_packaging,
                'consignment_description' => $request->consignment_description,
                'consignment_pieces' => $request->consignment_pieces,
                'vendor_weight_price' =>  $weight_price->price,
                'additional_services_type' =>  $weight_price->additional_services_type,
            ];

            $update = $order->update($updateData);
            if($update){
                return redirect()->route('adminDashboard')->with(['success' => 'Parcel Order Updated Successfully']);
            }else{
                return back()->with(['success' => 'Not Save Something went wrong!']);
            }

        }
        elseif($authUser->isHubManager())
        {
            $updateData = [
                'parcel_limit' => $request->parcel_limit,
            ];

            $update = $order->update($updateData);
            if($update){
                return redirect()->route('adminDashboard')->with(['success' => 'Parcel Order Updated Successfully']);
            }else{
                return back()->with(['success' => 'Not Save Something went wrong!']);
            }

        }
        elseif($authUser->isCSR())
        {
            $updateData = [
                'consignee_phone' => $request->consignee_phone,
                'consignee_first_name' => $request->consignee_first_name,
                'consignee_last_name' => $request->consignee_last_name,
                'consignee_email' => $request->consignee_email,
                'consignee_address' => $request->consignee_address,
                'parcel_limit' => $request->parcel_limit,
            ];

            $update = $order->update($updateData);
            if($update){
                return redirect()->route('adminDashboard')->with(['success' => 'Parcel Order Updated Successfully']);
            }else{
                return back()->with(['success' => 'Not Save Something went wrong!']);
            }
        }
        else
        {
            $updateData = [
                'consignee_phone' => $request->consignee_phone,
                'consignee_first_name' => $request->consignee_first_name,
                'consignee_last_name' => $request->consignee_last_name,
                'consignee_email' => $request->consignee_email,
                'consignee_address' => $request->consignee_address,
                'consignee_country' => $request->consignee_country,
                'consignee_state' => $request->consignee_state,
                'consignee_city' => $request->consignee_city,
                'consignment_description' => $request->consignment_description,
            ];

            $update = $order->update($updateData);
            if($update){
                return redirect()->route('index')->with(['success' => 'Parcel Order Updated Successfully']);
            }else{
                return back()->with(['success' => 'Not Save Something went wrong!']);
            }
        }

    }

    public function shiperAdvise(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Shiper Advise', 
        ];

        //$authVendorId = Auth::user()->vendor_id;

        /*$orders = Order::where(['vendor_id'=>$authVendorId])->whereIn('order_status',[7,9])
        ->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'orderStatus' => function($query){
                $query->select('id','name');
            },
            'shiperAdviser' => function($query){
                $query->select('id','order_id','advise');
            },
        ])
        ->get();*/

        if(Auth::user()->hasAnyRole('admin'))
        {    
            $shiperParcelsAdvise = ShiperAdviser::with([
                'order' => function($query){
                    $query->select('id','vendor_id','order_reference','consignee_first_name','consignee_last_name','consignment_cod_price','consignment_order_id','consignee_address','consignee_phone','order_status','consignee_city')
                    ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                    ])->whereIn('order_status',[9]);
                },
            ])->get();
        }
        elseif(Auth::user()->hasAnyRole('first_man','csr'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');
    
            $shiperParcelsAdvise = ShiperAdviser::with([
                'order' => function($query) use($usercity){
                    $query->select('id','vendor_id','order_reference','consignee_first_name','consignee_last_name','consignment_cod_price','consignment_order_id','consignee_address','consignee_phone','order_status','consignee_city')->whereIn('consignee_city',$usercity)
                    ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                    ])->whereIn('order_status',[9]);
                },
            ])->get();
        }

        // dd($shiperParcelsAdvise);

        
        //dd($shiperParcelsAdvise);
        
        return view('first_man.shiper-advise',compact('breadcrumbs','shiperParcelsAdvise'));
    }
    
    public function pickupHistory(Request $request)
    {
        $vendors = Vendor::where('status', 1)->get();
        $vendorId = $request->vendor_id;
        if($request->vendor_id)
        {
            $pickupRequests = PickupRequest::where('status', 3)->where('vendor_id', $vendorId)->orderBy('id', 'DESC')->get();
            $vendorRequest = $vendorId;
        }
        else
        {
            $pickupRequests = PickupRequest::where('status', 3)->orderBy('id', 'DESC')->get();
            $vendorRequest = 'any';
        }
        // dd($pickupRequests);
        return view('first_man.pickup-history', compact('vendors','pickupRequests','vendorRequest'));

    }

    public function shiperReply($id)
    {
        $shiperAdvise = ShiperAdviser::find($id);

        return view('first_man.reply', compact('shiperAdvise'));
    }

    public function saveReply(Request $request)
    {
        $shiperAdvise = ShiperAdviser::find($request->shiper_advise_id);

        $shiperReply = $request->reply;
        $shiperAdvise->update(['ahl_reply' => $shiperReply]);

        return redirect()->route('ahlShiperAdvise');
    }

    public function firstManPickUp()
    {
        $all_vendors = Vendor::where('status', 1)->get();
        $cities = City::all();

        return view('first_man/create_pickup',compact('all_vendors','cities'));
    }

    public function selectVendordata($id){

        $data['timing'] = VendorTiming::with('vendorTiming')->where('vendor_id', $id)->get();
        $data['location'] = WarehouseLocation::where('vendor_id', $id)->get();
        // dd($data);
            return response()->json($data);
    }

    public function saveFirstManPickUp(Request $request)
    {
        $validatedData = $request->validate([
            //company detail
            'vendor' => 'required',
            'pickup_date' => 'required',
            'estimated_parcel' => 'required|numeric',
            'time_slot' => 'required',
            'pickup_location' => 'required',
            'city' => 'required',
        ]);
        
        // dd($request->all());
        $pickup = [
            'vendor_id' => $request->vendor,
            'vendor_time_id' => $request->time_slot,
            'warehouse_location_id' => $request->pickup_location,
            'pickup_date' => $request->pickup_date,
            'estimated_parcel' => $request->estimated_parcel,
            'status' => 1,
            'remarks' => $request->remarks,
            'city_id' => $request->city,
        ];
        
        PickupRequest::create($pickup);
        
        return redirect()->back()->with('success','Pickup Request Generated Successfully!');
    }

    public function moveToWarehouse($id)
    {
        $order = Order::find($id);
        $order->update(['order_status'=>3]);

        return back();
    }

    public function returnToVendorInProgressList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Return To Vendor In Progress', 
        ];

        $vendors = Vendor::where('status',1)->get();
        
        if(Auth::user()->hasAnyRole('admin')){
            if($request->to && $request->from && $request->vendor <> 'any')
            {
                $vendorRequest = $request->vendor;
                $orders = Order::whereDate('updated_at', '>=', $request->from)->whereDate('updated_at', '<=', $request->to)->where('vendor_id', $request->vendor)->where('order_status',19)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
            elseif($request->to && $request->from && $request->vendor == 'any')
            {
                $vendorRequest = 'any';
                $orders = Order::whereDate('updated_at', '>=', $request->from)->whereDate('updated_at', '<=', $request->to)->where('order_status',19)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
            else
            {
                $vendorRequest = 'any';
                $orders = Order::where('order_status',19)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
        }

        if(Auth::user()->hasAnyRole('first_man'))
        {
            $userId = Auth::user()->id;
            $userCity = UserCity::where('user_id',$userId)->pluck('city_id');

            if($request->to && $request->from && $request->vendor <> 'any')
            {
                $vendorRequest = $request->vendor;
                $orders = Order::whereDate('updated_at', '>=', $request->from)->whereDate('updated_at', '<=', $request->to)->where('vendor_id', $request->vendor)->where('order_status',19)->whereIn('consignment_origin_city',$userCity)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
            elseif($request->to && $request->from && $request->vendor == 'any')
            {
                $vendorRequest = 'any';
                $orders = Order::whereDate('updated_at', '>=', $request->from)->whereDate('updated_at', '<=', $request->to)->where('order_status',19)->whereIn('consignment_origin_city',$userCity)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
            else
            {
                $vendorRequest = 'any';
                $orders = Order::where('order_status',19)->whereIn('consignment_origin_city',$userCity)
                ->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
            }
        }
        return view('first_man.return_to_vendor_in_progress_parcel_list',compact('breadcrumbs','orders','vendors','vendorRequest'));
    }

    public function returnToVendorUpdate($id)
    {
        $requestOrderId  = $id;
        $orderUpdate = Order::whereId($requestOrderId)->update(['order_status'=>10]);

        if($orderUpdate){
            return back()->with(['status'=>'Parcel Return To Vendor']);
        }
        
        return back()->with(['status'=>'Parcel Not Return To Vendor']);

    }

    public function bulkReturnToVendor(Request $request)
    {
        $parcelIds = $request->paracel_id;
        
        if(is_array($parcelIds)){
            $order = Order::whereIn('id',$parcelIds)->update(['order_status'=>10]);

        }else{
            Order::where('id',$parcelIds)->update(['order_status'=>10]);
        }        

        $response = [
            'status' => 1,
            'message' => 'Order has been marked Returned to Vendor',
        ];

        return response()->json($response);
    }

    public function shipperAdviseReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Shipper Advise Report', 
        ];

        $vendors = Vendor::where('status',1)->get();
        $vendorRequest = 'any';
        
        if(Auth::user()->hasAnyRole('vendor_admin|vendor_editor'))
        {
            $vendors = Vendor::where('id', Auth::user()->vendor_id)->get();
        }
        else
        {
            $vendors = Vendor::where('status',1)->get();
        }

        if($request->from && $request->to && $request->vendor == 'any')
        {
            $vendorRequest = 'any';
            $shipper_advise = ShiperAdviser::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->get();
        }
        elseif($request->from && $request->to && $request->vendor <> 'any')
        {
            $vendorRequest = $request->vendor;
            $shipper_advise = ShiperAdviser::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->get();
        }
        else
        {
            $shipper_advise = [];
            $vendorRequest = 'any';
        }
        
        return view('first_man.shipper-advise-report',compact('breadcrumbs','shipper_advise','vendors','vendorRequest'));
    }
}
