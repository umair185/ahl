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
                    <form method="POST" class="form-material" action="{{ route('updateStaff')}}">
                        @csrf

                        <input id="invisible_id" name="staff_id" type="hidden" value="{{ $staff->id}}">
                        @if(Auth::user()->isAdmin())
                        <h4 class="sub-title divider heading">STAFF DETAIL</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <select id="staf_roles" name="staff_role" class="form-control  @error('staff_role') is-invalid @enderror">
                                        <option selected="" disabled="" hidden="">Select New Staff Role (If want to Change)</option>
                                        @foreach ($roles as $key => $role)
                                            <option value="{{ $role->name }}">{{ $role->description }}</option>
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
                        @endif
                        
                        <h4 class="sub-title divider heading">Update Credentials</h4>
                        <?php
                            $readonly = '';
                            if(Auth::user()->isAdmin() || Auth::user()->isHR() || Auth::user()->isFirstMan())
                            {
                                $readonly = '';
                            }
                            elseif(Auth::user()->isSupervisor() || Auth::user()->isHubManager())
                            {
                                $readonly = 'readonly';
                            }
                            else
                            {
                                $readonly = '';
                            }
                        ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="user_name" class="form-control  @error('user_name') is-invalid @enderror" value="{{ $staff->name }}" {{$readonly}}>
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
                                    <input type="email" name="login_email" class="form-control  @error('login_email') is-invalid @enderror" value="{{ $staff->email }}" {{$readonly}}>
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
                        @if(Auth::user()->id != $staff->id)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="user_id" class="form-control  @error('user_id') is-invalid @enderror" value="{{ $staff->user_id }}" {{$readonly}}>
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
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="phone" class="form-control  @error('phone') is-invalid @enderror"value="{{ $staff->userDetail->phone }}" required>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Phone</label>
                                    @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="cnic" class="form-control  @error('cnic') is-invalid @enderror" value="{{ $staff->userDetail->cnic }}" {{$readonly}}>
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
                                    <input type="text" name="salary" class="form-control  @error('salary') is-invalid @enderror" value="{{ $staff->userDetail->salary }}" {{$readonly}}>
                                    <span class="form-bar"></span>
                                    <label class="float-label">SALARY</label>
                                    @error('salary')
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
                                    <input type="text" name="staff_address" class="form-control  @error('staff_address') is-invalid @enderror"value="{{ $staff->userDetail->address }}" {{$readonly}}>
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
                        <!-- new field -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="account_number" class="form-control  @error('account_number') is-invalid @enderror" value="{{ $staff->userDetail->account_number }}" {{$readonly}}>
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
                                    <input type="text" name="account_title" class="form-control  @error('account_title') is-invalid @enderror" value="{{ $staff->userDetail->account_title }}" {{$readonly}}>
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
                                    <input type="text" name="bank_name" class="form-control  @error('bank_name') is-invalid @enderror" value="{{ $staff->userDetail->bank_name }}" {{$readonly}}>
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
                                    <input type="text" name="reporting_to" class="form-control  @error('reporting_to') is-invalid @enderror" value="{{ $staff->userDetail->reporting_to }}" {{$readonly}}>
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
                                    <input type="text" name="location" class="form-control  @error('location') is-invalid @enderror" value="{{ $staff->userDetail->location }}" {{$readonly}}>
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
                                    <input type="text" name="hiring_by" class="form-control  @error('hiring_by') is-invalid @enderror" value="{{ $staff->userDetail->hiring_by }}" {{$readonly}}>
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
                                    <input type="text" name="interviewed_by" class="form-control  @error('interviewed_by') is-invalid @enderror" value="{{ $staff->userDetail->interviewed_by }}" {{$readonly}}>
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
                                    <input type="text" name="hiring_platform" class="form-control  @error('hiring_platform') is-invalid @enderror" value="{{ $staff->userDetail->hiring_platform }}" {{$readonly}}>
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
                                    <input type="date" name="joining_date" class="form-control  @error('joining_date') is-invalid @enderror" value="{{ $staff->userDetail->joining_date }}" {{$readonly}}>
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
                                    <input type="date" name="leaving_date" class="form-control  @error('leaving_date') is-invalid @enderror" value="{{ $staff->userDetail->leaving_date }}" {{$readonly}}>
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
                                    <input type="text" name="company_assets" class="form-control  @error('company_assets') is-invalid @enderror" value="{{ $staff->userDetail->company_assets }}" {{$readonly}}>
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
                                    <input type="text" name="remarks" class="form-control  @error('remarks') is-invalid @enderror" value="{{ $staff->userDetail->remarks }}" {{$readonly}}>
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
                        @endif

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection