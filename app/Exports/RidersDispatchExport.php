<?php

namespace App\Exports;

use App\Models\OrderAssigned;
use App\Models\UserCity;
use App\Models\City;
use App\Models\Order;

//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Auth;

class RidersDispatchExport implements FromArray,WithHeadings
{
    private $orderAssigned;

    public function __construct($date,$city)
    {
        // if login user is admin can download with all and selected city sheet.
        if(Auth::user()->hasAnyRole('admin'))
        {
            if($date && $city <> 'any')
            {
                $orders_assigned = OrderAssigned::whereDate('created_at','=',$date)->pluck('order_id');
                $orders = Order::where('parcel_nature',1)->whereIn('id', $orders_assigned)->pluck('id');
                $group_order = OrderAssigned::whereIn('order_id', $orders_assigned)->whereDate('created_at','=',$date)->groupBy('order_id')->pluck('id');

                $orderAssigned = OrderAssigned::whereIn('id', $group_order)->whereHas('order',function($query) use($city){
                    $query->where('consignee_city',$city);
                })
                ->with([
                'rider' => function($query){
                    $query->select('id','name');
                },
                'riderVendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'order' => function($query){
                    $query->select('id','order_reference','consignee_first_name','consignee_last_name',
                    'consignment_cod_price','consignment_order_id','consignment_order_type','vendor_weight_id',
                    'consignee_address','consignee_phone','dispatch_date','order_status','consignee_city','vendor_weight_price','vendor_tax_price','vendor_fuel_price')->with([
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
                        },
                        'customerCity' => function($query){
                            $query->select('id','name');
                        },
                    ]);
                },
                'tripStatus' => function($query){
                    $query->select('id','description');
                }
                ])
                ->get();
                // dd($orderAssigned);
                //->toArray();
                // dd($orderAssigned);
                $this->orderAssigned = $orderAssigned;
            }
            elseif($date && $city == 'any')
            {
                $orders_assigned = OrderAssigned::whereDate('created_at','=',$date)->pluck('order_id');
                $orders = Order::where('parcel_nature',1)->whereIn('id', $orders_assigned)->pluck('id');
                $group_order = OrderAssigned::whereIn('order_id', $orders_assigned)->whereDate('created_at','=',$date)->groupBy('order_id')->pluck('id');

                $orderAssigned = OrderAssigned::whereIn('id', $group_order)
                ->with([
                'rider' => function($query){
                    $query->select('id','name');
                },
                'riderVendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'order' => function($query){
                    $query->select('id','order_reference','consignee_first_name','consignee_last_name',
                    'consignment_cod_price','consignment_order_id','consignment_order_type','vendor_weight_id',
                    'consignee_address','consignee_phone','dispatch_date','order_status','consignee_city','vendor_weight_price','vendor_tax_price','vendor_fuel_price')->with([
                        'vendorWeight' => function($query){
                            $query->select('id','ahl_weight_id','price','city_id')->with([
                                'ahlWeight' =>  function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'customerCity' => function($query){
                            $query->select('id','name');
                        },
                    ]);
                },
                'tripStatus' => function($query){
                    $query->select('id','description');
                }
                ])
                ->get();
                //->toArray();
                // dd($orderAssigned);
                $this->orderAssigned = $orderAssigned;
            }
        }

        // if login user is financer or cashier can download with only assigned cities.
        if(Auth::user()->hasAnyRole('financer','cashier','head_of_account','sales','hub_manager'))
        {
            $userId = Auth::user()->id;
            $userCity = UserCity::where('user_id',$userId)->pluck('city_id');

            if($date && $city <> 'any')
            {
                $orders_assigned = OrderAssigned::whereDate('created_at','=',$date)->pluck('order_id');
                $orders = Order::where('parcel_nature',1)->whereIn('id', $orders_assigned)->pluck('id');
                $group_order = OrderAssigned::whereIn('order_id', $orders_assigned)->whereDate('created_at','=',$date)->groupBy('order_id')->pluck('id');

                $orderAssigned = OrderAssigned::whereIn('id', $group_order)->whereHas('order',function($query) use($city){
                $query->where('consignee_city',$city);
                })
                ->with([
                'order' => function($query){
                    $query->select('id','order_reference','consignee_first_name','consignee_last_name',
                    'consignment_cod_price','consignment_order_id','consignment_order_type','vendor_weight_id',
                    'consignee_address','consignee_phone','dispatch_date','order_status','consignee_city','vendor_weight_price','vendor_tax_price','vendor_fuel_price')->with([
                        'vendorWeight' => function($query){
                            $query->select('id','ahl_weight_id','price','city_id')->with([
                                'ahlWeight' =>  function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'customerCity' => function($query){
                            $query->select('id','name');
                        },
                    ]);
                },
                'rider' => function($query){
                    $query->select('id','name');
                },
                'riderVendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'tripStatus' => function($query){
                    $query->select('id','description');
                }
                ])
                ->get();
                //->toArray();
                // dd($orderAssigned);
                $this->orderAssigned = $orderAssigned;
            }
            elseif($date && $city == 'any')
            {
                $orders_assigned = OrderAssigned::whereDate('created_at','=',$date)->pluck('order_id');
                $orders = Order::where('parcel_nature',1)->whereIn('id', $orders_assigned)->pluck('id');
                $group_order = OrderAssigned::whereIn('order_id', $orders_assigned)->whereDate('created_at','=',$date)->groupBy('order_id')->pluck('id');
                
                $orderAssigned = OrderAssigned::whereIn('id', $group_order)->whereHas('order',function($query) use($userCity){
                    $query->whereIn('consignee_city',$userCity);
                })
                ->with([
                'rider' => function($query){
                    $query->select('id','name');
                },
                'riderVendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'order' => function($query){
                    $query->select('id','order_reference','consignee_first_name','consignee_last_name',
                    'consignment_cod_price','consignment_order_id','consignment_order_type','vendor_weight_id',
                    'consignee_address','consignee_phone','dispatch_date','order_status','consignee_city','vendor_weight_price','vendor_tax_price','vendor_fuel_price')->with([
                        'vendorWeight' => function($query){
                            $query->select('id','ahl_weight_id','price','city_id')->with([
                                'ahlWeight' =>  function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'customerCity' => function($query){
                            $query->select('id','name');
                        },
                    ]);
                },
                'tripStatus' => function($query){
                    $query->select('id','description');
                }
                ])
                ->get();
                //->toArray();
                // dd($orderAssigned);
                $this->orderAssigned = $orderAssigned;
            }
        }

        
    }
    /**s
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
                    $orderAssigned->riderVendor ? $orderAssigned->riderVendor->vendor_name : '',
                    Date('d-m-Y',strtotime($orderAssigned->order->scanOrder ? $orderAssigned->order->scanOrder->created_at : '')),
                    Date('d-m-Y',strtotime($orderAssigned->order->scanOrder ? $orderAssigned->order->scanOrder->supervisor_scan_date : '')),
                    $orderAssigned->order ? $orderAssigned->order->order_reference : '',
                    $orderAssigned->order ? $orderAssigned->order->consignment_order_id : '',
                    $orderAssigned->order ? $orderAssigned->order->consignee_first_name.''.$orderAssigned->order->consignee_last_name : '',
                    $orderAssigned->order ? $orderAssigned->order->consignee_phone : '',
                    $orderAssigned->order ? $orderAssigned->order->consignment_cod_price : '',
                    $orderAssigned->order ? $orderAssigned->order->orderType->name : '',
                    $orderAssigned->order ? $orderAssigned->order->orderStatus->name : '',
                    $orderAssigned->tripStatus ? $orderAssigned->tripStatus->description : '',
                    $orderAssigned->order ? $orderAssigned->order->consignee_address : '',
                    Date('d-m-Y',strtotime($orderAssigned->created_at)),
                    $orderAssigned->rider ? $orderAssigned->rider->name : '',
                    $orderAssigned->order ? $orderAssigned->order->vendorWeight->ahlWeight->weight . ' ('. $orderAssigned->order->vendorWeight->city->first()->name . ')' : '',
                    $orderAssigned->order ? $orderAssigned->order->vendor_weight_price : '',
                    $orderAssigned->order ? $orderAssigned->order->vendor_tax_price : '',
                    $orderAssigned->order ? $orderAssigned->order->vendor_fuel_price : '',
                    $orderAssigned->order ? $orderAssigned->order->customerCity->name : '',

                ];
                // dd($data);
            }
        }
        elseif(Auth::user()->hasAnyRole('sales','hub_manager'))
        {
            foreach ($orderAssigneds as $key => $orderAssigned) {
                // code...
                $data[] = [
                    $orderAssigned->riderVendor ? $orderAssigned->riderVendor->vendor_name : '',
                    Date('d-m-Y',strtotime($orderAssigned->order->scanOrder ? $orderAssigned->order->scanOrder->created_at : '')),
                    Date('d-m-Y',strtotime($orderAssigned->order->scanOrder ? $orderAssigned->order->scanOrder->supervisor_scan_date : '')),
                    $orderAssigned->order ? $orderAssigned->order->order_reference : '',
                    $orderAssigned->order ? $orderAssigned->order->consignment_order_id : '',
                    $orderAssigned->order ? $orderAssigned->order->consignee_first_name.''.$orderAssigned->order->consignee_last_name : '',
                    $orderAssigned->order ? $orderAssigned->order->consignee_phone : '',
                    $orderAssigned->order ? $orderAssigned->order->consignment_cod_price : '',
                    $orderAssigned->order ? $orderAssigned->order->orderType->name : '',
                    $orderAssigned->order ? $orderAssigned->order->orderStatus->name : '',
                    $orderAssigned->tripStatus ? $orderAssigned->tripStatus->description : '',
                    $orderAssigned->order ? $orderAssigned->order->consignee_address : '',
                    Date('d-m-Y',strtotime($orderAssigned->created_at)),
                    $orderAssigned->rider ? $orderAssigned->rider->name : '',
                    $orderAssigned->order ? $orderAssigned->order->vendorWeight->ahlWeight->weight . ' ('. $orderAssigned->order->vendorWeight->city->first()->name . ')' : '',
                    $orderAssigned->order ? $orderAssigned->order->vendor_weight_price : '',
                    $orderAssigned->order ? $orderAssigned->order->vendor_tax_price : '',
                    $orderAssigned->order ? $orderAssigned->order->vendor_fuel_price : '',
                    $orderAssigned->order ? $orderAssigned->order->customerCity->name : '',

                ];
                // dd($data);
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
        if(Auth::user()->hasAnyRole('admin','cashier'))
        {
            return [
                'vendor_name',
                'pickup_date',
                'supervisor_scan_date',
                'order_reference',
                'consignment_order_id',
                'consignee_name',
                'consignee_phone',
                'consignment_cod_price',
                'order_type' ,
                'order_current_status',
                'Rider_Status',
                'consignee_address',
                'dispatch_date',
                'rider_name',
                'vendor_order_weight',
                'weight_price',
                'vendor_tax_price',
                'vendor_fuel',
                'city'
            ];
        }
        elseif(Auth::user()->hasAnyRole('financer','head_of_account','sales','hub_manager'))
        {
            return [
                'vendor_name',
                'pickup_date',
                'supervisor_scan_date',
                'order_reference',
                'consignment_order_id',
                'consignee_name',
                'consignee_phone',
                'consignment_cod_price',
                'order_type' ,
                'order_current_status',
                'Rider_Status',
                'consignee_address',
                'dispatch_date',
                'rider_name',
                'vendor_order_weight',
                'weight_price',
                'vendor_tax_price',
                'vendor_fuel',
                'city'
            ];
        }
    }
}
