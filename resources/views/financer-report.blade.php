@extends('layouts.app')

@section('content')

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Vendor Payment Report</h5>
                <form method="get">
                    <div class="row">
                        <div class="col-xl-5 col-md-6">
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="date" name="date" id="date" class="form-control" required="required" value="<?php
                                if (isset($_GET['date'])) {
                                    echo $_GET['date'];
                                }
                                ?>">
                            </div>
                        </div>
                        <div class="col-xl-5 col-md-6">
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="date" name="to" id="to" class="form-control" required="required" value="<?php
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
                                <th>Tenure</th>
                                <th>Vendor</th>
                                <th>Total Delivered Parcel Amount</th>
                                <th>Commission</th>
                                <th>Fuel Adj</th>
                                <th>GST</th>
                                <th>Flyers</th>
                                <th>Current Payable</th>
                                <th>Payment Paid</th>
                                <th>Paid By</th>
                                <th>Date & Time</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $key => $payment)
                            @if($delivered[$key] > 0)
                            <?php
                                $vendor_financials = \App\Models\VendorFinancial::whereDate('date_from',$dateFrom)->whereDate('date_to',$dateTo)->where('vendor_id', $payment->id)->first();
                                if(!empty($vendor_financials))
                                {
                                    $payment_paid = $vendor_financials->amount;
                                    $payment_paid_date = date("d-m-Y", strtotime($vendor_financials->created_at));
                                    $paid_by = $vendor_financials->cashierName->name;
                                }
                                else
                                {
                                    $payment_paid = '';
                                    $payment_paid_date = '';
                                    $paid_by = '';
                                }
                            ?>
                            <tr>
                                <td></td>
                                <td>{{ date('d M', strtoTime($dateFrom)) }} - {{ date('d M', strtoTime($dateTo)) }}</td>
                                <td>{{$payment->vendor_name}}</td>
                                <td>{{$delivered[$key]}}</td>
                                <td>{{$commission[$key]}}</td>
                                <td>{{$fuel[$key]}}</td>
                                <td>{{$tax[$key]}}</td>
                                <td>{{$flyer[$key]}}</td>
                                <td>{{$delivered[$key] - ($commission[$key] + $fuel[$key] + $tax[$key] + $flyer[$key])}}</td>
                                <td>{{$payment_paid}}</td>
                                <td>{{$paid_by}}</td>
                                <td>{{$payment_paid_date}}</td>
                                <td>{{$balance[$key]}}</td>
                            </tr>
                            @endif
                            @endforeach
                            <tr>
                                <th></th>
                                <th></th>
                                <th style="font-weight: bold;font-size: 18px;">Total</th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="val"></span></th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="valTwo"></span></th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="valThree"></span></th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="valFour"></span></th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="valFive"></span></th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="valSix"></span></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script>
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.children[0].textContent = index + 1;
    });
</script>
<script>
    var table = document.getElementById("example"), sumVal = 0, sumValTwo = 0, sumValThree = 0, sumValFour = 0, sumValFive = 0, sumValSix = 0;
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumVal = sumVal + parseInt(table.rows[i].cells[3].innerHTML);
    }
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValTwo = sumValTwo + parseInt(table.rows[i].cells[4].innerHTML);
    }

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValThree = sumValThree + parseInt(table.rows[i].cells[5].innerHTML);
    }

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValFour = sumValFour + parseInt(table.rows[i].cells[6].innerHTML);
    }

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValFive = sumValFive + parseInt(table.rows[i].cells[7].innerHTML);
    }

    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValSix = sumValSix + parseInt(table.rows[i].cells[8].innerHTML);
    }
    document.getElementById("val").innerHTML = sumVal;
    document.getElementById("valTwo").innerHTML = sumValTwo;
    document.getElementById("valThree").innerHTML = sumValThree;
    document.getElementById("valFour").innerHTML = sumValFour;
    document.getElementById("valFive").innerHTML = sumValFive;
    document.getElementById("valSix").innerHTML = sumValSix;
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