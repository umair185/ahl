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
                    <h5>Create Staff</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveStaff') }}">
                        @csrf
                        <h4 class="sub-title divider heading">STAFF DETAIL</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <select id="staf_roles" name="staff_role" class="form-control  @error('staff_role') is-invalid @enderror" value="{{ old('staff_role') }}">
                                        <option selected="" disabled="" hidden="">Select Staff Role</option>
                                        @foreach ($roles as $key => $role)
                                            <option value="{{ $role->name }}" {{old('staff_role') == $role->name ? 'selected' : ''}}>{{ $role->description }}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('staff_role')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        
                        <h4 class="sub-title divider heading">Login Credentials</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="user_name" class="form-control  @error('user_name') is-invalid @enderror" value="{{ old('user_name') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Name</label>
                                    @error('user_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="email" name="login_email" class="form-control  @error('login_email') is-invalid @enderror" value="{{ old('login_email') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Email</label>
                                    @error('login_email')
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
                                    <input type="password" name="login_password" class="form-control  @error('login_password') is-invalid @enderror" value="{{ old('login_password') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Password</label>
                                    @error('login_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="password" name="login_confirm_password" class="form-control  @error('login_confirm_password') is-invalid @enderror" value="{{ old('login_confirm_password') }}">
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

                        <h4 class="sub-title divider heading">Staff Details</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select id="user_country" name="country" class="form-control  @error('country') is-invalid @enderror" value="{{ old('country') }}">
                                        <option selected="" disabled="" hidden="">Select Country</option>
                                        @foreach(Helper::getCountry() as $key=> $country)
                                            <option value="{{$country->id}}"  {{old('country') == $country->id ? 'selected' : ''}}>{{$country->name}}</option>
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
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select id="state" name="state" class="form-control  @error('state') is-invalid @enderror" value="{{ old('state') }}">
                                        <option selected="" disabled="" hidden="">Select State</option>
                                        @foreach(Helper::getStates() as $key=> $state)
                                            <option value="{{$state->id}}" {{old('state') == $state->id ? 'selected' : ''}}>{{$state->name}}</option>
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
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select id="city" name="city" class="form-control  @error('city') is-invalid @enderror" value="{{ old('city') }}">
                                        <option selected="" disabled="" hidden="">Select City</option>
                                        @foreach($cities as $key=> $city)
                                            <option value="{{$city->id}}" {{old('city') == $city->id ? 'selected' : ''}}>{{$city->name}}</option>
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
                                    <input type="text" name="vehicle" class="form-control  @error('vehicle') is-invalid @enderror" value="{{ old('vehicle') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Vehicle</label>
                                    @error('vehicle')
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
                                    <input type="text" name="phone" class="form-control  @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Phone</label>
                                    @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="user_id" class="form-control  @error('user_id') is-invalid @enderror" value="{{ old('user_id') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">User ID</label>
                                    @error('user_id')
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
                                    <input type="text" name="staff_address" class="form-control  @error('staff_address') is-invalid @enderror" value="{{ old('staff_address') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Staff Address</label>
                                    @error('staff_address')
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
                                    <input type="text" name="cnic" class="form-control  @error('cnic') is-invalid @enderror" value="{{ old('cnic') }}">
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
                                    <input type="text" name="salary" class="form-control  @error('salary') is-invalid @enderror" value="{{ old('salary') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">SALARY</label>
                                    @error('salary')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="commission" class="form-control  @error('commission') is-invalid @enderror" value="{{ old('commission') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">COMMISSION</label>   
                                    @error('commission')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- new field -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="account_number" class="form-control  @error('account_number') is-invalid @enderror" value="{{ old('account_number') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Bank Account #</label>
                                    @error('account_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="account_title" class="form-control  @error('account_title') is-invalid @enderror" value="{{ old('account_title') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Account Title</label>
                                    @error('account_title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="bank_name" class="form-control  @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Bank Name</label>   
                                    @error('bank_name')
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
                                    <input type="text" name="reporting_to" class="form-control  @error('reporting_to') is-invalid @enderror" value="{{ old('reporting_to') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Reporting To</label>
                                    @error('reporting_to')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="location" class="form-control  @error('location') is-invalid @enderror" value="{{ old('location') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Home Location URL</label>
                                    @error('location')
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
                                    <input type="text" name="hiring_by" class="form-control  @error('hiring_by') is-invalid @enderror" value="{{ old('hiring_by') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Hiring By</label>
                                    @error('hiring_by')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="interviewed_by" class="form-control  @error('interviewed_by') is-invalid @enderror" value="{{ old('interviewed_by') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Interviewed By</label>
                                    @error('interviewed_by')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="hiring_platform" class="form-control  @error('hiring_platform') is-invalid @enderror" value="{{ old('hiring_platform') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Hiring Platform</label>   
                                    @error('hiring_platform')
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
                                    <input type="date" name="joining_date" class="form-control  @error('joining_date') is-invalid @enderror" value="{{ old('joining_date') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Joining Date</label>
                                    @error('joining_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="leaving_date" class="form-control  @error('leaving_date') is-invalid @enderror" value="{{ old('leaving_date') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Leaving Date</label>
                                    @error('leaving_date')
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
                                    <input type="text" name="company_assets" class="form-control  @error('company_assets') is-invalid @enderror" value="{{ old('company_assets') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Company Assets</label>
                                    @error('company_assets')
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
                                    <input type="text" name="remarks" class="form-control  @error('remarks') is-invalid @enderror" value="{{ old('remarks') }}">
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

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection