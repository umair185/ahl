@extends('layouts.app')

@php 
use App\Helpers\RoleHelper;
$roleName  = '';
@endphp

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
<style>
        .missing-attributes {
            background-color: yellow;
        }
        .no-missing-attributes {
            background-color: green;
            color: white;
        }
    </style>
@endsection

@section('content')

<div class="page-body">
    <div class="row">

        <div class="col-xl-6 col-md-12">
            <div class="card mat-stat-card">
                <div class="card-block">
                    <div class="row align-items-center b-b-default">
                        <div class="col-sm-3 b-r-default p-b-20 p-t-20">
                            <div class="row align-items-center text-center">
                                <div class="col-4 p-r-0">
                                    <i class="fa fa-user-o text-c-purple f-24"></i>
                                </div>
                                <div class="col-8 p-l-0">
                                    <h5 class="staffHeading">First Man</h5>
                                    <p class="text-muted m-b-0 estimateNumber">{{ $staffCount['first_man'] }}</p>
                                </div>
                            </div>
                        </div>
                         <div class="col-sm-3 b-r-default p-b-20 p-t-20">
                            <div class="row align-items-center text-center">
                                <div class="col-4 p-r-0">
                                    <i class="far fa-user text-c-purple f-24"></i>
                                </div>
                                <div class="col-8 p-l-0">
                                    <h5 class="staffHeading">Picker</h5>
                                    <p class="text-muted m-b-0 estimateNumber">{{ $staffCount['picker'] }}</p>
                                </div>
                            </div>
                        </div>
                         <div class="col-sm-3 b-r-default p-b-20 p-t-20">
                            <div class="row align-items-center text-center">
                                <div class="col-4 p-r-0">
                                    <i class="far fa-user text-c-purple f-24"></i>
                                </div>
                                <div class="col-8 p-l-0">
                                    <h5 class="staffHeading">Middle Man</h5>
                                    <p class="text-muted m-b-0 estimateNumber">{{ $staffCount['middle_man'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 b-r-default p-b-20 p-t-20">
                            <div class="row align-items-center text-center">
                                <div class="col-4 p-r-0">
                                    <i class="far fa-user text-c-purple f-24"></i>
                                </div>
                                <div class="col-8 p-l-0">
                                    <h5 class="staffHeading">Sales</h5>
                                    <p class="text-muted m-b-0 estimateNumber">{{ $staffCount['sales'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12">
            <div class="card mat-stat-card">
                <div class="card-block">
                    <div class="row align-items-center b-b-default">
                        <div class="col-sm-3 b-r-default p-b-20 p-t-20">
                            <div class="row align-items-center text-center">
                                <div class="col-4 p-r-0">
                                    <i class="far fa-user text-c-purple f-24"></i>
                                </div>
                                <div class="col-8 p-l-0">
                                    <h5 class="staffHeading">Supervisor</h5>
                                    <p class="text-muted m-b-0 estimateNumber">{{ $staffCount['supervisor'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 b-r-default p-b-20 p-t-20">
                            <div class="row align-items-center text-center">
                                <div class="col-4 p-r-0">
                                    <i class="far fa-user text-c-purple f-24"></i>
                                </div>
                                <div class="col-8 p-l-0">
                                    <h5 class="staffHeading">Head of Cashier</h5>
                                    <p class="text-muted m-b-0 estimateNumber">{{ $staffCount['cashier'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 b-r-default p-b-20 p-t-20">
                            <div class="row align-items-center text-center">
                                <div class="col-4 p-r-0">
                                    <i class="far fa-user text-c-purple f-24"></i>
                                </div>
                                <div class="col-8 p-l-0">
                                    <h5 class="staffHeading">Accountant</h5>
                                    <p class="text-muted m-b-0 estimateNumber">{{ $staffCount['financer'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 b-r-default p-b-20 p-t-20">
                            <div class="row align-items-center text-center">
                                <div class="col-4 p-r-0">
                                    <i class="far fa-user text-c-purple f-24"></i>
                                </div>
                                <div class="col-8 p-l-0">
                                    <h5 class="staffHeading">Rider</h5>
                                    <p class="text-muted m-b-0 estimateNumber">{{ $staffCount['rider'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Staff List</h5>
            <span>List of staff</span>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Salary</th>
                            <th>Commission</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Documents Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffWithNulls as $key => $user)
                        <tr @if (count($user->null_fields) === 0) style="background-color: green; color: white;" @else style="background-color: yellow;" @endif>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$user->name}}</td>
                            <td>{{$user->email}}</td>
                            <td>{{ ($user->userDetail) ? $user->userDetail->phone : 'N\A'}}</td>
                            <td>{{ ($user->userDetail) ? $user->userDetail->address : 'N\A'}}</td>
                            <td>{{ ($user->userDetail) ? $user->userDetail->salary : 'N\A'}}</td>
                            <td>{{ ($user->userDetail) ? $user->userDetail->commission : 'N\A'}}</td>
                            <?php
                                $roleName = '';
                                $staffRole = RoleHelper::showRoleName($user->roles[0]->name);
                                if($staffRole == 'Financer')
                                {
                                    $roleName = 'Accountant';
                                }
                                elseif($staffRole == 'Cashier')
                                {
                                    $roleName = 'Head of Cashier';
                                }
                                else
                                {
                                    $roleName = $staffRole;
                                }
                            ?>
                            <td>{{ $roleName }}</td>
                            @php
                                $staffStatus = AHLHelper::checkStatus($user->status)
                            @endphp
                            <td class="{{ $staffStatus['class'] }}" >{{ $staffStatus['status'] }}</td>
                            <td>
                                @if (count($user->null_fields) > 0)
                                    {{ implode(', ', $user->null_fields) }}
                                @else
                                    None
                                @endif
                            </td>
                            <td>
                            @if(!$user->isCashier() && !$user->isMiddleMan() && !$user->isSupervisor() && !$user->isFirstMan())
                                <a target="_blank" href="{{route('staffVendorRecord',['id'=>Helper::encrypt($user->id),'role'=>Helper::encrypt($user->roles[0]->id)])}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Record</button></a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor() || Auth::user()->isFirstMan() || Auth::user()->isHubManager() || Auth::user()->isHR())
                                <a target="_blank" href="{{route('staffUpdate',['id'=> Helper::encrypt($user->id)])}}"><button style="margin: 1px;" class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Update</button></a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor() || Auth::user()->isFirstMan() || Auth::user()->isHubManager() || Auth::user()->isHR() )
                                <a href="{{route('staffStatusChange', Helper::encrypt($user->id))}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>{{ ($staffStatus['status'] == 'Active') ? 'Block' : 'Active'  }} </button></a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor() || Auth::user()->isFirstMan() || Auth::user()->isHubManager() || Auth::user()->isHR())
                                <a target="_blank" href="{{route('finduser', $user->id)}}"><button style="margin: 1px;" class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Assign City</button></a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->isHR())
                                <a target="_blank" href="{{route('staffVerification',$user->id)}}"><button style="margin: 1px;" class="btn waves-effect waves-light btn-primary"><i class="fa fa-plus"></i>Edit Verification</button></a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor() || Auth::user()->isHubManager() || Auth::user()->isHR())
                                <a target="_blank" href="{{route('viewVerification',$user->id)}}"><button style="margin: 1px;" class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-down"></i>Download Verification</button></a>
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

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">

$(function () {
    /* Data Table */
    var table = $('#example').DataTable();
});
</SCRIPT>
@endsection