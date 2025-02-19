@extends('layouts.app')

@php 
use App\Helpers\RoleHelper;
$roleName  = '';
@endphp


@section('content')

<div class="col-xl-12">
    <div class="card proj-progress-card">
        <div class="card-block">
            <form method="get">
                <div class="row">
                    <div class="col-xl-5 col-md-6">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="date" id="date" class="form-control" max="{{$today_date}}" required="required" value="<?php
                            if (isset($_GET['date'])) {
                                echo $_GET['date'];
                            }
                            ?>" onChange="restaurantTime()">
                        </div>
                    </div>
                    <div class="col-xl-5 col-md-6">
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="to" id="to" class="form-control" max="{{$today_date}}" required="required" value="<?php
                            if (isset($_GET['to'])) {
                                echo $_GET['to'];
                            }
                            ?>">
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <div class="form-group">
                            <br>
                            <button type="submit" class="btn btn-primary mt-1">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="col-xl-12">
    <div class="card proj-progress-card">
        <div class="card-block">
            <div class="row">
                <div class="col-xl-3 col-md-4">
                    <h6>Overall Parcels</h6>
                    <h5 class="m-b-30 f-w-700 text-c-green">{{$scanOrders['overall_count']}}</h5>
                </div>
                <div class="col-xl-3 col-md-4">
                    <h6>Total Scan Parcels</h6>
                    <h5 class="m-b-30 f-w-700 text-c-green">{{$scanOrders['total_scan']}}</h5>
                </div>
                <div class="col-xl-3 col-md-4">
                    <h6>Dispatch Parcels</h6>
                    <h5 class="m-b-30 f-w-700 text-c-green">{{$scanOrders['total_dispatch']}}</h5>
                </div>
                <div class="col-xl-3 col-md-4">
                    <h6>Remaining Parcels</h6>
                    <h5 class="m-b-30 f-w-700 text-c-red">{{$scanOrders['overall_count']}}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-xl-12 col-md-12">
    <div class="row">
        <div class="col-md-4">
            <div class="card total-card" style="background-color: #33C1FF">
                <div class="card-block">
                    <div class="text-left">
                        <h4>{{$scanOrders['at_ahl']}}</h4>
                        <p class="m-0">AT AHL</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card total-card" style="background-color: #A0E064">
                <div class="card-block">
                    <div class="text-left">
                        <h4>{{$scanOrders['reattempt']}}</h4>
                        <p class="m-0">Re-Attempt</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card total-card" style="background-color: #E25041">
                <div class="card-block">
                    <div class="text-left">
                        <h4>{{$scanOrders['cancel']}}</h4>
                        <p class="m-0">Cancel</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>
<div class="col-xl-12 col-md-12">
    <div class="row">
        <div class="col-md-6">
            <div class="card total-card" style="background-color: #E25041; cursor: pointer;" onclick="reattemptParcel()">
                <div class="card-block">
                    <div class="text-left">
                        <h4 id="at_ahl_parcel">{{$scanOrders['at_ahl'] + $scanOrders['reattempt']}}</h4>
                        <p class="m-0">AT AHL & Re-attempt</p>
                        <p class="m-0" style="font-size: 9px;">Click to view</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card total-card" style="background-color: #905AC4; cursor: pointer;" onclick="cancelParcel()">
                <div class="card-block">
                    <div class="text-left">
                        <h4 id="cancel_parcel">{{$scanOrders['cancel']}}</h4>
                        <p class="m-0">Cancel</p>
                        <p class="m-0" style="font-size: 9px;">Click to view</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>
<div class="col-xl-12 col-md-12">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 id="show-data-content">Parcels List</h5>
                </div>
                <div class="card-block table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover" id="show-data">
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Scan Parcels List</h5>
                </div>
                <div class="card-block table-border-style">
                    <input type="text" name="cancel_barcode" id="cancel_barcode" class="form-control" style="display: none;" placeholder="#AHLCancel">
                    <input type="text" name="barcode" id="barcode" class="form-control" style="display: none;" placeholder="#Reattempt">
                    <div class="table-responsive">
                        <table class="table table-hover" id="scan-data">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="remarks_section" style="display: none;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Add Closing Remarks</h5>
                </div>
                <div class="card-block table-border-style">
                    <div class="row">
                        <div class="col-md-10">
                            <label>Enter Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control" placeholder="Enter Closing Remarks">
                        </div>
                        <div class="col-md-2">
                            <br>
                            <button type="button" class="btn btn-primary mt-1" onclick="saveRack()">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<script type="text/javascript">
  function restaurantTime() {
    var time = $("#date").val();

    $("#to").attr({
      "min" : time,
    });
  }
