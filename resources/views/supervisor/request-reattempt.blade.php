@extends('layouts.app',$breadcrumbs)

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection
@section('content')

<div class="page-body">  
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Assign Rider</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-static-label form-default">
                                <select id="riders" name="rider" class="form-control  @error('rider') is-invalid @enderror" value="{{ old('rider') }}">
                                    <option selected="" disabled="" hidden="">Select Rider</option>
                                    @foreach($riders as $key=> $rider)
                                        <option value="{{$rider->id}}" value="@if(old('rider')) {{ old('rider') }} @endif" @if(old('rider')) {{ 'selected' }} @endif>{{$rider->name}}</option>
                                    @endforeach
                                </select>
                                <span class="form-bar"></span>
                                @error('rider')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">Assign Rider</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body display">
    <div class="card">
        <div class="card-header">
            <h5>Parcels For Reattempts</h5>
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
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Order ID</th>
                            <th>Rider</th>
                            <th>Undelivered Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @foreach($assignedOrderReattempts as $parcelAssigned)
                        <tr id="tr-{{ $parcelAssigned->id }}">
                            <td align="center"><input type="checkbox" class="case" name="case"
                             data-order_id="{{ $parcelAssigned->order_id }}"
                             value="{{$parcelAssigned->id}}"/>
                            </td>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{$parcelAssigned->order->order_reference}}</td>
                            <td>{{$parcelAssigned->order->consignment_cod_price}}</td>
                            <td>{{$parcelAssigned->order->consignment_pieces}}</td>
                            <td>{{$parcelAssigned->order->consignment_weight}}</td>
                            <td>{{$parcelAssigned->order->consignment_order_id}}</td>
                            <td>{{$parcelAssigned->rider->name}}</td>
                            <td>
                                Add Undelivered Reason's
                            </td>
                            <td>
                                <button data-id="{{$parcelAssigned->id}}" class="reattemptBtn btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Reattempt</button>
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

    $(".reattemptBtn").click(function () {
        //var newWin = window.open();
        var proceed = confirm("Are you sure you want to proceed?");
        if (proceed) {
            //proceed
            var parcelAssignedId = $(this).attr('data-id');
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            if(parcelAssignedId){
                $.ajax({
                    url: '/request-reattempt',
                    type: 'POST',
                    data: {_token: CSRF_TOKEN, paracel_assigned_id: parcelAssignedId},
                    dataType: 'json',
                    success: function (response) {
                        if(response.status == 1){
                            //$(this).closest("tr").remove();
                            $('#tr-'+parcelAssignedId).remove();
                            /*table.row( $(this).parents('tr') )
                            .remove()
                            .draw();*/
                            
                            alert(response.message);
                        }
                    }
                });
            }
        } else {
          //don't proceed
        }
        
    });

    $("#assign_parcel_to_rider").click(function () {
        var checkedArray = [];
        var checkedOrderIdArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
            checkedOrderIdArray.push($(this).attr('data-order_id'));
        });
        var rider = $("#riders").val();
        
        if(rider == null){
            alert('Select Rider');
        }

        if (checkedArray.length == 0) {
            alert('please select some paracel for assigning');
        } else {
            //var newWin = window.open();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                /* the route pointing to the post function */
                url: '/request-reattempt',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, paracel_assigned_id: checkedArray, parcels_assigned_rider_id: rider, parcel_order_ids:checkedOrderIdArray},
                dataType: 'json',
                /* remind that 'data' is the response of the AjaxController */
                success: function (response) {
                    if(response.status == 1){
                        alert(response.message);
                        location.reload();
                    }else{
                        //error
                        alert('Error');
                    }
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