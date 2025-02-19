@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body"> 
	@if (session('sucess'))
	    <div class="alert alert-success">
	        {{ session('sucess') }}
	    </div>
	@endif
	@if ($errors->has('pay_amount'))
        <div class="alert alert-danger" role="alert">
          {{$errors->getBag('default')->first('pay_amount')}}
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Vendor</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('newVendorFinancials')}}" method="POST">
	                   	@csrf
	                    <div class="row">
	                    	<div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date_from" id="date_from" class="form-control" required="required"   value="<?php
                                    if (isset($_POST['date_from'])) {
                                        echo $_POST['date_from'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date_to" id="date_to" class="form-control" required="required"  value="<?php
                                    if (isset($_POST['date_to'])) {
                                        echo $_POST['date_to'];
                                    }
                                    ?>">
                                </div>
                            </div>

	                        <div class="col-md-3">
	                            <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
	                            	Submit
	                        	</button>
	                        </div>
	                    </div>
	                </form>
                </div>

                @if(isset($vendors))
	                <div class="card-block table-border-style">
			            <div class="table-responsive">
			                <table class="table" id="example">
			                    <thead>
			                        <tr>
			                            <th>#</th>
			                            <th>Vendor Name</th>
			                            <th>Payable Amount</th>
			                            <th>Order's Commission</th>
			                            <th>Order's Amount</th>
			                            <th>Gst</th>
			                            <th>Amount Paid</th>
			                            <th>Reason</th>
			                            <th>Action</th>
			                        </tr>
			                    </thead>
			                    <tbody>
			                        @foreach($vendors as $key => $vendor)
			                        	@if($vendor->vendorOrders)
			                        		@php
			                        			//$allDeliveredOrdersAmount = $vendor->vendorOrders->all_delivered_orders_amount;
			                        		@endphp
			                        	@endif
			                        	@if(isset($ahlCommission[$vendor->id]))
			                            	@php
			                            		$loopIteration = $loop->iteration;
			                            		$vendorId = $vendor->id;
			                            		$ahlOrderCommission = $ahlCommission[$vendor->id]['ahl_orders_commission'];
			                            		$vendorOrdersAmount = $ahlCommission[$vendor->id]['vendor_delivered_orders_amount'];

			                            		$totalAhlCommissionDeduction = isset($vendor->vendorFinancials[0]) ? $vendor->vendorFinancials[0]->total_ahl_commission_deduction : 0;

				                            	$data = [
				                            		'vendor_id' => $vendorId,
				                            		'gst' => $vendor->gst,
				                            		'vendor_total_pay_amount' => isset($vendor->vendorFinancials[0]) ? $vendor->vendorFinancials[0]->total_pay_amount : 0,
				                            		'count_vendor_financials' => isset($vendor->vendorFinancials[0]) ? count($vendor->vendorFinancials[0]->toArray()) : 0
				                            	];
			                            		$payable = Helper::newPayableToVendor($data,$ahlCommission);
			                            	@endphp
			                            	@else
			                            	@php
			                            		$payable =  0;
			                            	@endphp
			                            @endif

			                        <tr id="tr-{{ $loop->iteration }}" class="{{ ($payable > 0) ? $rowColor[1] : $rowColor[0]  }}">
			                            <th scope="row">{{ $loop->iteration }}</th>
			                            <td>{{$vendor->vendor_name}}</td>
			                            <td id="payable-{{ $loop->iteration }}">

			                            	{{ $payable }}

			                            </td>
			                            <td>{{ $ahlOrderCommission }}</td>
			                            <td>{{ $vendorOrdersAmount }}</td>
			                            <td>{{ $vendor->gst }}%</td>
			                            <td><input type="text" id="amount-paid-{{ $loop->iteration }}" name="amount-paid-{{ $loop->iteration }}"> </td>
			                            <td><input type="text" id="reason-{{ $loop->iteration }}" name="reason-{{ $loop->iteration }}"> </td>
			                            <!-- <td><a href="{{route('payVendor', $vendor->id)}}"><i class="fa fa fa-save"></i></a></td> -->
			                            <td>
			                            	@if($payable > 0)
			                            	<button id="btn-{{ $loop->iteration }}" class="btn btn-primary waves-effect waves-light" onclick="payVendor({{$payable}},{{$vendorId}},{{$loopIteration}},{{$totalAhlCommissionDeduction}},{{$ahlOrderCommission}})">Pay</button>
			                            	@endif
			                            </td>
			                        </tr>
			                        @endforeach
			                    </tbody>
			                </table>
			            </div>
			        </div>
			    @endif
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
	    var table = $('#example').DataTable();
	});
	</SCRIPT>

	<script type="text/javascript">
		$(document).ready(function(){
			
		});

		function payVendor(payableAmount,vendorId,loopIteration,totalAhlCommissionDeduction,ahlOrderCommission)
		{
			var dateFrom = $("#date_from").val();
			var dateTo = $("#date_to").val();
			var amountPay = $("#amount-paid-" + loopIteration).val();
			var reason = $("#reason-" + loopIteration).val();
			
			console.log(ahlOrderCommission);
			if(payableAmount < amountPay){
				alert('Not grater than payable amount');
			}else{
				var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
				var requestData = {
					_token: CSRF_TOKEN,
					vendor_id: vendorId,
					payable_amount: payableAmount,
					pay_amount: amountPay,
					reason: reason,
					total_ahl_commission_deduction: totalAhlCommissionDeduction,
					date_from: dateFrom,
					date_to: dateTo,
					ahl_order_commission: ahlOrderCommission,
				}

	            $.ajax({
	                url: '/new-pay-vendor-financials',
	                type: 'POST',
	                data: requestData,
	                dataType: 'json',
	                success: function (response) {
	                    if(response.status == 1){
	                    	$('#tr-'+loopIteration).addClass('bg-success text-white').fadeIn(3000).css({opacity:0.5});
	                        $('#payable-'+loopIteration).text(response.remaining_amount);
	                        $('#btn-'+loopIteration).remove();
	                        $( "#amount-paid-"+loopIteration ).prop( "disabled", true );
	                        $( "#reason-"+loopIteration ).prop( "disabled", true );
	                        alert(response.message);
	                    }else{
	                        alert(response.message);
	                    }
	                }
	            });
			}
		}
	</script>
@endsection