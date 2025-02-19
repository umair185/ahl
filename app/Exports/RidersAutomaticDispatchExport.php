<?php

namespace App\Exports;

use App\Models\OrderAssigned;
use App\Models\Order;
use App\Models\UserCity;
use Auth;

//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RidersAutomaticDispatchExport implements FromArray,WithHeadings
{
    private $orderAssigned;

    public function __construct($from,$to,$vendor)
    {
        if(Auth::user()->hasAnyRole('admin'))
        {
            if($from && $to && $vendor <> 'any')
            {
                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('vendor_id', $vendor)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('vendor_id', $vendor)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $orders = Order::where('parcel_nature',1)->whereIn('id', $orders_assigned)->pluck('id');
                $group_order = OrderAssigned::whereIn('order_id', $orders_assigned)->groupBy('order_id')->pluck('id');
                
                $orders_delivered =  OrderAssigned::whereIn('id', $group_order)
                ->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'riderVendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'order' => function($query){
                        $query->select('id','order_reference','consignee_first_name','consignee_last_name','consignment_cod_price','consignment_order_id','consignment_order_type','vendor_weight_id','consignee_address','consignee_phone','dispatch_date','order_status','vendor_weight_price','vendor_tax_price','vendor_fuel_price')->with([
                            'vendorWeight' => function($query){
                                $query->select('id','ahl_weight_id','price','city_id')->with([
                                    'ahlWeight' =>  function($query){
                                        $query->select('id','weight');
                                    },
                                    'city' => function($query){
                                        $query->select('id','name');
                                    }
                                ]);
                            },
                            'orderType' => function($query){
                                $query->select('id','name');
                            },
                            'orderStatus' => function($query){
                                $query->select('id','name');
                            }
                        ]);
                    },
                    'tripStatus' => function($query){
                        $query->select('id','description');
                    }
                ])
                ->get();
            }
            elseif($from && $to && $vendor == 'any')
            {   
                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $orders = Order::where('parcel_nature',1)->whereIn('id', $orders_assigned)->pluck('id');
                $group_order = OrderAssigned::whereIn('order_id', $orders_assigned)->groupBy('order_id')->pluck('id');

                $orders_delivered = OrderAssigned::whereIn('id', $group_order)
                ->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'riderVendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'order' => function($query){
                        $query->select('id','order_reference','consignee_first_name','consignee_last_name','consignment_cod_price','consignment_order_id','consignment_order_type','vendor_weight_id','consignee_address','consignee_phone','dispatch_date','order_status','vendor_weight_price','vendor_tax_price','vendor_fuel_price')->with([
                            'vendorWeight' => function($query){
                                $query->select('id','ahl_weight_id','price','city_id')->with([
                                    'ahlWeight' =>  function($query){
                                        $query->select('id','weight');
                                    },
                                    'city' => function($query){
                                        $query->select('id','name');
                                    }
                                ]);
                            },
                            'orderType' => function($query){
                                $query->select('id','name');
                            },
                            'orderStatus' => function($query){
                                $query->select('id','name');
                            }
                        ]);
                    },
                    'tripStatus' => function($query){
                        $query->select('id','description');
                    }
                ])
                ->get();
            }
            
        }
        elseif(Auth::user()->hasAnyRole('supervisor','cashier','head_of_account','financer','sales'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

            if($from && $to && $vendor <> 'any'){
                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('vendor_id', $vendor)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('vendor_id', $vendor)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $orders = Order::where('parcel_nature',1)->whereIn('id', $orders_assigned)->pluck('id');
                $group_order = OrderAssigned::whereIn('order_id', $orders_assigned)->groupBy('order_id')->pluck('id');

                $orders_delivered = OrderAssigned::whereIn('id', $group_order)->whereHas('order',function($query) use($usercity){
                    $query->whereIn('consignee_city',$usercity);
                })
                ->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'riderVendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'order' => function($query){
                        $query->select('id','order_reference','consignee_first_name','consignee_last_name','consignment_cod_price','consignment_order_id','consignment_order_type','vendor_weight_id','consignee_address','consignee_phone','dispatch_date','order_status','vendor_weight_price','vendor_tax_price','vendor_fuel_price')->with([
                            'vendorWeight' => function($query){
                                $query->select('id','ahl_weight_id','price','city_id')->with([
                                    'ahlWeight' =>  function($query){
                                        $query->select('id','weight');
                                    },
                                    'city' => function($query){
                                        $query->select('id','name');
                                    }
                                ]);
                            },
                            'orderType' => function($query){
                                $query->select('id','name');
                            },
                            'orderStatus' => function($query){
                                $query->select('id','name');
                            }
                        ]);
                    },
                    'tripStatus' => function($query){
                        $query->select('id','description');
                    }
                ])
                ->get();
            }
            elseif($from && $to && $vendor == 'any'){

                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status', 1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $orders = Order::where('parcel_nature',1)->whereIn('id', $orders_assigned)->pluck('id');
                $group_order = OrderAssigned::whereIn('order_id', $orders_assigned)->groupBy('order_id')->pluck('id');

                $orders_delivered = OrderAssigned::whereIn('id', $group_order)->whereHas('order',function($query) use($usercity){
                    $query->whereIn('consignee_city',$usercity);
                })
                ->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'riderVendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'order' => function($query){
                        $query->select('id','order_reference','consignee_first_name','consignee_last_name','consignment_cod_price','consignment_order_id','consignment_order_type','vendor_weight_id','consignee_address','consignee_phone','dispatch_date','order_status','vendor_weight_price','vendor_tax_price','vendor_fuel_price')->with([
                            'vendorWeight' => function($query){
                                $query->select('id','ahl_weight_id','price','city_id')->with([
                                    'ahlWeight' =>  function($query){
                                        $query->select('id','weight');
                                    },
                                    'city' => function($query){
                                        $query->select('id','name');
                                    }
                                ]);
                            },
                            'orderType' => function($query){
                                $query->select('id','name');
                            },
                            'orderStatus' => function($query){
                                $query->select('id','name');
                            }
                        ]);
                    },
                    'tripStatus' => function($query){
                        $query->select('id','description');
                    }
                ])
                ->get();
            }
        }
        $this->orderAssigned = $orders_delivered;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $orderAssigneds = $this->orderAssigned;
        
        if(Auth::user()->hasAnyRole('admin','cashier','head_of_account','financer'))
        {
            foreach ($orderAssigneds as $key => $orderAssigned) {
                // code...
                $data[] = [
                    $orderAssigned->riderVendor->vendor_name,
                    Date('d-m-Y',strtotime($orderAssigned->order->scanOrder ? $orderAssigned->order->scanOrder->created_at : '')),
                    $orderAssigned->order->order_reference,
                    $orderAssigned->order->consignment_order_id,
                    $orderAssigned->order->consignee_first_name.''.$orderAssigned->order->consignee_last_name,
                    $orderAssigned->order->consignee_phone,
                    $orderAssigned->order->consignment_cod_price,
                    $orderAssigned->order->orderType->name,
                    $orderAssigned->order->orderStatus->name,
                    $orderAssigned->tripStatus->description,
                    $orderAssigned->order->consignee_address,
                    Date('d-m-Y',strtotime($orderAssigned->created_at)),
                    $orderAssigned->rider->name,
                    $orderAssigned->order->vendorWeight->ahlWeight->weight . ' (' . $orderAssigned->order->vendorWeight->city->first()->name . ')',
                    $orderAssigned->order->vendor_weight_price,
                    $orderAssigned->order->vendor_tax_price,
                    $orderAssigned->order->vendor_fuel_price,

                ];
            }
        }
        elseif(Auth::user()->hasAnyRole('supervisor','sales'))
        {
            foreach ($orderAssigneds as $key => $orderAssigned) {
                // code...
                $data[] = [
                    $orderAssigned->riderVendor->vendor_name,
                    Date('d-m-Y',strtotime($orderAssigned->order->scanOrder ? $orderAssigned->order->scanOrder->created_at : '')),
                    $orderAssigned->order->order_reference,
                    $orderAssigned->order->consignment_order_id,
                    $orderAssigned->order->consignee_first_name.''.$orderAssigned->order->consignee_last_name,
                    $orderAssigned->order->consignee_phone,
                    $orderAssigned->order->consignment_cod_price,
                    $orderAssigned->order->orderType->name,
                    $orderAssigned->order->orderStatus->name,
                    $orderAssigned->tripStatus->description,
                    $orderAssigned->order->consignee_address,
                    Date('d-m-Y',strtotime($orderAssigned->created_at)),
                    $orderAssigned->rider->name,
                    $orderAssigned->order->vendorWeight->ahlWeight->weight . ' (' . $orderAssigned->order->vendorWeight->city->first()->name . ')',
                    $orderAssigned->order->vendor_weight_price,
                    $orderAssigned->order->vendor_tax_price,
                    $orderAssigned->order->vendor_fuel_price,

                ];
            }
        }
        
        if(isset($data)){
            return $data;
        }else{
            return [];
        }
    }

    public function headings(): array
    {
        if(Auth::user()->hasAnyRole('admin','cashier','head_of_account','financer'))
        {
            return [
                'vendor_name',
                'pickup_date',
                'order_reference',
                'consignment_order_id',
                'consignee_name',
                'consignee_phone',
                'consignment_cod_price',
                'order_type' ,
                'order_current_status',
                'Rider_order_status',
                'consignee_address',
                'dispatch_date',
                'rider_name',
                'vendor_order_weight',
                'weight_price',
                'vendor_tax_price',
                'vendor_fuel',
            ];
        }
        elseif(Auth::user()->hasAnyRole('supervisor','sales'))
        {
            return [
                'vendor_name',
                'pickup_date',
                'order_reference',
                'consignment_order_id',
                'consignee_name',
                'consignee_phone',
                'consignment_cod_price',
                'order_type' ,
                'order_current_status',
                'Rider_order_status',
                'consignee_address',
                'dispatch_date',
                'rider_name',
                'vendor_order_weight',
                'weight_price',
                'vendor_tax_price',
                'vendor_fuel',
            ];
        }
    }
}
