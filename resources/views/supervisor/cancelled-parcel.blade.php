@extends('layouts.app',$breadcrumbs)

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection
@section('content')

<div class="page-body display">
    <div class="card">
    <button id="selected_parcel_reattempt" class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Reattempt</button>
        <div class="card-header">
            <h5>Cancelled Parcels</h5>
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
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Rider</th>
                            <th>Undelivered Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @foreach($cancelledOrder as $parcel)
                        <tr id="tr-{{ $parcel->id }}">
                            <td align="center"><input type="checkbox" class="case" name="case"
                             data-order_id="{{ $parcel->id }}"
                             value="{{$parcel->id}}"/>
                            </td>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{$parcel->order_reference}}</td>
                            <td>{{$parcel->consignment_order_id}}</td>
                            <td>{{$parcel->consignment_cod_price}}</td>
                            <td>{{$parcel->consignment_pieces}}</td>
                            <td>{{$parcel->consignment_weight}}</td>
                            <td>{{$parcel->orderAssigned->rider->name}}</td>
                            <td>
                                {{ $parcel->orderAssigned->orderDecline->orderDeclineReason->name }}
                            </td>
                            <td>
                                <button data-id="{{$parcel->id}}" class="reattemptBtn btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Reattempt</button>
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
            var parcelId = $(this).attr('data-id');
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            if(parcelId){
                $.ajax({
                    url: '/canelled-parcel-reattempt',
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
                            alert(response.message);
                        }
                    }
                });
            }
        } else {
          //don't proceed
        }
        
    });

    $("#selected_parcel_reattempt").click(function () {
        var checkedArray = [];
        var checkedOrderIdArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
            //checkedOrderIdArray.push($(this).attr('data-order_id'));
        });

        if (checkedArray.length == 0) {
            alert('please select some paracel reattempt');
        } else {
            //var newWin = window.open();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                /* the route pointing to the post function */
                url: '/canelled-parcel-reattempt',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, paracel_id: checkedArray},
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