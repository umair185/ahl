@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection
@section('content')

@if(session('flash'))
    <div class="alert alert-{{ session('flash_alert') }}">
        {{ session('flash_message') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            <!-- foreach ($errors->all() as $error) -->
                <li>Please Select Vendor</li>
            <!-- endforeach -->
        </ul>
    </div>
@endif

<form method="POST" class="form-material" action="{{ route('saveAssignVendor') }}">
    @csrf
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Assign Vendors To {{ $picker->name }}</h5>
                <div class="card-header-right">
                    <ul class="list-unstyled card-option">
                        <li><i class="fa fa fa-wrench open-card-option"></i></li>
                        <li><i class="fa fa-window-maxiprintableAreamize full-card"></i></li>
                        <li><i class="fa fa-minus minimize-card"></i></li>
                        <li><i class="fa fa-refresh reload-card"></i></li>
                        <li><i class="fa fa-trash close-card"></i></li>
                    </ul>
                </div>
            </div>
            <input type="hidden" name="picker_rider" value="{{$pickerId}}">
            <div class="card-block table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectall"/></th>
                                <th>#</th>
                                <th>Vendor Name</th>
                                <th>Vendor Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $counter = 0; @endphp
                            @foreach($vendors as $vendor)
                            <tr>
                                <td align="center"><input type="checkbox" class="case" name="vendor_id[]" value="{{$vendor->id}}"/></td>
                                <th scope="row">{{ ++$counter}}</th>
                                <td>{{$vendor->vendor_name}}</td>
                                <td>{{$vendor->vendor_address}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
        </div>
    </div>
</form>
@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">
var checkedArray = [];
$(function () {
    /* Data Table */
    var table = $('#example').DataTable();

    // add multiple select / deselect functionality

    $("#selectall").click(function () {
        var checkedArray = [];
        //$('.case').attr('checked', this.checked);
        $('.case').prop('checked', this.checked);
        /*$("input:checkbox[name=case]:checked").map(function(){
         checkedArray.push($(this).val());
         })*/
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });

        console.log(checkedArray);
    });



    // if all checkbox are selected, check the selectall checkbox
    // and viceversa
    $(".case").click(function () {
        if ($(".case").length == $(".case:checked").length) {
            //$("#selectall").attr("checked", "checked");
            $("#selectall").prop("checked", true);
        } else {
            //$("#selectall").removeAttr("checked");
            $("#selectall").prop('checked', false);
        }

        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
        console.log(checkedArray);

    });

});
</SCRIPT>
@endsection