@extends('layouts.app')

@php
use App\Helpers\RoleHelper;
@endphp
@section('content')
<!-- Page-body start -->
<div class="page-body">
    <!-- Basic table card start -->
    <div class="card">
        <div class="card-header">
            <h5>{{ $staff->name }} Parcel Detail | {{ RoleHelper::getUserRoleName($staff) }} </h5>
            <span>Staff Parcel detail's</span>
        </div>
        <div class="card-block list-tag">
            <div class="row">
                <div class="col-sm-12 col-xl-4">
                    <h4 class="sub-title">{{ $staff->name }} Detail</h4>
                    <ul>
                        <li><b>Name:</b> {{ $staff->name }}</li>
                        <li><b>Phone:</b> {{ $staff->userDetail->phone }}</li>
                        <li><b>Email:</b> {{ $staff->email }}</li>
                        <li><b>Salary:</b> {{ $staff->userDetail->salary }}</li>
                        <li><b>Per Parcel Commision:</b> {{ $staff->userDetail->commission }}</li>
                        <li><b>Joining Date: {{ date('d M Y', strtoTime($staff->created_at))   }}</li></b>
                        <hr>
                        <li><b>{{ $staff->name }} Assign Cities: </b>
                            @foreach($staff->userDetail->assignCity as $state)
                            <ol>
                                <li>
                                    {{ $state->userCity->name }},
                                </li>
                            </ol>
                            @endforeach
                        </li>
                    </ul>
                </div>
                <div class="col-sm-12 col-xl-4">
                    <h4 class="sub-title">Parcel's Detail</h4>
                    <ol>
                        <li>Total Parcel's: {{ $commission['totalOrder']  }} </li>
                        
                        <!-- <li>Consignee Country Detail
                            <ol>
                                <li>Country:  </li>
                                <li>State:</li>
                                <li>City:  </li>
                            </ol>
                        </li> -->
                    </ol>
                </div>
                <div class="col-sm-12 col-xl-4">
                    <h4 class="sub-title">Commission Detail's</h4>
                    <div class="row col-sm-12">
                        <ul class="list-inline m-b-0">
                            <li class="list-inline-item">Total Commission: {{ $commission['totalCommission']  }} </li>
                            <li class="list-inline-item">Total paid Commission: {{ $commission['totalPaidCommission']  }} </li>
                            <li class="list-inline-item">Total Remaing Commission: {{ $commission['remaingCommission']  }} </li>
                        </ul>
                    </div>
                    <!-- <div class="row col-sm-12">
                        <div class="inline-order-list">
                            <h4 class="sub-title">Commission Detail's</h4>
                            <p class="text-muted">
                                Place all list items on a single line with <code>
                                    display: inline-block;</code>
                            </p>

                        </div>
                        <div class="card-block"> 
                            <ul class="list-inline m-b-0">
                                <li class="list-inline-item">Total Commission: {{ $commission['totalCommission'] }} </li>
                                <li class="list-inline-item">Total paid Commission: </li>
                                <li class="list-inline-item">Total Remaing Commission: </li>
                            </ul>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    <!-- List Tag card end -->
</div>
<!-- Page-body end -->
@endsection