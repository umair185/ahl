@extends('layouts.app')


@php 
use App\Helpers\RoleHelper;
$roleName  = '';
@endphp
@section('custom-css')
<style type="text/css">
    .btn {
        padding: .375rem .75rem;
    }
</style>
@endsection
@section('content')

<div class="page-body">
    <div class="card">
        <div class="card-header">
            <h5>{{$vendor->vendor_name}}'s Users List</h5>
            @hasanyrole('admin|vendor_admin')
            <div class="card-header-right">
                <a href="{{route('createEditor', $vendor->id)}}"><button class="btn waves-effect waves-light btn-primary">Create Vendor Editors</button></a>
            </div>
            @endhasanyrole
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
                            @hasanyrole('admin|vendor_admin')
                            <th>Action</th>
                            @endhasanyrole
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendor_user as $vendors)
                        <tr>
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{$vendors->name}}</td>
                            <td>{{$vendors->email}}</td>
                            <td>{{$vendors->phone_number}}</td>
                            <td>{{ RoleHelper::getUserRoleName($vendors) }}</td>
                            @hasanyrole('admin|vendor_admin')
                            <td>
                                <a href="{{route('updateEditor', $vendors->id)}}"><i class="fa fa-edit"></i>Edit</a>
                            </td>
                            @endhasanyrole
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection