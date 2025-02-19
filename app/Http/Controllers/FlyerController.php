<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Flyer;
use App\Models\FlyerRequest;
use App\Models\FlyerDetails;
use App\Models\Order;
use App\Models\FlyerInventory;

class FlyerController extends Controller
{
    public function flyerIndex()
    {
        $breadcrumbs = [
            'name' => 'Flyers Lists', 
        ];

        $flyers = Flyer::all();

        return view('admin.flyer.index', compact('flyers','breadcrumbs'));
    }

    public function flyerCreate()
    {
        $breadcrumbs = [
            'name' => 'Create Flyers',
        ];

        return view('admin.flyer.create', compact('breadcrumbs'));
    }

    public function saveNewFlyer(Request $request)
    {
        $name = $request->flyer_name;
        $price = $request->flyer_price;

        $data = [
            'name' => $name,
            'price' => $price,
            'status' => 1,
        ];

        Flyer::create($data);

        return redirect()->route('flyerCreate');
    }

    public function editFlyer($id)
    {
        $breadcrumbs = [
            'name' => 'Edit Flyers',
        ];

        $find_flyer = Flyer::find($id);

        return view('admin.flyer.edit', compact('find_flyer','breadcrumbs'));
    }

    public function updateFlyer(Request $request)
    {
        $flyer_id = $request->flyer_id;
        $name = $request->flyer_name;
        $price = $request->flyer_price;

        $find_flyer = Flyer::find($flyer_id);
        $data = [
            'name' => $name,
            'price' => $price,
            'status' => 1,
        ];
        $find_flyer->update($data);

        return redirect()->route('flyerIndex');
    }

    public function createFlyerRequest()
    {
        $breadcrumbs = [
            'name' => 'Create Flyers Request',
        ];

        $flyers = Flyer::all();

        return view('vendor.flyer.create_request', compact('flyers','breadcrumbs'));
    }

    public function saveFlyerRequest(Request $request)
    {
        $flyerIds = $request->flyer_id;
        $flyerQuantity = $request->quantity;
        $vendorId = Auth::user()->vendor_id;
        $sum = 0;

        foreach($flyerIds as $key => $flyerId)
        {
            $find_flyer = Flyer::where('id', $flyerIds[$key])->first();
            $c_stock = $find_flyer->current_stock - $flyerQuantity[$key];
            $find_flyer->update(['current_stock'=> $c_stock]);
            $find_flyer_price = $find_flyer->price;
            $find_flyer_total = $find_flyer_price * $flyerQuantity[$key];
            
            $sum = $sum + $find_flyer_total;
        }

        $sum = $sum;

        $requestData = [
            'vendor_id' => $vendorId,
            'status' => 1,
            'total' => $sum,
        ];
        // dd($requestData);

        $saveRequestData = FlyerRequest::create($requestData);

        foreach($flyerIds as $key => $flyerId)
        {
            $FlyerDetail = Flyer::where('id', $flyerIds[$key])->first();
            $flyerPrice = $FlyerDetail->price;
            $flyerTotal = $flyerPrice * $flyerQuantity[$key];
            $data = [
                'request_id' => $saveRequestData->id,
                'flyer_id' => $flyerIds[$key],
                'quantity' => array_key_exists($key, $flyerQuantity) ? $flyerQuantity[$key] : 0,
                'flyer_price' => $flyerPrice,
                'flyer_total' => $flyerTotal,
            ];

            FlyerDetails::create($data);
        }

        return redirect()->route('flyerRequestIndex');
    }

    public function flyerRequestIndex()
    {
        $breadcrumbs = [
            'name' => 'Pending Flyers Request',
        ];

        $flyer_requests = FlyerRequest::whereNotIn('status',  [4,5])->where('vendor_id', Auth::user()->vendor_id)->get();

        return view('vendor.flyer.flyer_requests', compact('flyer_requests','breadcrumbs'));
    }

    public function completedFlyerRequestIndex()
    {
        $breadcrumbs = [
            'name' => 'Compelted Flyers Request',
        ];

        $flyer_requests = FlyerRequest::where('status', 4)->where('vendor_id', Auth::user()->vendor_id)->get();

        return view('vendor.flyer.complete_flyer', compact('flyer_requests','breadcrumbs'));
    }

    public function pendingFlyerRequest()
    {
        $breadcrumbs = [
            'name' => 'Pending Flyers Request',
        ];

        $flyer_requests = FlyerRequest::where('status', 1)->get();

        return view('admin.flyer.pending_flyer_request', compact('flyer_requests','breadcrumbs'));
    }

    public function acceptedFlyerRequest()
    {
        $breadcrumbs = [
            'name' => 'Accepted Flyers Request',
        ];

        $flyer_requests = FlyerRequest::where('status', 2)->get();

        return view('admin.flyer.accepted_flyer_request', compact('flyer_requests','breadcrumbs'));
    }

