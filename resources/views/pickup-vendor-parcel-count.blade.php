@extends('layouts.app')

@section('content')

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Vendor Pickup Parcels Record List</h5>
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
                                <th style="font-weight: bold;">Vendor Name</th>
                                <th style="font-weight: bold;">Assigned Sales Person</th>
                                <th style="font-weight: bold;">Assigned Sales Person Number</th>
                                <th style="font-weight: bold;">Number of Parcels</th>
                                <th style="font-weight: bold;">Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendor_details as $vendor)
                            <?php
                                $find_vendor = App\Models\Vendor::where('id', $vendor->vendor_id)->first();
                            ?>
                            <tr>
                                <td>{{$vendor->name}}</td>
                                <td>{{$find_vendor->pocPerson ? $find_vendor->pocPerson->name : ''}}</td>
                                <td>{{$find_vendor->pocPerson ? $find_vendor->pocPerson->userDetail->phone : ''}}</td>
                                <td>{{$vendor->total}}</td>
                                <td>
                                    <form method="POST" class="form-material" action="{{ route('pickupVendorParcelCountDownload') }}">
                                    @csrf
                                        <input type="hidden" name="vendor_id" class="form-control" value="{{$vendor->vendor_id}}">
                                        <input type="hidden" name="from" class="form-control" value="<?php
                                            if (isset($_GET['date'])) {
                                                echo $_GET['date'];
                                            }
                                            ?>">
                                        <input type="hidden" name="to" class="form-control" value="<?php
                                            if (isset($_GET['to'])) {
                                                echo $_GET['to'];
                                            }
                                            ?>">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Download</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <th style="font-weight: bold;"></th>
                                <th style="font-weight: bold;"></th>
                                <th style="font-weight: bold;">Total Parcels</th>
                                <th style="font-weight: bold;">{{$overall_count}}</th>
                                <th style="font-weight: bold;"></th>
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