@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .anyClass {
      height:150px;
      overflow-y: scroll;
    }
</style>
@endsection

@section('content')

<div class="page-body"> 
	@if (session('sucess'))
	    <div class="alert alert-success">
	        {{ session('sucess') }}
	    </div>
	@endif
	@if ($errors->has('collect_amount'))
        <div class="alert alert-danger" role="alert">
          {{$errors->getBag('default')->first('collect_amount')}}
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Staff</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('cashCollection')}}" method="POST">
	                   	@csrf
	                    <div class="row">
                            <div class="col-md-5">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date" id="date" class="form-control" required="required" value="<?php
                                    if (isset($_GET['date'])) {
                                        echo $_GET['date'];
                                    }
                                    ?>">
                                </div>
                            </div>
	                        <div class="col-md-5">
	                            <div class="form-group form-static-label form-default">
	                                <select id="riders" name="staff_id" class="form-control  @error('staff_id') is-invalid @enderror" value="{{ old('staff_id') }}">
	                                    <option selected="" disabled="" hidden="">Select Staff</option>
	                                    @foreach($staffList as $key=> $staff)
	                                        <option value="{{$staff->id}}" value="@if(old('staff_id')) {{ old('staff_id') }} @endif" @if(old('staff_id')) {{ 'selected' }} @endif>{{$staff->name}} ( {{ $staff->userDetail->cnic }} )</option>
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
                            <h6>Today Delivered Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{ $staffCashCollection['todayOrder'] }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Remaing Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{   $staffCashCollection['remaingOrder']  }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Total Cash</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{ $staffCashCollection['totalCashByRider'] }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Deposit Cash</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{  $staffCashCollection['totalCollectCashFromRider'] }}</h5>
                        </div>

                        <div class="col-xl-3 col-md-3">
                            <h6>Remaing Cash</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{  $staffCashCollection['remainingCash'] }}</h5>
                        </div>
                    </div>
	            </div>
	        </div>
	    </div>
    </div>
    @if($staffId)
    <!-- Collect Cash From Rider -->
    
    <div class="row">
        <div class="col-xl-3">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <h6>Parcels</h6>
                    <div class="row anyClass">
                        <div class="col-md-2" style="max-width: 100%;">
                            @if($rackBalancing['deliveredParcels'] != 0)
                                @foreach($rackBalancing['deliveredParcels'] as $parcel)
                                    {{$parcel}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <h6>Cancelled</h6>
                    <div class="row anyClass">
                        <div class="col-md-2" style="max-width: 100%;">
                            @if($rackBalancing['cancelledParcels'] != 0)
                                @foreach($rackBalancing['cancelledParcels'] as $parcel)
                                    {{$parcel}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <h6>Reatttempt</h6>
                    <div class="row anyClass">
                        <div class="col-md-2" style="max-width: 100%;">
                            @if($rackBalancing['requestReattemptParcels'] != 0)
                                @foreach($rackBalancing['requestReattemptParcels'] as $parcel)
                                    {{$parcel}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <h6>At AHL Warehouse</h6>
                    <div class="row anyClass">
                        <div class="col-md-2" style="max-width: 100%;">
                            @if($rackBalancing['atAHLParcels'] != 0)
                                @foreach($rackBalancing['atAHLParcels'] as $parcel)
                                    {{$parcel}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	@endif
</div>
@endsection