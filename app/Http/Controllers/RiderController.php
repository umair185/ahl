<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Helper;
use App\Models\User;
use App\Models\City;
use App\Models\AssignCity;
use App\Models\UserCity;
use App\Models\ScanOrder;
use App\Models\Order;
use App\Models\UserDetail;
use App\Models\Grantor;
use Carbon;
use PDF;

class RiderController extends Controller
{
    public function assignCity()
    {
    	
    	$breadcrumbs = [
            'name' => 'Assign City To Rider', 
        ];

        if(Auth::user()->hasAnyRole('admin'))
        {
            $cities = Helper::getCities();
            $riders = User::whereHas(
                'roles', function($q){
                    $q->where('name', 'rider');
                }
            )
            ->with([
                'userDetail',
            ])
            ->where('status',1)
            ->get();
        }
        elseif(Auth::user()->hasAnyRole('supervisor','lead_supervisor'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            $riders = User::whereHas(
                'roles', function($q){
                    $q->where('name', 'rider');
                }
            )->whereHas('usercity',function($query) use($usercity){
                    $query->whereIn('city_id',$usercity);
            })
            ->with([
                'userDetail',
            ])
            ->where('status',1)
            ->get();
        }
    	return view('admin.rider.assign-city',compact('breadcrumbs','cities','riders'));
    }

    public function assignCityToRider(Request $request)
    {
        $validatedData = $request->validate([
            'city_id' => 'required',
            'rider_id' => 'required',
        ]);

        $cityId = $request->city_id;
        $riderId = $request->rider_id;

        $authUser = Auth::user();
        $authUserId = $authUser->id;
        
        $cityData = City::with([
            'state.country' => function($query){
                $query->select('id','name');
            }
        ])
        ->where('id',$cityId)
        ->get();

        $user = User::where('id',$riderId)
        ->with([
            'userDetail' => function($query){
                $query->select('id','user_id');
            },
        ])->first();

        $assignCity = [
            'country_id' => $cityData[0]->state->country->id,
            'state_id' => $cityData[0]->state->id,
            'city_id' => $cityData[0]->id,
            'assign_by' => $authUserId,
            'user_detail_id' => $user->userDetail->id,
        ];

        AssignCity::create($assignCity);

        return back()->with('success','City Assign To Rider!');
    }

    public function delayedOrders()
    {
        $check_order = ScanOrder::where('middle_man_scan_date', '>', \Carbon\Carbon::now()->subDays(2))->get();
        foreach($check_order as $check)
        {
            $time_check = round((strtotime(date("Y/m/d H:i:s")) - strtotime($check->middle_man_scan_date))/60);
            if($time_check > 1400)
            {
                $order = Order::where('id', $check->order_id)->where('delayed_status', 0)->where('order_status', 3)->first();
                if(!empty($order))
                {
                    $order->update(['delayed_status' => 1]);
                }
            }
        }

        return back();
    }

    public function staffVerification($id)
    {
        $breadcrumbs = [
            'name' => 'AHL Staff Verification', 
        ];

        $find_user = User::where('id', $id)->first();
        
        return view('admin/staff/add-verification',compact('find_user','breadcrumbs'));
    }

    public function saveStaffVerification(Request $request, $id)
    {
        $find_user = UserDetail::where('user_id', $id)->first();

        if($request->payment_cheque)
        {
            $name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
            $photo = $request->file('payment_cheque');
            $photo_name = $name.'_'.$photo->getClientOriginalName();

            $upload_dir = 'payment_cheque';
            if(!is_dir($upload_dir)) 
                mkdir($upload_dir, 0755, true);
                $path = $upload_dir.'/'.$photo_name;
                $photo->move($upload_dir,$photo_name);
        }
        else
        {
            $path = $find_user->payment_cheque;
        }

        if($request->house_image)
        {
            $house_image_name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
            $photo_house_image = $request->file('house_image');
            $house_image_photo = $house_image_name.'_'.$photo_house_image->getClientOriginalName();

            $house_image_upload_dir = 'house_image';
            if(!is_dir($house_image_upload_dir)) 
                mkdir($house_image_upload_dir, 0755, true);
                $path_house_image = $house_image_upload_dir.'/'.$house_image_photo;
                $photo_house_image->move($house_image_upload_dir,$house_image_photo);
        }
        else
        {
            $path_house_image = $find_user->house_image;
        }

        if($request->cnic_front)
        {
            $cnic_front_name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
            $photo_cnic_front = $request->file('cnic_front');
            $photo_cnic_front_two = $cnic_front_name.'_'.$photo_cnic_front->getClientOriginalName();

            $upload_dir_cnic_front = 'cnic';
            if(!is_dir($upload_dir_cnic_front)) 
                mkdir($upload_dir_cnic_front, 0755, true);
                $path_cnic_front = $upload_dir_cnic_front.'/'.$photo_cnic_front_two;
                $photo_cnic_front->move($upload_dir_cnic_front,$photo_cnic_front_two);
        }
        else
        {
            $path_cnic_front = $find_user->cnic_front;
        }

        if($request->cnic_back)
        {
            $cnic_back_name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
            $photo_cnic_back = $request->file('cnic_back');
            $photo_cnic_back_two = $cnic_back_name.'_'.$photo_cnic_back->getClientOriginalName();

            $upload_dir_cnic_back = 'cnic';
            if(!is_dir($upload_dir_cnic_back)) 
                mkdir($upload_dir_cnic_back, 0755, true);
                $path_cnic_back = $upload_dir_cnic_back.'/'.$photo_cnic_back_two;
                $photo_cnic_back->move($upload_dir_cnic_back,$photo_cnic_back_two);
        }
        else
        {
            $path_cnic_back = $find_user->cnic_back;
        }

        if($request->emergency_picture)
        {
            $emergency_picture = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
            $photo_emergency_picture = $request->file('emergency_picture');
            $photo_emergency_picture_two = $emergency_picture.'_'.$photo_emergency_picture->getClientOriginalName();

            $upload_dir_emergency = 'emergency';
            if(!is_dir($upload_dir_emergency)) 
                mkdir($upload_dir_emergency, 0755, true);
                $path_emergency_picture = $upload_dir_emergency.'/'.$photo_emergency_picture_two;
                $photo_emergency_picture->move($upload_dir_emergency,$photo_emergency_picture_two);
        }
        else
        {
            $path_emergency_picture = $find_user->emergency_picture;
        }

        $userDetail = [
            'cnic' => $request->cnic,
            'phone'  => $request->phone,
            'father_name' => $request->father_name,
            'father_cnic' => $request->father_cnic,
            'father_phone' => $request->father_phone,
            'marital_status' => $request->marital_status,
            'pin_location' => $request->pin_location,
            'siblings' => $request->siblings,
            'bike_number' => $request->bike_number,
            'address' => $request->staff_address,
            'permanent_staff_address' => $request->permanent_staff_address,
            'payment_cheque' => $path,
            'house_image' => $path_house_image,
            'dob' => $request->dob,
            'cnic_front' => $path_cnic_front,
            'cnic_back' => $path_cnic_back,
            'house_status' => $request->house_status,
            'live_from' => $request->live_from,
            'emergency_name' => $request->emergency_name,
            'emergency_relation' => $request->emergency_relation,
            'emergency_phone' => $request->emergency_phone,
            'emergency_picture' => $path_emergency_picture,
        ];
        $find_user->update($userDetail);

        $find_grantor = Grantor::where('user_id', $id)->first();
        if(!empty($find_grantor))
        {
            if($request->grantor_house)
            {
                $grantor_name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
                $photo_grantor = $request->file('grantor_house');
                $photo_name_grantor = $grantor_name.'_'.$photo_grantor->getClientOriginalName();

                $upload_dir_grantor = 'grantor';
                if(!is_dir($upload_dir_grantor)) 
                    mkdir($upload_dir_grantor, 0755, true);
                    $path_grantor = $upload_dir_grantor.'/'.$photo_name_grantor;
                    $photo_grantor->move($upload_dir_grantor,$photo_name_grantor);
            }
            else
            {
                $path_grantor = $find_grantor->grantor_house;
            }

            if($request->grantor_house_two)
            {
                $grantor_name_two = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
                $photo_grantor_two = $request->file('grantor_house_two');
                $photo_name_grantor_two = $grantor_name_two.'_'.$photo_grantor_two->getClientOriginalName();

                $upload_dir_grantor_two = 'grantor';
                if(!is_dir($upload_dir_grantor_two)) 
                    mkdir($upload_dir_grantor_two, 0755, true);
                    $path_grantor_two = $upload_dir_grantor_two.'/'.$photo_name_grantor_two;
                    $photo_grantor_two->move($upload_dir_grantor_two,$photo_name_grantor_two);
            }
            else
            {
                $path_grantor_two = $find_grantor->grantor_house_two;
            }

            if($request->grantor_image_one)
            {
                $grantor_image_name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
                $photo_grantor_image = $request->file('grantor_image_one');
                $photo_grantor_image_one = $grantor_image_name.'_'.$photo_grantor_image->getClientOriginalName();

                $upload_dir_grantor = 'grantor';
                if(!is_dir($upload_dir_grantor)) 
                    mkdir($upload_dir_grantor, 0755, true);
                    $path_grantor_image = $upload_dir_grantor.'/'.$photo_grantor_image_one;
                    $photo_grantor_image->move($upload_dir_grantor,$photo_grantor_image_one);
            }
            else
            {
                $path_grantor_image = $find_grantor->grantor_image_one;
            }

            if($request->grantor_image_two)
            {
                $grantor_image_two_name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
                $photo_grantor_image_two = $request->file('grantor_image_two');
                $photo_grantor_image_two_two = $grantor_image_two_name.'_'.$photo_grantor_image_two->getClientOriginalName();

                $upload_dir_grantor_two = 'grantor';
                if(!is_dir($upload_dir_grantor_two)) 
                    mkdir($upload_dir_grantor_two, 0755, true);
                    $path_grantor_image_two = $upload_dir_grantor_two.'/'.$photo_grantor_image_two_two;
                    $photo_grantor_image_two->move($upload_dir_grantor_two,$photo_grantor_image_two_two);
            }
            else
            {
                $path_grantor_image_two = $find_grantor->grantor_image_two;
            }
        }
        else
        {
            if($request->grantor_house)
            {
                $grantor_name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
                $photo_grantor = $request->file('grantor_house');
                $photo_name_grantor = $grantor_name.'_'.$photo_grantor->getClientOriginalName();

                $upload_dir_grantor = 'grantor';
                if(!is_dir($upload_dir_grantor)) 
                    mkdir($upload_dir_grantor, 0755, true);
                    $path_grantor = $upload_dir_grantor.'/'.$photo_name_grantor;
                    $photo_grantor->move($upload_dir_grantor,$photo_name_grantor);
            }
            else
            {
                $path_grantor = null;
            }

            if($request->grantor_house_two)
            {
                $grantor_name_two = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
                $photo_grantor_two = $request->file('grantor_house_two');
                $photo_name_grantor_two = $grantor_name_two.'_'.$photo_grantor_two->getClientOriginalName();

                $upload_dir_grantor_two = 'grantor';
                if(!is_dir($upload_dir_grantor_two)) 
                    mkdir($upload_dir_grantor_two, 0755, true);
                    $path_grantor_two = $upload_dir_grantor_two.'/'.$photo_name_grantor_two;
                    $photo_grantor_two->move($upload_dir_grantor_two,$photo_name_grantor_two);
            }
            else
            {
                $path_grantor_two = null;
            }

            if($request->grantor_image_one)
            {
                $grantor_image_name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
                $photo_grantor_image = $request->file('grantor_image_one');
                $photo_grantor_image_one = $grantor_image_name.'_'.$photo_grantor_image->getClientOriginalName();

                $upload_dir_grantor = 'grantor';
                if(!is_dir($upload_dir_grantor)) 
                    mkdir($upload_dir_grantor, 0755, true);
                    $path_grantor_image = $upload_dir_grantor.'/'.$photo_grantor_image_one;
                    $photo_grantor_image->move($upload_dir_grantor,$photo_grantor_image_one);
            }
            else
            {
                $path_grantor_image = null;
            }

            if($request->grantor_image_two)
            {
                $grantor_image_two_name = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(7 / strlen($x)))), 1, 7);
                $photo_grantor_image_two = $request->file('grantor_image_two');
                $photo_grantor_image_two_two = $grantor_image_two_name.'_'.$photo_grantor_image_two->getClientOriginalName();

                $upload_dir_grantor_two = 'grantor';
                if(!is_dir($upload_dir_grantor_two)) 
                    mkdir($upload_dir_grantor_two, 0755, true);
                    $path_grantor_image_two = $upload_dir_grantor_two.'/'.$photo_grantor_image_two_two;
                    $photo_grantor_image_two->move($upload_dir_grantor_two,$photo_grantor_image_two_two);
            }
            else
            {
                $path_grantor_image_two = null;
            }
        }

        $find_grantor_data = Grantor::where('user_id', $id)->first();
        if(!empty($find_grantor_data))
        {
            $userGrantorDetail = [
                'user_id' => $id,
                'grantor_name' => $request->grantor_name,
                'grantor_cnic'  => $request->grantor_cnic,
                'grantor_phone' => $request->grantor_phone,
                'grantor_father_name' => $request->grantor_father_name,
                'grantor_relation' => $request->grantor_relation,
                'grantor_pin_location' => $request->grantor_pin_location,
                'grantor_age' => $request->grantor_age,
                'grantor_job' => $request->grantor_job,
                'grantor_income' => $request->grantor_income,
                'grantor_address' => $request->grantor_address,
                'grantor_house' => $path_grantor,
                'grantor_name_two' => $request->grantor_name_two,
                'grantor_cnic_two'  => $request->grantor_cnic_two,
                'grantor_phone_two' => $request->grantor_phone_two,
                'grantor_father_name_two' => $request->grantor_father_name_two,
                'grantor_relation_two' => $request->grantor_relation_two,
                'grantor_pin_location_two' => $request->grantor_pin_location_two,
                'grantor_age_two' => $request->grantor_age_two,
                'grantor_job_two' => $request->grantor_job_two,
                'grantor_income_two' => $request->grantor_income_two,
                'grantor_address_two' => $request->grantor_address_two,
                'grantor_house_two' => $path_grantor_two,
                'grantor_image_one' => $path_grantor_image,
                'grantor_image_two' => $path_grantor_image_two,
            ];

            $find_grantor_data->update($userGrantorDetail);
        }
        else
        {
            $userGrantorDetail = [
                'user_id' => $id,
                'grantor_name' => $request->grantor_name,
                'grantor_cnic'  => $request->grantor_cnic,
                'grantor_phone' => $request->grantor_phone,
                'grantor_father_name' => $request->grantor_father_name,
                'grantor_relation' => $request->grantor_relation,
                'grantor_pin_location' => $request->grantor_pin_location,
                'grantor_age' => $request->grantor_age,
                'grantor_job' => $request->grantor_job,
                'grantor_income' => $request->grantor_income,
                'grantor_address' => $request->grantor_address,
                'grantor_house' => $path_grantor,
                'grantor_name_two' => $request->grantor_name_two,
                'grantor_cnic_two'  => $request->grantor_cnic_two,
                'grantor_phone_two' => $request->grantor_phone_two,
                'grantor_father_name_two' => $request->grantor_father_name_two,
                'grantor_relation_two' => $request->grantor_relation_two,
                'grantor_pin_location_two' => $request->grantor_pin_location_two,
                'grantor_age_two' => $request->grantor_age_two,
                'grantor_job_two' => $request->grantor_job_two,
                'grantor_income_two' => $request->grantor_income_two,
                'grantor_address_two' => $request->grantor_address_two,
                'grantor_house_two' => $path_grantor_two,
                'grantor_image_one' => $path_grantor_image,
                'grantor_image_two' => $path_grantor_image_two,
            ];

            Grantor::create($userGrantorDetail);
        }
        
        return redirect('/staff-list');
    }

    public function viewVerification($id)
    {
        $find_user = User::where('id', $id)->first();
        
        // return view('admin/staff/view-staff-verification',compact('find_user'));

        $pdf = PDF::loadView('admin/staff/view-staff-verification',compact('find_user'));
        $pdf_name =$find_user->name."_Staff_Verification.pdf";
//        dd($pdf_name);
        return $pdf->download($pdf_name);
    }

    public function getRiderInfo(Request $request)
    {
        $authUser = Auth::user();
        $authUserId = $authUser->id;
        $usercity = UserCity::where('user_id',$authUserId)->pluck('city_id');
        
        $rider_id = $request->rider_id;
        $find_rider = User::where('user_id', $rider_id)->where('status',1)
            ->whereHas('roles', function($q){
                $q->where('name', 'rider');
            })
            ->whereHas('usercity',function($query) use($usercity){
                $query->whereIn('city_id',$usercity);
            })->first();
        $html = '';

        if(!empty($find_rider))
        {
            $html .= '<p>"'.$find_rider->name.'" - "'.$find_rider->userDetail->cnic.'"</p>';
        }
        else
        {
            $html .='<p>No Rider Found</p>';
        }

        $data = [
            'status' => 1,
            'data' => $html,
        ];

        return response()->json(['response' => $data]);
    }
}
