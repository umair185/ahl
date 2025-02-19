@extends('layouts.app')

@section('content')

<div class="page-body">  
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Add Cancelled Parcel</h5>
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
            <h5>Return to Vendor Parcels List</h5>
            <div class="text-right">
                <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
            </div>
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
                            <th>Weight</th>
                            <th>Order ID</th>
                            <th>Scan By</th>
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

            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/add-cancel-parcel',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, order_parcel_reference_no: parcelOrderReferenceId},
                dataType: 'json',

                success: function(response){
                    var result = response.parcel;
                    console.log(result);
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
                               + "<th scope='row'>"+ i +"</th>"
                               + "<td>" + result.order_reference +"</td>"
                               + "<td>" + result.vendor.vendor_name +"</td>"
                               + "<td>" + result.consignment_cod_price +"</td>"
                               + "<td>" + result.consignment_pieces + "</td>"
                               + "<td>" + result.vendor_weight.ahl_weight.weight + ' ('+ result.vendor_weight.city[0].name + ')' + "</td>"
                               + "<td>" + result.consignment_order_id +"</td>"
                               + "<td>" + response.user_name +"</td>"
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