    public function dispatchFlyerRequest()
    {
        $breadcrumbs = [
            'name' => 'En-Route Flyers Request',
        ];

        $flyer_requests = FlyerRequest::where('status', 3)->get();

        return view('admin.flyer.dispatched_flyer_request', compact('flyer_requests','breadcrumbs'));
    }

    public function delvieredFlyerRequest()
    {
        $breadcrumbs = [
            'name' => 'Delivered Flyers Request',
        ];

        $flyer_requests = FlyerRequest::where('status', 4)->get();

        return view('admin.flyer.delivered_flyer_request', compact('flyer_requests','breadcrumbs'));
    }

    public function flyerRequestStatusChange(Request $request)
    {
        $requestId = $request->id;

        $request = FlyerRequest::where('id',$requestId)->first();

        if($request->status == 1){
            $changeStatus = 2;
            $request->update(['status'=>$changeStatus]);
        }elseif($request->status == 2){
            $changeStatus = 3;
            $request->update(['status'=>$changeStatus]);
        }elseif($request->status == 3){
            $changeStatus = 4;
            $request->update(['status'=>$changeStatus]);
        }else{
            return back()->with(['danger'=> 'Flyer Request Status cannot be Changed!']);
        }
        
        
        return back()->with(['success'=> 'Flyer Request Status Change Successfully!']);
    }

    public function cancelFlyer(Request $request){
        $id = $request->flyer_id;

        $cancelFlyer = FlyerRequest::where('id', '=', $id)->update(['status' => 5]);

        if($cancelFlyer){
            return redirect()->back();
        }
    }

    public function reversePickupRequest()
    {
        $breadcrumbs = [
            'name' => 'Reverse Pickup Parcels', 
        ];
        $authVendorId = Auth::user()->vendor_id;

        $orders = Order::where('order_status', '=', 1)->where('vendor_id', $authVendorId)->with([
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

        return view('vendor.reverse_pickup.pending-reverse-pickup',compact('orders','breadcrumbs'));
    }

    public function progressReversePickupRequest()
    {
        $breadcrumbs = [
            'name' => 'In-Progress Reverse Pickup Parcels', 
        ];
        $authVendorId = Auth::user()->vendor_id;

        $orders = Order::whereIn('order_status',[2,3,4,5])->where('vendor_id', $authVendorId)->with([
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

        return view('vendor.reverse_pickup.in-progress-reverse-pickup',compact('orders','breadcrumbs'));
    }

    public function completeReversePickup()
    {
        $breadcrumbs = [
            'name' => 'Complete Reverse Pickup Parcels', 
        ];
        $authVendorId = Auth::user()->vendor_id;

        $orders = Order::where('order_status', 6)->where('vendor_id', $authVendorId)->with([
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

        return view('vendor.reverse_pickup.complete-reverse-pickup',compact('orders','breadcrumbs'));
    }

    public function cancelReversePickup()
    {
        $breadcrumbs = [
            'name' => 'Cancel Reverse Pickup Parcels', 
        ];
        $authVendorId = Auth::user()->vendor_id;

        $orders = Order::where('order_status', 12)->where('vendor_id', $authVendorId)->with([
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

        return view('vendor.reverse_pickup.cancel-reverse-pickup',compact('orders','breadcrumbs'));
    }

    public function cancelReversePickupParcel($id)
    {
        $find_parcel = Order::where('id', $id)->where('parcel_nature', 2)->first();
        $find_parcel->update(['order_status' => 12]);

        return redirect()->back();
    }

    public function addInventory($id)
    {
        $breadcrumbs = [
            'name' => 'Add Inventory',
        ];

        $find_flyer = Flyer::find($id);

        return view('admin.flyer.inventory.add-inventory', compact('find_flyer','breadcrumbs'));
    }

    public function saveInventory(Request $request)
    {
        $flyer_id = $request->flyer_id;
        $quantity = $request->quantity;
        $remarks = $request->remarks;

        $find_flyer = Flyer::find($flyer_id);
        $final_quantity = $quantity + $find_flyer->current_stock;

        $data = [
            'flyer_id' => $flyer_id,
            'qty' => $quantity,
            'remarks' => $remarks,
        ];

        $save_inventory = FlyerInventory::create($data);
        if($save_inventory)
        {
            $find_flyer->update(['current_stock' => $final_quantity]);
        }

        return redirect()->route('flyerIndex')->with('message','Flyer stock added Successfully!');
    }

    public function viewInventory($id)
    {
        $breadcrumbs = [
            'name' => 'View Inventory',
        ];

        $flyer_inventories = FlyerInventory::where('flyer_id', $id)->orderBy('id','DESC')->get();

        return view('admin.flyer.inventory.inventory-ledger', compact('flyer_inventories','breadcrumbs'));
    }
}