</script>
<script type="text/javascript">
    $(function () {

        /* Barcode */
        $('#barcode').on('keyup',function(e){
            if(e.keyCode == 13){
                var order_parcel_reference_no = $("#barcode").val();
                var date = $("#date").val();
                var to = $("#to").val();

                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '/save-reattempt-rack-parcel',
                    type: 'POST',
                    data: {_token: CSRF_TOKEN, order_parcel_reference_no: order_parcel_reference_no, date: date, to: to},
                    dataType: 'json',

                    success: function(response){
                        console.log(response.html_data)
                        var result = response.parcel;
                        if(response.status == 'Invalid'){
                            alert(response.message);
                        }

                        if(response.status == 'Scanned'){
                            alert(response.message);
                        }

                        if(response.status == 'Already'){
                            alert(response.message);
                        }

                        if(response.status == 'Success'){
                            $("#show-data").html(response.html_data);
                            $("#scan-data").html(response.scan_html);
                            $("#show-data-content").html('Re-attempt Parcels List');

                            var element = document.getElementById('cancel_barcode');
                            element.style.display = 'none';
                            var elementTwo = document.getElementById('barcode');
                            elementTwo.style.display = 'block';
                            var elementThree = document.getElementById('remarks_section');
                            elementThree.style.display = 'block';
                        }
                        
                        $("#barcode").val("");
                    }
                });
            }
        });

        //1st block
        $('#cancel_barcode').on('keyup',function(e){
            if(e.keyCode == 13){
                var order_parcel_reference_no = $("#cancel_barcode").val();
                var date = $("#date").val();
                var to = $("#to").val();

                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '/save-cancel-rack-parcel',
                    type: 'POST',
                    data: {_token: CSRF_TOKEN, order_parcel_reference_no: order_parcel_reference_no, date: date, to: to},
                    dataType: 'json',

                    success: function(response){
                        console.log(response.html_data)
                        var result = response.parcel;
                        if(response.status == 'Invalid'){
                            alert(response.message);
                        }

                        if(response.status == 'Scanned'){
                            alert(response.message);
                        }

                        if(response.status == 'Already'){
                            alert(response.message);
                        }

                        if(response.status == 'Success'){
                            $("#show-data").html(response.html_data);
                            $("#scan-data").html(response.scan_html);
                            $("#show-data-content").html('At AHL & Cancel Parcels List');

                            var element = document.getElementById('barcode');
                            element.style.display = 'none';
                            var elementTwo = document.getElementById('cancel_barcode');
                            elementTwo.style.display = 'block';
                            var elementThree = document.getElementById('remarks_section');
                            elementThree.style.display = 'block';
                        }
                        
                        $("#cancel_barcode").val("");
                    }
                });
            }
        });
    });

    function cancelParcel()
    {
        var date = $("#date").val();
        var to = $("#to").val();

        $.ajax({
            type:"POST",
            url: "/rack-cancel-parcels",
            dataType : "json",
            data:{
                "_token": "{{ csrf_token() }}",
                "date": date,
                "to": to,
            },
            success:function(response)
            {
                if(response.status == 'dateFrom'){
                    alert(response.message);
                }

                if(response.status == 'dateTo'){
                    alert(response.message);
                }

                if(response.status == 'success'){
                    $("#show-data").html(response.html_data);
                    $("#scan-data").html(response.scan_html_data);
                    $("#show-data-content").html('Cancel Parcels List');
                    $("#remarks").val(response.fetch_remarks);

                    var element = document.getElementById('barcode');
                    element.style.display = 'none';
                    var elementTwo = document.getElementById('cancel_barcode');
                    elementTwo.style.display = 'block';
                    var elementThree = document.getElementById('remarks_section');
                    elementThree.style.display = 'block';
                }
            },
        });
    }
    function reattemptParcel()
    {
        var date = $("#date").val();
        var to = $("#to").val();

        $.ajax({
            type:"POST",
            url: "/rack-reattempt-parcels",
            dataType : "json",
            data:{
                "_token": "{{ csrf_token() }}",
                "date": date,
                "to": to,
            },
            success:function(response)
            {
                if(response.status == 'dateFrom'){
                    alert(response.message);
                }

                if(response.status == 'dateTo'){
                    alert(response.message);
                }

                if(response.status == 'success'){
                    $("#show-data").html(response.html_data);
                    $("#scan-data").html(response.scan_html_data);
                    $("#show-data-content").html('At AHL and Re-attempt Parcels List');
                    $("#remarks").val(response.fetch_remarks);

                    var element = document.getElementById('cancel_barcode');
                    element.style.display = 'none';
                    var elementTwo = document.getElementById('barcode');
                    elementTwo.style.display = 'block';
                    var elementThree = document.getElementById('remarks_section');
                    elementThree.style.display = 'block';
                }
            },
        });
    }

    //remarks
    function saveRack()
    {
        var date = $("#date").val();
        var to = $("#to").val();
        var remarks = $("#remarks").val();
        var at_ahl_parcel = $("#at_ahl_parcel").html();
        var cancel_parcel = $("#cancel_parcel").html();
        var show_data_content = $("#show-data-content").html();

        var table = document.getElementById("scan-data"), sumVal = 0;
            
        for(var i = 1; i < table.rows.length; i++)
        {
            sumVal = sumVal + parseInt(table.rows[i].cells[1].innerHTML);
        }

        $.ajax({
            type:"POST",
            url: "/rack-balance-remarks",
            dataType : "json",
            data:{
                "_token": "{{ csrf_token() }}",
                "date": date,
                "to": to,
                "remarks": remarks,
                "at_ahl_parcel": at_ahl_parcel,
                "cancel_parcel": cancel_parcel,
                "show_data_content": show_data_content,
                "sumVal": sumVal,
            },
            success:function(response)
            {
                if(response.status == 'dateFrom'){
                    alert(response.message);
                }

                if(response.status == 'dateTo'){
                    alert(response.message);
                }

                if(response.status == 'remarks'){
                    alert(response.message);
                }

                if(response.status == 'Already'){
                    alert(response.message);
                }

                if(response.status == 'success'){
                    window.location.href = "{{ route('scanHistory')}}";
                }
            },
        });
    }
</script>
@endsection