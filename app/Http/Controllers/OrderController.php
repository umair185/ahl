<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Vendor;
use App\Models\Order;
use App\Models\OrderAssigned;
use App\Models\Packing;
use App\Models\Status;
use App\Models\OrderType;
use App\Models\ScanOrder;
use App\Models\WarehouseLocation;
use App\Models\VendorWeight;
use App\Models\Template;
use App\Models\City;
use App\Helpers\Helper;
use App\Models\OrderDecline;
use App\Models\ParcelNature;
use App\Models\ParcelSag;
use App\Models\OrderInSag;
use App\Models\Bilty;
use App\Models\SagInBilty;
use App\Models\ParcelLimit;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BulkExport;
use App\Imports\BulkImport;

class OrderController extends Controller
{
    
    public function parcelList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Parcel Lists', 
        ];

        $cancelledStatusByVendor = [1,2,3];

        if ($request->from && $request->to && $request->status <> 'any') {
            $user = Auth::user();
            $statuses = Status::all();
            $statusRequest = $request->status;
            $authVendorId = $user->vendor_id;
            $parcels = Order::with(['vendorWeight.city'])->where('vendor_id',$authVendorId)
                    ->whereBetween('updated_at', [$request->from, $request->to])->where('parcel_nature',1)
                    ->where('order_status', $request->status)->get();
            return view('vendor.parcels_list', compact('parcels','statuses','statusRequest','cancelledStatusByVendor','breadcrumbs'));
        }
        if ($request->from && $request->to && $request->status == 'any') {
            $user = Auth::user();
            $statuses = Status::all();
            $statusRequest = 'any';
            $authVendorId = $user->vendor_id;
            $parcels = Order::with(['vendorWeight.city'])->where('vendor_id',$authVendorId)
                    ->whereBetween('updated_at', [$request->from, $request->to])->where('parcel_nature',1)
                    ->orderBy('id','DESC')->get();
            return view('vendor.parcels_list', compact('parcels','statuses','statusRequest','cancelledStatusByVendor','breadcrumbs'));
        }
        
        $user = Auth::user();
        $statuses = Status::all();
        $authVendorId = $user->vendor_id;
        $statusRequest = 'any';
        $parcels = Order::with(['vendorWeight.city'])->where('vendor_id',$authVendorId)->where('order_status',1)->where('parcel_nature',1)->get();

        // dd($parcels);
        
        return view('vendor.parcels_list', compact('parcels','statuses','statusRequest','cancelledStatusByVendor','breadcrumbs'));
    }

    public function manualOrder()
    {
        $breadcrumbs = [
            'name' => 'Manual Order', 
        ];

        $user = Auth::user();
        $authVendorId = $user->vendor_id;
        $vendor = Vendor::where('id',$authVendorId)->first();
        $packing = Packing::all();
        $order_type = OrderType::all();
        $vendorWeights = VendorWeight::with('city')->where('vendor_id',$authVendorId)->where('status',1)->get();
        $pickup_location = WarehouseLocation::where('vendor_id',$authVendorId)->get();
        $parcelNatures = ParcelNature::all();

        // dd($vendorWeights);

    	return view('vendor.manual-order', compact('vendor','packing','order_type','pickup_location','vendorWeights','breadcrumbs','parcelNatures'));
    }

    public function saveManualOrder(Request $request)
    {

    	$validatedData = $request->validate([

        	//order
        	'consignee_phone'=> 'required|numeric',
	        'consignee_first_name'=> 'required',
	        'consignee_last_name'=> 'required',
	        'consignee_email'=> 'required|email',
	        'consignee_address'=> 'required',
	        'consignee_country'=> 'required',
	        'consignee_state'=> 'required',
	        'consignee_city'=> 'required',
	        'consignment_order_id'=> 'required',
	        'consignment_order_type'=> 'required',
	        'consignment_cod_price'=> 'required|numeric',
	        //'consignment_weight'=> 'required',
            'vendor_weight_id'=> 'required',
	        'consignment_packaging'=> 'required',
	        'consignment_pieces'=> 'required|numeric',
	        'consignment_pickup_location'=> 'required',
	        'consignment_origin_city'=> 'required',
            'parcel_nature'=> 'required',
        ]);
        $check_city = City::where('id', $request->consignee_city)->first();
        $weight_price = VendorWeight::where('id',$request->vendor_weight_id)->first();
        // dd($weight_price->price);
        //fuel value
        $payee = Vendor::where('id',$request->vendor_id)->first();
        $fuel_adjustment = ($weight_price->price * $payee->fuel)/100;
        $round_fuel_adjustment = round($fuel_adjustment);
        //GST
        $gst_adjustment = ($weight_price->price * $payee->gst)/100;
        $round_gst_adjustment = round($gst_adjustment);
        $fetch_limit = ParcelLimit::where('city_id', $request->consignee_city)->first();
        if(!empty($fetch_limit))
        {
            $parcel_limit = $fetch_limit->limit;
        }
        else
        {
            $parcel_limit = 1;
        }

    	$orderdata = [
            'vendor_id'=> $request->vendor_id,
            'consignee_first_name'=> $request->consignee_first_name,
	        'consignee_last_name'=> $request->consignee_last_name,
	        'consignee_email'=> $request->consignee_email,
	        'consignee_address'=> $request->consignee_address,
    		'consignee_phone'=> $request->consignee_phone,
	        'consignee_country'=> $request->consignee_country,
	        'consignee_state'=> $request->consignee_state,
	        'consignee_city'=> $request->consignee_city,
            'parcel_nature'=>$request->parcel_nature,
	        'consignment_order_id'=> $request->consignment_order_id,
	        'consignment_order_type'=> $request->consignment_order_type,
	        'consignment_cod_price'=> $request->consignment_cod_price,
	        'consignment_weight'=> 0,//force fully remove when ahl weight id done every where 
            'vendor_weight_id'=> $request->vendor_weight_id,
	        'consignment_packaging'=> $request->consignment_packaging,
	        'consignment_pieces'=> $request->consignment_pieces,
	        'consignment_description'=> $request->consignment_description,
	        'pickup_location'=> $request->consignment_pickup_location,
	        'consignment_origin_city'=> $request->consignment_origin_city,
	        'additional_services_type'=> $request->additional_services_type,
            'order_reference'=> '#'.$check_city->code.Helper::genrateOrderReference(),
            'vendor_weight_price' => $weight_price->price,
            'order_status'=> 1,
            'vendor_fuel_price' => $round_fuel_adjustment,
            'vendor_tax_price' => $round_gst_adjustment,
            'parcel_limit' => $parcel_limit,
    	];

        // dump($orderdata);
    	$order = Order::create($orderdata);
        // dd($order);

    	return redirect('/manual-order')->with('message', 'Order Place Successfully!');
    }

    public function exportBulkFormat()
    {
        $bulkfileTitle = 'AHL bulk format ( '.date("l jS \of F Y h:i:s A").' )';
        return Excel::download(new BulkExport, $bulkfileTitle.'.xlsx');
    }

    public function bulkOrder()
    {
        $breadcrumbs = [
            'name' => 'Bulk Order', 
        ];

        $user = Auth::user();
        $authVendorId = $user->vendor_id;
        $vendorPickupLocations = WarehouseLocation::whereVendorId($authVendorId)->get();
        $bulkFormat = Helper::bulkFormat();
        $vendorWeights = VendorWeight::where('vendor_id',$authVendorId)->get();
        $vendor_details = Vendor::where('id', $authVendorId)->first();
        return view('vendor/bulk-order',compact('bulkFormat','vendorPickupLocations','vendorWeights','breadcrumbs','vendor_details'));
    }

    public function saveBulkOrder(Request $request)
    {

        $validatedData = $request->validate([
            'bulk_order'=> 'required|mimes:xls,xlsx,csv,txt',
        ]);

        Excel::import(new BulkImport, request()->file('bulk_order'));

        return back()->withStatus('Data Imported Successfully!');
    }

    public function parcelQR(Request $request)
    {
        //dd($request->paracels);
        $parcels = $request->paracels;
        $user = Auth::user();
        $authVendorId = $user->vendor_id;
        $orders = Order::whereIn('id',$parcels)->where('parcel_nature',1)
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
        ->whereVendorId($authVendorId)
        ->get();
        $vendor = Vendor::whereId($authVendorId)->with([
            'vendorCity' => function($query){
                $query->select('id','name');
            }
        ])->first();
        
        return view('vendor/parcels-qr',compact('orders','vendor'));
    }

    public function parcelDetail(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Parcel Order Detail', 
        ];

        $parcelId = $request->parcel;
        $orderDetail =  Order::where('id',$parcelId)->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderPacking' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
                $query->select('id','name');
            },
            'customerCountry' => function($query){
                $query->select('id','name');
            },
            'customerCity' => function($query){
                $query->select('id','name');
            },
            'originCity' => function($query){
                $query->select('id','name');
            },
            'customerState' => function($query){
                $query->select('id','name');
            },
            'orderAssigned' => function($query){
                $query->select('id','order_id','trip_status_id','updated_at','rider_id');
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
            },
            'orderStaff' => function($query){
                $query->select('id','name');
            }
        ])->first();

        //dd($orderDetail);
        $scanOrder = ScanOrder::where('order_id',$parcelId)
        ->with([
            'scanByPicker' => function($query){
                $query->select('id','name');
            },

            'scanByMiddleMan' => function($query){
                $query->select('id','name');
            },

            'scanBySupervisor' => function($query){
                $query->select('id','name');
            },
        ])
        ->first();

        //$notDeliveryOrDeclineStatuses = [1,2,3,4,5];

        /*if(in_array($notDeliveryOrDeclineStatuses,[$orderDetail->order_status])){

        }*/ 

        $delivery = OrderAssigned::select('id','vendor_id',
        'order_id','rider_id','drop_off_location','latitude','longitude','trip_status_id','status')
        ->where(['order_id'=>$parcelId,'trip_status_id'=>4,'status'=>1])
        ->with([
            'rider' => function($query){
                $query->select('id','name');
            },
            'orderDelivery' => function($query){
                $query->select('id','order_assigned_id','amount','consignee_relation_id','other_relation','receiver_name','cnic','comment','signature','location_picture')->with([
                    'consigneeRelation' => function($query){
                        $query->select('id','name');
                    }
                ]);
            },
            'orderDecline' => function($query){
                $query->select('id','order_assigned_id','order_decline_status_id','order_decline_reason_id','additional_note','image')->with([
                    'orderDeclineStatus' => function($query){
                        $query->select('id','name');
                    },
                    'orderDeclineReason' => function($query){
                        $query->select('id','name');
                    },
                ]);
            }
        ])
        ->first();

        $decline = OrderAssigned::select('id','vendor_id',
        'order_id','rider_id','drop_off_location','latitude','longitude','trip_status_id','status')
        ->where(['order_id'=>$parcelId,'status'=>0])
        ->with([
            'rider' => function($query){
                $query->select('id','name');
            },
            'orderDecline' => function($query){
                $query->select('id','order_assigned_id','order_decline_status_id','order_decline_reason_id','additional_note','image')->with([
                    'orderDeclineStatus' => function($query){
                        $query->select('id','name');
                    },
                    'orderDeclineReason' => function($query){
                        $query->select('id','name');
                    },
                ]);
            }
        ])
        ->get();

        $decline_dates = OrderAssigned::where('order_id', $parcelId)->where('status', 0)->whereIn('trip_status_id',[4,5,6])->get();
        $assigned_dates = OrderAssigned::where('order_id', $parcelId)->whereIn('trip_status_id',[4,5,6])->get();

        $rider_detail = OrderAssigned::where('order_id', $parcelId)->orderBy('id', 'DESC')->first();

        $get_sag_orders = OrderInSag::where('order_id', $parcelId)->get();
        
        return view('parcel-detail',compact('orderDetail','decline','delivery','scanOrder','breadcrumbs', 'decline_dates','assigned_dates','rider_detail','get_sag_orders'));
    }

    public function uploadPhoto(Request $request, $id){
        
        $userId = Auth::user()->id;
        $findparcel = Order::find($id);
        if(!$findparcel)
        {
            return response()->json(['error' => 'Parcel not found'], 404);
        }

        if($request->photo)
        {
            $photo = $request->file('photo');
            $photo_name = $photo->getClientOriginalName();

            $upload_dir = 'orders_Proof';
            if(!is_dir($upload_dir)) 
                mkdir($upload_dir, 0755, true);

                // get path to store in DB
                $path = $upload_dir.'/'.$photo_name;

                // move image to firectory
                $photo->move($upload_dir,$photo_name);
            
                $data = [
                    'photo' => $path,
                    'photo_upload_by' => $userId,
                ];
                $result = $findparcel->update($data);

                return back()->with('Image Uploaded successfully');
        }
    }

    public function uploadVendorPhoto(Request $request, $id){
        
        $findparcel = Vendor::find($id);
        if(!$findparcel)
        {
            return response()->json(['error' => 'Vendor not found'], 404);
        }
        // dump($findparcel);

        if($request->photo)
        {
            $photo = $request->file('photo');
            $photo_name = $photo->getClientOriginalName();

            $upload_dir = 'logo';
            if(!is_dir($upload_dir)) 
                mkdir($upload_dir, 0755, true);

                // get path to store in DB
                $path = $upload_dir.'/'.$photo_name;

                // move image to firectory
                $photo->move($upload_dir,$photo_name);
            
                $data = [
                    'vendor_image' => $path,
                ];
                // dump($data);
                $result = $findparcel->update($data);
                // dd($result);

                return back()->with('Image Uploaded successfully');
        }
    }

    /*public function printParcelQR($id)
    {
        
        dd($id);
        //return redirect('/home/dashboard');
        return view('vendor/parcels-qr',compact('orders','vendor'));
    }*/
    
    public function replaceParcel(Request $request)
    {
        $orderParcelId = $request->id;

        $order = Order::where('id',$orderParcelId)->first();
        $order->update(['order_status'=>14]);
        
        return redirect()->back();
    }

    public function selectCitydata(Request $request){

        $city_id = $request->city_id;
        $vendor_id = $request->vendor_id;

        $weights = VendorWeight::where('vendor_id', $vendor_id)->where('city_id', $city_id)->get();
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
    
    public function editAdditionalNote($id)
    {
        $breadcrumbs = [
            'name' => 'Add Additional Note', 
        ];

        $orders_decline = OrderDecline::find($id);

        return view('add-additional-note', compact('breadcrumbs','orders_decline'));
    }

    public function saveAdditionalNote(Request $request)
    {
        // dd($request->all());
        $note_id = $request->note_id;
        $additional_note = $request->additional_note;

        $data = [
            'id' => $note_id,
            'additional_note' => $additional_note
        ];

        $find_decline_order = OrderDecline::where('id', $note_id)->first();
        $find_decline_order->update($data);

        $orderAssigned = OrderAssigned::where('id', $find_decline_order->order_assigned_id)->first();

        return redirect()->route('parcelDetail', $orderAssigned->order_id);
    }

    public function awaitingParcelList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Awaiting Parcel Lists', 
        ];

        $vendors = Vendor::all();
        $from = $request->from;
        $to = $request->to;

        if($request->to && $request->from && $request->vendor <> 'all') {
            $authVendorId = $request->vendor;
            $vendorRequest = $request->vendor;
            $parcels = Order::with(['vendorWeight.city'])->whereDate('created_at','>=', $from)->whereDate('created_at','<=',$to)->where('vendor_id',$authVendorId)->where('order_status', 1)->where('parcel_nature',1)->get();
        }
        elseif($request->to && $request->from && $request->vendor == 'all') {
            $authVendorId = $request->vendor;
            $vendorRequest = 'all';
            $parcels = Order::with(['vendorWeight.city'])->whereDate('created_at','>=', $from)->whereDate('created_at','<=',$to)->where('order_status', 1)->where('parcel_nature',1)->get();
        }
        else
        {
            $vendorRequest = 'all';
            $parcels = Order::with(['vendorWeight.city'])->where('order_status',1)->where('parcel_nature',1)->get();
        }
        // dd($parcels);
        
        return view('admin.cancel_all', compact('parcels','vendors','vendorRequest','breadcrumbs'));
    }

    public function awaitingParcelListCancel(Request $request)
    {
        //dd($request->paracels);
        $parcels = $request->paracels;
        foreach($parcels as $parcel)
        {
            $order = Order::find($parcel);
            $order->update(['order_status' => 11]);
        }
        
        return response()->json([
            'status' => 1, 
        ]);
    }

    public function ReversePickupRemarks($id)
    {
        $breadcrumbs = [
            'name' => 'Pending Reverse Pickup Parcel Remarks', 
        ];

        $orders = Order::where('id', $id)->first();

        return view('admin.reverse_pickup.reverse-pickup-remarks',compact('orders','breadcrumbs'));
    }

    public function saveReversePickupRemarks(Request $request, $id)
    {
        $orders = Order::where('id', $id)->first();
        $orders->update(['reverse_remarks'=> $request->remarks]);

        return redirect()->route('pendingReversePickupRequest');
    }

    public function pendingReversePickupRequest()
    {
        $breadcrumbs = [
            'name' => 'Pending Reverse Pickup Parcels', 
        ];

        $orders = Order::where('order_status', 1)->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderPacking' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
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
        ])->where('parcel_nature',2)->orderBy('id','DESC')->get();

        return view('admin.reverse_pickup.pending-reverse-pickup',compact('orders','breadcrumbs'));
    }

    public function receivedReversePickupRequest()
    {
        $breadcrumbs = [
            'name' => 'Received Reverse Pickup Parcels', 
        ];

        $orders = Order::where('order_status', 3)->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderPacking' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
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
        ])->where('parcel_nature',2)->orderBy('id','DESC')->get();

        return view('admin.reverse_pickup.receieved-reverse-pickup',compact('orders','breadcrumbs'));
    }

    public function dispatchedReversePickupRequest()
    {
        $breadcrumbs = [
            'name' => 'Dispatched Reverse Pickup Parcels', 
        ];

        $orders = Order::where('order_status', 5)->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderPacking' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
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
        ])->where('parcel_nature',2)->orderBy('id','DESC')->get();

        return view('admin.reverse_pickup.dispatched-reverse-pickup',compact('orders','breadcrumbs'));
    }

    public function deliveredReversePickupRequest(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Delivered Reverse Pickup Parcels', 
        ];

        $vendors = Vendor::where('status',1)->get();

        if($request->vendor_id)
        {
            $orders = Order::where('order_status', 6)->where('vendor_id', $request->vendor_id)->with([
                'vendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'orderType' => function($query){
                    $query->select('id','name');
                },
                'orderPacking' => function($query){
                    $query->select('id','name');
                },
                'orderStatus' => function($query){
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
            ])->where('parcel_nature',2)->orderBy('id','DESC')->get();

            return view('admin.reverse_pickup.delivered-reverse-pickup',compact('vendors','orders','breadcrumbs'));
        }

        $orders = Order::where('order_status', 6)->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderPacking' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
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
        ])->where('parcel_nature',2)->orderBy('id','DESC')->get();

        return view('admin.reverse_pickup.delivered-reverse-pickup',compact('vendors','orders','breadcrumbs'));
    }

    public function cancelReversePickupRequest(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Cancel Reverse Pickup Parcels', 
        ];

        $vendors = Vendor::where('status',1)->get();

        if($request->vendor_id)
        {
            $orders = Order::where('order_status', 12)->where('vendor_id', $request->vendor_id)->with([
                'vendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'orderType' => function($query){
                    $query->select('id','name');
                },
                'orderPacking' => function($query){
                    $query->select('id','name');
                },
                'orderStatus' => function($query){
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
            ])->where('parcel_nature',2)->orderBy('id','DESC')->get();

            return view('admin.reverse_pickup.delivered-reverse-pickup',compact('vendors','orders','breadcrumbs'));
        }

        $orders = Order::where('order_status', 12)->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'orderType' => function($query){
                $query->select('id','name');
            },
            'orderPacking' => function($query){
                $query->select('id','name');
            },
            'orderStatus' => function($query){
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
        ])->where('parcel_nature',2)->orderBy('id','DESC')->get();

        return view('admin.reverse_pickup.cancel-reverse-pickup',compact('vendors','orders','breadcrumbs'));
    }

    public function printParcelQR(Request $request)
    {
        //dd($request->paracels);
        $parcels = $request->parcel_id;
        $find_parcel = Order::where('id', $parcels)->first();
        $authVendorId = $find_parcel->vendor_id;
        $orders = Order::where('id',$parcels)->where('parcel_nature',2)
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
        ->whereVendorId($authVendorId)
        ->get();
        $vendor = Vendor::whereId($authVendorId)->with([
            'vendorCity' => function($query){
                $query->select('id','name');
            }
        ])->first();
        
        return view('vendor/parcels-qr',compact('orders','vendor'));
    }
    
}
