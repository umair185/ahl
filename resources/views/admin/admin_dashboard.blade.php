@extends('layouts.app')

@section('content')
<?php
function format_number_in_k_notation($number)
{
  if ($number < 1000) {
  return sprintf('%d', $number);
  }

  if ($number < 1000000) {
  return sprintf('%d%s', floor($number / 1000), 'K+');
  }

  if ($number >= 1000000 && $number < 1000000000) {
  return sprintf('%d%s', floor($number / 1000000), 'M+');
  }

  if ($number >= 1000000000 && $number < 1000000000000) {
  return sprintf('%d%s', floor($number / 1000000000), 'B+');
  }

  return sprintf('%d%s', floor($number / 1000000000000), 'T+');
}
?>
<div class="page-body">
    <div class="row">
        
        <!-- Project statustic start -->
        <div class="col-xl-12">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <form method="get">
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="date" name="from" id="from" class="form-control" value="<?php
                                    if (isset($_GET['from'])) {
                                        echo $_GET['from'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>To Date</label>
                                    <input type="date" name="to" id="to" class="form-control" value="<?php
                                    if (isset($_GET['to'])) {
                                        echo $_GET['to'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>Vendors</label>
                                    <select name="vendor" id="vendor" class="form-control" required="required">
                                        <option value="any">Any</option>
                                        @foreach($vendors as $vendor)
                                        <option {{$vendorRequest == $vendor->id ? 'selected' : ''}} value="{{$vendor->id}}">{{$vendor->vendor_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>Cities</label>
                                    <select name="city" id="city" class="form-control" required="required">
                                        <option value="any">Any</option>
                                        @foreach($cities as $city)
                                        <option {{$cityRequest == $city->id ? 'selected' : ''}} value="{{$city->id}}">{{$city->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-2 col-md-6">
                                <div class="form-group">
                                    <br>
                                    <button type="submit" class="btn btn-primary mt-1">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @hasanyrole('admin|hub_manager|lead_supervisor|cashier')
        <div class="col-xl-12">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <div class="row">
                        @hasanyrole('admin|hub_manager|lead_supervisor')
                        <div class="col-xl-3 col-md-6">
                            <h6>Total Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{$total_parcel}}</h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Delivered Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{$delivered_parcel}}</h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Return to Vendor Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{$returntovendor_parcel}}</h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>In-Progress Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">{{$pending}}</h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>In-Progress Parcels<br>(At-Ahl & Re-Attempt)</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">{{$pending_new}}</h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Cancelled Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">{{$allCancelledParcel}}</h5>
                        </div>
                        @endhasanyrole
                        @hasanyrole('admin|hub_manager|lead_supervisor|cashier')
                        <div class="col-xl-3 col-md-6">
                            <h6>Delivery Ratio</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{$delivery_ratio}}%</h5>
                        </div>
                        @endhasanyrole
                    </div>
                </div>
            </div>
        </div>
        @endhasanyrole
        @hasanyrole('admin')
        <div class="col-xl-12">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <h6>Overall Parcels Amount</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span title="<?php echo number_format($overall_sum) ?>"><?php echo format_number_in_k_notation($overall_sum); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Delivered Parcels Amount</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span title="<?php echo number_format($delivered_sum) ?>"><?php echo format_number_in_k_notation($delivered_sum); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Return to Vendor Parcels Amount</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span title="<?php echo number_format($returntovendor_parcel_sum) ?>"><?php echo format_number_in_k_notation($returntovendor_parcel_sum); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Not Delivered Parcels Amount</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. <span title="<?php echo number_format($pending_sum) ?>"><?php echo format_number_in_k_notation($pending_sum); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Cancelled Parcels Amount</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. <span title="<?php echo number_format($cancelled_sum) ?>"><?php echo format_number_in_k_notation($cancelled_sum); ?></span></h5>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        @endhasanyrole
        
        @hasanyrole('cashier|head_of_account|admin|financer|hub_manager|lead_supervisor|supervisor|data_analyst')
        <div class="col-xl-12">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <div class="row">
                        @hasanyrole('cashier|head_of_account|admin|financer|hub_manager|lead_supervisor|data_analyst')
                        <div class="col-xl-3 col-md-6">
                            <h6>Today Expected Amount</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span title="<?php echo number_format($expected_order_amount) ?>"><?php echo format_number_in_k_notation($expected_order_amount); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Delivered Parcel Amount</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span title="<?php echo number_format($expected_order_delivered_amount) ?>"><?php echo format_number_in_k_notation($expected_order_delivered_amount); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Remaing Parcel Amount</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. <span title="<?php echo number_format($expected_order_remaining_amount) ?>"><?php echo format_number_in_k_notation($expected_order_remaining_amount); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Cash Collected by Cashier</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. <span title="<?php echo number_format($cash_collected) ?>"><?php echo format_number_in_k_notation($cash_collected); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>In-Cash Collection</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. <span title="<?php echo number_format($in_cash_collected) ?>"><?php echo format_number_in_k_notation($in_cash_collected); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>IBFT Collection</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. <span title="<?php echo number_format($ibft_collected) ?>"><?php echo format_number_in_k_notation($ibft_collected); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>COD Parcels in Process</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. <span title="<?php echo number_format($cod_parcel) ?>"><?php echo format_number_in_k_notation($cod_parcel); ?></span></h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>COD Parcels at AHL</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. <span title="<?php echo number_format($cod_parcel_ahl) ?>"><?php echo format_number_in_k_notation($cod_parcel_ahl); ?></span></h5>
                        </div>
                        @endhasanyrole
                        @hasanyrole('admin')
                        <div class="col-xl-3 col-md-6">
                            <h6>Commission + Fuel</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. <span title="<?php echo number_format($commission_value) ?>"><?php echo format_number_in_k_notation($commission_value); ?></span></h5>
                        </div>
                        @endhasanyrole
                        @hasanyrole('admin|hub_manager|lead_supervisor|supervisor|cashier')
                        <div class="col-xl-3 col-md-6">
                            <h6>Total Dispatch Riders</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">{{count($rider_count)}}</h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Cash Closed Riders</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">{{count($cash_count)}}</h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Remaining Riders</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">{{(count($rider_count)) - (count($cash_count))}}</h5>
                        </div>
                        @endhasanyrole
                    </div>
                </div>
            </div>
        </div>
        @endhasanyrole
        <!-- Project statustic end -->

        <!--  sale analytics start -->
        <div class="col-xl-12 col-md-12">
            <div class="row">
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #E25041">
                        <a href="{{route('awaitingAdminReport',Helper::encrypt(1))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$awaiting}}</h4>
                                    <p class="m-0">Awaiting Pickup</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #E4AE39">
                        <a href="{{route('pickupAdminReport',Helper::encrypt(2))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$pickup}}</h4>
                                    <p class="m-0">Pickup</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #33C1FF">
                        <a href="{{route('atAhlAdminReport',Helper::encrypt(3))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$warehouse}}</h4>
                                    <p class="m-0">At Ahl Warehouse</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #905AC4">
                        <a href="{{route('dispatchedAdminReport',Helper::encrypt(5))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$dispatched}}</h4>
                                    <p class="m-0">Out for Delivery</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #D548CB">
                        <a href="{{route('deliveredAdminReport',Helper::encrypt(6))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$delivered}}</h4>
                                    <p class="m-0">Delivered</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #8A2BE2">
                        <a href="{{route('riderReattemptAdminReport',Helper::encrypt(16))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$riderreattempt}}</h4>
                                    <p class="m-0">Re-attempt by Rider</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #A09E9E">
                        <a href="{{route('requestforReattemptAdminReport',Helper::encrypt(7))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$requestforreattempt}}</h4>
                                    <p class="m-0">Re-attempt by Supervisor</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #A0E064">
                        <a href="{{route('ReattemptAdminReport',Helper::encrypt(8))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$reattempt}}</h4>
                                    <p class="m-0">Re-attempt</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #8A2BE2">
                        <a href="{{route('cancelbyRiderAdminReport',Helper::encrypt(17))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$cancel_by_rider}}</h4>
                                    <p class="m-0">Cancel by Rider</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #0EA854">
                        <a href="{{route('cancelbySupervisorAdminReport',Helper::encrypt(18))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$cancel_by_supervisor}}</h4>
                                    <p class="m-0">Cancel by Supervisor</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #E25041">
                        <a href="{{route('cancelAdminReport',Helper::encrypt(9))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$cancelled}}</h4>
                                    <p class="m-0">Cancelled</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #64A823">
                        <a href="{{route('returnAdminReport',Helper::encrypt(10))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$returntovendor}}</h4>
                                    <p class="m-0">Return to Vendor</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #5858F4">
                        <a href="{{route('cancelAhlAdminReport',Helper::encrypt(11))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$cancelbyadmin}}</h4>
                                    <p class="m-0">Cancel by AHL (Not Picked Parcel)</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #8A2BE2">
                        <a href="{{route('cancelVendorAdminReport',Helper::encrypt(12))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$cancelbyvendor}}</h4>
                                    <p class="m-0">Cancel by Vendor (Not Picked Parcel)</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #5F9EA0">
                        <a href="{{route('voidAdminReport',Helper::encrypt(13))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$voidlabel}}</h4>
                                    <p class="m-0">Void Label</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #8A2BE2">
                        <a href="{{route('replaceAdminReport',Helper::encrypt(14))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$replace}}</h4>
                                    <p class="m-0">Replace</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                @hasrole('cashier|admin|supervisor')
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #8A2BE2">
                        <a href="{{route('supervisorAdminReport',Helper::encrypt(4))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$check_by_supervisor}}</h4>
                                    <p class="m-0">Scan by Supervisor</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                @endhasrole
                @hasrole('admin|middle_man')
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #8A2BE2">
                            <a href="{{route('statusReport',Helper::encrypt(15))}}" target="_blank">
                                <div class="card-block">
                                    <div class="text-left">
                                        <h4>{{$enroute}}</h4>
                                        <p class="m-0">En-Route</p>
                                    </div>
                                </div>
                            </a>
                    </div>
                </div>
                @endhasrole
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #E25041">
                        <a href="{{route('atAhlDelayedAdminReport',Helper::encrypt(3))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$delayed_warehouse}}</h4>
                                    <p class="m-0">Delayed Orders</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card total-card" style="background-color: #64A823">
                        <a href="{{route('returnInProgressAdminReport',Helper::encrypt(19))}}" target="_blank">
                            <div class="card-block">
                                <div class="text-left">
                                    <h4>{{$returntovendorinprogress}}</h4>
                                    <p class="m-0">Return to Vendor In-Progress</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection