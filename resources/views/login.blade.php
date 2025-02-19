@extends('layouts.login')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <!-- Authentication card start -->

            <form method="POST" class="form-material" action="{{ route('authLogin') }}">
            @csrf
                <div class="text-center">
                    <img src="{{ asset('logo/AHL_for_portal.png') }}" height="60" width="114" alt="logo.png">
                </div>
                <div class="auth-box card">
                    <div class="card-block">
                        <div class="row m-b-20">
                            <div class="col-md-12">
                                <h3 class="text-center">Sign In</h3>
                            </div>
                        </div>
                        <div class="form-group form-primary form-static-label">
                            <input type="email" name="email" class="form-control  @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Your Email Address</label>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group form-primary form-static-label">
                            <input type="password" name="password" class="form-control  @error('password') is-invalid @enderror" value="{{ old('password') }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Password</label>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <!-- <div class="row m-t-25 text-left">
                            <div class="col-12">
                                <div class="checkbox-fade fade-in-primary d-">
                                    <label>
                                        <input type="checkbox" value="">
                                        <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                        <span class="text-inverse">Remember me</span>
                                    </label>
                                </div>
                                <div class="forgot-phone text-right f-right">
                                    <a href="auth-reset-password.html" class="text-right f-w-600"> Forgot Password?</a>
                                </div>
                            </div>
                        </div> -->
                        <div class="row m-t-30">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary btn-md btn-block waves-effect waves-light text-center m-b-20">Sign in</button>
                            </div>
                        </div>
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
    <!-- end of col-sm-12 -->
</div>
@endsection