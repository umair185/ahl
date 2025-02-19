@extends('layouts.app')


@php 
use App\Helpers\RoleHelper;
$roleName  = '';
@endphp
@section('custom-css')
<style type="text/css">

</style>
@endsection
@section('content')

<div class="page-body">
    <div class="card">
        <div class="card-header">
            <h5>Staff List</h5>
            <span>List of staff</span>
            <div class="card-header-right">
                <a href="{{route('createVendorEditor')}}"><button class="btn waves-effect waves-light btn-primary">Create Vendor Editors</button></a>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>User Email</th>
                            <th>User Phone</th>
                            <th>User Role</th>
                            <th>Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendor_user as $key => $vendors)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$vendors->name}}</td>
                            <td>{{$vendors->email}}</td>
                            <td>{{$vendors->phone_number}}</td>
                            <td>{{ RoleHelper::getUserRoleName($vendors) }}</td>
                            @php
                                $staffStatus = AHLHelper::checkStatus($vendors->status)
                            @endphp
                            <td class="{{ $staffStatus['class'] }}" >{{ $staffStatus['status'] }}</td>
                            <td>
                                @if(Auth::user()->id != $vendors->id)
                                    <a href="{{route('staffStatusChange', Helper::encrypt($vendors->id))}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>{{ ($staffStatus['status'] == 'Active') ? 'Block' : 'Active'  }} </button></a>
                                    <a href="{{route('vendorUpdateEditor', $vendors->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-edit"></i>Edit </button></a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection