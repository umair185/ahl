<?php

namespace App\Http\ViewComposers;

//use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

use App\Models\ShiperAdviser;
use App\Models\Order;
use App\Models\UserCity;

class ShiperAdviseComposer
{
    /**
     * The user repository implementation.
     *
     * @var \App\Repositories\UserRepository
     */
    //protected $pickupRequest;

    /**
     * Create a new profile composer.
     *
     * @param  \App\Repositories\UserRepository  $users
     * @return void
     */
    /*public function __construct(PickupRequest $pickupRequest)
    {
        // Dependencies are automatically resolved by the service container...
        $this->pickupRequest = $pickupRequest;
    }*/

    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if(Auth::user()->isVendorAdmin() || Auth::user()->isVendorEditor()){

            $authVendorId = Auth::user()->vendor_id;
            //for vendor dashbaord
            $shipper_advise = ShiperAdviser::all()->pluck('order_id');
            //$requestCount = ShiperAdviser::where('status',1)->count();
            $adviseParcelsCount = Order::whereNotIn('id', $shipper_advise)->where(['vendor_id'=>$authVendorId])->whereIn('order_status',[9])
            ->count();
        }else{
            //for ahl dashboard
            //$adviseParcels = ShiperAdviser::get()->count();

            if(Auth::user()->hasAnyRole('admin','hr')){
                $adviseParcelsOrder = Order::whereIn('order_status',[9])
                ->with([
                    'shiperAdviser' => function($query){
                        $query->select('id','order_id','advise');
                    }
                ])
                ->get();
            }
            elseif(Auth::user()->hasAnyRole('first_man','middle_man','supervisor','cashier','sales','financer','vendor_admin','csr','bd','bdm','hub_manager','data_analyst','lead_supervisor','head_of_account'))
            {
                $userId = Auth::user()->id;
                $usercitycount = UserCity::where('user_id',$userId)->pluck('city_id');

                $adviseParcelsOrder = Order::whereIn('consignee_city', $usercitycount)->whereIn('order_status',[9])
                ->with([
                    'shiperAdviser' => function($query){
                        $query->select('id','order_id','advise');
                    }
                ])
                ->get();
            }
            
            
            $adviseParcelsCount = 0;
            foreach ($adviseParcelsOrder as $key => $order) {
                if($order->shiperAdviser){
                    $adviseParcelsCount = $adviseParcelsCount + 1;
                }
            }
        }
        //$view->with('request_count', $this->pickupRequest->where('status',1)->count());
        $view->with('advise_parcels_count', $adviseParcelsCount);
    }
}
