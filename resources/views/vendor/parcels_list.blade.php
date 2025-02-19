@extends('layouts.app',$breadcrumbs)

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection
@section('content')

<button class="btn btn-primary waves-effect waves-light" id='print_qr'>Print Qr</button>
<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Parcels List</h5>
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
                            <label>Status</label>
                            <select name="status" id="status" class="form-control" required="required">
                                <option value="any">Any</option>
                                @foreach($statuses as $status)
                                <option {{$statusRequest == $status->id ? 'selected': ''}} value="{{$status->id}}">{{$status->name}}</option>
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
            <div class="text-right">
                <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
            </div>
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
                            <th>Customer Address</th>
                            <th>Customer Phone</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Order ID</th>
                            <th>Description</th>
                            <th>Order Type</th>
                            <th>Pickup Location</th>
                            <th>Current Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 0; @endphp
                        @foreach($parcels as $parcel)
                        <tr id="tr-{{$parcel->id }}">
                            <td align="center"><input type="checkbox" class="case" name="case" value="{{$parcel->id}}"/></td>
                            <th scope="row">{{ ++$counter}}</th>
                            <td>{{$parcel->order_reference}}</td>
                            <td>{{$parcel->consignee_first_name }} {{$parcel->consignee_last_name }}</td>
                            <td>{{$parcel->consignee_address }}</td>
                            <td>{{$parcel->consignee_phone }}</td>
                            <td>{{$parcel->consignment_cod_price}}</td>
                            <td>{{$parcel->consignment_pieces}}</td>
                            <td>{{$parcel->vendorWeight->ahlWeight->weight . ' (' . $parcel->vendorWeight->city->first()->name . ')'}}</td>
                            <td>{{$parcel->consignment_order_id}}</td>
                            <td>{{$parcel->pickupLocation->address }}</td>
                            <td>{{$parcel->consignment_description}}</td>
                            <td>{{$parcel->orderType->name}}</td>
                            <td class="text-success font-bold">{{$parcel->orderStatus->name}}</td>
                            <td>{{date('d M Y h:m a', strtoTime($parcel->created_at))}}</td>
                            <td>
                                <a target="_blank" href="{{route('editParcel', $parcel->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-edit"></i>Edit</button>
                                @if(in_array($parcel->order_status, $cancelledStatusByVendor))
                                <button value="{{ $parcel->id }}" class="cancelByVendor btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Cancel</button>
                            </td>
                            @else
                            <td></td>
                            @endif
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
        pageLength : 250
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

    $(".cancelByVendor").click(function () {
        //var newWin = window.open();
        var proceed = confirm("Are you sure you want to proceed?");
        if (proceed) {
            //proceed
            var parcelId = $(this).attr('value');
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            if(parcelId){
                $.ajax({
                    url: '/cancel-by-vendor',
                    type: 'POST',
                    data: {_token: CSRF_TOKEN, paracel_id: parcelId},
                    dataType: 'json',
                    success: function (response) {
                        if(response.status == 1){
                            //$(this).closest("tr").remove();
                            $('#tr-'+parcelId).remove();
                            /*table.row( $(this).parents('tr') )
                            .remove()
                            .draw();*/
                            
                            alert('Order Cancelled');
                        }
                    }
                });
            }
        } else {
          //don't proceed
        }
        
    });

    $("#print_qr").click(function () {
        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });

        if (checkedArray.length == 0) {
            alert('please select some paracel for print QR');
        } else {
            //var newWin = window.open();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                /* the route pointing to the post function */
                url: '/parcels-qr',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, paracels: checkedArray},
                dataType: 'html',
                /* remind that 'data' is the response of the AjaxController */
                success: function (data) {
                    //$('#printableArea').html(data);
                    var w = window.open('', 'Print parcels', 'width=100%,height=auto');
                    w.document.open();
                    w.document.write(data);
                    w.document.close();
                    //var w = window.open();
                    //$(w.document.body).html(data);
                }
            });
        }
    });
});
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