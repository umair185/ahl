@extends('layouts.app')

@section('content')

<div class="page-body">  
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Scan Parcel</h5>
                </div>
                <div class="card-block">
                    <form method="get">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date" id="date" class="form-control" required="required" value="<?php
                                    if (isset($_GET['date'])) {
                                        echo $_GET['date'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group form-static-label form-default">
                                    <select id="riders" name="staff_id" class="form-control  @error('staff_id') is-invalid @enderror" value="{{ old('staff_id') }}">
                                        <option selected="" disabled="" hidden="">Select Staff</option>
                                        @foreach($staffList as $key=> $staff)
                                            <option value="{{$staff->id}}" @if(isset($_GET['staff_id']) && $_GET['staff_id'] == $staff->id) {{ 'selected' }} @endif>{{$staff->name}} ( {{ $staff->userDetail->cnic }} )</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('staff_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
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
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="parcel-data">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer Ref #</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Order ID</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $key => $order)
                        <tr>
                            <td>{{$key++}}</td>
                            <td>{{$order->order_reference}}</td>
                            <td>{{$order->consignment_cod_price}}</td>
                            <td>{{$order->consignment_pieces}}</td>
                            <td>{{$order->vendorWeight->ahlWeight->weight }}</td>
                            <td>{{$order->consignment_order_id}}</td>
                            <td>{{$order->consignment_description   }}</td>
                        </tr>
                        @endforeach
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
$(function () {

    /* Barcode */
    $('#barcode').on('keyup',function(e){
        if(e.keyCode == 13){
            var parcelOrderReferenceId = $("#barcode").val();
            var date = $("#date").val();
            var staff = $("#riders").val();
            
            //console.log(barcode);
            //document.getElementById("sendForm").submit();
            //$("#barcode").val('');
            //$("#barcode").focus();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/reattempt-barcode-parcel',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, order_parcel_reference_no: parcelOrderReferenceId, requestedDate: date, staffId: staff},
                dataType: 'json',

                success: function(response){
                    console.log(response.html_data)
                    var result = response.parcel;
                    if(response.status == 'Invalid'){
                        alert(response.message);
                    }

                    if(response.status == 'Before'){
                        alert(response.message);
                    }

                    if(response.status == 'Scanned'){
                        alert(response.message);
                    }

                    if(response.status == 'Success'){
                        $("#parcel-data").html(response.html_data);
                        
                    }
                    
                    $("#barcode").val("");
                }
            });
        }
    });
});
</SCRIPT>
@endsection