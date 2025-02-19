@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .divider {
      margin-top: 2%; /* or whatever */
    }
    .heading
    {
        font-weight: bold !important;
    }
</style>
@endsection
@section('content')

<div class="page-body">
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Generate Pickup Request</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('generatePickupRequest') }}">
                        @csrf
                        <h4 class="sub-title divider heading">Pickup Request</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" name="vendor_id" class="form-control  @error('vendor_id') is-invalid @enderror" value="{{$vendorId}}">
                                    <input type="date" name="pickup_date" class="form-control  @error('pickup_date') is-invalid @enderror" required="" value="{{ old('pickup_date') }}">
                                    <span class="form-bar"></span>
                                    
                                    @error('pickup_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="estimated_parcel" class="form-control  @error('estimated_parcel') is-invalid @enderror" required="" min="0" max="10000" value="{{ old('estimated_parcel') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Estimated Parcel</label>
                                    @error('estimated_parcel')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>  
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select name="time_slot" class="form-control  @error('time_slot') is-invalid @enderror" required="" value="{{ old('time_slot') }}">
                                        <option selected="" disabled="" hidden="">Select Time Slot</option>
                                        @foreach($timing as $key=> $country)
                                            <option value="{{$country->id}}" value="@if(old('country')) {{ old('country') }} @endif" @if(old('country')) {{ 'selected' }} @endif>{{$country->vendorTiming->timings}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('time_slot')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select name="pickup_location" class="form-control  @error('pickup_location') is-invalid @enderror" required="" value="{{ old('pickup_location') }}">
                                        <option selected="" disabled="" hidden="">Select Pickup Location</option>
                                        @foreach($location as $key=> $country)
                                            <option value="{{$country->id}}" value="@if(old('country')) {{ old('country') }} @endif" @if(old('country')) {{ 'selected' }} @endif>{{$country->address}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('pickup_location')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select name="city" class="form-control  @error('city') is-invalid @enderror" required="" value="{{ old('city') }}">
                                        <option selected="" disabled="" hidden="">Select City</option>
                                        @foreach($cities as $key=> $city)
                                            <option value="{{$city->id}}" value="@if(old('city')) {{ old('city') }} @endif" @if(old('city')) {{ 'selected' }} @endif>{{$city->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('city')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="remarks" class="form-control  @error('remarks') is-invalid @enderror" required="" min="0" max="10000" value="{{ old('remarks') }}" placeholder="Enter Remarks">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Remarks</label>
                                    @error('remarks')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Send Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection