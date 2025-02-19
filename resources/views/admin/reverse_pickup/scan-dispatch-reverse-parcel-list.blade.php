@extends('layouts.app')

@section('content')

<div class="page-body">  
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Add Parcel</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-static-label form-default">
                                <input type="text" name="barcode" id="barcode" autofocus="" class="form-control  @error('order_parcel_reference_no') is-invalid @enderror" value="{{ old('order_parcel_reference_no') }}" placeholder="1617184618949" autofocus="">
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

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Assign Rider</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-static-label form-default">
                                <select id="riders" name="rider" class="form-control  @error('rider') is-invalid @enderror" value="{{ old('rider') }}">
                                    <option selected="" disabled="" hidden="">Select Rider</option>
                                    @foreach($riders as $key=> $rider)
                                        <option value="{{$rider->id}}" value="@if(old('rider')) {{ old('rider') }} @endif" @if(old('rider')) {{ 'selected' }} @endif>{{$rider->name}} ( {{$rider->userDetail->cnic}} )</option>
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
                            <th>Vendor Name</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Customer Name</th>
                            <th>Phone #</th>
                            <th>Weight</th>
                            <th>Order ID</th>
                        </tr>
                    </thead>
                    <tbody id="parcel-data">
                        @foreach($parcels as $key => $parcel)
                        <tr>
                            <td align='center'>
                            <input type='checkbox' class='case' name='case' value="{{$parcel->id}}">
                            </td>
                            <th scope='row'>{{$key++}}</th>
                            <td>{{$parcel->order_reference}}</td>
                            <td>{{$parcel->vendor->vendor_name}}</td>
                            <td>{{$parcel->consignment_cod_price}}</td>
                            <td>{{$parcel->consignment_pieces}}</td>
                            <td>{{$parcel->consignee_first_name}} {{$parcel->consignee_last_name}}</td>
                            <td>{{$parcel->consignee_phone}}</td>
                            <td>{{$parcel->vendorWeight->ahlWeight->weight}} </td>
                            <td>{{$parcel->consignment_order_id}}</td>
                        </tr>
                        @endforeach
                        <!-- parcel data prepand here through ajax -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<SCRIPT language="javascript">
var checkedArray = [];
$(function () {

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

    var i = 0;
    /* Barcode */
    $('#barcode').on('keyup',function(e){
        console.log('Enter');
        if(e.keyCode == 13){
            var parcelOrderReferenceId = $("#barcode").val();
            console.log(parcelOrderReferenceId);
            var totaltr = $(".parcel_id").text();

            var trRowId = $('#example').find('tbody tr:first').attr('id');
            if(trRowId == undefined){
                incrementTrRowId = 1;
            }else{
                incrementTrRowId = ++trRowId;
            }
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/check-by-firstman',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, order_parcel_reference_no: parcelOrderReferenceId},
                dataType: 'json',

                success: function(response){
                    //console.log(result)
                    var result = response.parcel;
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
                        console.log(result);
                        $("#total").html(incrementTrRowId);
                        $("table tbody").prepend(
                            "<tr>"
                               + "<td align='center'>"
                               + "<input type='checkbox' class='case' name='case' value=" + result.id + ">"
                               + "</td>"
                               + "<th scope='row'>"+ incrementTrRowId +"</th>"
                               + "<td>" + result.order_reference +"</td>"
                               + "<td>" + result.vendor.vendor_name +"</td>"
                               + "<td>" + result.consignment_cod_price +"</td>"
                               + "<td>" + result.consignment_pieces + "</td>"
                               + "<td>" + result.consignee_first_name + '' + result.consignee_last_name +"</td>"
                               + "<td>" + result.consignee_phone +"</td>"
                               + "<td>" + result.vendor_weight.ahl_weight.weight + ' ('+ result.vendor_weight.city[0].name + ')' + "</td>"
                               + "<td>" + result.consignment_order_id +"</td>"
                            + "</tr>"
                        );
                    }
                    
                    $("#barcode").val("").focus();
                }
            });
        }
    });

    $("#assign_parcel_to_rider").click(function () {
        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
        var rider = $("#riders").val();
        console.log(rider);
        if(rider == null){
            alert('Select Rider');
        }

        if (checkedArray.length == 0) {
            alert('please select some paracel for assigning');
        } else {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/reverse-dispatch-to-rider',
                type: 'POST',
                data: {_token: CSRF_TOKEN, paracels: checkedArray, rider_id: rider},
                dataType: 'json',
                success: function (responses) {
                    if(responses.status == 'success'){
                        window.location.href = "/generate-reverse-dispatch-pdf/" + checkedArray + "/" + rider;
                        setTimeout(function(){ location.reload() }, 5000);
                    }else{
                        //error
                        alert('something wrong');
                    }
                }
            });
        }
    });
});
</SCRIPT>
@endsection