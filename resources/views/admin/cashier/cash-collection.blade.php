@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .anyClass {
      height:150px;
      overflow-y: scroll;
    }
</style>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body"> 

	@if ($errors->has('collect_amount'))
        <div class="alert alert-danger" role="alert">
          {{$errors->getBag('default')->first('collect_amount')}}
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Staff</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('cashCollection')}}" method="POST">
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
	                                <select id="riders" name="staff_id" class="form-control  @error('staff_id') is-invalid @enderror" value="{{ old('staff_id') }}">
	                                    <option selected="" disabled="" hidden="">Select Staff</option>
	                                    @foreach($staffList as $key=> $staff)
	                                        <option value="{{$staff->id}}" @if(isset($_POST['staff_id']) && $_POST['staff_id'] == $staff->id) {{ 'selected' }} @endif>{{$staff->name}} ( {{ $staff->userDetail->cnic }} )</option>
	                                    @endforeach
	                                </select>
	                                <span class="form-bar"></span>
	                                @error('staff_id')
	                                    <span class="invalid-feedback" role="alert">
	                                        <strong>{{ $message }}</strong>
	                                    </span>
	                                @enderror
	                            </div>
	                        </div>

	                        <div class="col-md-2">
	                            <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
	                            	Submit
	                        	</button>
	                        </div>
	                    </div>
	                </form>
                </div>
            </div>
        </div>
    </div>
    @hasanyrole('admin|cashier')
    <div class="row">
	    <div class="col-xl-12">
	        <div class="card proj-progress-card">
	            <div class="card-block">
                    <div class="row">
                        <div class="col-xl-3 col-md-3" style="display: none;">
                            <h6>Today Delivered Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{ $staffCashCollection['todayOrder'] }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3" style="display: none;">
                            <h6>Remaing Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{   $staffCashCollection['remaingOrder']  }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Total Cash</h6>
                            @if($unactionedParcels == 0)
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{ $staffCashCollection['totalCashByRider'] }}</h5>
                            @endif
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Deposit Cash</h6>
                            @if($unactionedParcels == 0)
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{  $staffCashCollection['totalCollectCashFromRider'] }}</h5>
                            @endif
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Remaing Cash</h6>
                            @if($unactionedParcels == 0)
                            <h5 class="m-b-30 f-w-700 text-c-green">Rs. {{  $staffCashCollection['remainingCash'] }}</h5>
                            @endif
                        </div>

                        <div class="col-xl-3 col-md-3">
                            <h6>Delivered Parcel Commission</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. {{  $deliveredParcelACommission }}</h5>
                        </div>
                        @hasanyrole('admin|cashier')
                        <div class="col-xl-3 col-md-3">
                            <h6>Cash shown in Rider App</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">Rs. {{ $remainingCash }}</h5>
                        </div>
                        @endhasanyrole
                    </div>
	            </div>
	        </div>
	    </div>
    </div>
    @endhasanyrole
    @hasanyrole('supervisor|cashier|lead_supervisor|hub_manager')
    <div class="row">
        <div class="col-xl-12">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-3 col-md-3">
                            <h6>Today Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-blue">{{ $total_parcels }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Today Delivered Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{ $confirm_delivered }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Total Cancel Parcels</h6>
                            <h5 class="m-b-30 f-w-700" style="color: #990ae9">{{ $confirm_cancel }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Total Reattempt Parcels</h6>
                            <h5 class="m-b-30 f-w-700" style="color: #0f5cdc">{{ $confirm_reattempt }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Total In-Progress Parcels</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">{{ $confirm_inprogress }}</h5>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <h6>Delivery Ratio</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{ number_format($wining_ratio) }}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endhasanyrole
    @if($staffId)
    <!-- Collect Cash From Rider -->
    @hasanyrole('cashier')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Collect Money From  {{ $staffData->name }}</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{route('cashCollect')}}">
                        @csrf
                        
                        <input type="hidden" id="collect_staff_id" name="collect_staff_id" value="{{$staffId}}">

                        <input type="hidden" id="total_order" name="remaining_cash" value="{{ $staffCashCollection['remainingCash'] }}">
                        
                        <input type="hidden" name="cash_date" id="date" class="form-control" required="required" value="<?php
                            if (isset($_POST['date'])) {
                                echo $_POST['date'];
                            }
                            ?>">
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="collect_amount" min="0" max="{{ $staffCashCollection['remainingCash'] }}" class="form-control  @error('collect_amount') is-invalid @enderror" required value="{{ old('collect_amount') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Total Cash</label>
                                    @error('collect_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="in_cash_collection" min="0" class="form-control  @error('in_cash_collection') is-invalid @enderror" required value="{{ old('in_cash_collection') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">In-Cash Form Collection</label>
                                    @error('in_cash_collection')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="ibft_collection" min="0" class="form-control  @error('ibft_collection') is-invalid @enderror" required value="{{ old('ibft_collection') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">IBFT Collection</label>
                                    @error('ibft_collection')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="ibft_comment" class="form-control  @error('ibft_comment') is-invalid @enderror" required value="{{ old('ibft_comment') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">IBFT Comment</label>
                                    @error('ibft_comment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="note" class="form-control  @error('note') is-invalid @enderror" value="{{ old('note') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Additional Note</label>
                                    @error('note')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">Collect</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endhasanyrole
    @role('supervisor|lead_supervisor|hub_manager')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Assigned Parcel Status (click on references to view proofs of delivery)</h5>
                </div>
                <div class="card-block table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover" id="example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Order Reference Number</th>
                                    <th>Amount</th>
                                    <th>Dispatch Time</th>
                                    <th>Last Action Time</th>
                                    <th>Total Duration<br>(in Minutes)</th>
                                    <th>Parcel Age</th>
                                    <th>Order Type</th>
                                    <th>Limits Attempt</th>
                                    <th>IVR Remarks</th>
                                    <th>Control Tower Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                @foreach($defaultOrders as $defaultOrder)
                                <?php 
                                    $call_input_value = '';
                                    if($defaultOrder->countOrderAssigned[0]->ivr_value == '479') //Re-attempt
                                    {
                                        if($defaultOrder->countOrderAssigned[0]->call_input == '0')
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                        elseif($defaultOrder->countOrderAssigned[0]->call_input == '1')
                                        {
                                            $call_input_value = 'Please Re-Attempt My Parcel';
                                        }
                                        elseif($defaultOrder->countOrderAssigned[0]->call_input == '2')
                                        {
                                            $call_input_value = 'Do-Not Re Attempt I want my parcel /Rider Add fake Remakrs';
                                        }
                                        else
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                    }
                                    elseif($defaultOrder->countOrderAssigned[0]->ivr_value == '480') //Cancel
                                    {
                                        if($defaultOrder->countOrderAssigned[0]->call_input == '0')
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                        elseif($defaultOrder->countOrderAssigned[0]->call_input == '1')
                                        {
                                            $call_input_value = 'Please Cancel my order /Confirm Cancel by Custumer';
                                        }
                                        elseif($defaultOrder->countOrderAssigned[0]->call_input == '2')
                                        {
                                            $call_input_value = 'Do-Not Cancel my order I want may order / Rider Add fake Remarks';
                                        }
                                        else
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                    }
                                    else
                                    {
                                        if($defaultOrder->countOrderAssigned[0]->call_input == '0')
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                        elseif($defaultOrder->countOrderAssigned[0]->call_input == '1')
                                        {
                                            $call_input_value = 'Cancel Input';
                                        }
                                        elseif($defaultOrder->countOrderAssigned[0]->call_input == '2')
                                        {
                                            $call_input_value = 'Re-Attempt Input';
                                        }
                                        else
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                    }

                                    $start = \Carbon\Carbon::parse($defaultOrder->countOrderAssigned[0]->created_at);
                                    $end = \Carbon\Carbon::parse($defaultOrder->countOrderAssigned[0]->updated_at);

                                    $diff = $start->diffInMinutes($end);
                                ?>
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>
                                    <td>
                                        <a target="_blank" href="{{route('parcelDetail', $defaultOrder->id)}}" style="@if($defaultOrder->countOrderAssigned[0]->trip_status_id === 1 || $defaultOrder->countOrderAssigned[0]->trip_status_id === 2 || $defaultOrder->countOrderAssigned[0]->trip_status_id === 3 ) color: red; font-weight: bold; @elseif ($defaultOrder->countOrderAssigned[0]->trip_status_id === 5 ) color: #990ae9; font-weight: bold; @elseif ($defaultOrder->countOrderAssigned[0]->trip_status_id === 6 || $defaultOrder->countOrderAssigned[0]->trip_status_id === 7) color: #0f5cdc; font-weight: bold; @else color: green; font-weight: bold; @endif">
                                            {{$defaultOrder->order_reference}} 
                                            <!-- ({{count($defaultOrder->countOrderAssigned)}}) -->
                                        </a>
                                    </td>
                                    <td>
                                        {{$defaultOrder->consignment_cod_price}}
                                    </td>
                                    <td>
                                        {{date('d-m-Y h:i a', strtoTime($defaultOrder->countOrderAssigned[0]->created_at))}}
                                    </td>
                                    <td>
                                        {{date('d-m-Y h:i a', strtoTime($defaultOrder->countOrderAssigned[0]->updated_at))}}
                                    </td>
                                    <td>
                                        {{$diff}}
                                    </td>
                                    @if(!empty($defaultOrder->scanOrder->created_at))
                                    <td>
                                        {{\Carbon\Carbon::parse($defaultOrder->scanOrder->created_at)->diffInDays(\Carbon\Carbon::now())}} Days
                                    </td>
                                    @else
                                    <td></td>
                                    @endif
                                    <td>{{$defaultOrder->consignment_order_type == 1 ? 'COD' : 'NON-COD'}}</td>
                                    <td>{{$defaultOrder->parcel_attempts}}/{{$defaultOrder->parcel_limit}}</td>
                                    <td>
                                        @if(!empty($defaultOrder->countOrderAssigned[0]->cdrid))
                                        Call Status: {{$defaultOrder->countOrderAssigned[0]->call_response}}<br>Call Input: {{$call_input_value}}
                                        @endif
                                    </td>
                                    <td>
                                        @if($defaultOrder->countOrderAssigned[0]->remarks_status == 1)
                                        Remarks by {{$defaultOrder->countOrderAssigned[0] ? $defaultOrder->countOrderAssigned[0]->remarksBy->name : ''}}<br>Remarks: {{$defaultOrder->countOrderAssigned[0]->remarks}}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                
                <span class="alert text-success" style="display:none;" id="success"></span>
                <span class="alert text-danger"  style="display:none;" id="danger"></span>

                <div class="card-header">
                    <h5>Force Status Change</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" id="changeStatus">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="order_reference" id="order_reference" class="form-control  @error('order_reference') is-invalid @enderror" value="{{ old('order_reference') }}" required autocomplete="off">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Order Reference</label>
                                    @error('order_reference')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group form-static-label form-default">
                                    <select id="status" name="status_id" class="form-control  @error('status_id') is-invalid @enderror" value="{{ old('status_id') }}">
                                        <option selected="" disabled="" hidden="">Select Status</option>
                                        @foreach($statuses as $key=> $status)
                                            <option value="{{$key}}" value="@if(old('status_id')) {{ old('status_id') }} @endif" @if(old('status_id')) {{ 'selected' }} @endif>{{$status}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('status_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">Change</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endrole


    <div class="row">

        <div class="col-xl-2">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <h6>Delivered Parcels</h6>
                    <div class="row">
                        <div class="col-md-2" style="max-width: 100%;">
                            @if($rackBalancing['deliveredParcels'] != 0)
                                @foreach($rackBalancing['deliveredParcels'] as $parcel)
                                    {{$parcel}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <h6>Reattempt by Rider</h6>
                    <div class="row">
                        <div class="col-md-2" style="max-width: 100%;">
                            @if($rackBalancing['reattemptParcels'] != 0)
                                @foreach($rackBalancing['reattemptParcels'] as $parcel)
                                    {{$parcel}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <h6>Cancelled by Rider</h6>
                    <div class="row">
                        <div class="col-md-2" style="max-width: 100%;">
                            @if($rackBalancing['riderCancelled'] != 0)
                                @foreach($rackBalancing['riderCancelled'] as $parcel)
                                    {{$parcel}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <h6>Reattempt by Supervisor</h6>
                    <div class="row">
                        <div class="col-md-2" style="max-width: 100%;">
                            @if($rackBalancing['requestReattemptParcels'] != 0)
                                @foreach($rackBalancing['requestReattemptParcels'] as $parcel)
                                    {{$parcel}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <h6>Cancelled by Supervisor</h6>
                    <div class="row">
                        <div class="col-md-2" style="max-width: 100%;">
                            @if($rackBalancing['supervisorCancelled'] != 0)
                                @foreach($rackBalancing['supervisorCancelled'] as $parcel)
                                    {{$parcel}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
	@endif
</div>
@endsection



@section('custom-js')	
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">
var table;
$(function () {
    var table = $('#example').DataTable({
        'lengthMenu': [250],
        'pageLength': 250
    });
});
</SCRIPT>
	<script type="text/javascript">

            $('#changeStatus').on('submit',function(e){
                e.preventDefault();

                $.ajax({
                    type:"POST",
                    url: "/force-status-change",
                    dataType : "json",
                    data:{
                        "_token": "{{ csrf_token() }}",
                        order_reference: $('#order_reference').val(),
                        status_id: $('#status').val(),
                    },
                    success:function(response)
                    {
                        //   alert(response.data);

                        if(response.status == 'success')
                        {
                            $('#success').show().text(response.message);
                        }else{
                            $('#danger').show().text(response.message);
                        }

                    },
                });
            });

    </script>

@endsection   