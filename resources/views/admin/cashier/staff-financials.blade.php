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
                    <h5>Staff</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('staffFinancials',['staff'=>app('request')->staff])}}" method="POST">
	                   	@csrf
	                    <div class="row">
	                        <div class="col-md-10">
	                            <div class="form-group form-static-label form-default">
	                                <select id="riders" name="staff_id" class="form-control  @error('staff_id') is-invalid @enderror" value="{{ old('staff_id') }}">
	                                    <option selected="" disabled="" hidden="">Select Staff</option>
	                                    @foreach($staffList as $key=> $staff)
	                                        <option value="{{$staff->id}}" value="@if(old('staff_id')) {{ old('staff_id') }} @endif" @if(isset($_POST['staff_id']) && $_POST['staff_id'] == $staff->id) {{ 'selected' }} @endif>{{$staff->name}} ( {{ $staff->userDetail->cnic }} )</option>
	                                    @endforeach
	                                </select>
	                                <span class="form-bar"></span>
	                                @error('staff_id')
	                                    <span class="invalid-feedback" role="alert">
	                                        <strong>{{ $message }}</strong>
	                                    </span>
	                                @enderror
	                            </div>
	                        </div>

	                        <div class="col-md-2">
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
	                        <h6>Total Parcels</h6>
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{ $totalOrder }}</h5>
	                    </div>
	                    <div class="col-xl-3 col-md-3">
	                        <h6>Total Commission</h6>
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{ $totalCommission }}</h5>
	                    </div>
	                    <div class="col-xl-3 col-md-3">
	                        <h6>Total Paid Commission</h6>
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{ $totalPaidCommission }}</h5>
	                    </div>

	                    <div class="col-xl-3 col-md-3">
	                        <h6>Remaing Commission</h6>
	                        <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{ $remaingCommission }}</h5>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
    </div>
    @if($staffId)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Pay To {{ $staffData->name }}</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('payStaffFinancials') }}">
                        @csrf
                        
                    	<input type="hidden" id="staff_id" name="staff_id" value="{{$staffId}}">

                    	<input type="hidden" id="total_order" name="total_order" value="{{$totalOrder}}">
                    	<input type="hidden" id="total_commission" name="total_commission" value="{{$totalCommission}}">
                    	
                    	<input type="hidden" id="total_paid_commission" name="total_paid_commission" value="{{$totalPaidCommission}}">
                    	<input type="hidden" id="remaing_commission" name="remaing_commission" value="{{$remaingCommission}}">
                    	
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="pay_amount" class="form-control  @error('pay_amount') is-invalid @enderror" value="{{ old('pay_amount') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Amount</label>
                                    @error('pay_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="pay_note" class="form-control  @error('pay_note') is-invalid @enderror" value="{{ old('pay_note') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Note</label>
                                    @error('pay_note')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                        		<button type="submit" class="btn btn-primary waves-effect waves-light">Pay</button>
                        	</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
	@endif
</div>
@endsection