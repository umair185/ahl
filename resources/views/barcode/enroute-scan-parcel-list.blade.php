@extends('layouts.app')

@section('content')

<div class="page-body">  
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Add En-route Parcel</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">From City</span>
                                <select id="from_city" name="from_city" class="form-control  @error('from_city') is-invalid @enderror" value="{{ old('from_city') }}" required>
                                    <option selected="" disabled="" hidden="">Select From City</option>
                                    @foreach($userCities as $key=> $userCity)
                                        <option value="{{$userCity->id}}">{{$userCity->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">To City</span>
                                <select id="to_city" name="to_city" class="form-control  @error('to_city') is-invalid @enderror" value="{{ old('to_city') }}" required>
                                    <option selected="" disabled="" hidden="">Select To City</option>
                                    @foreach($getCities as $key=> $getCity)
                                        <option value="{{$getCity->id}}">{{$getCity->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">Manual Seal Number</span>
                                <input type="text" name="seal_number" id="seal_number" class="form-control" placeholder="LHR-SKP-0001">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">Sag Number</span>
                                <input type="text" readonly name="parcel_sag" id="parcel_sag" class="form-control" placeholder="LHR-SKP-0001">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-static-label form-default">
                                <br>
                                <p class="btn btn-primary" id="getSag" onclick="getSag()">Get Sag Number</p>
                                <p class="btn btn-primary" id="closeSag" style="display:none;" onclick="closeSag()">Close Sag</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">Scan Parcel</span>
                                <input type="text" name="barcode" id="barcode" autofocus="" class="form-control  @error('order_parcel_reference_no') is-invalid @enderror" value="{{ old('order_parcel_reference_no') }}" placeholder="1617184618949">
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
            <h5>Scan Barcode En-route Parcels List</h5>
        </div>
        <div class="card-header">
            <h5>Total Scan En-route Parcel: <span style="font-size: 20px" id="total">0</span></h5>
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
        if(e.keyCode == 13){
            var parcelOrderReferenceId = $("#barcode").val();
            var fromCity = $("#from_city").val();
            var toCity = $("#to_city").val();
            var parcel_sag = $("#parcel_sag").val();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/enroute-add-barcode-parcel',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, order_parcel_reference_no: parcelOrderReferenceId, fromCity: fromCity, toCity: toCity, parcel_sag: parcel_sag},
                dataType: 'json',

                success: function(response){
                    //console.log(result)
                    var result = response.parcel;
                    if(response.status == 'fromCity'){
                        alert(response.message);
                    }

                    if(response.status == 'toCity'){
                        alert(response.message);
                    }

                    if(response.status == 'parcelSag'){
                        alert(response.message);
                    }

                    if(response.status == 'Invalid'){
                        alert(response.message);
                    }

                    if(response.status == 'Before'){
                        alert(response.message);
                    }

                    if(response.status == 'After'){
                        alert(response.message);
                    }

                    if(response.status == 'Scanned'){
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

function getSag() {

    var fromCity = $("#from_city").val();
    var toCity = $("#to_city").val();
    var seal_number = $("#seal_number").val();

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: '/get-sag-number',
        type: 'POST',
        /* send the csrf-token and the input to the controller */
        data: {_token: CSRF_TOKEN, fromCity: fromCity, toCity: toCity, seal_number: seal_number},
        dataType: 'json',
        success: function(response){
            var result = response.parcel;

            if(response.status == 'fromCity'){
                alert(response.message);
            }

            if(response.status == 'toCity'){
                alert(response.message);
            }

            if(response.status == 'Duplicate'){
                alert(response.message);
            }

            if(response.status == 'SealNumber'){
                alert(response.message);
            }

            if(response.status == 'Success'){
                $("#parcel_sag").val(result);
                var paragraph = document.getElementById("getSag");
                // Hide the paragraph by changing its display property
                paragraph.style.display = "none";
                var close_paragraph = document.getElementById("closeSag");
                // Hide the paragraph by changing its display property
                close_paragraph.style.display = "block";

                var message = 'Sag '+ result +' has been created successfully! Start Scanning Parcels.';
                alert(message);
            }
        }
    });
}

function closeSag()
{
    var parcel_sag = $("#parcel_sag").val();
    var fromCity = $("#from_city").val();

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: '/close-sag-number',
        type: 'POST',
        /* send the csrf-token and the input to the controller */
        data: {_token: CSRF_TOKEN, parcel_sag: parcel_sag, fromCity: fromCity},
        dataType: 'json',
        success: function(response){
            if(response.status == 'Invalid'){
                alert(response.message);
            }

            if(response.status == 'Success'){
                alert(response.message);
                window.location.href = "/generate-enroute-pdf/" + parcel_sag;
                setTimeout(function(){ location.reload() }, 5000);
            }
        }
    });
}
</SCRIPT>
@endsection