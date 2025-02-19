<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\ScanOrder;
use App\Models\UserCity;
use App\Models\City;
use Illuminate\Support\Facades\Log;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorDispatchReportExport implements FromArray,WithHeadings
{
    private $orderAssigned;

    public function __construct($from,$to,$vendor,$status)
    {
        try
        {
            if(Auth()->user()->hasAnyRole('admin','vendor_admin','hr','vendor_editor'))
            {
                if($from && $to && $vendor <> 'any' && $status <> 'any')
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->where('vendor_id', $vendor)
                                        ->where('order_status',$status)
                                        ->with([
                                            'vendorWeight' => function($query)
                                            {
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            },
                                            'pickupLocation' => function($query)
                                            {
                                                $query->select('id','address','vendor_id');
                                            }
                                        ])->get();
                }
                elseif($from && $to && $vendor == 'any' && $status <> 'any')
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->where('order_status',$status)
                                        ->with([
                                            'vendorWeight' => function($query)
                                            {
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            },
                                            'pickupLocation' => function($query)
                                            {
                                                $query->select('id','address','vendor_id');
                                            }
                                        ])->get();
                }
                elseif($from && $to && $vendor <> 'any' && $status == 'any')
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->where('vendor_id', $vendor)
                                        ->with([
                                            'vendorWeight' => function($query)
                                            {
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            },
                                            'pickupLocation' => function($query)
                                            {
                                                $query->select('id','address','vendor_id');
                                            }
                                        ])->get();
                }
                elseif($from && $to && $vendor == 'any' && $status == 'any')
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->with([
                                            'vendorWeight' => function($query)
                                            {
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            },
                                            'pickupLocation' => function($query)
                                            {
                                                $query->select('id','address','vendor_id');
                                            }
                                        ])->get();
                }       
                else
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->where('vendor_id', $vendor)
                                        ->with([
                                            'vendorWeight' => function($query)
                                            {
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            },
                                            'pickupLocation' => function($query)
                                            {
                                                $query->select('id','address','vendor_id');
                                            }
                                        ])->get();
                }
            }
            elseif(Auth()->user()->hasAnyRole('middle_man','financer','sales','first_man','sales','bd','bdm','csr','cashier','head_of_account','hub_manager','data_analyst'))
            {
                $userid     =   Auth()->user()->id;
                $usercity   =   UserCity::where('user_id',$userid)->pluck('city_id');
                $cities     =   City::whereIn('id',$usercity)->get();
                
                if($from && $to && $vendor <> 'any' && $status <> 'any')
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->where('vendor_id', $vendor)
                                        ->where('order_status',$status)
                                        ->whereIn('consignee_city',$usercity)
                                        ->with([
                                            'vendorWeight' => function($query)
                                            {
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            }
                                        ])->get();
                }
                elseif($from && $to && $vendor == 'any' && $status <> 'any')
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->where('order_status',$status)
                                        ->whereIn('consignee_city',$usercity)
                                        ->with([
                                            'vendorWeight' => function($query)
                                            {
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            }
                                        ])->get();
                }
                elseif($from && $to && $vendor <> 'any' && $status == 'any')
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->where('vendor_id', $vendor)
                                        ->whereIn('consignee_city',$usercity)
                                        ->with([
                                            'vendorWeight' => function($query){
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            }
                                        ])->get();
                }
                elseif($from && $to && $vendor == 'any' && $status == 'any')
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->whereIn('consignee_city',$usercity)
                                        ->with([
                                            'vendorWeight' => function($query)
                                            {
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            }
                                        ])->get();
                }       
                else
                {
                    $scan_order_data = ScanOrder::whereDate('created_at', '>=', $from)
                                        ->whereDate('created_at', '<=', $to)->pluck('order_id');

                    $orderAssigned  =   Order::whereIn('id', $scan_order_data)
                                        ->where('parcel_nature',1)
                                        ->where('vendor_id', $vendor)
                                        ->whereIn('consignee_city',$usercity)
                                        ->with([
                                            'vendorWeight' => function($query)
                                            {
                                                $query->select('id','ahl_weight_id','price','city_id')
                                                ->with([
                                                    'ahlWeight' =>  function($query)
                                                    {
                                                        $query->select('id','weight');
                                                    },
                                                    'city' => function($query)
                                                    {
                                                        $query->select('id','name');
                                                    },
                                                ]);
                                            },
                                            'orderStatus' => function($query)
                                            {
                                                $query->select('id','name');
                                            },
                                            'orderAssigned' => function($query)
                                            {
                                                $query->select('id','order_id','rider_id')
                                                ->with([
                                                    'rider' =>  function($query)
                                                    {
                                                        $query->select('id','name');
                                                    }
                                                ]);
                                            },
                                            'scanOrder' => function($query)
                                            {
                                                $query->select('id','order_id','created_at','supervisor_scan_date');
                                            }
                                        ])->get();
                }
            }

            //->toArray();
            //dd($orderAssigned);
            $this->orderAssigned = $orderAssigned;
        }
        catch (\Throwable $th)
        {
            Log::critical([
                "message"   =>  "Error in VendorDispatchReportExport",
                "error"     =>  $th->getMessage()." Line:".$th->getLine()
            ]);
        }
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        if(Auth()->user()->hasAnyRole('admin','vendor_admin','vendor_editor'))
        {
            foreach ($this->orderAssigned as $key => $orderAssigned)
            {
                $data[] = [
                    $orderAssigned->order_reference,
                    $orderAssigned->vendor ? $orderAssigned->vendor->vendor_name : 'N\A',
                    Date('d-m-Y',strtotime($orderAssigned->created_at)),
                    $orderAssigned->consignment_order_id,
                    $orderAssigned->consignee_first_name.''.$orderAssigned->consignee_last_name,
                    $orderAssigned->consignee_phone,
                    $orderAssigned->consignee_address,
                    Date('d-m-Y',strtotime($orderAssigned->scanOrder ? $orderAssigned->scanOrder->created_at : '')),
                    Date('d-m-Y',strtotime($orderAssigned->scanOrder ? $orderAssigned->scanOrder->supervisor_scan_date : '')),
                    $orderAssigned->orderStatus->name,
                    'Lahore',
                    $orderAssigned->consignment_pieces,
                    $orderAssigned->consignment_description,
                    $orderAssigned->vendorWeight->ahlWeight->weight . ' ('. $orderAssigned->vendorWeight->city->first()->name . ')',
                    $orderAssigned->vendor_weight_price,
                    $orderAssigned->vendor_tax_price,
                    $orderAssigned->vendor_fuel_price,
                    $orderAssigned->consignment_cod_price,
                    $orderAssigned->orderAssigned ? $orderAssigned->orderAssigned->rider->name : 'N\A',
                    Date('d-m-Y',strtotime($orderAssigned->updated_at)),
                    $orderAssigned->pickupLocation ? $orderAssigned->pickupLocation->address : 'N\A',
                ];
            }
        }
        elseif(Auth()->user()->hasAnyRole('middle_man','financer','sales','first_man','sales','bd','bdm','csr','cashier','head_of_account','hub_manager','hr','data_analyst'))
        {
            foreach ($this->orderAssigned as $key => $orderAssigned)
            {
                $data[] = [
                    $orderAssigned->order_reference,
                    $orderAssigned->vendor ? $orderAssigned->vendor->vendor_name : 'N\A',
                    Date('d-m-Y',strtotime($orderAssigned->created_at)),
                    $orderAssigned->consignment_order_id,
                    $orderAssigned->consignee_first_name.''.$orderAssigned->consignee_last_name,
                    $orderAssigned->consignee_phone,
                    $orderAssigned->consignee_address,
                    Date('d-m-Y',strtotime($orderAssigned->scanOrder ? $orderAssigned->scanOrder->created_at : '')),
                    Date('d-m-Y',strtotime($orderAssigned->scanOrder ? $orderAssigned->scanOrder->supervisor_scan_date : '')),
                    $orderAssigned->orderStatus->name,
                    'Lahore',
                    $orderAssigned->consignment_pieces,
                    $orderAssigned->consignment_description,
                    $orderAssigned->vendorWeight->ahlWeight->weight . ' ('. $orderAssigned->vendorWeight->city->first()->name . ')',
                    $orderAssigned->vendor_weight_price,
                    $orderAssigned->vendor_tax_price,
                    $orderAssigned->vendor_fuel_price,
                    $orderAssigned->consignment_cod_price,
                    $orderAssigned->orderAssigned ? $orderAssigned->orderAssigned->rider->name : 'N\A',
                    Date('d-m-Y',strtotime($orderAssigned->updated_at)),
                ];
            }
        }
        
        if(isset($data))
        {
            return $data;
        }
        else
        {
            return [];
        }
    }

    public function headings(): array
    {
        if(Auth()->user()->hasAnyRole('admin','vendor_admin','vendor_editor'))
        {
            return [
                'order_reference',
                'vendor_name',
                'booking_date',
                'consignment_order_id',
                'consignee_name',
                'consignee_phone',
                'consignee_address',
                'pickup_date',
                'dispatch_date',
                'order_current_status',
                'destination',
                'order_pieces',
                'order_description',
                'vendor_order_weight',
                'vendor_order_weight_price',
                'vendor_tax_price',
                'vendor_fuel',
                'consignment_cod_price',
                'rider_name',
                'last_update_at',
                'pickup_location'
            ];
        }
        elseif(Auth()->user()->hasAnyRole('middle_man','financer','sales','first_man','sales','bd','bdm','csr','cashier','head_of_account','hub_manager','hr','data_analyst'))
        {
            return [
                'order_reference',
                'vendor_name',
                'booking_date',
                'consignment_order_id',
                'consignee_name',
                'consignee_phone',
                'consignee_address',
                'pickup_date',
                'dispatch_date',
                'order_current_status',
                'destination',
                'order_pieces',
                'order_description',
                'vendor_order_weight',
                'vendor_order_weight_price',
                'vendor_tax_price',
                'vendor_fuel',
                'consignment_cod_price',
                'rider_name',
                'last_update_at'
            ];
        }
    }
}
