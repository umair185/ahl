@extends('layouts.app')

@section('content')

<button class="btn btn-primary waves-effect waves-light" id='weight_yes'>Change Bulk Weight</button>

<div class="page-body" id="weight_view" style="display:none">  
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Select Weight</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-5">
                            <span class="form-bar">Select Vendor</span>
                            <select id="vendor_id" name="vendor_id" class="form-control  @error('vendor_id') is-invalid @enderror" value="{{ old('vendor_id') }}" required>
                                <option selected="" disabled="" hidden="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->vendor_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <span class="form-bar">Select Weight</span>
                            <select id="vendor_weight_id" name="vendor_weight_id" class="form-control  @error('vendor_weight_id') is-invalid @enderror" value="{{ old('vendor_weight_id') }}" required>
                                <option selected="" disabled="" hidden="">Select Vendor Weight</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-static-label form-default">
                                <br>
                                <p class="btn btn-primary" id="bulkChange" onclick="bulkChange()">Change Weight</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">  
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Add Parcel</h5>
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
            <h5>Scan Barcode Parcels List</h5>
        </div>
        <div class="card-header">
            <h5>Total Scan Parcel: <span style="font-size: 20px" id="total">0</span></h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectall"/></th>
                            <th>#</th>
                            <th>Customer Ref #</th>
                            <th>Cust. Name</th>
                            <th>Cust. Address</th>
                            <th>Phone Number</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Order ID</th>
                            <th>Description</th>
                            <th>Change Weight</th>
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
    
    // add multiple select / deselect functionality
    $("#selectall").click(function () {
        var checkedArray = [];
        $('.case').prop('checked', this.checked);
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });

        console.log(checkedArray);
    });

    $(".case").click(function () {
        if ($(".case").length == $(".case:checked").length) {
            $("#selectall").prop("checked", true);
        } else {
            $("#selectall").prop('checked', false);
        }

        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
        console.log(checkedArray);

    });

    $("#weight_yes").click(function () {
        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });

        if (checkedArray.length == 0) {
            alert('please select some paracels to change the weight');
        } else {
            var paragraph = document.getElementById("weight_yes");
            // Hide the paragraph by changing its display property
            paragraph.style.display = "none";
            var close_paragraph = document.getElementById("weight_view");
            // Hide the paragraph by changing its display property
            close_paragraph.style.display = "block";
        }
    });

    $('#vendor_id').change(function(){
        var vendorId = $(this).val(); 
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({  
            url: '/get-vendor-weight',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN, vendor_id: vendorId},
            dataType: 'json',
            success:function(data){ 
                if(data.status == 'success'){
                    $('#vendor_weight_id').html(data.html_data);
                }                    
            }  
        });  
    }); 

    var i = 0;
    /* Barcode */
    $('#barcode').on('keyup',function(e){
        if(e.keyCode == 13){
            var parcelOrderReferenceId = $("#barcode").val();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/add-barcode-parcel',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, order_parcel_reference_no: parcelOrderReferenceId},
                dataType: 'json',

                success: function(response){
                    var result = response.parcel;
                    if(response.status == 'Invalid'){
                        alert(response.message);
                    }

                    if(response.status == 'Before'){
                        alert(response.message);
                    }

                    if(response.status == 'SagStatus'){
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
                               + "<td align='center'>"
                               + "<input type='checkbox' class='case' name='case' value=" + result.id + ">"
                               + "</td>"
                               + "<th scope='row'>"+ i +"</th>"
                               + "<td>" + result.order_reference +"</td>"
                               + "<td>" + result.consignee_first_name + " " + result.consignee_last_name +"</td>"
                               + "<td>" + result.consignee_address +"</td>"
                               + "<td>" + result.consignee_phone +"</td>"
                               + "<td>" + result.consignment_cod_price +"</td>"
                               + "<td>" + result.consignment_pieces + "</td>"
                               + "<td>" + result.vendor_weight.ahl_weight.weight + ' ('+ result.vendor_weight.city[0].name + ')' + "</td>"
                               + "<td>" + result.consignment_order_id +"</td>"
                               + "<td>" + result.consignment_description + "</td>"
                               + "<td>" 
                               + "<a href='https://admin.ahlogistic.pk/change-weight/" + result.id + "' value=" + result.id + " target='_blank'>"
                               + "<button type='button' class='btn btn-primary'>Edit</button></a>"
                               + "</td>"
                            + "</tr>"
                        );
                    }

                    if(response.status == 'Scanned'){
                        ++i;
                        $("#total").html(i);
                        $("table tbody").prepend(
                            "<tr>"
                               + "<td align='center'>"
                               + "<input type='checkbox' class='case' name='case' value=" + result.id + ">"
                               + "</td>"
                               + "<th scope='row'>"+ i +"</th>"
                               + "<td>" + result.order_reference +"</td>"
                               + "<td>" + result.consignee_first_name + " " + result.consignee_last_name +"</td>"
                               + "<td>" + result.consignee_address +"</td>"
                               + "<td>" + result.consignee_phone +"</td>"
                               + "<td>" + result.consignment_cod_price +"</td>"
                               + "<td>" + result.consignment_pieces + "</td>"
                               + "<td>" + result.vendor_weight.ahl_weight.weight + "</td>"
                               + "<td>" + result.consignment_order_id +"</td>"
                               + "<td>" + result.consignment_description + "</td>"
                               + "<td>" 
                               + "<a href='https://admin.ahlogistic.pk/change-weight/" + result.id + "' value=" + result.id + " target='_blank'>"
                               + "<button type='button' class='btn btn-primary'>Edit</button></a>"
                               + "</td>"
                            + "</tr>"
                        );
                    }
                    
                    $("#barcode").val("");
                }
            });
        }
    });
});

function bulkChange()
{
    var checkedArray = [];
    $("input:checkbox[name=case]:checked").each(function () {
        checkedArray.push($(this).val());
    });

    if (checkedArray.length == 0) {
        alert('please select some Parcels to change the weight');
    }
    else
    {
        var vendor_weight_id = $("#vendor_weight_id").val();
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: '/bulk-vendor-weight',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN, vendor_weight_id: vendor_weight_id, parcels: checkedArray},
            dataType: 'json',
            success: function(response){
                if(response.status == 'Success'){
                    alert(response.message);
                }
            }
        });
    }
}
</SCRIPT>
@endsection