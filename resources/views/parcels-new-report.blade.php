@extends('layouts.app')

@section('content')

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Awaiting & Picked Parcels Report</h5>
                <form method="get">
                    <div class="row">
                        <div class="col-xl-5 col-md-6">
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="datetime-local" name="date" id="date" class="form-control" required="required" value="<?php
                                if (isset($_GET['date'])) {
                                    echo $_GET['date'];
                                }
                                ?>">
                            </div>
                        </div>
                        <div class="col-xl-5 col-md-6">
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="datetime-local" name="to" id="to" class="form-control" required="required" value="<?php
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
                <div class="text-right">
                    <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
                </div>
            </div>
            <div class="card-block table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vendor Name</th>
                                <th>New Entered Parcels</th>
                                <th>Picked Parcels</th>
                                <th>Assigned Sales Person</th>
                                <th>Assigned Sales Person Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $key => $vendor)
                            @php
                                $freshOrdersCount = count($vendor->freshOrders);
                                $totalScanOrders = 0;
                                foreach ($vendor->pickOrders as $pickOrder) {
                                    $totalScanOrders += $pickOrder->scanOrder ? 1 : 0;
                                }
                            @endphp
                            @if($freshOrdersCount > 0 || $totalScanOrders > 0)
                                <tr>
                                    <td></td>
                                    <td>{{ $vendor->vendor_name }}</td>
                                    <td>{{ $freshOrdersCount }}</td>
                                    <td>{{ $totalScanOrders }}</td>
                                    <td>{{ $vendor->pocPerson ? $vendor->pocPerson->name : ''}}</td>
                                    <td>{{ $vendor->pocPerson ? $vendor->pocPerson->userDetail->phone : ''}}</td>
                                </tr>
                            @endif
                            @endforeach
                            <tr>
                                <th></th>
                                <th style="font-weight: bold;font-size: 18px;">Total</th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="val"></span></th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="valTwo"></span></th>
                                <th style="font-weight: bold;font-size: 18px;"></th>
                                <th style="font-weight: bold;font-size: 18px;"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
            
    var table = document.getElementById("example"), sumVal = 0, sumValTwo = 0;
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumVal = sumVal + parseInt(table.rows[i].cells[2].innerHTML);
    }
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValTwo = sumValTwo + parseInt(table.rows[i].cells[3].innerHTML);
    }
    document.getElementById("val").innerHTML = sumVal;
    document.getElementById("valTwo").innerHTML = sumValTwo;
    // alert(sumVal);
            
</script>
<script>
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.children[0].textContent = index + 1;
    });
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