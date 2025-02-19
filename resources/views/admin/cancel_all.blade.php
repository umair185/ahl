@extends('layouts.app',$breadcrumbs)

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection
@section('content')

<button class="btn btn-primary waves-effect waves-light" id='print_qr'>Cancel Bulk Orders</button>
<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Parcels List</h5>
            <hr>
            <form method="get">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="from" id="from" class="form-control" required="required" value="<?php
                            if (isset($_GET['from'])) {
                                echo $_GET['from'];
                            }
                            ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="to" id="to" class="form-control" required="required" value="<?php
                            if (isset($_GET['to'])) {
                                echo $_GET['to'];
                            }
                            ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Select Vendor</label>
                            <select name="vendor" id="vendor" class="form-control" required="required">
                                <option value="all">All Vendor</option>
                                @foreach($vendors as $vendor)
                                <option {{$vendorRequest == $vendor->id ? 'selected': ''}} value="{{$vendor->id}}">{{$vendor->vendor_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <br>
                            <button type="submit" class="btn btn-primary mt-1">Filter</button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectall"/></th>
                            <th>#</th>
                            <th>Customer Ref #</th>
                            <th>Customer Name</th>
                            <th>Vendor Name</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Order ID</th>
                            <th>Description</th>
                            <th>Order Type</th>
                            <th>Current Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 0; @endphp
                        @foreach($parcels as $parcel)
                        <tr id="tr-{{$parcel->id }}">
                            <td align="center"><input type="checkbox" class="case" name="case" value="{{$parcel->id}}"/></td>
                            <th scope="row">{{ ++$counter}}</th>
                            <td>{{$parcel->order_reference}}</td>
                            <td>{{$parcel->consignee_first_name}} {{$parcel->consignee_last_name}}</td>
                            <td>{{$parcel->Vendor->vendor_name}}</td>
                            <td>{{$parcel->consignment_cod_price}}</td>
                            <td>{{$parcel->consignment_pieces}}</td>
                            <td>{{$parcel->vendorWeight->ahlWeight->weight . ' (' . $parcel->vendorWeight->city->first()->name . ')'}}</td>
                            <td>{{$parcel->consignment_order_id}}</td>
                            <td>{{$parcel->consignment_description}}</td>
                            <td>{{$parcel->orderType->name}}</td>
                            <td class="text-success font-bold">{{$parcel->orderStatus->name}}</td>
                            <td>{{date('d M Y h:m a', strtoTime($parcel->created_at))}}</td>
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
var checkedArray = [];
$(function () {
    /* Data Table */
    var table = $('#example').DataTable({
      "lengthMenu": [ 10, 25, 50, 75, 100, 500, 1000, 5000 ]
    });

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

        //console.log(checkedArray);
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
        //console.log(checkedArray);

    });

    $("#print_qr").click(function () {
        var proceed = confirm("Are you sure you want to proceed?");
        if(proceed)
        {
            var checkedArray = [];
            $("input:checkbox[name=case]:checked").each(function () {
                checkedArray.push($(this).val());
            });

            if (checkedArray.length == 0) {
                alert('please select some paracel for Cancelation');
            } else {
                //var newWin = window.open();
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    /* the route pointing to the post function */
                    url: '/awaiting-parcels-list-cancel',
                    type: 'POST',
                    /* send the csrf-token and the input to the controller */
                    data: {_token: CSRF_TOKEN, paracels: checkedArray},
                    dataType: 'json',
                    /* remind that 'data' is the response of the AjaxController */
                    success: function (response) {
                        if(response.status == 1){                            
                            alert('Orders Cancelled, let the page refresh to view results');
                            location.reload();
                        }
                    }
                });
            }
        }
        else
        {

        }
    });
});
</SCRIPT>
@endsection