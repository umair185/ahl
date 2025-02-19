<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use App\Models\UserDetail;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'vendor_id',
        'user_detail_id',
        'status',
        'created_by',
        'user_id',
        'password_status',
        'otp_status',
        'phone_number',
        'supervisor_id',
        'sup_datentime',
        'sup_assigned_by',
        'picker_id',
        'pickup_datentime',
        'pickup_assigned_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin() 
    {
        return $this->hasRole('admin'); // ?? something like this! should return true or false
    }

    public function isFirstMan() 
    {
        return $this->hasRole('first_man'); // ?? something like this! should return true or false
    }

    public function isVendorAdmin() 
    {
        return $this->hasRole('vendor_admin'); // ?? something like this! should return true or false
    }

    public function isVendorEditor() 
    {
        return $this->hasRole('vendor_editor'); // ?? something like this! should return true or false
    }

    public function isMiddleMan() 
    {
        return $this->hasRole('middle_man'); // ?? something like this! should return true or false
    }

    public function isSupervisor() 
    {
        return $this->hasAnyRole('supervisor','lead_supervisor'); // ?? something like this! should return true or false
    }

    public function isPicker() 
    {
        return $this->hasRole('picker'); // ?? something like this! should return true or false
    }

    public function isRider() 
    {
        return $this->hasRole('rider'); // ?? something like this! should return true or false
    }

    public function isCashier() 
    {
        return $this->hasRole('cashier'); // ?? something like this! should return true or false
    }

    public function isFinancer() 
    {
        return $this->hasRole('financer','head_of_account'); // ?? something like this! should return true or false
    }

    public function isSales() 
    {
        return $this->hasRole('sales'); // ?? something like this! should return true or false
    }
    
    
    public function isCSR() 
    {
        return $this->hasRole('csr'); // ?? something like this! should return true or false
    }

    public function isBD() 
    {
        return $this->hasRole('bd'); // ?? something like this! should return true or false
    }

    public function isBDM() 
    {
        return $this->hasRole('bdm'); // ?? something like this! should return true or false
    }

    public function isHR() 
    {
        return $this->hasRole('hr'); // ?? something like this! should return true or false
    }
    
    public function isHubManager() 
    {
        return $this->hasRole('hub_manager'); // ?? something like this! should return true or false
    }

    public function isDataAnalyst() 
    {
        return $this->hasRole('data_analyst'); // ?? something like this! should return true or false
    }

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'user_id', 'id');
    }

    public function userGrantor()
    {
        return $this->hasOne(Grantor::class, 'user_id', 'id');
    }
    
    public function vendorDetail()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id');
    }

    public function pickerVendor()
    {
        return $this->hasMany(PickerAssign::class, 'picker_id', 'id');
    }
    
    public function totalOrders()
    {
        return $this->hasMany(OrderAssigned::class, 'rider_id', 'id');
    }
    public function deliveredOrders()
    {
        return $this->hasMany(OrderAssigned::class, 'rider_id', 'id')->where('trip_status_id', 4);
    }
    public function cancelOrders()
    {
        return $this->hasMany(OrderAssigned::class, 'rider_id', 'id')->whereIn('trip_status_id', [1,2,3,4,5]);
    }
    public function returnOrders()
    {
        return $this->hasMany(OrderAssigned::class, 'rider_id', 'id')->whereIn('trip_status_id', [6]);
    }
    public function forceOrders()
    {
        return $this->hasMany(OrderAssigned::class, 'rider_id', 'id')->whereIn('trip_status_id', [1,2,3,4]);
    }
    public function forceFulOrders()
    {
        return $this->hasMany(OrderAssigned::class, 'rider_id', 'id')->where('force_status', 1);
    }
    public function totalOrdersSum()
    {
        return $this->hasMany(OrderAssigned::class, 'rider_id', 'id');
    }
    public function deliveredOrdersSum()
    {
        return $this->hasMany(OrderAssigned::class, 'rider_id', 'id')->where('trip_status_id', 4);
    }
    public function usercity(){
        return $this->hasMany(UserCity::class,'user_id','id');
    }

    public function scanOrder()
    {
        return $this->hasMany(ScanOrder::class, 'picker_id', 'id');
    }

    public function saleVendor()
    {
        return $this->hasMany(Vendor::class, 'poc', 'id');
    }

    public function csrVendor()
    {
        return $this->hasMany(Vendor::class, 'csr', 'id');
    }

    public function pickupVendor()
    {
        return $this->hasMany(Vendor::class, 'pickup', 'id');
    }

    public function supervisorRiders()
    {
        return $this->hasMany(User::class, 'supervisor_id', 'id');
    }

    public function supervisorPerson() {
        return $this->hasOne(User::class, 'id','supervisor_id');
    }

    public function supervisorAssignedBy() {
        return $this->hasOne(User::class, 'id','sup_assigned_by');
    }

    public function pickerRiders()
    {
        return $this->hasMany(User::class, 'picker_id', 'id');
    }

    public function pickerPerson() {
        return $this->hasOne(User::class, 'id','picker_id');
    }

    public function pickerAssignedBy() {
        return $this->hasOne(User::class, 'id','pickup_assigned_by');
    }
}
