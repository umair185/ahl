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
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Create Vendor Editor</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveVendorEditor') }}">
                        @csrf
                        <h4 class="sub-title divider heading">STAFF DETAIL</h4>
                        <input type="hidden" value="{{$vendor->id}}" name="vendor_id">

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
                                <div class="form-group form-static-label form-primary">
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
                                <div class="form-group form-static-label form-success">
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="phone_number" class="form-control  @error('phone_number') is-invalid @enderror" value="{{ old('phone_number') }}" minlength="11" maxlength="11">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Phone Number</label>
                                    @error('phone_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Editor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection