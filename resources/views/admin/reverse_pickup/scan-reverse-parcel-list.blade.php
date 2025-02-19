@extends('layouts.app')

@section('content')

<div class="page-body">  
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Add Reverse Pickup Parcel</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-static-label form-default">
                                <input type="text" name="barcode" id="barcode" autofocus="" class="form-control  @error('order_parcel_reference_no') is-invalid @enderror" value="{{ old('order_parcel_reference_no') }}" placeholder="1617184618949">
                                <span class="form-bar"></span>
                                @error('order_parcel_reference_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Scan Barcode Reverse Parcels List</h5>
        </div>
        <div class="card-header">
            <h5>Total Scan Parcel: <span style="font-size: 20px" id="total">0</span></h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer Ref #</th>
                            <th>Vendor Name</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Customer Name</th>
                            <th>Phone #</th>
                            <th>Weight</th>
                            <th>Order ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="parcel-data">
                        <!-- parcel data prepand here through ajax -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')<!-- 
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script> -->
<SCRIPT language="javascript">
var checkedArray = [];
$(function () {

    var i = 0;
    /* Barcode */
    $('#barcode').on('keyup',function(e){
        if(e.keyCode == 13){
            var parcelOrderReferenceId = $("#barcode").val();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/add-barcode-reverse-parcel',
                type: 'POST',
                data: {_token: CSRF_TOKEN, order_parcel_reference_no: parcelOrderReferenceId},
                dataType: 'json',

                success: function(response){
                    var result = response.parcel;
                    // console.log(result);
                    if(response.status == 'Invalid'){
                        alert(response.message);
                    }

                    if(response.status == 'Before'){
                        alert(response.message);
                    }

                    if(response.status == 'After'){
                        alert(response.message);
                    }

                    if(response.status == 'Success'){
                        ++i;
                        $("#total").html(i);
                        $("table tbody").prepend(
                            "<tr>"
                               + "<th scope='row'>"+ i +"</th>"
                               + "<td>" + result.order_reference +"</td>"
                               + "<td>" + result.vendor.vendor_name +"</td>"
                               + "<td>" + result.consignment_cod_price +"</td>"
                               + "<td>" + result.consignment_pieces + "</td>"
                               + "<td>" + result.consignee_first_name + '' + result.consignee_last_name +"</td>"
                               + "<td>" + result.consignee_phone +"</td>"
                               + "<td>" + result.vendor_weight.ahl_weight.weight + ' ('+ result.vendor_weight.city[0].name + ')' + "</td>"
                               + "<td>" + result.consignment_order_id +"</td>"
                               + "<td>At AHL WareHouse</td>"
                            + "</tr>"
                        );
                    }

                    if(response.status == 'Scanned'){
                        ++i;
                        $("#total").html(i);
                        $("table tbody").prepend(
                            "<tr>"
                               + "<th scope='row'>"+ i +"</th>"
                               + "<td>" + result.order_reference +"</td>"
                               + "<td>" + result.vendor.vendor_name +"</td>"
                               + "<td>" + result.consignment_cod_price +"</td>"
                               + "<td>" + result.consignment_pieces + "</td>"
                               + "<td>" + result.consignee_first_name + '' + result.consignee_last_name +"</td>"
                               + "<td>" + result.consignee_phone +"</td>"
                               + "<td>" + result.vendor_weight.ahl_weight.weight + ' ('+ result.vendor_weight.city[0].name + ')' + "</td>"
                               + "<td>" + result.consignment_order_id +"</td>"
                               + "<td>At AHL WareHouse</td>"
                            + "</tr>"
                        );
                    }
                    
                    $("#barcode").val("");
                }
            });
        }
    });
});
</SCRIPT>
@endsection