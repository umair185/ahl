@extends('layouts.app')

@section('content')

<div class="page-body"> 
	@if (session('sucess'))
	    <div class="alert alert-success">
	        {{ session('sucess') }}
	    </div>
	@endif
	@if ($errors->has('pay_amount'))
        <div class="alert alert-danger" role="alert">
          {{$errors->getBag('default')->first('pay_amount')}}
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Vendor</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('vendorFinancials')}}" method="POST">
	                   	@csrf
	                    <div class="row">
	                    	<div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date_from" id="date_from" max="{{$today_date}}" class="form-control" required="required" value="<?php
                                    if (isset($_POST['date_from'])) {
                                        echo $_POST['date_from'];
                                    }
                                    ?>" onChange="restaurantTime()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date_to" id="date_to" max="{{$today_date}}" class="form-control" required="required" value="<?php
                                    if (isset($_POST['date_to'])) {
                                        echo $_POST['date_to'];
                                    }
                                    ?>">
                                </div>
                            </div>
	                        <div class="col-md-3">
	                            <div class="form-group form-static-label form-default">
	                                <select id="riders" name="vendor_id" class="form-control  @error('vendor_id') is-invalid @enderror" value="{{ old('vendor_id') }}">
	                                    <option selected="" disabled="" hidden="">Select Vendor</option>
	                                    @foreach($vendors as $key=> $vendor)
	                                        <option value="{{$vendor->id}}" @if(isset($_POST['vendor_id']) && $_POST['vendor_id'] == $vendor->id) {{ 'selected' }} @endif>{{$vendor->vendor_name}}</option>
	                                    @endforeach
	                                </select>
	                                <span class="form-bar"></span>
	                                @error('vendor_id')
	                                    <span class="invalid-feedback" role="alert">
	                                        <strong>{{ $message }}</strong>
	                                    </span>
	                                @enderror
	                            </div>
	                        </div>

	                        <div class="col-md-3">
	                            <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
	                            	Submit
	                        	</button>
	                        </div>
	                    </div>
	                </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
	    <div class="col-xl-12">
	        <div class="card proj-progress-card">
	            <div class="card-block">
                    <div class="row">
                        <div class="col-xl-3 col-md-3">
                            <h5 class="m-b-30 f-w-700 text-c-green">{{$payment_status}} - ({{$payment_paid}} - {{$payment_paid_date}})</h5>
                        </div>
                    </div>
	                <div class="row">
	                    <div class="col-xl-3 col-md-3">
	                        <h6>Overall Parcels Amount</h6>
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{$overallParcelSum}}</h5>
	                    </div>
	                    <div class="col-xl-3 col-md-3">
	                        <h6>Delivered Parcels Amount @if($vendorId)<span style="font-size: 9px"><a style="font-size: 9px" href="{{ route('calculateDeliveredOrders') }}?from=<?php echo $_POST['date_from']; ?>&to=<?php echo $_POST['date_to'] ?>&vendor_id=<?php echo $_POST['vendor_id'] ?>">(Click to Download)</a></span>@endif</h6>
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{$deliveredParcelSum}}</h5>
	                    </div>
	                    <div class="col-xl-3 col-md-3">
	                        <h6>AHL Commission @if($vendorId)<span style="font-size: 9px"><a style="font-size: 9px" href="{{ route('calculateCommissionOrders') }}?from=<?php echo $_POST['date_from']; ?>&to=<?php echo $_POST['date_to'] ?>&vendor_id=<?php echo $_POST['vendor_id'] ?>">(Click to Download)</a></span>@endif</h6>
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span id="ahlCommission">{{$ahlCommissionParcelSum}}</span></h5>
	                    </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Fuel Adjustment @if($vendorId)<span style="font-size: 9px">({{App\Models\Vendor::where('id', $vendorId)->first()->fuel}})</span>@endif</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span id="round_fuel">{{$round_fuel_adjustment}}</span></h5>
                        </div>

	                    <div class="col-xl-3 col-md-3">
	                        <h6>GST On Commission @if($vendorId)<span style="font-size: 9px">({{App\Models\Vendor::where('id', $vendorId)->first()->gst}})</span>@endif</h6>
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span id="taxAmount">{{$taxAmount}}</span></h5>
	                    </div>
	                    <div class="col-xl-3 col-md-3">
	                        <h6>Return To Vendor Parcel Amount @if($vendorId)<span style="font-size: 9px"><a style="font-size: 9px" href="{{ route('calculateRtvOrders') }}?from=<?php echo $_POST['date_from']; ?>&to=<?php echo $_POST['date_to'] ?>&vendor_id=<?php echo $_POST['vendor_id'] ?>">(Click to Download)</a></span>@endif</h6>
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{$returnToVendorParcelSum}}</h5>
	                    </div>
	                    <div class="col-xl-3 col-md-3">
	                        <h6>Filter Payable Amount</h6>
	                        <!-- status 2 or 4 -->
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span id="filter_payable">{{$filterPayableToVendor - $round_fuel_adjustment - $taxAmount}}</span></h5>
	                    </div>
	                    
	                    <div class="col-xl-3 col-md-3">
	                        <h6>Payable To Vendor With Out Filter</h6>
	                        <!-- status 2 or 4 -->
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{$notFilterPayableToVendor}}</h5>
	                    </div>

                        <div class="col-xl-3 col-md-3">
                            <h6>Total Flyers Amount</h6>
                            <!-- status 2 or 4 -->
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{$total_flyer}}</h5>
                        </div>

                        <div class="col-xl-3 col-md-3">
                            <h6>Received Flyers Amount from vendor</h6>
                            <!-- status 2 or 4 -->
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{$total_received_flyer}}</h5>
                        </div>

                        <div class="col-xl-3 col-md-3">
                            <h6>Delivered Flyers Amount</h6>
                            <!-- status 2 or 4 -->
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{$total_delivered_flyer}}</h5>
                        </div>

                        <div class="col-xl-3 col-md-3">
                            <h6>Remaining Flyers Amount to be received</h6>
                            <!-- status 2 or 4 -->
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. <span id="total_flyer">{{$remaining_flyer_amount}}</span></h5>
                        </div>

                        <div class="col-xl-3 col-md-3">
                            <h6>Remaining Advance Amount</h6>
                            <!-- status 2 or 4 -->
                            @if($vendorId)
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{ $payee->advance }}</h5>
                            @else
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. 0</h5>
                            @endif
                        </div>
	                </div>
	            </div>
	        </div>
	    </div>
    </div>
    @if($vendorId)

    <div class="alert alert-success">
    	You Have To Pay {{ $notFilterPayableToVendor }}
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Pay To {{ $payee->vendor_name }}</h5>
                    <button type="button" onclick="fillInput()" class="btn btn-primary waves-effect waves-light" style="float: right;">Fill All</button>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('payVendorFinancials') }}" enctype="multipart/form-data">
                        @csrf
                        
                    	<input type="hidden" id="vendor_id" name="vendor_id" value="{{$vendorId}}">
                    	<!-- <input type="hidden" id="payable_to_vendor" name="payable_to_vendor" value="{{$payableToVendor}}"> -->

                    	<input type="hidden" id="payable_to_vendor" name="payable_to_vendor" value="{{$notFilterPayableToVendor}}">

                    	<!--<input type="text" id="ahl_commission" name="ahl_commission" value="{{$ahlCommissionParcelSum}}">-->
                    	
                    	<input type="hidden" id="total_pay_amount" name="total_pay_amount" value="{{$total_pay_amount}}">
                    	<input type="hidden" id="total_ahl_commission_deduction" name="total_ahl_commission_deduction" value="{{$total_ahl_commission_deduction}}">
                    	
                    	<input type="hidden" id="date_from" name="date_from" value="{{ request()->date_from }}">
                    	<input type="hidden" id="date_to" name="date_to" value="{{ request()->date_to }}">
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <select name="invoice_type" class="form-control  @error('invoice_type') is-invalid @enderror" value="{{ old('invoice_type') }}" required>
                                        <option value="">Select Invoice Type</option>
                                        <option value="IBFT">IBFT</option>
                                        <option value="CASH">CASH</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="pay_amount" id="pay_amount" class="form-control  @error('pay_amount') is-invalid @enderror" value="{{ old('pay_amount') }}" required>
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">Amount</label>
                                    @error('pay_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="ahl_commission" id="ahl_commission" class="form-control  @error('ahl_commission') is-invalid @enderror" required="">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">AHL Commission</label>
                                    @error('ahl_commission')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="fuel_adjustment" id="fuel_adjustment" class="form-control  @error('fuel_adjustment') is-invalid @enderror" required="">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">FUEL ADJUSTMENT</label>
                                    @error('fuel_adjustment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="ahl_gst" id="ahl_gst" class="form-control  @error('ahl_gst') is-invalid @enderror"  required="">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">AHL GST</label>
                                    @error('ahl_gst')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="advance_amount" id="advance_amount" class="form-control  @error('advance_amount') is-invalid @enderror" required="">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">ADVANCE PAYMENT</label>
                                    @error('advance_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="flyer_amount" id="flyer_amount" class="form-control  @error('flyer_amount') is-invalid @enderror" required="" max="{{ $remaining_flyer_amount }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">FLYERS AMOUNT</label>
                                    @error('flyer_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="deduction_amount" id="deduction_amount" class="form-control  @error('deduction_amount') is-invalid @enderror" required="">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">DEDUCTION</label>
                                    @error('deduction_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <select name="deduction_remarks" class="form-control  @error('deduction_remarks') is-invalid @enderror" value="{{ old('deduction_remarks') }}" required>
                                        <option value="">Select DEDUCTION REMARKS</option>
                                        <option value="normal">NORMAL</option>
                                        <option value="advance_deduction">ADVANCE DEDUCTION</option>
                                    </select>
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">DEDUCTION REMARKS</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="remarks" class="form-control  @error('remarks') is-invalid @enderror" value="{{ old('remarks') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">Remarks</label>
                                    @error('remarks')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="extra_phone_number" class="form-control  @error('extra_phone_number') is-invalid @enderror" value="{{ old('extra_phone_number') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">Extra Phone Number</label>
                                    @error('extra_phone_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="financial_report" class="form-control  @error('financial_report') is-invalid @enderror" value="{{ old('financial_report') }}" required>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Upload Excel or CSV file</label>
                                    @error('financial_report')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            @hasrole('admin')
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="pay_date" class="form-control  @error('pay_date') is-invalid @enderror" value="{{$today_date}}" max="{{$today_date}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">Payment Date</label>
                                    @error('pay_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            @endhasrole
                            <div class="col-md-3">
                        		<button type="submit" class="btn btn-primary waves-effect waves-light {{$class_check}}" {{$class_check}}>Pay</button>
                                <p style="color: red">{{$class_check_message}}</p>
                        	</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
	@endif
</div>
<script type="text/javascript">
  function restaurantTime() {
    var time = $("#date_from").val();

    $("#date_to").attr({
      "min" : time,
    });
  }
</script>
<script>
    function fillInput() {
        // Get the input field by its ID
        var paragraphText = document.getElementById("filter_payable").textContent;
        var inputField = document.getElementById("pay_amount");

        var paragraphTextOne = document.getElementById("ahlCommission").textContent;
        var inputFieldOne = document.getElementById("ahl_commission");

        var paragraphTextTwo = document.getElementById("round_fuel").textContent;
        var inputFieldTwo = document.getElementById("fuel_adjustment");

        var paragraphTextThree = document.getElementById("taxAmount").textContent;
        var inputFieldThree = document.getElementById("ahl_gst");

        var paragraphTextFour = document.getElementById("total_flyer").textContent;
        var inputFieldFour = document.getElementById("flyer_amount");

        var inputFieldFive = document.getElementById("advance_amount");
        
        var inputFieldSix = document.getElementById("deduction_amount");

        // Set the value of the input field
        inputField.value = paragraphText;
        inputFieldOne.value = paragraphTextOne;
        inputFieldTwo.value = paragraphTextTwo;
        inputFieldThree.value = paragraphTextThree;
        inputFieldFour.value = paragraphTextFour;
        inputFieldFive.value = 0;
        inputFieldSix.value = 0;
    }
</script>
@endsection