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
                    <h5>Edit Vendor</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('updateVendor',$vendor->id) }}">
                        @csrf
                        <!-- user table id -->
                        <input type="hidden" name="user_id" value="{{ $vendor->vendorLoginDetail->id }}">
                        <input type="hidden" name="db_vendor_weights" value="{{ json_encode($dbweight) }}">
                        <input type="hidden" name="db_vendor_pickuplocations" value="{{ json_encode($dbPickupLocation) }}">
                        <input type="hidden" name="db_vendor_timings" value="{{ json_encode($dbTiming) }}">
                        
                        <!-- <input type="hidden" id="checkedTiming" name="checkedTiming" value="">
                        <input type="hidden" id="unCheckedTiming" name="uncheckedTiming" value=""> -->
                        
                        <h4 class="sub-title divider heading">COMPANY DETAILS</h4>
                        @if ($errors->has('timing'))
                            <div class="alert alert-danger" role="alert">
                              Add At least One Timing 
                            </div>
                        @endif
                        <!-- if ($errors->has('ahl_weight')) -->
                            <!-- <div class="alert alert-danger" role="alert">
                              Add At least One Weight Price
                            </div> -->
                        <!-- endif -->
                        @if ($errors->has('pickupAddress'))
                            <div class="alert alert-danger" role="alert">
                              Add At least One Pickup Location
                            </div>
                        @endif
                        @if ($errors->has('vendorWeights'))
                            <div class="alert alert-danger" role="alert">
                              Add At least One Weight
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="vendor_name" class="form-control  @error('vendor_name') is-invalid @enderror" value="{{ $vendor->vendor_name ??  old('vendor_name') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Name</label>
                                    @error('vendor_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="vendor_email" class="form-control  @error('vendor_email') is-invalid @enderror" value="{{ $vendor->vendor_email ?? old('vendor_email') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Email</label>
                                    @error('vendor_email')
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
                                    <input type="text" name="vendor_phone" class="form-control  @error('vendor_phone') is-invalid @enderror" value="{{ $vendor->vendor_phone ?? old('vendor_phone') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Phone Number</label>
                                    @error('vendor_phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="website" class="form-control  @error('website') is-invalid @enderror" value="{{ $vendor->website ?? old('website') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Website URL</label>
                                    @error('website')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>  
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" id="addational_Kgs" name="addational_Kgs" class="form-control  @error('addational_Kgs') is-invalid @enderror" value="{{ $vendor->	addational_kgs ?? old('addational_Kgs') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Addational Kgs</label>
                                    @error('addational_Kgs')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" id="fuel" name="fuel" class="form-control  @error('fuel') is-invalid @enderror" value="{{ $vendor->fuel ?? old('fuel') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Fuel</label>
                                    @error('fuel')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" id="complain_number" name="complain_number" class="form-control  @error('complain_number') is-invalid @enderror" value="{{ $vendor->complain_number ?? old('complain_number') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Complain Number</label>
                                    @error('complain_number')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" id="address" name="vendor_address" class="form-control  @error('vendor_address') is-invalid @enderror" value="{{ $vendor->vendor_address ?? old('vendor_address') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Address</label>
                                    @error('vendor_address')
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
                                    <input type="text" id="latitude" placeholder="latitude" name="latitude" class="form-control  @error('latitude') is-invalid @enderror" value="{{ $vendor->latitude ?? old('latitude') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label"></label>
                                    @error('latitude')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" id="longitude" placeholder="longitude" name="longitude" class="form-control  @error('longitude') is-invalid @enderror" value="{{ $vendor->longitude ?? old('longitude') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label"></label>
                                    @error('longitude')
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
                                    <input type="text" name="ntn_buyer" class="form-control  @error('ntn_buyer') is-invalid @enderror" value="{{ $vendor->ntn_buyer ?? old('ntn_buyer') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">NTN Buyer Name</label>
                                    @error('ntn_buyer')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="ntn_city" class="form-control  @error('ntn_city') is-invalid @enderror" value="{{ $vendor->ntn_city ?? old('ntn_city') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">NTN Buyer City</label>
                                    @error('ntn_city')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="ntn" class="form-control  @error('ntn') is-invalid @enderror" value="{{ $vendor->ntn ?? old('ntn') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">NTN Number</label>
                                    @error('ntn')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="strn" class="form-control  @error('strn') is-invalid @enderror" value="{{ $vendor->strn ?? old('strn') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">STRN Number</label>
                                    @error('strn')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>  
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="gst" class="form-control  @error('gst') is-invalid @enderror" value="{{ $vendor->gst ?? old('gst') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GST</label>
                                    @error('gst')
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
                                    <input type="text" name="cnic" class="form-control  @error('cnic') is-invalid @enderror" value="{{ $vendor->cnic ?? old('cnic') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">CNIC Number</label>
                                    @error('cnic')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select id="consignee_country" name="country" class="form-control  @error('country') is-invalid @enderror" value="{{ $vendor->vendorCountry->id ?? old('country') }}">
                                        <option selected="" disabled="" hidden="">Select Country</option>
                                        @foreach(Helper::getCountry() as $key=> $country)
                                            <option value="{{$country->id}}" {{$vendor->vendorCountry->id == $country->id  ? 'selected' : ''}}>{{$country->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('country')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>  
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-primary">
                                    <select id="state" name="state" class="form-control  @error('state') is-invalid @enderror" value="{{ $vendor->vendorState->name ?? old('state') }}">
                                        <option selected="" disabled="" hidden="">Select State</option>
                                        @foreach(Helper::getStates() as $key=> $state)
                                            <option value="{{$state->id}}" {{$vendor->vendorState->id == $state->id  ? 'selected' : ''}}>{{$state->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('state')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-primary">
                                    <select id="city" name="city" class="form-control  @error('city') is-invalid @enderror" value="{{ $vendor->city ?? old('city') }}">
                                        <option selected="" disabled="" hidden="">Select City</option>
                                        @foreach(Helper::getCities() as $key=> $city)
                                            <option value="{{$city->id}}" {{$vendor->vendorCity->id == $city->id  ? 'selected' : ''}}>{{$city->name}}</option>
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
                        </div>
                        @hasanyrole('admin|bdm|bd')
                        <h4 class="sub-title divider heading">AHL Staff Assign</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <select id="poc" name="poc" class="form-control  @error('poc') is-invalid @enderror" value="{{ $vendor->poc ?? old('poc') }}">
                                        <option selected="" disabled="" hidden="">Select Sales Person</option>
                                        @foreach($sale_staffs as $key=> $sale_staff)
                                            <option value="{{$sale_staff->id}}" {{$vendor->poc == $sale_staff->id  ? 'selected' : ''}}>{{$sale_staff->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    <label class="float-label">POC</label>
                                    @error('poc')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <select id="csr" name="csr" class="form-control  @error('csr') is-invalid @enderror" value="{{ $vendor->csr ?? old('csr') }}">
                                        <option selected="" disabled="" hidden="">Select CSR Person</option>
                                        @foreach($csr_staffs as $key=> $csr_staff)
                                            <option value="{{$csr_staff->id}}" {{$vendor->csr == $csr_staff->id  ? 'selected' : ''}}>{{$csr_staff->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    <label class="float-label">CSR</label>
                                    @error('csr')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <select id="pickup" name="pickup" class="form-control  @error('pickup') is-invalid @enderror" value="{{ $vendor->pickup ?? old('pickup') }}">
                                        <option selected="" disabled="" hidden="">Select Pickup Supervisor</option>
                                        @foreach($pickup_staffs as $key=> $pickup_staff)
                                            <option value="{{$pickup_staff->id}}" {{$vendor->pickup == $pickup_staff->id  ? 'selected' : ''}}>{{$pickup_staff->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Pickup Supervisor</label>
                                    @error('pickup')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        <h4 class="sub-title divider heading">FOCAL PERSON DETAIL</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="focal_person_name" class="form-control  @error('focal_person_name') is-invalid @enderror" value="{{ $vendor->focal_person_name ?? old('focal_person_name') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Name</label>
                                    @error('focal_person_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="focal_person_email" class="form-control  @error('focal_person_email') is-invalid @enderror" value="{{ $vendor->focal_person_email ?? old('focal_person_email') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Email</label>
                                    @error('focal_person_email')
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
                                    <input type="text" name="focal_person_phone" class="form-control  @error('focal_person_phone') is-invalid @enderror" value="{{ $vendor->focal_person_phone ?? old('focal_person_phone') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Phone #</label>
                                    @error('focal_person_phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label  form-default">
                                    <input type="text" name="focal_person_address" class="form-control  @error('focal_person_address') is-invalid @enderror" value="{{ $vendor->focal_person_address ?? old('focal_person_address') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Address</label>
                                    @error('focal_person_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>  
                        </div>

                        <h4 class="sub-title divider heading">BANK DETAILS</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="bank_name" class="form-control  @error('bank_name') is-invalid @enderror" value="{{ $vendor->bank_name ?? old('bank_name') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Bank Name</label>
                                    @error('bank_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <input type="text" name="bank_title" class="form-control  @error('bank_title') is-invalid @enderror" value="{{ $vendor->bank_title ?? old('bank_title') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Bank Account Title</label>
                                    @error('bank_title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-success">
                                    <input type="text" name="bank_account" class="form-control  @error('bank_account') is-invalid @enderror" value="{{ $vendor->bank_account ?? old('bank_account') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Bank Account #</label>
                                    @error('bank_account')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="sub-title divider heading">EXTRA DETAILS</h4>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="remarks" class="form-control  @error('remarks') is-invalid @enderror" value="{{$vendor->remarks ?? old('remarks') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Remarks</label>
                                    @error('remarks')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <select id="payment_mode" name="payment_mode" class="form-control  @error('payment_mode') is-invalid @enderror" value="{{ old('payment_mode') }}" required>
                                        <option selected="" disabled="" hidden="">Select Payment Mode</option>
                                        <option value="24 Hours" {{$vendor->payment_mode == '24 Hours'  ? 'selected' : ''}}>24 Hours</option>
                                        <option value="Bi-Weekly" {{$vendor->payment_mode == 'Bi-Weekly'  ? 'selected' : ''}}>Bi-Weekly</option>
                                        <option value="Monthly" {{$vendor->payment_mode == 'Monthly'  ? 'selected' : ''}}>Monthly</option>
                                    </select>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Payment Mode</label>
                                    @error('payment_mode')
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
                                    <select id="category" name="category" class="form-control  @error('category') is-invalid @enderror" value="{{ old('category') }}" required>
                                        <option selected="" disabled="" hidden="">Select Category</option>
                                        <option value="A+ (100-200 Parcels/Day)" {{$vendor->category == 'A+ (100-200 Parcels/Day)'  ? 'selected' : ''}}>A+ (100-200 Parcels/Day)</option>
                                        <option value="A (50-100 Parcels/Day)" {{$vendor->category == 'A (50-100 Parcels/Day)'  ? 'selected' : ''}}>A (50-100 Parcels/Day)</option>
                                        <option value="B (20-50 Parcels/Day)" {{$vendor->category == 'B (20-50 Parcels/Day)'  ? 'selected' : ''}}>B (20-50 Parcels/Day)</option>
                                        <option value="C (0-20 Parcels/Day)" {{$vendor->category == 'C (0-20 Parcels/Day)'  ? 'selected' : ''}}>C (0-20 Parcels/Day)</option>
                                    </select>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Category</label>
                                    @error('category')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select id="payment" name="payment" class="form-control  @error('payment') is-invalid @enderror" value="{{ old('payment') }}" required>
                                        <option selected="" disabled="" hidden="">Select Payment</option>
                                        <option value="Cash" {{$vendor->payment == 'Cash'  ? 'selected' : ''}}>Cash</option>
                                        <option value="IBFT" {{$vendor->payment == 'IBFT'  ? 'selected' : ''}}>IBFT</option>
                                        <option value="Cheque" {{$vendor->payment == 'Cheque'  ? 'selected' : ''}}>Cheque</option>
                                    </select>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Payment</label>
                                    @error('payment')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
 
                        <h4 class="sub-title divider heading">Pickup Locations</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <button type="button" onclick="newAddress();" class="btn" style="background-color: #448AFF; color: white; font-weight: bold"> <i class="fa fa-plus"></i> Pickup Addresses </button>
                                </div>
                            </div>
                        </div>
                        <div id="address-container"></div>

                        <h4 class="sub-title divider heading">Vendor Weights</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <button type="button" onclick="newVendorWeight();" class="btn" style="background-color: #448AFF; color: white; font-weight: bold"> <i class="fa fa-plus"></i> Add Vendor Weight </button>
                                </div>
                            </div>
                        </div>
                        <div id="vendor-weight-container"></div>
                        
                        <h4 class="sub-title divider heading">Select Pickup Timings</h4>
                        <div class="row">
                            @foreach($ahlTimings as $key => $timing)
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input class="timing" type="checkbox" value="{{$timing->id}}" style="margin-right: 10px" name="timing[]" 
                                    @if(isset($vendor->timings[$key])) 
                                        {{ ($timing->id == $vendor->timings[$key]->timing_slot_id) ? 'checked disabled' : '' }} 
                                    @endif >{{$timing->timings}}
                                </div>
                            </div>
                            @endforeach
                            @if($errors->has('timing'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>Ahl Timing Required</strong>
                                </span>
                            @endif
                            
                        </div>

                        <!-- <h4 class="sub-title divider heading">Add Weight Price</h4>
                        <div class="row">
                            foreach($ahlWeights as $key => $ahl)
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="checkbox" value="$ahl->id" style="margin-right: 10px" name="ahl_weight[ $key ][weight_id]" value="">$ahl->weight
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="ahl_weight[ $key ][vendor_price]" value="" placeholder="Enter Price">
                                </div>
                            </div>
                            endforeach
                            if($errors->has('ahl_weight.*'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>Ahl Weight Required</strong>
                                </span>
                            endif
                        </div> -->

                        <h4 class="sub-title divider heading">Login Credentials</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="email" name="login_email" class="form-control  @error('login_email') is-invalid @enderror" value="{{ $vendor->vendorLoginDetail->email ?? old('login_email') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Email</label>
                                    @error('login_email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <input type="password" name="login_password" class="form-control  @error('login_password') is-invalid @enderror" value=''>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Password</label>
                                    @error('login_password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-success">
                                    <input type="password" name="login_confirm_password" class="form-control  @error('login_confirm_password') is-invalid @enderror" value=''>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Retype Password</label>
                                    @error('login_confirm_password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button  type="submit" class="btn btn-primary waves-effect waves-light">Save Vendor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection

@section('custom-js')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBl-jpsktXKHLD7rFQo9NT3Hfgm16b27C0&libraries=places&callback=initialize" async defer></script>
<script>
    //$.noConflict();

        var vendor = @json($vendor);
        var pickupLocations = vendor.pickup_location;
        var weights = vendor.vendor_weights;
        
        //var timings = vendor.timings;
        /*var checkedTiming = [];
        $.each(timings, function(key,timing){
            var timingId = timing.id;
            checkedTiming.push(timingId.toString());
        });*/
        
        /*$("#checkedTiming").val(JSON.stringify(checkedTiming));
        console.log(checkedTiming);*/
        
        /*var unCheckedTiming = [];
        $('.timing').click(function(){
            var timeingVal = $(this).val();
            if (checkedTiming.includes(timeingVal) === false){
                if($(this).is(':checked')){
                    alert($(this).val());
                    checkedTiming.push(timeingVal);
                    console.log(checkedTiming);
                    $("#checkedTiming").val(JSON.stringify(checkedTiming));
                }
            }

            if(unCheckedTiming.includes(timeingVal) === false){
                unCheckedTiming.push($(this).val());
                alert('unchecked' + $(this).val());
                console.log(unCheckedTiming);
                $("#unCheckedTiming").val(JSON.stringify(unCheckedTiming));
            } 
        });*/
        
        
        var addressId = 0;
        if(pickupLocations){
            $.each(pickupLocations, function( key, location ) {
                addressId++;
                var address = '<div style="margin-top: 10px" id="row-' + addressId + '" class="row">' +
                    '<div class="col-md-6">' +
                    '<input type="text" required name="pickupAddress[]" class="rowLocation-'+ addressId +' form-control" placeholder="Enter Pickup Address" value="'+ location.address +'">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                    '<div class="input-group-btn">' +
                    /*'<button class="btn" style="background-color: #448AFF; color: white" onclick="deleteAddress(' + addressId +','+ location.id + ');" type="button"> <i class="fas fa-trash"></i> </button>' +*/
                    '<button  class="btn" style="background-color: #448AFF; color: white" onclick="updateAddress(' + addressId +','+ location.id + ');" type="button"> <i class="fas fa-edit"></i> </button>' +
                    '<button  class="btn" style="background-color: red; color: white; margin-left: 5px" onclick="deleteAddress(' + addressId +','+ location.id + ');" type="button"> Delete </button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                $("#address-container").append(address);

            });
        }

        function newAddress()
        {
        addressId++;
        var newAddress = '<div style="margin-top: 10px" id="row-' + addressId + '" class="row">' +
            '<div class="col-md-6">' +
            '<input type="text" required name="pickupAddress[]" class="rowLocation-'+ addressId +' form-control" placeholder="Enter Pickup Address">' +
            '</div>' +
            '<div class="col-md-6">' +
            '<div class="input-group-btn">' +
            '<button class="btn" style="background-color: #448AFF; color: white" onclick="removeAddress(' + addressId + ');" type="button"> <i class="fas fa-minus"></i> </button>' +
            '<button  class="btn" style="background-color: #448AFF; color: white" onclick="insertAddress(' + addressId +','+ vendor.id + ');" type="button"> <i class="fas fa-save"></i> </button>' +
            '</div>' +
            '</div>' +
            '</div>';

            $("#address-container").append(newAddress);
        }

        function removeAddress(rowId) {
            $("#row-" + rowId).remove();
        }

        function deleteAddress(rowId,dbLocation) {
            var address = $(".rowLocation-" + rowId).val();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/delete-vendor-location',
                type: 'POST',
                data: {_token: CSRF_TOKEN, db_location_id: dbLocation,vendor_id: vendor.id},
                dataType: 'json',
                success: function (response) {
                    if(response.status == 1){
                        $("#row-" + rowId).remove();
                        alert(response.message);
                    }else{
                        alert(response.message);
                    }
                }
            });
        }

        function updateAddress(rowId,dbLocation) {
            var address = $(".rowLocation-" + rowId).val();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/update-vendor-location',
                type: 'POST',
                data: {_token: CSRF_TOKEN, db_location_id: dbLocation,vendor_id: vendor.id,location_address:address},
                dataType: 'json',
                success: function (response) {
                    if(response.status == 1){
                        alert(response.message);
                    }
                }
            });
        }

        function insertAddress(rowId,vendorId) {
            var address = $(".rowLocation-" + rowId).val();
            
            if(address == ''){
                alert('Enter Location');
            }
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/insert-vendor-location',
                type: 'POST',
                data: {_token: CSRF_TOKEN, vendor_id: vendorId,location_address:address},
                dataType: 'json',
                success: function (response) {
                    if(response.status == 1){
                        alert(response.message);
                    }
                }
            });
        }

        var weightRowInc = 0;
        if(weights){
            $.each(weights, function( key, weight ) {
                weightRowInc++;
                var vendorWeight = '<div style="margin-top: 10px" id="row-weight' + weightRowInc + '" class="row">' +
                    '<div class="col-md-2">' +
                    '<input type="text" required name="vendorWeights[]" class="vendor_weight-'+ weightRowInc  +'  form-control" placeholder="Enter Vendor Weight" value="'+ weight.ahl_weight.weight +'">' +
                    '</div>' +
                    '<div class="col-md-2">' +
                    '<input type="text" required name="vendorWeightsPrice[]" class="vendor_weight_price-'+ weightRowInc  +'  form-control" placeholder="Enter Vendor Weight Price" value="'+ weight.price +'">' +
                    '</div>' +
                    '<div class="col-md-2">' +
                    '<input type="number" required name="vendorWeightsMin[]" class="vendor_weight_min-'+ weightRowInc  +'  form-control" placeholder="Enter Vendor Weight Min" value="'+ weight.min_weight +'">' +
                    '</div>' +
                    '<div class="col-md-2">' +
                    '<input type="number" required name="vendorWeightsMax[]" class="vendor_weight_max-'+ weightRowInc  +'  form-control" placeholder="Enter Vendor Weight Max" value="'+ weight.max_weight +'">' +
                    '</div>' +
                    '<div class="col-md-2">' +
                    '<select name="vendorWeightscity[]" class="vendor_city-'+ weightRowInc  +' form-control" required><option value="">Select City</option>@foreach($cities as $city)<option value="{{$city->id}}" {{ 31456 == $city->id  ? 'selected' : ''}}>{{$city->name}}</option>@endforeach</select>' 
                    +'</div>' +

                    '<div class="col-md-2">' +
                    '<div class="input-group-btn">' +
                    /*'<button class="btn" style="background-color: #448AFF; color: white" onclick="deleteVendorWeight(' + weightRowInc +','+ weight.id + ');" type="button"> <i class="fas fa-trash"></i> </button>' +*/
                    '<button class="btn" style="background-color: #448AFF; color: white" onclick="updateVendorWeight(' + weightRowInc +','+ weight.id +','+ weight.ahl_weight_id + ');" type="button"> <i class="fas fa-edit"></i> </button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                $("#vendor-weight-container").append(vendorWeight);
                
            });
        }

        function newVendorWeight()
        {
        weightRowInc++;
        var newVendorWeight = '<div style="margin-top: 10px" id="row-weight' + weightRowInc + '" class="row">' +
            '<div class="col-md-2">' +
            '<input type="text" required name="vendorWeights[]" class="vendor_weight-'+ weightRowInc  +' form-control" placeholder="Enter Vendor Weight">' +
            '</div>' +
            '<div class="col-md-2">' +
            '<input type="text" required name="vendorWeightsPrice[]" class="vendor_weight_price-'+ weightRowInc  +' form-control" placeholder="Enter Vendor Weight Price">' +
            '</div>' +
            '<div class="col-md-2">' +
            '<input type="number" required name="vendorWeightsMin[]" class="vendor_weight_min-'+ weightRowInc  +' form-control" placeholder="Enter Vendor Weight Min">' +
            '</div>' +
            '<div class="col-md-2">' +
            '<input type="number" required name="vendorWeightsMax[]" class="vendor_weight_max-'+ weightRowInc  +' form-control" placeholder="Enter Vendor Weight Max">' +
            '</div>' +
            '<div class="col-md-2">' +
            '<select name="vendorWeightscity[]" class="vendor_city-'+ weightRowInc  +' form-control" required="required"><option value="">Select City</option>@foreach($cities as $city)<option value={{$city->id}}>{{$city->name}}</option>@endforeach</select>' 
            +'</div>' +
            '<div class="col-md-2">' +
            '<div class="input-group-btn">' +
            '<button class="btn" style="background-color: #448AFF; color: white" onclick="removeVendorWeight(' + weightRowInc +');" type="button"> <i class="fas fa-minus"></i> </button>' +
            '<button class="btn" style="background-color: #448AFF; color: white" onclick="insertVendorWeight(' + weightRowInc +','+ vendor.id + ');" type="button"> <i class="fas fa-save"></i> </button>' +
            '</div>' +
            '</div>' +
            '</div>';

            $("#vendor-weight-container").append(newVendorWeight);
        }

        function removeVendorWeight(rowId) {
            $("#row-weight" + rowId).remove();
        }

        function deleteVendorWeight(rowId,dbWeightId) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/delete-vendor-weight',
                type: 'POST',
                data: {_token: CSRF_TOKEN, weight_id: dbWeightId,vendor_id: vendor.id},
                dataType: 'json',
                success: function (response) {
                    if(response.status == 1){
                        $("#row-weight" + rowId).remove();
                        alert(response.message);
                    }else{
                        alert(response.message);
                    }
                }
            });
        }

        function updateVendorWeight(rowId,dbWeightId,dbAhlWeightId) {
            var weightTitle = $(".vendor_weight-" + rowId).val();
            var weightPrice = $(".vendor_weight_price-" + rowId).val();
            var weightMin= $(".vendor_weight_min-" + rowId).val();
            var weightMax= $(".vendor_weight_max-" + rowId).val();
            var weightCity= $(".vendor_city-" + rowId).val();
            console.log(weightCity);
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/update-vendor-weight',
                type: 'POST',
                data: {_token: CSRF_TOKEN,min_weight: weightMin, max_weight: weightMax, city_id:weightCity,  weight_id: dbWeightId,ahl_weight_id:dbAhlWeightId,weight_title: weightTitle,vendor_id: vendor.id,weight_price:weightPrice},
                dataType: 'json',
                success: function (response) {
                    if(response.status == 1){
                        alert(response.message);
                    }
                }
            });
        }

        function insertVendorWeight(rowId,vendorId) {
            var weightTitle = $(".vendor_weight-" + rowId).val();
            var weightPrice = $(".vendor_weight_price-" + rowId).val();
            var weightMin= $(".vendor_weight_min-" + rowId).val();
            var weightMax= $(".vendor_weight_max-" + rowId).val();
            var weightCity= $(".vendor_city-" + rowId).val();
            
            if(address == ''){
                alert('Enter Location');
            }
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/insert-vendor-weight',
                type: 'POST',
                data: {_token: CSRF_TOKEN,min_weight: weightMin, max_weight: weightMax, city_id:weightCity, vendor_id: vendorId,weight_title:weightTitle,weight_price:weightPrice},
                dataType: 'json',
                success: function (response) {
                    if(response.status == 1){
                        alert(response.message);
                    }
                }
            });
        }

</script>
@endsection