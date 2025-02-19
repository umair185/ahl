@extends('layouts.app')

@php 
use App\Helpers\RoleHelper;
@endphp
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
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h5>{{ $staff->name }} As ( {{ RoleHelper::getUserRoleName($staff) }} ) </h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('updatePassword') }}">
                        @csrf

                        <input id="invisible_id" name="staff_id" type="hidden" value="{{ $staff->id }}">
                        
                        <h4 class="sub-title divider heading">Update Credentials</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="user_name" disabled class="form-control  @error('user_name') is-invalid @enderror" value="{{ $staff->name }}">
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
                                    <input type="email" name="login_email" disabled class="form-control  @error('login_email') is-invalid @enderror" value="{{ $staff->email }}">
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
                                    <input type="password" name="login_password" class="form-control  @error('login_password') is-invalid @enderror" value="">
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
                                    <input type="password" name="login_confirm_password" class="form-control  @error('login_confirm_password') is-invalid @enderror" value="">
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

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection