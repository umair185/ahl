@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body">

    <div class="row">
        <div class="col-xl-6 col-md-6">
            <div class="card mat-clr-stat-card text-white green">
                <div class="card-block">
                    <div class="row">
                        <div class="col-3 text-center bg-c-green">
                            <i class="fas fa-user mat-icon f-24"></i>
                        </div>
                        <div class="col-9 cst-cont">
                            <h5 class="estimateNumber"> {{ $estimateVendor['active'] }} </h5>
                            <p class="m-b-0">Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6">
            <div class="card mat-clr-stat-card text-white red">
                <div class="card-block">
                    <div class="row">
                        <div class="col-3 text-center bg-c-red">
                            <i class="fas fa-lock mat-icon f-24"></i>
                        </div>
                        <div class="col-9 cst-cont">
                            <h5 class="estimateNumber">{{ $estimateVendor['block'] }}</h5>
                            <p class="m-b-0">Block</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Vendors List</h5>
            <div class="card-header-right">
                <div class="text-right">
                    <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
                </div>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendor Number</th>
                            <th>Company Name</th>
                            @hasanyrole('admin')
                            <th>Company Phone</th>
                            <th>Company Email</th>
                            <th>Focal Person Name</th>
                            <th>Focal Person Email</th>
                            <th>Focal Person Phone</th>
                            <th>Bank Name</th>
                            <th>Bank Account</th>
                            @endhasanyrole
                            <th>Commission</th>
                            <th>Payment Mode</th>
                            <th>Fuel</th>
                            <th>Addational Kgs</th>
                            <th>Created By</th>
                            <th>POC Person</th>
                            <th>POC Assigned At</th>
                            <th>POC Assigned By</th>
                            <th>CSR Person</th>
                            <th>CSR Assigned At</th>
                            <th>CSR Assigned By</th>
                            <th>P.Supervisor Person</th>
                            <th>P.Supervisor Assigned At</th>
                            <th>P.Supervisor Assigned By</th>
                            <th>Status</th>
                            <th>User Management</th>
                            @hasanyrole('admin|bdm')
                            <th>Commission Management</th>
                            <th>Upload Logo (Logo should be in 1:1)</th>
                            @endhasanyrole('admin|bdm')
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendor as $vendors)
                        <tr>
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{$vendors->vendor_number}}</td>
                            <td>{{$vendors->vendor_name}}</td>
                            @hasanyrole('admin')
                            <td>{{$vendors->vendor_phone}}</td>
                            <td>{{$vendors->vendor_email}}</td>
                            <td>{{$vendors->focal_person_name}}</td>
                            <td>{{$vendors->focal_person_email}}</td>
                            <td>{{$vendors->focal_person_phone}}</td>
                            <td>{{$vendors->bank_name}}</td>
                            <td>{{$vendors->bank_account}}</td>
                            @endhasanyrole
                            @if($vendors->commision == 1)
                            <td>Charged</td>
                            @else
                            <td>Not Charged</td>
                            @endif
                            <td>{{$vendors->payment_mode}}</td>
                            <td>{{$vendors->fuel}}</td>
                            <td>{{$vendors->addational_kgs}}</td>
                            <td>{{$vendors->createdBy ? $vendors->createdBy->name : ''}}</td>
                            <td>{{$vendors->pocPerson ? $vendors->pocPerson->name : ''}}</td>
                            @if($vendors->datentime == NULL)
                            <td></td>
                            @else
                            <td>{{date('d M Y h:i a', strtoTime($vendors->datentime))}}</td>
                            @endif
                            <td>{{$vendors->pocAssignedBy ? $vendors->pocAssignedBy->name : ''}}</td>
                            <td>{{$vendors->csrPerson ? $vendors->csrPerson->name : ''}}</td>
                            @if($vendors->csr_datentime == NULL)
                            <td></td>
                            @else
                            <td>{{date('d M Y h:i a', strtoTime($vendors->csr_datentime))}}</td>
                            @endif
                            <td>{{$vendors->csrAssignedBy ? $vendors->csrAssignedBy->name : ''}}</td>
                            <td>{{$vendors->pickupPerson ? $vendors->pickupPerson->name : ''}}</td>
                            @if($vendors->pickup_datentime == NULL)
                            <td></td>
                            @else
                            <td>{{date('d M Y h:i a', strtoTime($vendors->pickup_datentime))}}</td>
                            @endif
                            <td>{{$vendors->pickupAssignedBy ? $vendors->pickupAssignedBy->name : ''}}</td>
                            @php
                                $vendorStatus = AHLHelper::checkStatus($vendors->status)
                            @endphp
                            <td class="{{ $vendorStatus['class'] }}" >{{ $vendorStatus['status'] }}</td>
                            <td>
                                <a href="{{route('vendorUsersList', $vendors->id)}}"><button style="margin: 1px;" class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Users</button></a>
                                @hasanyrole('admin|sales|bdm')
                                <a href="{{route('editVendor', $vendors->id)}}"><button style="margin: 1px;" class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Edit</button></a>
                                <a href="{{route('vendorStatusChange', $vendors->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>{{ ($vendorStatus['status'] == 'Active') ? 'Block' : 'Active'  }} </button></a>
                                @endhasanyrole('admin|sales|bdm')
                            </td>
                            @hasanyrole('admin|bdm')
                            <td>
                                @if($vendors->commision == 1)
                                <a href="{{route('vendorCommissionChange', $vendors->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Don't want to Charge </button></a>
                                @else
                                <a href="{{route('vendorCommissionChange', $vendors->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Want to Charge </button></a>
                                @endif
                            </td>
                            <td>
                                <img width="200px" height="150px" src="{{ asset($vendors->vendor_image)}}">
                                <br>
                                <form action="{{ route('uploadVendorPhoto',$vendors->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="photo" accept="image/*" />
                                    <br>
                                    <button class="btn btn-primary">Save</button>
                                </form>
                            </td>
                            @endhasanyrole('admin|bdm')
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
<SCRIPT language="javascript">
function fnExcelReport()
    {
        var tab_text="<table border='2px'><tr bgcolor='#87AFC6'>";
        var textRange; var j=0;
        tab = document.getElementById('example'); // id of table
    
        for(j = 0 ; j < tab.rows.length ; j++) 
        {     
            tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
            //tab_text=tab_text+"</tr>";
        }
    
        tab_text=tab_text+"</table>";
        tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
        tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
        tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params
    
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE "); 
    
        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
        {
            txtArea1.document.open("txt/html","replace");
            txtArea1.document.write(tab_text);
            txtArea1.document.close();
            txtArea1.focus(); 
            sa=txtArea1.document.execCommand("SaveAs",true,"Say Thanks to Sumit.xls");
        }  
        else                 //other browser not tested on IE 11
            sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));  
    
        return (sa);
    }
</SCRIPT>
@endsection