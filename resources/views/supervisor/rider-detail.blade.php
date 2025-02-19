@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body"> 
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Riders Record</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('riderParcelsReport')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date" id="date" class="form-control" required="required" value="<?php
                                    if (isset($_POST['date'])) {
                                        echo $_POST['date'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="to" id="to" class="form-control" required="required" value="<?php
                                    if (isset($_POST['to'])) {
                                        echo $_POST['to'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="text-right">
                        <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Rider Parcels List</h5>
            </div>
            <div class="card-block table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Rider Name</th>
                                <th>Assigned Supervisor</th>
                                <th>Joining Date</th>
                                <th>Joining Days</th>
                                <th>Working Days</th>
                                <th>Total Parcels COD</th>
                                <th>Delivered Parcels COD</th>
                                <th>Cash Submitted</th>
                                <th>Cashier Name</th>
                                <th>Total Parcels</th>
                                <th>Delivered</th>
                                <th>Cancel</th>
                                <th>Re-Attempt</th>
                                <th>Un-Attempted</th>
                                <th>Delivered Ratio</th>
                                <th>Cancel Ratio</th>
                                <th>Re-Attempt Ratio</th>
                                <th>Load Sheet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staffList as $key => $rider)
                            @if(count($rider->totalOrders) > 0)
                            <tr>
                                <td></td>
                                <td>{{$rider->name}} ({{$rider->userDetail ? $rider->userDetail->phone : 'N/A'}})</td>
                                <td>{{ ($rider->supervisorPerson) ? $rider->supervisorPerson->name : ''}}</td>
                                @if(!empty($rider->userDetail->joining_date))
                                <td>{{date('d-M-Y', strtotime($rider->userDetail ? $rider->userDetail->joining_date : ''))}}</td>
                                <td>{{\Carbon\Carbon::parse($rider->userDetail->joining_date)->diffInDays(\Carbon\Carbon::now())}} Days</td>
                                @else
                                <td></td>
                                <td></td>
                                @endif
                                @if(!empty($_POST['date']))
                                <td>{{\App\Models\OrderAssigned::where('rider_id', $rider->id)->whereDate('created_at','>=', $_POST['date'])->whereDate('created_at','<=',$_POST['to'])->distinct()->count(DB::raw('DATE(created_at)'))}} Days</td>
                                @else
                                <td>1 Day</td>
                                @endif
                                <?php
                                    $total_sum = 0;
                                    $delivered_sum = 0;

                                    foreach ($rider->totalOrders as $key => $torder) {
                                        $total_sum = $torder->order->consignment_cod_price + $total_sum;
                                    }

                                    foreach ($rider->deliveredOrders as $key => $dorder) {
                                        $delivered_sum = $dorder->order->consignment_cod_price + $delivered_sum;
                                    }

                                    $amount = \App\Models\RiderCashCollection::select('amount','cashier_id')
                                    ->where('rider_id', $rider->id)
                                    ->whereDate('created_at', '>=', $requestDate)
                                    ->whereDate('created_at', '<=', $requestTo)
                                    ->first();

                                    $cashir_name = '';
                                    if(!empty($amount))
                                    {
                                        $cashir_name = $amount->cashier->name;
                                    }
                                    else
                                    {
                                        $cashir_name = 'No Cash Submitted';
                                    }
                                ?>
                                <td>{{$total_sum}}</td>
                                <td>{{$delivered_sum}}</td>
                                @if($amount)
                                    <td>{{ $amount->amount }}</td>
                                @else
                                    <td>0</td>
                                @endif
                                <td>{{$cashir_name}}</td>
                                <td>{{count($rider->totalOrders)}}</td>
                                <td>{{count($rider->deliveredOrders)}}</td>
                                <td>{{count($rider->cancelOrders)}}</td>
                                <td>{{count($rider->returnOrders)}}</td>
                                <td>{{count($rider->forceFulOrders)}}</td>

                                <?php
                                    $delivered_ratio = 0;
                                    $cancel_ratio = 0;
                                    $return_ratio = 0;
                                    $total_ratio = count($rider->totalOrders);

                                    if($total_ratio > 0)
                                    {
                                        $delivered = (count($rider->deliveredOrders) / $total_ratio ) * 100;
                                        $delivered_ratio = number_format($delivered);

                                        $cancel = (count($rider->cancelOrders) / $total_ratio ) * 100;
                                        $cancel_ratio = number_format($cancel);

                                        $return = (count($rider->returnOrders) / $total_ratio ) * 100;
                                        $return_ratio = number_format($return);
                                    }
                                    else
                                    {
                                        $delivered_ratio = 0;
                                        $cancel_ratio = 0;
                                        $return_ratio = 0;
                                    }
                                ?>
                                <td>{{$delivered_ratio}}<span style="font-size: 9px">%</span></td>
                                <td>{{$cancel_ratio}}<span style="font-size: 9px">%</span></td>
                                <td>{{$return_ratio}}<span style="font-size: 9px">%</span></td>
                                <td>
                                    <a href="{{route('riderLoadSheet')}}?from=<?php echo $requestDate; ?>&to=<?php echo $requestTo ?>&rider_id=<?php echo $rider->id ?>">
                                        <button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>View Scan Parcels</button>
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                        <tfooter>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Rs. <span id="valFive"></span></td>
                                <td>Rs. <span id="valSix"></span></td>
                                <td>Rs. <span id="valSixNew"></span></td>
                                <td></td>
                                <td><span id="val"></span></td>
                                <td><span id="valTwo"></span></td>
                                <td><span id="valThree"></span></td>
                                <td><span id="valFour"></span></td>
                                <td><span id="valFourNew"></span></td>
                                <td><span id="valSeven"></span>%</td>
                                <td><span id="valEight"></span>%</td>
                                <td><span id="valNine"></span>%</td>
                                <td></td>
                            </tr>
                        </tfooter>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">

$(function () {
    /* Data Table */
    var table = $('#example').DataTable({
        'lengthMenu': [100, 250, 500, 1000, 5000, 10000],
        'pageLength': 100
    });
});
</SCRIPT>
<script>
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.children[0].textContent = index + 1;
    });
</script>
<script>
            
    var table = document.getElementById("example"), sumVal = 0, sumValTwo = 0, sumValThree = 0, sumValFour = 0, sumValFourNew = 0, sumValFive = 0, sumValSix = 0, sumValSixNew = 0, deliveredRatio = 0, cancelRatio = 0, reattemptRatio = 0;
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumVal = sumVal + parseInt(table.rows[i].cells[9].innerHTML);
    }
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValTwo = sumValTwo + parseInt(table.rows[i].cells[10].innerHTML);
    }

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValThree = sumValThree + parseInt(table.rows[i].cells[11].innerHTML);
    }

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValFour = sumValFour + parseInt(table.rows[i].cells[12].innerHTML);
    }

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValFourNew = sumValFourNew + parseInt(table.rows[i].cells[13].innerHTML);
    }

    //dispatch amount

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValFive = sumValFive + parseInt(table.rows[i].cells[5].innerHTML);
    }

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValSix = sumValSix + parseInt(table.rows[i].cells[6].innerHTML);
    }

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValSixNew = sumValSixNew + parseInt(table.rows[i].cells[7].innerHTML);
    }

    //ratio calculation

    var dRatio = (sumValTwo / sumVal) * 100;
    deliveredRatio = parseFloat(dRatio).toFixed(2);

    var CRatio = (sumValThree / sumVal) * 100;
    cancelRatio = parseFloat(CRatio).toFixed(2);

    var rARatio = (sumValFour / sumVal) * 100;
    reattemptRatio = parseFloat(rARatio).toFixed(2);

    document.getElementById("val").innerHTML = sumVal;
    document.getElementById("valTwo").innerHTML = sumValTwo;
    document.getElementById("valThree").innerHTML = sumValThree;
    document.getElementById("valFour").innerHTML = sumValFour;
    document.getElementById("valFourNew").innerHTML = sumValFourNew;
    document.getElementById("valFive").innerHTML = sumValFive;
    document.getElementById("valSix").innerHTML = sumValSix;
    document.getElementById("valSixNew").innerHTML = sumValSixNew;
    document.getElementById("valSeven").innerHTML = deliveredRatio;
    document.getElementById("valEight").innerHTML = cancelRatio;
    document.getElementById("valNine").innerHTML = reattemptRatio;
    // alert(sumVal);
            
</script>

<SCRIPT language="javascript">
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