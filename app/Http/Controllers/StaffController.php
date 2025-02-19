<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\ScanOrder;
use App\Models\City;
use App\Models\UserCity;

use Helper;
use AHLHelper;

class StaffController extends Controller
{
    public function list()
    {
        $breadcrumbs = [
            'name' => 'Staff List', 
        ];

        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $allowStaffList = AHLHelper::staffAllow();
        $authUser = Auth::user();
        if($authUser->isAdmin()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 1)
            ->get();
        }
        elseif($authUser->isHr()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 1)
            ->get();
        }elseif($authUser->isSupervisor()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 1)
            ->get();
        }elseif($authUser->isHubManager()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 1)
            ->get();
        }elseif($authUser->isFirstMan()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 1)
            ->get();
        }else{
            
            $authCityId = [$authUser->userDetail->city_id];
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', [$allowStaffList]);
                }
            )->whereHas('usercity',function($query) use($usercity){
                $query->whereIn('city_id',$usercity);
            }
            )->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 1)
            ->get();
        }

        //staff
        $first_man = 0;
        $picker = 0;
        $middle_man = 0;
        $supervisor = 0;
        $cashier = 0;
        $rider = 0;
        $financer = 0;
        $sales = 0;
        
        //estimate
        $active = 0;
        $block = 0;

        $missingAttributes = [];

        foreach ($staffList as $key => $staff) {

            switch ($staff->roles[0]->name) {
               case 'first_man':
                   $first_man++;
                   break;
                case 'picker':
                   $picker++;
                   break;
                case 'middle_man':
                   $middle_man++;
                   break;
                case 'supervisor':
                   $supervisor++;
                   break;
                case 'cashier':
                   $cashier++;
                   break;
                case 'rider':
                   $rider++;
                   break;
                case 'financer':
                   $financer++;
                   break;
                case 'sales':
                   $sales++;
                   break;
               
               default:
                   // code...
                   break;
            }

            if($staff->status == 1){
                $active++;
            }else{
                $block++;
            }

            if ($staff->userDetail) {
                $details = $staff->userDetail->toArray();

                foreach ($details as $key => $value) {
                    if (is_null($value)) {
                        $missingAttributes[$staff->id][] = $key;
                    }
                }
            } else {
                // Handle case where userDetail relation does not exist
                $missingAttributes[$staff->id] = ['user_detail_missing'];
            }
        }

        $attributesToCheck = ['cnic', 'phone', 'father_name','father_cnic', 'father_phone', 'marital_status','pin_location', 'dob', 'siblings','bike_number','address','permanent_staff_address','house_status','live_from','house_image','payment_cheque','cnic_front','cnic_back','emergency_name','emergency_phone','emergency_relation','emergency_picture'];

        $grantorAttributesToCheck = ['grantor_name', 'grantor_cnic', 'grantor_phone','grantor_father_name', 'grantor_relation', 'grantor_pin_location','grantor_age', 'grantor_job', 'grantor_income','grantor_address','grantor_house','grantor_image_one','grantor_name_two','grantor_cnic_two','grantor_phone_two','grantor_father_name_two','grantor_relation_two','grantor_pin_location_two','grantor_age_two','grantor_job_two','grantor_income_two','grantor_address_two','grantor_house_two','grantor_image_two'];

        $staffWithNulls = $staffList->map(function ($staff_value) use ($attributesToCheck, $grantorAttributesToCheck) {
            $nullAttributes = [];

            if ($staff_value->userDetail) {

                foreach ($attributesToCheck as $attribute) {
                    if (is_null($staff_value->userDetail->$attribute)) {
                        $nullAttributes[] = 'userDetail: '. $attribute;  // Add the field name to the list of null fields
                    }
                }
            } else {
                $nullAttributes[] = 'Basic Verification Missing';
            }

            if ($staff_value->userGrantor) {

                foreach ($grantorAttributesToCheck as $attribute) {
                    if (is_null($staff_value->userGrantor->$attribute)) {
                        $nullAttributes[] = 'userGrantor: '. $attribute;  // Add the field name to the list of null fields
                    }
                }
            } else {
                $nullAttributes[] = 'Grantor Information Missing';
            }

            // Attach the null attributes info to the staff record
            $staff_value->null_fields = $nullAttributes;

            return $staff_value;
        });

        $staffCount = [
            'first_man' => $first_man,
            'picker' => $picker,
            'middle_man' => $middle_man,
            'sales' => $sales,
            'supervisor' => $supervisor,
            'cashier' => $cashier,
            'rider' => $rider,
            'financer' => $financer,
        ];

        $estimateUser = [
            'active' => $active,
            'block' => $block
        ];

    	return view('admin.staff.list',compact('staffCount','estimateUser','staffList','breadcrumbs','missingAttributes','staffWithNulls'));
    }
    
    public function blockStaffList()
    {
        $breadcrumbs = [
            'name' => 'Block Staff List', 
        ];

        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $allowStaffList = AHLHelper::staffAllow();
        $authUser = Auth::user();
        if($authUser->isAdmin()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 0)
            ->get();
        }
        elseif($authUser->isHr()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 0)
            ->get();
        }elseif($authUser->isSupervisor()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 0)
            ->get();
        }elseif($authUser->isHubManager()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 0)
            ->get();
        }elseif($authUser->isFirstMan()){ 
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', $allowStaffList);
                }
            )
            ->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 0)
            ->get();
        }else{
            
            $authCityId = [$authUser->userDetail->city_id];
            $staffList = User::whereHas(
                'roles', function($q) use($allowStaffList){
                    $q->select(['id','name'])->whereIn('id', [$allowStaffList]);
                }
            )->whereHas('usercity',function($query) use($usercity){
                $query->whereIn('city_id',$usercity);
            }
            )->with([
                'userDetail',
                'roles' => function($query){
                    $query->select('id','name');
                }
            ])->where('status', 0)
            ->get();
        }

        //staff
        $first_man = 0;
        $picker = 0;
        $middle_man = 0;
        $supervisor = 0;
        $cashier = 0;
        $rider = 0;
        $financer = 0;
        $sales = 0;
        
        //estimate
        $active = 0;
        $block = 0;

        $missingAttributes = [];

        foreach ($staffList as $key => $staff) {

            switch ($staff->roles[0]->name) {
               case 'first_man':
                   $first_man++;
                   break;
                case 'picker':
                   $picker++;
                   break;
                case 'middle_man':
                   $middle_man++;
                   break;
                case 'supervisor':
                   $supervisor++;
                   break;
                case 'cashier':
                   $cashier++;
                   break;
                case 'rider':
                   $rider++;
                   break;
                case 'financer':
                   $financer++;
                   break;
                case 'sales':
                   $sales++;
                   break;
               
               default:
                   // code...
                   break;
            }

            if($staff->status == 1){
                $active++;
            }else{
                $block++;
            }

            if ($staff->userDetail) {
                $details = $staff->userDetail->toArray();

                foreach ($details as $key => $value) {
                    if (is_null($value)) {
                        $missingAttributes[$staff->id][] = $key;
                    }
                }
            } else {
                // Handle case where userDetail relation does not exist
                $missingAttributes[$staff->id] = ['user_detail_missing'];
            }
        }

        $attributesToCheck = ['cnic', 'phone', 'father_name','father_cnic', 'father_phone', 'marital_status','pin_location', 'dob', 'siblings','bike_number','address','permanent_staff_address','house_status','live_from','house_image','payment_cheque','cnic_front','cnic_back','emergency_name','emergency_phone','emergency_relation','emergency_picture'];

        $grantorAttributesToCheck = ['grantor_name', 'grantor_cnic', 'grantor_phone','grantor_father_name', 'grantor_relation', 'grantor_pin_location','grantor_age', 'grantor_job', 'grantor_income','grantor_address','grantor_house','grantor_image_one','grantor_name_two','grantor_cnic_two','grantor_phone_two','grantor_father_name_two','grantor_relation_two','grantor_pin_location_two','grantor_age_two','grantor_job_two','grantor_income_two','grantor_address_two','grantor_house_two','grantor_image_two'];

        $staffWithNulls = $staffList->map(function ($staff_value) use ($attributesToCheck, $grantorAttributesToCheck) {
            $nullAttributes = [];

            if ($staff_value->userDetail) {

                foreach ($attributesToCheck as $attribute) {
                    if (is_null($staff_value->userDetail->$attribute)) {
                        $nullAttributes[] = 'userDetail: '. $attribute;  // Add the field name to the list of null fields
                    }
                }
            } else {
                $nullAttributes[] = 'Basic Verification Missing';
            }

            if ($staff_value->userGrantor) {

                foreach ($grantorAttributesToCheck as $attribute) {
                    if (is_null($staff_value->userGrantor->$attribute)) {
                        $nullAttributes[] = 'userGrantor: '. $attribute;  // Add the field name to the list of null fields
                    }
                }
            } else {
                $nullAttributes[] = 'Grantor Information Missing';
            }

            // Attach the null attributes info to the staff record
            $staff_value->null_fields = $nullAttributes;

            return $staff_value;
        });

        $staffCount = [
            'first_man' => $first_man,
            'picker' => $picker,
            'middle_man' => $middle_man,
            'sales' => $sales,
            'supervisor' => $supervisor,
            'cashier' => $cashier,
            'rider' => $rider,
            'financer' => $financer,
        ];

        $estimateUser = [
            'active' => $active,
            'block' => $block
        ];

        return view('admin.staff.block-staff',compact('staffCount','estimateUser','staffList','breadcrumbs','missingAttributes','staffWithNulls'));
    }

    public function vendorRecord(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Staff Record', 
        ];

        //$staffId = $request->id;
        //$staffRole = $request->role;
        $staffId = Helper::decrypt($request->id);
        $staffRole = Helper::decrypt($request->role);

        $staff = User::where('id',$staffId)
        ->with([
            'userDetail.assignCity' => function($userDetailQuery){
                $userDetailQuery->with([
                    'userCountry' => function($userCountryQuery){
                        $userCountryQuery->select('id','name');
                    },
                    'userState' => function($userStateQuery){
                        $userStateQuery->select('id','name');
                    },
                    'userCity' => function($userCityQuery){
                        $userCityQuery->select('id','name');
                    },
                ]);
            },
        ])
        ->first();
        
        switch ($staffRole) {
            case 4:
                //middle man
                //$orderParcel = ScanOrder::where('middle_man_id',$staffId)->with('scanByMiddleMan')->count();

                //commission
                $commission = Helper::StaffCommission($staff);
                //$commission = $orderParcel * $staff->userDetail->commission;

                break;
            case 5:
                //supervisor
                //$orderParcel = ScanOrder::where('supervisor_id',$staffId)->with('scanBySupervisor')->count();

                //commission
                $commission = Helper::StaffCommission($staff);
                //$commission = $orderParcel * $staff->userDetail->commission;
                break;
            case 6:
                //picker
                //$orderParcel = ScanOrder::where('picker_id',$staffId)->with('scanByPicker')->count();

                //commission
                $commission = Helper::StaffCommission($staff);

                //$commission = $orderParcel * $staff->userDetail->commission;
                break;
            case 7:
                //rider when trip status is 3 mean's complete
                //$orderParcel = OrderAssigned::where(['rider_id'=>$staffId,'trip_status'=>3])->count();
                
                //commission
                //$commission = $orderParcel * $staff->userDetail->commission;
                $commission = Helper::StaffCommission($staff);
                break;
            
            default:
                # code...
                break;
        }



        return view('admin.staff.record',compact('breadcrumbs','staff','commission'));

    }

    public function staffUpdate(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Update Staff', 
        ];

        //$staffId = $request->id;
        $staffId = Helper::decrypt($request->id);
        $staff = User::where('id',$staffId)->with('userDetail')->first();

        $cities = City::all();
        $allowStaffList = AHLHelper::staffAllow();

        $roles = DB::table('roles')->whereIn('id',$allowStaffList)->get();

        return view('admin.staff.staff-update',compact('breadcrumbs','staff','roles'));
    }

    public function updateStaff(Request $request)
    {
        $rules = [
            'login_password' => 'nullable|min:6',
            'login_confirm_password' => 'nullable|same:login_password',
        ];

        $authUser = Auth::user();
        $authUserId = $authUser->id;
        
        //request
        $staffId = $request->staff_id;

        $staffData = User::find($staffId);

        //dd($staffData);
        $staffDetailData = UserDetail::where('user_id',$staffId)->first();
        $name = $request->user_name;
        $email = $request->login_email;
        $password = $request->login_password;
        $phone = $request->phone;
        $address = $request->staff_address;
        $cnic = $request->cnic;
        $salary = $request->salary;
        $user_id = $request->user_id;
        $staff_role = $request->staff_role;
        //new data
        $account_number = $request->account_number;
        $account_title = $request->account_title;
        $bank_name = $request->bank_name;
        $reporting_to = $request->reporting_to;
        $location = $request->location;
        $hiring_by = $request->hiring_by;
        $interviewed_by = $request->interviewed_by;
        $hiring_platform = $request->hiring_platform;
        $joining_date = $request->joining_date;
        $leaving_date = $request->leaving_date;
        $company_assets = $request->company_assets;
        $remarks = $request->remarks;

        $update_data = [
            'account_number' => $account_number,
            'account_title' => $account_title,
            'bank_name' => $bank_name,
            'reporting_to' => $reporting_to,
            'location' => $location,
            'hiring_by' => $hiring_by,
            'interviewed_by' => $interviewed_by,
            'hiring_platform' => $hiring_platform,
            'joining_date' => $joining_date,
            'leaving_date' => $leaving_date,
            'company_assets' => $company_assets,
            'remarks' => $remarks,
        ];

        $validation = Validator::make($request->all(),$rules);
        if ($validation->fails()) {
            return redirect()->back()
                ->withErrors($validation)
                ->withInput();
        }

        if($staff_role)
        {
            $staffData->roles()->detach();
            $staffData->assignRole($request->staff_role);
        }

        if($name){
            $staffData->name =  $name;
        }

        if($email){
            $staffData->email = $email;
        }

        if($password){
            $staffData->password =  Hash::make($password);
            $staffData->password_status =  1;
        }

        if($user_id){
           $staffData->user_id =  $user_id;
        }

        if($phone){
            $staffDetailData->phone = $phone;
            $staffData->phone_number =  $phone;
        }

        if($address){
            $staffDetailData->address = $address;
        }

        if($cnic){
            $staffDetailData->cnic = $cnic;
        }

        if($salary){
           $staffDetailData->salary =  $salary;
        }

        if(Auth::user()->id == $staffId){
            $staffData->save();
        }else{
            $staffData->save();
            $staffDetailData->save();

            $staffDetailData->update($update_data);
        }


        return back()->with(['success'=>'Credentials Updated!']);
    }

    public function finduser($id){

       
        // if(Auth::user()->hasAnyRole('admin'))
        // {
            $user = User::find($id);
            $cities = City::all();

            $usercity = UserCity::where('user_id',$user->id)->pluck('city_id');
        // }

        // if(Auth::user()->hasAnyRole('supervisor','first_man'))
        // {
        //     $user = User::find($id);
        //     $userId = Auth::user()->id;
        //     $usercities = UserCity::where('user_id',$userId)->pluck('city_id');
        //     $cities  = City::whereIn('id', $usercities)->get();

        //     $usercity = UserCity::where('user_id',$user->id)->pluck('city_id');
        // }

        return view('admin.staff.assign_city',compact('user','cities','usercity'));

        
    }

    public function saveuserCity(Request $request){

        $city_id = $request->city_id;
        $user_id = $request->userid;

        $get_data = UserCity::where('user_id', $user_id)->get();
        foreach($get_data as $data)
        {
            $data->delete();
        }

        foreach($city_id as $key => $id){
            $savecity = new UserCity();

            $savecity->user_id = $user_id;
            $savecity->city_id = $city_id[$key];

            $savecity->save();

        }

        return redirect()->back();
    }

    public function passwordUpdate(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Update Staff', 
        ];

        //$staffId = $request->id;
        $staffId = Helper::decrypt($request->id);
        $staff = User::where('id',$staffId)->with([
            'userDetail' => function($query){
                $query->select('id','user_id','cnic','salary','phone','address');
            }
        ])
        ->first();

        return view('admin.staff.password-update',compact('breadcrumbs','staff'));
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'login_password' => 'nullable|min:6',
            'login_confirm_password' => 'nullable|same:login_password',
        ];
        
        $staffId = $request->staff_id;

        $staffData = User::find($staffId);

        $password = $request->login_password;

        $validation = Validator::make($request->all(),$rules);
        if ($validation->fails()) {
            return redirect()->back()
                ->withErrors($validation)
                ->withInput();
        }

        if($password){
            $staffData->password =  Hash::make($password);
            $staffData->password_status =  1;
        }

        $staffData->save();


        return back()->with(['success'=>'Credentials Updated!']);
    }
}
