@extends('layouts.login')

@section('content')
<div class="row">
    <div class="col-sm-12">
        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <!-- Authentication card start -->
        <form method="POST" class="form-material" action="{{ route('verifyOTP') }}">
        @csrf
            <div class="text-center">
                <img src="{{ asset('logo/AHL_for_portal.png') }}" height="60" width="114" alt="logo.png">
            </div>
            <div class="auth-box card">
                <div class="card-block">
                    <div class="row m-b-20">
                        <div class="col-md-12">
                            <h3 class="text-center">Please Enter OTP</h3>
                        </div>
                    </div>
                    <div class="form-group form-primary form-static-label">
                        <input type="number" name="otp" class="form-control  @error('otp') is-invalid @enderror" value="{{ old('otp') }}">
                        <span class="form-bar"></span>
                        <label class="float-label">Your OTP</label>
                        @error('otp')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="row m-t-30">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-md btn-block waves-effect waves-light text-center m-b-20">Proceed</button>
                        </div>
                    </div>
                    <p class="text-inverse text-center m-b-0">OTP Not Received?</p>
                    <a href="{{route('resendOTP')}}">
                        <p class="text-inverse text-center m-b-0"><i class="fa fa-refresh"></i> Click Resend</p>
                    </a>
                    <hr/>
                    <div class="row">
                        <div class="col-md-10">
                            <p class="text-inverse text-left m-b-0">AHL</p>
                            <p class="text-inverse text-left"><b>Powered By Mind Tech</b></p>
                        </div>
                        <div class="col-md-2">
                            <img src="{{ asset('logo/hari_chirya.png') }}" style="padding-right: 20px; " height="40" width="61" alt="small-logo.png">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- end of form -->
    </div>
</div>
@endsection