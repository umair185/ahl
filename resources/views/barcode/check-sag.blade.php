@extends('layouts.app')

@section('content')

<div class="page-body">  
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Open Received Sag</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">City</span>
                                <select id="from_city" name="from_city" class="form-control" required>
                                    <option selected="" disabled="" hidden="">Select City</option>
                                    @foreach($userCities as $key=> $userCity)
                                        <option value="{{$userCity->id}}">{{$userCity->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">Sag Number</span>
                                <input type="text" name="parcel_sag" id="parcel_sag" class="form-control" placeholder="LHR-SKP-0001">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-static-label form-default">
                                <br>
                                <p class="btn btn-primary" id="getSag" onclick="openSag()">Open Received Sag</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body display" id="printableArea" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h5>En-route Parcels List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group form-static-label form-default">
                        <span class="form-bar">Scan Parcel</span>
                        <input type="text" name="barcode" id="barcode" autofocus="" class="form-control" placeholder="1617184618949">
                    </div>
                </div>
            </div>
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
                            <th>Description</th>
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
            var parcel_sag = $("#parcel_sag").val();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/check-sag-parcel',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, order_parcel_reference_no: parcelOrderReferenceId, parcel_sag: parcel_sag},
                dataType: 'json',

                success: function(response){
                    //console.log(result)
                    var result = response.parcel;

                    if(response.status == 'Invalid'){
                        alert(response.message);
                    }

                    if(response.status == 'Error'){
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
                               + "<td>" + result.consignment_cod_price +"</td>"
                               + "<td>" + result.consignment_pieces + "</td>"
                               + "<td>" + result.vendor_weight.ahl_weight.weight + "</td>"
                               + "<td>" + result.consignment_order_id +"</td>"
                               + "<td>" + result.consignment_description + "</td>"
                            + "</tr>"
                        );
                    }
                    
                    $("#barcode").val("");
                }
            });
        }
    });
});

function openSag()
{
    var parcel_sag = $("#parcel_sag").val();
    var fromCity = $("#from_city").val();

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: '/open-sag-number',
        type: 'POST',
        /* send the csrf-token and the input to the controller */
        data: {_token: CSRF_TOKEN, parcel_sag: parcel_sag, fromCity: fromCity},
        dataType: 'json',
        success: function(response){
            if(response.status == 'fromCity'){
                alert(response.message);
            }

            if(response.status == 'Invalid'){
                alert(response.message);
            }

            if(response.status == 'CloseBilty'){
                alert(response.message);
            }

            if(response.status == 'EmptyBilty'){
                alert(response.message);
            }

            if(response.status == 'Already'){
                alert(response.message);
            }

            if(response.status == 'Success'){
                alert(response.message);
                var paragraph = document.getElementById("getSag");
                // Hide the paragraph by changing its display property
                paragraph.style.display = "none";
                var close_paragraph = document.getElementById("printableArea");
                // Hide the paragraph by changing its display property
                close_paragraph.style.display = "block";
            }
        }
    });
}
</SCRIPT>
@endsection