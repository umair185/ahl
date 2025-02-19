@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .divider {
        margin-top: 2%; / or whatever /
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
                    <h5>Add Staff Verification</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveStaffVerification', $find_user->id) }}" enctype="multipart/form-data">
                        @csrf
                        <h4 class="sub-title divider heading">Basic Information</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="user_name" class="form-control  @error('user_name') is-invalid @enderror" value="{{$find_user->name}}" readonly>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Name</label>
                                    @error('user_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="cnic" class="form-control numeric_characters  @error('cnic') is-invalid @enderror" value="{{$find_user->userDetail->cnic}}" minlength="13" maxlength="15">
                                    <span class="form-bar"></span>
                                    <label class="float-label">CNIC</label>
                                    @error('cnic')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="phone" class="form-control numeric_characters  @error('phone') is-invalid @enderror" value="{{$find_user->userDetail->phone}}" minlength="11" maxlength="11">
                                    <span class="form-bar"></span>
                                    <label class="float-label">PHONE</label>
                                    @error('phone')
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
                                    <input type="text" name="father_name" class="form-control  @error('father_name') is-invalid @enderror" value="{{$find_user->userDetail->father_name}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">FATHER NAME</label>
                                    @error('father_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="father_cnic" class="form-control numeric_characters  @error('father_cnic') is-invalid @enderror" minlength="13" maxlength="15" value="{{$find_user->userDetail->father_cnic}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">FATHER CNIC</label>
                                    @error('father_cnic')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="father_phone" class="form-control numeric_characters  @error('father_phone') is-invalid @enderror" minlength="11" maxlength="11" value="{{$find_user->userDetail->father_phone}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">FATHER PHONE</label>
                                    @error('father_phone')
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
                                    <select id="user_country" name="marital_status" class="form-control  @error('marital_status') is-invalid @enderror">
                                    <option selected="" disabled="" hidden="">Select Marital Status</option>
                                        <option value="Married" {{"Married" == $find_user->userDetail->marital_status  ? 'selected' : ''}}>Married</option>
                                        <option value="UnMarried" {{"UnMarried" == $find_user->userDetail->marital_status  ? 'selected' : ''}}>UnMarried</option>
                                    </select>
                                    @error('marital_status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="pin_location" class="form-control  @error('pin_location') is-invalid @enderror" value="{{$find_user->userDetail->pin_location}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">PIN LOCATION</label>
                                    @error('pin_location')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="dob" class="form-control  @error('dob') is-invalid @enderror" value="{{$find_user->userDetail->dob}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Date of Birth</label>
                                    @error('dob')
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
                                    <input type="number" name="siblings" class="form-control  @error('siblings') is-invalid @enderror" value="{{$find_user->userDetail->siblings}}" min="0">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Number of Sibling</label>
                                    @error('siblings')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="bike_number" class="form-control  @error('bike_number') is-invalid @enderror" value="{{$find_user->userDetail->bike_number}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">BIKE NUMBER</label>
                                    @error('bike_number')
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
                                    <input type="text" name="staff_address" class="form-control  @error('staff_address') is-invalid @enderror" value="{{$find_user->userDetail->address}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Current Staff Address</label>
                                    @error('staff_address')
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
                                    <input type="text" name="permanent_staff_address" class="form-control  @error('permanent_staff_address') is-invalid @enderror" value="{{$find_user->userDetail->permanent_staff_address}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Permanent Staff Address</label>
                                    @error('permanent_staff_address')
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
                                    <select id="user_country" name="house_status" class="form-control  @error('house_status') is-invalid @enderror">
                                    <option selected="" disabled="" hidden="">Select House Status</option>
                                        <option value="Owner" {{"Owner" == $find_user->userDetail->house_status  ? 'selected' : ''}}>Owner</option>
                                        <option value="Rental" {{"Rental" == $find_user->userDetail->house_status  ? 'selected' : ''}}>Rental</option>
                                    </select>
                                    @error('house_status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="live_from" class="form-control  @error('live_from') is-invalid @enderror" value="{{$find_user->userDetail->live_from}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">LIVE FROM</label>
                                    @error('live_from')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="house_image" class="form-control  @error('house_image') is-invalid @enderror" onchange="loadHouseImage()" accept="image/*">
                                    <p><img id="house_output" width="200" height="100" src="{{asset('')}}{{$find_user->userDetail->house_image}}" /></p>
                                    <span class="form-bar"></span>
                                    <label class="float-label">House Image</label>
                                    @error('house_image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="payment_cheque" class="form-control  @error('payment_cheque') is-invalid @enderror" onchange="loadImage()" accept="image/*">
                                    <p><img id="output" width="200" height="100" src="{{asset('')}}{{$find_user->userDetail->payment_cheque}}" /></p>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Payment Cheque</label>
                                    @error('payment_cheque')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="cnic_front" class="form-control  @error('cnic_front') is-invalid @enderror" onchange="loadFrontImage()" accept="image/*">
                                    <p><img id="front_output" width="200" height="100" src="{{asset('')}}{{$find_user->userDetail->cnic_front}}" /></p>
                                    <span class="form-bar"></span>
                                    <label class="float-label">CNIC Front</label>
                                    @error('cnic_front')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="cnic_back" class="form-control  @error('cnic_back') is-invalid @enderror" onchange="loadBackImage()" accept="image/*">
                                    <p><img id="back_output" width="200" height="100" src="{{asset('')}}{{$find_user->userDetail->cnic_back}}" /></p>
                                    <span class="form-bar"></span>
                                    <label class="float-label">CNIC Back</label>
                                    @error('cnic_back')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="sub-title divider heading">Grantor Details</h4>
                        <p style="font-weight: bold;">Grantor 1</p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_name" class="form-control  @error('grantor_name') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_name : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 Name</label>
                                    @error('grantor_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_cnic" class="form-control numeric_characters  @error('grantor_cnic') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_cnic : ''}}" minlength="13" maxlength="15">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 CNIC</label>
                                    @error('grantor_cnic')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_phone" class="form-control numeric_characters  @error('grantor_phone') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_phone : ''}}" minlength="11" maxlength="11">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 PHONE</label>
                                    @error('grantor_phone')
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
                                    <input type="text" name="grantor_father_name" class="form-control  @error('grantor_father_name') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_father_name : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 FATHER NAME</label>
                                    @error('grantor_father_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_relation" class="form-control  @error('grantor_relation') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_relation : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 RELATION</label>
                                    @error('grantor_relation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_pin_location" class="form-control  @error('grantor_pin_location') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_pin_location : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 PIN LOCATION</label>
                                    @error('grantor_pin_location')
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
                                    <input type="text" name="grantor_age" class="form-control  @error('grantor_age') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_age : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 AGE</label>
                                    @error('grantor_age')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_job" class="form-control  @error('grantor_job') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_job : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 JOB</label>
                                    @error('grantor_job')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_income" class="form-control  @error('grantor_income') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_income : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 INCOME</label>
                                    @error('grantor_income')
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
                                    <input type="text" name="grantor_address" class="form-control  @error('grantor_address') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_address : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 ADDRESS</label>
                                    @error('grantor_address')
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
                                    <input type="file" name="grantor_house" class="form-control  @error('grantor_house') is-invalid @enderror" onchange="loadImageOne()" accept="image/*">
                                    <p><img id="outputOne" width="200" height="100" src="{{asset('')}}{{$find_user->userGrantor ? $find_user->userGrantor->grantor_house : ''}}"/></p>
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 HOUSE IMAGE</label>
                                    @error('grantor_house')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="grantor_image_one" class="form-control  @error('grantor_image_one') is-invalid @enderror" onchange="loadGrantorImageOne()" accept="image/*">
                                    <p><img id="outputGrantorOne" width="200" height="100" src="{{asset('')}}{{$find_user->userGrantor ? $find_user->userGrantor->grantor_image_one : ''}}"/></p>
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 1 IMAGE</label>
                                    @error('grantor_image_one')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <p style="font-weight: bold;">Grantor 2</p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_name_two" class="form-control  @error('grantor_name_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_name_two : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 Name</label>
                                    @error('grantor_name_two')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_cnic_two" class="form-control numeric_characters  @error('grantor_cnic_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_cnic_two : ''}}" minlength="13" maxlength="15">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 CNIC</label>
                                    @error('grantor_cnic_two')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_phone_two" class="form-control numeric_characters  @error('grantor_phone_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_phone_two : ''}}" minlength="11" maxlength="11">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 PHONE</label>
                                    @error('grantor_phone_two')
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
                                    <input type="text" name="grantor_father_name_two" class="form-control  @error('grantor_father_name_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_father_name_two : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 FATHER NAME</label>
                                    @error('grantor_father_name_two')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_relation_two" class="form-control  @error('grantor_relation_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_relation_two : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 RELATION</label>
                                    @error('grantor_relation_two')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_pin_location_two" class="form-control  @error('grantor_pin_location_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_pin_location_two : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 PIN LOCATION</label>
                                    @error('grantor_pin_location_two')
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
                                    <input type="text" name="grantor_age_two" class="form-control  @error('grantor_age_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_age_two : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 AGE</label>
                                    @error('grantor_age_two')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_job_two" class="form-control  @error('grantor_job_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_job_two : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 JOB</label>
                                    @error('grantor_job_two')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="grantor_income_two" class="form-control  @error('grantor_income_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_income_two : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 INCOME</label>
                                    @error('grantor_income_two')
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
                                    <input type="text" name="grantor_address_two" class="form-control  @error('grantor_address_two') is-invalid @enderror" value="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_address_two : ''}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 ADDRESS</label>
                                    @error('grantor_address_two')
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
                                    <input type="file" name="grantor_house_two" class="form-control  @error('grantor_house_two') is-invalid @enderror" onchange="loadImageTwo()" accept="image/*">
                                    <p><img id="outputTwo" width="200" height="100"  src="{{asset('')}}{{$find_user->userGrantor ? $find_user->userGrantor->grantor_house_two : ''}}"/></p>
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 HOUSE IMAGE</label>
                                    @error('grantor_house_two')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="grantor_image_two" class="form-control  @error('grantor_image_two') is-invalid @enderror" onchange="loadGrantorImageTwo()" accept="image/*">
                                    <p><img id="outputGrantorTwo" width="200" height="100"  src="{{asset('')}}{{$find_user->userGrantor ? $find_user->userGrantor->grantor_image_two : ''}}"/></p>
                                    <span class="form-bar"></span>
                                    <label class="float-label">GRANTOR 2 IMAGE</label>
                                    @error('grantor_image_two')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="sub-title divider heading">Emergency Contact Details</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="emergency_name" class="form-control  @error('emergency_name') is-invalid @enderror" value="{{$find_user->userDetail->emergency_name}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">EMERGENCY NAME</label>
                                    @error('emergency_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="emergency_phone" class="form-control numeric_characters  @error('emergency_phone') is-invalid @enderror" value="{{$find_user->userDetail->emergency_phone}}" minlength="11" maxlength="11">
                                    <span class="form-bar"></span>
                                    <label class="float-label">EMERGENCY PHONE</label>
                                    @error('emergency_phone')
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
                                    <input type="text" name="emergency_relation" class="form-control  @error('emergency_relation') is-invalid @enderror" value="{{$find_user->userDetail->emergency_relation}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">EMERGENCY RELATION</label>
                                    @error('emergency_relation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="emergency_picture" class="form-control  @error('emergency_picture') is-invalid @enderror" onchange="loadEmergencyImage()" accept="image/*">
                                    <p><img id="emergency_output" width="200" height="100"  src="{{asset('')}}{{$find_user->userDetail ? $find_user->userDetail->emergency_picture : ''}}"/></p>
                                    <span class="form-bar"></span>
                                    <label class="float-label">EMERGENCY PERSON IMAGE</label>
                                    @error('emergency_picture')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <hr>
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Verification</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
<script type="text/javascript">
  $('.numeric_characters').bind('input', function() {
    var c = this.selectionStart,
        r = /[^0-9 +-]/gi,
        v = $(this).val();
    if(r.test(v)) {
      $(this).val(v.replace(r, ''));
      c--;
    }
    this.setSelectionRange(c, c);
  });
</script>
<script>
  function loadImage()
  {
    var image = document.getElementById('output');
    image.src = URL.createObjectURL(event.target.files[0]);
  }
  function loadHouseImage()
  {
    var image = document.getElementById('house_output');
    image.src = URL.createObjectURL(event.target.files[0]);
  }
  function loadFrontImage()
  {
    var image = document.getElementById('front_output');
    image.src = URL.createObjectURL(event.target.files[0]);
  }
  function loadBackImage()
  {
    var image = document.getElementById('back_output');
    image.src = URL.createObjectURL(event.target.files[0]);
  }
  function loadImageOne()
  {
    var image = document.getElementById('outputOne');
    image.src = URL.createObjectURL(event.target.files[0]);
  }
  function loadImageTwo()
  {
    var image = document.getElementById('outputTwo');
    image.src = URL.createObjectURL(event.target.files[0]);
  }
  function loadGrantorImageOne()
  {
    var image = document.getElementById('outputGrantorOne');
    image.src = URL.createObjectURL(event.target.files[0]);
  }
  function loadGrantorImageTwo()
  {
    var image = document.getElementById('outputGrantorTwo');
    image.src = URL.createObjectURL(event.target.files[0]);
  }
  function loadEmergencyImage()
  {
    var image = document.getElementById('emergency_output');
    image.src = URL.createObjectURL(event.target.files[0]);
  }
</script>
@endsection