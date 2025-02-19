<?php

namespace App\Imports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

use App\Models\Order;
use App\Models\City;
use App\Models\Vendor;
use App\Models\OrderType;
use App\Models\VendorWeight;
use App\Models\ParcelLimit;

use App\Helpers\Helper;
use App\Helpers\AHLHelper;

//use Maatwebsite\Excel\Concerns\WithStartRow;
//use Maatwebsite\Excel\Concerns\SkipOnEror;
//use Illuminate\Support\Collection;
//use Maatwebsite\Excel\Concerns\ToCollection;

class BulkImport implements ToModel, WithHeadingRow, SkipsOnError, WithValidation {

    use Importable,
        SkipsErrors;

    private $country;
    private $states;
    private $cities;
    private $orderTypes;
    private $vendorWeights;
    private $vendorWarehouseLocations;
    private $packaging;
    /**
     * @return int
     */
    //implement trait  WithStartRow
    /* public function startRow(): int
      {
      return 2;
      } */

    public function __construct()
    {
        // Dependencies are automatically resolved by the service container...

        $this->country = Helper::getCountry()->pluck('id');
        $this->states =  Helper::getStates()->pluck('id');
        $this->cities = Helper::getCities()->pluck('code');

        $this->orderTypes = AHLHelper::orderType()->pluck('id');
        $this->packaging = AHLHelper::packaging()->pluck('id');

        //pluck vendor weight id from vendor weight
        $this->vendorWeights = AHLHelper::vendorWeight()->pluck('id');
        //pluck vendor warehouse location id from warehouse locations
        $this->vendorWarehouseLocations = AHLHelper::vendorWarehouseLocation()->pluck('id');

    }

    public function model(array $row) {
        $authVendorId = Auth::user()->vendor_id;
        $check_city = City::where('code', $row['consignee_city'])->first();
        if(!empty($check_city))
        {
            $use_city = $check_city->id;
            $use_state = $check_city->state_id;
            $city_code = $check_city->code;

            $fetch_limit = ParcelLimit::where('city_id', $use_city)->first();
            if(!empty($fetch_limit))
            {
                $parcel_limit = $fetch_limit->limit;
            }
            else
            {
                $parcel_limit = 1;
            }
        }
        $check_city_origin = City::where('code', $row['consignment_origin_city'])->first();
        if(!empty($check_city_origin))
        {
            $use_city_origin = $check_city_origin->id;
        }
        $check_order_type = OrderType::where('name', $row['consignment_order_type'])->first();
        if(!empty($check_order_type))
        {
            $use_order_type = $check_order_type->id;
        }
        else
        {
            $use_order_type = 1;
        }
        $weight_price = VendorWeight::where('id',$row['vendor_weight_id'])->first();
        if(!empty($weight_price))
        {
            $vendor_weight_price = $weight_price->price;
            //fuel
            $payee = Vendor::where('id',$authVendorId)->first();
            $fuel_adjustment = ($weight_price->price * $payee->fuel)/100;
            $round_fuel_adjustment = round($fuel_adjustment);
            //GST
            $gst_adjustment = ($weight_price->price * $payee->gst)/100;
            $round_gst_adjustment = round($gst_adjustment);
        }
        else
        {
            $vendor_weight_price = 0;
            $round_fuel_adjustment = 0;
            $round_gst_adjustment = 0;
        }
        
            return new Order([
            'vendor_id' => $authVendorId,
            'order_reference' => '#'.$city_code.Helper::genrateOrderReference(),
            'parcel_limit' => $parcel_limit,
            'consignee_first_name' => $row['consignee_first_name'],
            'consignee_last_name' => $row['consignee_last_name'],
            'consignee_email' => $row['consignee_email'],
            'consignee_address' => $row['consignee_address'],
            'consignee_phone' => $row['consignee_phone'],
            'consignee_country' => 166,
            'consignee_state' => $use_state,
            'consignee_city' => $use_city,
            'consignment_order_id' => $row['consignment_order_id'],
            'consignment_order_type' => $use_order_type,
            'consignment_cod_price' => $row['consignment_cod_price'],
            'consignment_weight' => 0,
            'vendor_weight_id' => $row['vendor_weight_id'],
            'consignment_packaging' => 1,
            'consignment_pieces' => $row['consignment_pieces'],
            'consignment_description' => $row['consignment_description'],
            'consignment_origin_city' => $use_city_origin,
            'pickup_location' => $row['pickup_location_id'],
            'vendor_weight_price' => $vendor_weight_price,
            'vendor_fuel_price' => $round_fuel_adjustment,
            'vendor_tax_price' => $round_gst_adjustment,
            ]);
    }

    public function rules(): array
    {
        
        return [
            '*.consignee_first_name' => ['required'],
            '*.consignee_last_name' => ['required'],
            '*.consignee_email' => ['required', 'email'],
            '*.consignee_address' => ['required'],
            '*.consignee_phone' => ['required','integer'],//add integer value only
            '*.consignee_city' => [
                'required',
                Rule::in($this->cities)
            ],//add integer value only
            '*.consignment_order_id' => ['required'],
            '*.consignment_order_type' => [
                'required'
            ],//add integer value only
            '*.consignment_cod_price' => ['required','integer'],//add integer value only
            //'*.consignment_weight' => ['required'],
            '*.vendor_weight_id' => [
                'required',
                'integer',
                Rule::in($this->vendorWeights)
            ],//add integer value only
            '*.consignment_pieces' => ['required','integer'],//add integer value only
            '*.consignment_description' => ['required','string'],
            '*.consignment_origin_city' => [
                'required',
                Rule::in($this->cities)
            ],//add integer value only
            '*.pickup_location_id' => [
                'required',
                'integer',
                Rule::in($this->vendorWarehouseLocations)
            ],//add integer value only
        ];
}

}
