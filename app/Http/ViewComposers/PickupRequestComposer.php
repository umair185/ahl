<?php

namespace App\Http\ViewComposers;

//use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;

use Illuminate\View\View;
use App\Models\PickupRequest;
use App\Models\UserCity;
use Auth;

class PickupRequestComposer
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
        if(Auth::user()->hasAnyRole('admin','vendor_admin','vendor_editor','hr')){
            $requestCount = PickupRequest::where('status',1)->count();
        }
        elseif(Auth::user()->hasAnyRole('first_man','middle_man','supervisor','cashier','sales','financer','bd','bdm','csr','hub_manager','lead_supervisor','data_analyst','lead_supervisor','head_of_account')){

            $userId = Auth::user()->id;
            $usercitycount = UserCity::where('user_id',$userId)->pluck('city_id');

            $requestCount = PickupRequest::whereIn('city_id',$usercitycount)->where('status',1)->count();
        }
        else
        {
            $requestCount = PickupRequest::where('status',1)->count();
        }
        
        //$view->with('request_count', $this->pickupRequest->where('status',1)->count());
        $view->with('request_count', $requestCount);
    }
}
