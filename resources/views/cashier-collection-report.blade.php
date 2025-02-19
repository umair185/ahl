@extends('layouts.app')

@section('content')

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Cashier Collection Report</h5>
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
                                <th>Joining Date</th>
                                <th>Thumb ID</th>
                                <th>Rider Name</th>
                                <th>Supervisor Name</th>
                                <th>Fuel</th>
                                <th>Route Area</th>
                                <th>Total Dispatch Parcel Amount</th>
                                <th>Total Delivered Parcel Amount</th>
                                <th>Cash Collected</th>
                                <th>IBFT Collection</th>
                                <th>Total Cash Received</th>
                                <th>Closed by Cashier</th>
                                <th>Closing Time</th>
                                <th>Total Parcel</th>
                                <th>Delivered</th>
                                <th>Cancel</th>
                                <th>Re-attempt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($collection_report as $collection)
                            <tr>
                                @if(!empty($collection->rider->userDetail->joining_date))
                                <td>{{date('d-M-Y', strtotime($collection->rider->userDetail ? $collection->rider->userDetail->joining_date : ''))}}</td>
                                @else
                                <td></td>
                                @endif
                                <td>{{$collection->rider->user_id}}</td>
                                <td>{{$collection->rider->name}}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <?php
                                    $total_sum = 0;
                                    $delivered_sum = 0;
                                    $total_parcel = 0;
                                    $delivered_parcel = 0;
                                    $cancel_parcel = 0;
                                    $reattempt_parcel = 0;

                                    $orders_assigned = \App\Models\OrderAssigned::where('rider_id', $collection->rider_id)
                                    ->whereDate('created_at', $collection->created_at)->groupBy('order_id')
                                    ->get();

                                    foreach ($orders_assigned as $key => $torder) {
                                        $total_sum = $torder->order->consignment_cod_price + $total_sum;
                                        $total_parcel = 1 + $total_parcel;
                                    }

                                    foreach ($orders_assigned as $key => $dorder) {
                                        if($dorder->trip_status_id == 4 && $dorder->status == 1 && $dorder->force_status == 1)
                                        {
                                            $delivered_sum = $dorder->order->consignment_cod_price + $delivered_sum;
                                            $delivered_parcel = 1 + $delivered_parcel;
                                        }
                                        if(($dorder->trip_status_id == 1 || $dorder->trip_status_id == 2 || $dorder->trip_status_id == 3|| $dorder->trip_status_id == 4|| $dorder->trip_status_id == 5) && $dorder->status == 0 && $dorder->force_status == 1)
                                        {
                                            $cancel_parcel = 1 + $cancel_parcel;
                                        }
                                        if($dorder->trip_status_id == 6 && $dorder->status == 0 && $dorder->force_status == 1)
                                        {
                                            $reattempt_parcel = 1 + $reattempt_parcel;
                                        }
                                    }
                                ?>
                                <td>{{$total_sum}}</td>
                                <td>{{$delivered_sum}}</td>
                                <td>{{$collection->in_cash_collection}}</td>
                                <td>{{$collection->ibft_collection}}</td>
                                <td>{{$collection->amount}}</td>
                                <td>{{$collection->cashier->name}}</td>
                                <td>{{ date('d M Y', strtoTime($collection->created_at)) }}</td>
                                <td>{{$total_parcel}}</td>
                                <td>{{$delivered_parcel}}</td>
                                <td>{{$cancel_parcel}}</td>
                                <td>{{$reattempt_parcel}}</td>
                            </tr>
                            @endforeach
                            <!-- <tr>
                                <td></td>
                                <td style="font-weight: bold;">Total</td>
                                <td style="font-weight: bold;">{{$collection_amount}}</td>
                                <td></td>
                            </tr> -->
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