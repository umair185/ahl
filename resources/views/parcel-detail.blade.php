@extends('layouts.app')

@section('content')
<!-- Page-body start -->
<div class="page-body">
    <!-- Basic table card start -->
    <div class="card">
        <div class="card-header">
            <h5>Order Detail </h5>
            <span>Your order parcel detail's</span>
            <div class="card-header-right">

            @hasrole('supervisor')
                <!-- Upload parcel photo -->
                <form action="{{ route('uploadPhoto',$orderDetail->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                    <input type="file" name="photo" id="fileupload" onchange="form.submit()" style="display:none" accept="image/*" />  
                    <div id="OpenFileUpload" class="card text-center order-visitor-card">
                        <div class="card-block">
                            <h6 class="m-b-0">Upload proof</h6>
                        </div>
                    </div>
                </form>
            @endrole    

                <a href="{{route('editParcel',$orderDetail->id)}}"><button class="cardHeaderBtn btn waves-effect waves-light btn-primary">Edit Parcel</button></a>
            </div>
        </div>
        <div class="card-header">
            <h5 style="font-weight: bold;font-size: 25px;color: darkgreen;">
                {{ $orderDetail->orderStatus->name }}
                @hasanyrole('admin|middle_man|supervisor|picker|rider|cashier|first_man|financer|sales|csr|bd|bdm|hr|hub_manager|lead_supervisor|data_analyst|head_of_account')
                @if(($orderDetail->order_status == 2) || ($orderDetail->order_status == 3) || ($orderDetail->order_status == 4) || ($orderDetail->order_status == 5) || ($orderDetail->order_status == 7) || ($orderDetail->order_status == 8) || ($orderDetail->order_status == 9) || ($orderDetail->order_status == 11) || ($orderDetail->order_status == 12) || ($orderDetail->order_status == 13) || ($orderDetail->order_status == 14) || ($orderDetail->order_status == 15) || ($orderDetail->order_status == 16) || ($orderDetail->order_status == 17) || ($orderDetail->order_status == 18) || ($orderDetail->order_status == 19))
                    @if(!empty($scanOrder))
                        ({{\Carbon\Carbon::parse($scanOrder->created_at)->diffInDays(\Carbon\Carbon::now())}} Days)
                    @endif
                @endif
                @endhasanyrole
            </h5>
            @hasanyrole('admin|first_man|supervisor')
            @if($orderDetail->order_status == 6)
            <!--<div class="card-header-right">-->
            <!--    <a href="{{route('replaceParcel',$orderDetail->id)}}"><button class="cardHeaderBtn btn waves-effect waves-light btn-primary">Replace</button></a>-->
            <!--</div>-->
            @endif
            @endhasanyrole
        </div>
        <div class="card-block list-tag">
            <div class="row">
                <div class="col-sm-12 col-xl-4">
                    <h4 class="sub-title">Order ( {{ $orderDetail->order_reference }} ) Detail</h4>
                    Vendor Consignee:
                    <hr>
                    <ul>
                        <li>Name: {{ $orderDetail->consignee_first_name . ' ' . $orderDetail->consignee_last_name  }} </li>
                        <li>Email: {{ $orderDetail->consignee_email }} </li>
                        <li>Address: {{ $orderDetail->consignee_address }} </li>
                        <li>Phone: {{ $orderDetail->consignee_phone }} </li>
                        <li>Country: Pakistan </li>
                        <li>State: Punjab </li>
                        <li>City: Lahore </li>
                    </ul>
                    <hr>
                    Parcel Details
                    <hr>
                    <ul>
                        <li>Parcel Vendor: {{ $orderDetail->vendor->vendor_name }}</li>
                        <li>Parcel Status: {{ $orderDetail->orderStatus->name }}</li>
                        <li>Parcel Type: {{ $orderDetail->orderType->name }}</li>
                        <li>Parcel Reference No: {{ $orderDetail->consignment_order_id }} </li>
                        <li>Parcel Price: {{ $orderDetail->consignment_cod_price }}</li>
                        <li>Parcel Weight: {{$orderDetail->vendorWeight->ahlWeight->weight . ' (' .           $orderDetail->vendorWeight->city->first()->name . ')'}}</li>
                        <li>Parcel Packaging: {{ $orderDetail->orderPacking->name }}</li>
                        <li>Parcel Pieces: {{ $orderDetail->consignment_pieces }}</li>
                        <li>Parcel Attempts Set by Admin: {{ $orderDetail->parcel_limit }}</li>
                        <li>Parcel Actual Attempts: {{ $orderDetail->parcel_attempts }}</li>
                        <li>Parcel Description: {{ $orderDetail->consignment_description }}</li>
                        <li>Parcel Pickup Location: {{ $orderDetail->pickupLocation->address }}</li>
                        <li>Origin City: Lahore</li>
                        <li>Parcel Update Record
                            <ol>
                                <li style="font-weight: bold;">Last Updated By: {{ $orderDetail->orderUpdateStaff ? $orderDetail->orderUpdateStaff->name : '' }}</li>
                                @if(!empty($orderDetail->previous_value))
                                <li style="font-weight: bold;">Last Amount: {{ $orderDetail->previous_value }}</li>
                                @endif
                            </ol>
                        </li>
                        <li>Parcel Detail
                            <ol>
                                <li>Created At: {{ date('d M Y', strtoTime($orderDetail->created_at))  }} </li>
                                <li>Picker Scan At: {{ ($scanOrder) ? date('d M Y', strtoTime($scanOrder->created_at)) : 'N\A' }}</li>
                            </ol>
                        </li>
                    </ul>
                    <br>
                    <br>
                    @if(!empty($orderDetail->photo))
                        <img src="{{asset($orderDetail->photo)}}" alt="Parcel proof" style="width:200px; height:150px;">
                    @else
                    <span>NO IMAGE FOUND</span>
                    @endif

                    <br>
                    @if(!empty($orderDetail->photo_upload_by))
                        <label for="">Uploaded By</label>
                       <h4>{{$orderDetail->orderStaff->name}}</h4>
                    @endif


                   
                   
                </div>
                <div class="col-sm-12 col-xl-4">
                    <h4 class="sub-title">Parcel Delivery Detail</h4>

                    <li><b>Order Delivered </b>
                        @if(isset($delivery))
                        @if($delivery->orderDelivery)
                        <ol>
                            <li>
                                Amount: {{ $delivery->orderDelivery ? $delivery->orderDelivery->amount : '' }}
                            </li>
                            <li>
                                Consignee Relation: 
                                @if($delivery->orderDelivery->consignee_relation_id == 14)
                                {{ $delivery->orderDelivery ? $delivery->orderDelivery->other_relation : '' }}
                                @else
                                {{ $delivery->orderDelivery ? $delivery->orderDelivery->consigneeRelation->name : '' }}
                                @endif
                            </li>
                            <li>
                                Reciver Name: {{ $delivery->orderDelivery ? $delivery->orderDelivery->receiver_name : '' }}
                            </li>
                            <li>
                                CNIC: {{ $delivery->orderDelivery ? $delivery->orderDelivery->cnic : '' }}
                            </li>
                            <li>
                                Comment: {{ $delivery->orderDelivery ? $delivery->orderDelivery->comment : '' }}
                            </li>
                            <li>
                                Rider Name: {{ $delivery->rider->name }}
                            </li>
                            <li>
                                Signature:
                                <br>
                                <img width="200px" height="150px" src="{{ asset($delivery->orderDelivery->signature)}}">
                            </li>
                            <li>
                                Location Picture:
                                <br>
                                <img width="200px" height="150px" src="{{ asset($delivery->orderDelivery->location_picture)}}">
                            </li>
                        </ol>
                        @endif
                            @if(!empty($delivery->orderDecline->image))
                            <li>
                                Additional Note: {{ $delivery->orderDecline ? $delivery->orderDecline->additional_note : '' }} @if(!empty($delivery->orderDecline))<a href="{{ route('editAdditionalNote',$delivery->orderDecline->id) }}"><i class="fa fa-edit"></i></a>@endif
                            </li>
                            <li>
                                Decline Status: {{ $delivery->orderDecline ? $delivery->orderDecline->orderDeclineStatus->name : '' }}
                            </li>
                            <li>
                                Decline Reasone: {{ $delivery->orderDecline ? $delivery->orderDecline->orderDeclineReason->name : '' }}
                            </li>
                            <li>
                                Rider Name: {{ $delivery->rider->name }}
                            </li>
                            <li>
                                Location Picture:
                                <br>
                                <img width="200px" height="150px" src="{{ asset($delivery->orderDecline ? $delivery->orderDecline->image : '')}}">
                            </li>
                            @endif
                        @endif
                    </li>
                    <hr>
                    <li><b>Order Decline</b>
                        @if(isset($decline))
                        <ol>
                            @foreach($decline as $orderAssigned)
                            @if(!empty($orderAssigned->orderDecline->image))
                            <li>
                                Additional Note: {{ $orderAssigned->orderDecline ? $orderAssigned->orderDecline->additional_note : '' }} @if(!empty($orderAssigned->orderDecline))<a href="{{ route('editAdditionalNote',$orderAssigned->orderDecline->id) }}"><i class="fa fa-edit"></i></a>@endif
                            </li>
                            <li>
                                Decline Status: {{ $orderAssigned->orderDecline ? $orderAssigned->orderDecline->orderDeclineStatus->name : '' }}
                            </li>
                            <li>
                                Decline Reasone: {{ $orderAssigned->orderDecline ? $orderAssigned->orderDecline->orderDeclineReason->name : '' }}
                            </li>
                            <li>
                                Rider Name: {{ $orderAssigned->rider->name }}
                            </li>
                            <li>
                                Location Picture:
                                <br>
                                <img width="200px" height="150px" src="{{ asset($orderAssigned->orderDecline ? $orderAssigned->orderDecline->image : '')}}">
                            </li>
                            @endif
                            @endforeach
                        </ol>
                        @endif
                    </li>
                </div>
                <div class="col-sm-12 col-xl-4">
                    <h4 class="sub-title">Current Rider</h4>
                    <div class="row col-sm-12">
                        <ul class="list-inline m-b-0">
                            <li class="list-inline-item">Rider Name: {{ $rider_detail->rider->name ??  'N\A' }}</li>
                            <li class="list-inline-item">Phone: {{ $rider_detail->rider->userDetail->phone ?? 'N\A'}}</li>
                        </ul>
                        @if(empty($rider_detail->rider))
                        <ul class="list-inline m-b-0">
                            <li class="list-inline-item">Rider Supervisor Name: N\A</li>
                            <li class="list-inline-item">Rider Supervisor Phone: N\A</li>
                        </ul>
                        @else
                        <ul class="list-inline m-b-0">
                            <li class="list-inline-item">Rider Supervisor Name: {{ $rider_detail->rider->supervisorPerson ? $rider_detail->rider->supervisorPerson->name : 'N/A' }}</li>
                            <li class="list-inline-item">Rider Supervisor Phone: {{ $rider_detail->rider->supervisorPerson ? $rider_detail->rider->supervisorPerson->userDetail->phone : 'N/A' }}</li>
                        </ul>
                        @endif
                    </div>
                    <br><br>
                    @if($orderDetail->order_status == 10)
                        <ul class="list-inline m-b-0">
                            <li class="list-inline-item">Return Date: {{ ($orderDetail->order_status == 10) ? $orderDetail->updated_at : 'N\A' }}</li>
                        </ul>
                    @endif
                    <br><br>
                    <h4 class="sub-title">Parcel Journey</h4>
                    <div class="row col-sm-12">
                        <ul class="list-unstyled">
                            <li>AWAITING PICKUP: {{date('d M Y h:i a', strtoTime($orderDetail->created_at))}}</li>
                            @if(!empty($orderDetail->scanOrder->created_at))
                            <li>PICKED UP: {{date('d M Y h:i a', strtoTime($orderDetail->scanOrder->created_at))}}</li>
                            @endif
                            @if(!empty($orderDetail->scanOrder->middle_man_scan_date))
                            <li>AT AHL WAREHOUSE: {{date('d M Y h:i a', strtoTime($orderDetail->scanOrder->middle_man_scan_date))}}</li>
                            @endif
                            @if(count($get_sag_orders) > 0)
                                @foreach($get_sag_orders as $get_sag_order)
                                    <li>EN-ROUTE to {{$get_sag_order->toCity->name}}: {{date('d M Y h:i a', strtoTime($get_sag_order->created_at))}}</li>
                                    @if($get_sag_order->status == 2)
                                        <li>UN-LOAD AT {{$get_sag_order->toCity->name}} WAREHOUSE: {{date('d M Y h:i a', strtoTime($get_sag_order->updated_at))}}</li>
                                    @endif
                                @endforeach
                            @endif
                            @if(!empty($orderDetail->scanOrder->supervisor_scan_date))
                            <li>PARCEL ASSIGNED TO RIDER: {{date('d M Y h:i a', strtoTime($orderDetail->scanOrder->supervisor_scan_date))}}</li>
                            @endif
                            @if(!empty($orderDetail->scanOrder->supervisor_scan_date))
                            <li>DISPATCHED: {{date('d M Y h:i a', strtoTime($orderDetail->scanOrder->supervisor_scan_date))}}</li>
                            @endif
                            @if($orderDetail->orderAssigned)
                                @if(($orderDetail->orderAssigned->trip_status_id) == 4)
                                <li>DELIVERED: {{date('d M Y h:i a', strtoTime($orderDetail->orderAssigned->updated_at))}}</li>
                                @endif
                            @endif
                        <hr>
                        @if(isset($assigned_dates))
                        <p style="font-weight: bold">PARCEL ASSIGNED DATES</p>
                        <hr>
                            @foreach($assigned_dates as $reattempt)
                            <li>ASSIGNED AT: {{date('d M Y h:i a', strtoTime($reattempt->created_at))}}</li>
                            <li>ASSIGNED TO: {{$reattempt->rider->name}}</li>
                            <li>Phone Number: {{$reattempt->rider->userDetail->phone}}</li>
                            <hr>
                            @endforeach
                        @endif
                        @if(isset($decline_dates))
                        <p style="font-weight: bold">IF PARCEL RE-ATTEMPTED / CANCELLED</p>
                        <hr>
                            @foreach($decline_dates as $reattempt)
                            <li>RE-ATTEMPTED AT: {{date('d M Y h:i a', strtoTime($reattempt->updated_at))}}</li>
                            <li>RIDER NAME: {{$reattempt->rider->name}}</li>
                            <li>CLOSING SUPERVISOR NAME: {{$reattempt->supervisorName ? $reattempt->supervisorName->name : ''}}</li>
                            <li>CONTROL TOWER REMARKS: {{$reattempt->remarks}}</li>
                            <li>CONTROL TOWER REMARKS BY: {{$reattempt->remarksBy ? $reattempt->remarksBy->name : ''}}</li>
                            @if(!empty($reattempt->cdrid))
                            <li>IVR CALL RESPONSE: {{$reattempt->call_response}}</li>
                            <?php 
                                $call_input_value = '';
                                if($reattempt->ivr_value == '479') //Re-attempt
                                {
                                    if($reattempt->call_input == '0')
                                    {
                                        $call_input_value = 'No Input';
                                    }
                                    elseif($reattempt->call_input == '1')
                                    {
                                        $call_input_value = 'Please Re-Attempt My Parcel';
                                    }
                                    elseif($reattempt->call_input == '2')
                                    {
                                        $call_input_value = 'Do-Not Re Attempt I want my parcel /Rider Add fake Remakrs';
                                    }
                                    else
                                    {
                                        $call_input_value = 'No Input';
                                    }
                                }
                                elseif($reattempt->ivr_value == '480') //Cancel
                                {
                                    if($reattempt->call_input == '0')
                                    {
                                        $call_input_value = 'No Input';
                                    }
                                    elseif($reattempt->call_input == '1')
                                    {
                                        $call_input_value = 'Please Cancel my order /Confirm Cancel by Custumer';
                                    }
                                    elseif($reattempt->call_input == '2')
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
                                    if($reattempt->call_input == 0)
                                    {
                                        $call_input_value = 'No Input';
                                    }
                                    elseif($reattempt->call_input == 1)
                                    {
                                        $call_input_value = 'Cancel Input';
                                    }
                                    elseif($reattempt->call_input == 2)
                                    {
                                        $call_input_value = 'Re-Attempt Input';
                                    }
                                    else
                                    {
                                        $call_input_value = 'No Input';
                                    }
                                }
                            ?>
                            <li>IVR CALL INPUT: {{$call_input_value}}</li>
                            @endif
                            <hr>
                            @endforeach
                        </ul>
                        @endif
                        <hr>
                    </div>
                    <div class="row col-sm-12">
                        <div class="inline-order-list">
                            <h4 class="sub-title">Scan Order</h4>
                        </div>
                        <div class="card-block"> 
                            <ul class="list-inline m-b-0">
                                <li class="list-inline-item">Picker Scan By: {{ ($scanOrder && $scanOrder->scanByPicker) ? $scanOrder->scanByPicker->name : 'N\A' }}</li>
                                <li class="list-inline-item">Middle Man Scan By: {{ ($scanOrder && $scanOrder->scanByMiddleMan) ? $scanOrder->scanByMiddleMan->name : 'N\A'}} </li>
                                <li class="list-inline-item">Supervisor Scan By: {{ ($scanOrder && $scanOrder->scanBySupervisor) ? $scanOrder->scanBySupervisor->name : 'N\A'}} </li>
                                @if($orderDetail->orderAssigned)
                                <li class="list-inline-item">Delivered By: {{ ($orderDetail->orderAssigned->rider) ? $orderDetail->orderAssigned->rider->name : 'N\A'}} </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- List Tag card end -->
</div>
<!-- Page-body end -->
@endsection()

@section('custom-js')

    <SCRIPT language="javascript">
        $('#OpenFileUpload').click(function(){ $('#fileupload').trigger('click'); });
    </SCRIPT>

@endsection()