@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .divider {
        margin-top: 2%; / or whatever /
    }
    .heading
    {
        font-weight: bold !important;
    }
</style>
@endsection
@section('content')

<div class="page-body">

    <div class="row">
        <div class="col-md-12">
            @if (session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h5>Control Tower Remarks</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('savePendingRemark') }}">
                        @csrf
                        <h4 class="sub-title divider heading">Control Tower Remarks</h4>

                        <input type="hidden" name="order_id" value="{{$order->id}}">
                        <div class="row">
                            <div class="col-md-6">
                                Order Reference: {{$order->order->order_reference}}
                            </div>
                            <div class="col-md-6">
                                Vendor Name: {{$order->riderVendor->vendor_name}}
                            </div>
                            <div class="col-md-6">
                                Amount: {{$order->order->consignment_cod_price}}
                            </div>
                            <div class="col-md-6">
                                Order ID: {{$order->order->consignment_order_id}}
                            </div>
                            <div class="col-md-6">
                                Customer Name: {{$order->order->full_name}}
                            </div>
                            <div class="col-md-6">
                                Customer Phone: {{$order->order->consignee_phone}}
                            </div>
                            <div class="col-md-6">
                                Rider Status: {{$order->trip_status_id == 5 ? 'Cancel' : 'Re-Attempt' }}
                            </div>
                            <div class="col-md-6">
                                Rider Name: {{$order->rider ? $order->rider->name : 'N/A' }}
                            </div>
                            <div class="col-md-12">
                                Cancellation Reason: {{$order->orderDecline ? $order->orderDecline->additional_note : 'N/A' }}
                            </div>
                            <div class="col-md-12">
                                Customer Address: {{$order->order->consignee_address}}
                            </div>
                        </div>
                        <br><br>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="remarks" class="form-control  @error('reply') is-invalid @enderror" placeholder="Add Remarks here...!" value="{{$order->remarks}}" required>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Add Remarks</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection