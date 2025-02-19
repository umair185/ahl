@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body">
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h5>Vendor Financial Report</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('vendorFinancialReport')}}" method="POST">
	                   	@csrf
	                    <div class="row">
	                        <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?php
                                    if (isset($_POST['date_from'])) {
                                        echo $_POST['date_from'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php
                                    if (isset($_POST['date_to'])) {
                                        echo $_POST['date_to'];
                                    }
                                    ?>">
                                </div>
                            </div>
	                        <div class="col-md-4">
	                            <div class="form-group form-static-label form-default">
	                                <select id="riders" name="vendor_id" class="form-control  @error('vendor_id') is-invalid @enderror" value="{{ old('vendor_id') }}">
	                                    <option value="any">Any</option>
	                                    @foreach($vendors as $key=> $vendor)
	                                        <option value="{{$vendor->id}}" value="@if(old('vendor_id')) {{ old('vendor_id') }} @endif" @if(old('vendor_id')) {{ 'selected' }} @endif>{{$vendor->vendor_name}}</option>
	                                    @endforeach
	                                </select>
	                                <span class="form-bar"></span>
	                                @error('vendor_id')
	                                    <span class="invalid-feedback" role="alert">
	                                        <strong>{{ $message }}</strong>
	                                    </span>
	                                @enderror
	                            </div>
	                        </div>

	                        <div class="col-md-2">
	                            <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
	                            	Report
	                        	</button>
	                        </div>
	                    </div>
	                </form>
                </div>
            </div>
        </div>
    </div>
    @if(isset($VendorFinancialsReport))
    @hasanyrole('admin')
    @if(count($VendorFinancialsReport) > 0)
    <button class="btn btn-danger waves-effect waves-light" id='delete_br'>Delete Bulk Invoices</button>
    @endif
    @endhasanyrole
    <div class="card">
        <div class="card-header">
            <h5>Vendor Financial Report</h5>
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
	                        <th>Vendor Code</th>
	                        <th>Vendor</th>
	                        <th>Cashier</th>
	                        <th>Invoice #</th>
							<th>Total Invoice Amount</th>
							<th>Advance Amount</th>
	                        <th>Parcels Amount</th>
	                        <th>AHL Commission</th>
	                        <th>GST</th>
	                        <th>Fuel Adjustment Charges</th>
							<th>Flyer Amount</th>
							<th>Deduction</th>
							<th>Deduction Remarks</th>
							<th>Amount Paid</th>
	                        <th>Remarks</th>
	                        <th>Date From</th>
	                        <th>Date To</th>
	                        <th>Amount Pay Date</th>
	                        <th>Action</th>
	                        <th>Upload Payment Proof</th>
	                        <th>View Payment Proof</th>
	                        <th>Download Invoice</th>
	                        <th>Automatic Dispatch Sheet</th>
	                        @hasanyrole('admin|head_of_account')
	                        <th>Edit</th>
	                        <th>Delete</th>
	                        @endhasanyrole
	                    </tr>
	                </thead>
	                <tbody>
	                	
	                    @foreach($VendorFinancialsReport as $financial)
	                    <tr id="tr-{{$financial->id }}">
	                    	<td align="center"><input type="checkbox" class="case" name="case" value="{{$financial->id}}"/></td>
	                        <th scope="row">{{ $loop->iteration }}</th>
	                        <td>{{$financial->vendorName->vendor_number}}</td>
	                        <td>{{$financial->vendorName->vendor_name}}</td>
	                        <td>{{$financial->cashierName->name}}</td>
	                        <td>Invoice #: {{$financial->invoice_number}}</td>
	                        <td>{{$financial->amount + $financial->ahl_commission + $financial->ahl_gst + $financial->fuel_adjustment + $financial->flyer_amount}}</td>
	                        <td>{{$financial->advance_amount}}</td>
	                        <td>{{$financial->amount}}</td>
	                        <td>{{$financial->ahl_commission}}</td>
	                        <td>{{$financial->ahl_gst}}</td>
	                        <td>{{$financial->fuel_adjustment}}</td>
	                        <td>{{$financial->flyer_amount}}</td>
	                        <?php 
					            $deduction_remarks = '';
					            if($financial->deduction_remarks == 'normal')
					            {
					                $deduction_remarks = 'Normal Amount Deduction';
					            }
					            else
					            {
					                $deduction_remarks = 'Advance Amount Deduction';
					            }
					        ?>
	                        <td>{{$financial->deduction_amount}}</td>
	                        <td>{{$deduction_remarks}}</td>
	                        <td>{{$financial->amount + $financial->advance_amount - $financial->deduction_amount}}</td>
	                        <td>{{$financial->remarks}}</td>
	                        <td>{{date('d M Y', strtoTime($financial->date_from))}}</td>
	                        <td>{{date('d M Y', strtoTime($financial->date_to))}}</td>
	                        <td>{{date('d M Y H:i A', strtoTime($financial->created_at))}}</td>
	                        <td>
	                        	<a target="_blank" href="{{route('addVendorFinancialReport', $financial->id)}}"><i class="fa fa fa-upload"></i></a>
	                        	@if($financial->financial_report)
	                        	<a target="_blank" href="{{route('downloadVendorFinancialReport', $financial->id)}}"><i class="fa fa fa-download"></i></a>
	                        	@endif
	                        </td>
	                        <td>
	                        	@if(empty($financial->financial_payment))
	                        	<a target="_blank" href="{{route('addVendorFinancialPaymentProof', $financial->id)}}">
	                        		<button class="btn btn-primary">Upload</button>
	                        	</a>
	                        	@else
	                        	<button class="btn btn-primary-light" disabled>Upload</button>
	                        	@endif
	                        </td>
	                        <td>
			                    <div class="modal fade text-left" id="backdropsol{{$financial->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4" aria-hidden="true">
			                      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
			                        <div class="modal-content">
			                          <div class="modal-header">
			                            <h4 class="modal-title" id="myModalLabel4">Payment Proof</h4>
			                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			                              <i class="bx bx-x"></i>
			                            </button>
			                          </div>
			                          <div class="modal-body">
			                          	@if($financial->financial_payment)
			                          	<img src="{{asset('')}}{{$financial->financial_payment}}" alt="Payment Proof #{{$financial->id}} image" style="width: 400px; height: 500px">
			                          	@else
			                          	No Proof Uploaded Yet!
			                          	@endif
			                          </div>
			                          <div class="modal-footer">
			                            <button type="button" class="btn btn-light-secondary" data-dismiss="modal">
			                              <i class="bx bx-x d-block d-sm-none"></i>
			                              <span class="d-none d-sm-block">Close</span>
			                            </button>
			                          </div>
			                        </div>
			                      </div>
			                    </div>
			                    @if($financial->financial_payment)
			                    <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" data-target="#backdropsol{{$financial->id}}">View Proof</button>
			                    @else
			                    <button class="btn btn-primary-light" disabled>View Proof</button>
			                    @endif
	                        </td>
	                         <td><a target="_blank" href="{{route('indiviualTaxInvoice', $financial->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Download</button></a></td>
	                         <td><a target="_blank" href="{{route('automaticDispatchSheet', $financial->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>View Excel</button></a></td>
	                         @hasanyrole('admin|head_of_account')
	                         <td><a href="{{route('editVendorFinancials', $financial->id)}}"><button class="btn waves-effect waves-light btn-success"><i class="fa fa-edit"></i>Edit</button></a></td>
	                         <td><a href="{{route('VendorFinancialDelete', $financial->id)}}"><button class="btn waves-effect waves-light btn-danger"><i class="fa fa-trash"></i>Delete</button></a></td>
	                         @endhasanyrole
	                    </tr>
	                    @endforeach
	                    
	                </tbody>
	            </table>
	        </div>
	    </div>
	</div>
    @endif
</div>
@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">
$(function () {
    /* Data Table */
    var table = $('#example').DataTable({
	  "lengthMenu": [ 10, 25, 50, 75, 100, 500, 1000, 5000 ]
	});

	// add multiple select / deselect functionality
    $("#selectall").click(function () {
        var checkedArray = [];
        $('.case').prop('checked', this.checked);
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
    });



    // if all checkbox are selected, check the selectall checkbox and viceversa
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
    });

    $("#delete_br").click(function () {
        var proceed = confirm("Are you sure you want to proceed?");
        if(proceed)
        {
            var checkedArray = [];
            $("input:checkbox[name=case]:checked").each(function () {
                checkedArray.push($(this).val());
            });

            if (checkedArray.length == 0) {
                alert('please select some Invoices for Deletion');
            } else {
                //var newWin = window.open();
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    /* the route pointing to the post function */
                    url: '/bulk-invoice-delete',
                    type: 'POST',
                    /* send the csrf-token and the input to the controller */
                    data: {_token: CSRF_TOKEN, invoices: checkedArray},
                    dataType: 'json',
                    /* remind that 'data' is the response of the AjaxController */
                    success: function (response) {
                        if(response.status == 1){                            
                            alert('Invoices Deleted, let the page refresh to view results');
                            location.reload();
                        }
                    }
                });
            }
        }
        else
        {

        }
    });
});
</SCRIPT>
<script type="text/javascript">
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
</script>
@endsection