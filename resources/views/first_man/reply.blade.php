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
            <div class="card">
                <div class="card-header">
                    <h5>Shiper Advise Reply</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveReply') }}">
                        @csrf
                        <h4 class="sub-title divider heading">Shiper Advise</h4>

                        <input type="hidden" name="shiper_advise_id" value="{{$shiperAdvise->id}}">
                        <div class="row">
                            <div class="col-md-6">
                                Order Reference : {{$shiperAdvise->order->order_reference}}
                            </div>
                            <div class="col-md-6">
                                Vendor Name : {{$shiperAdvise->order->vendor->vendor_name}}
                            </div>
                            <div class="col-md-6">
                                Amount : {{$shiperAdvise->order->consignment_cod_price}}
                            </div>
                            <div class="col-md-6">
                                Order ID : {{$shiperAdvise->order->consignment_order_id}}
                            </div>
                            <div class="col-md-6">
                                Customer Name : {{$shiperAdvise->order->full_name}}
                            </div>
                            <div class="col-md-6">
                                Customer Phone : {{$shiperAdvise->order->consignee_phone}}
                            </div>
                            <div class="col-md-6">
                                Shiper Advise Date : {{date('d-m-Y H:i A', strtotime($shiperAdvise->created_at))}}
                            </div>
                            <div class="col-md-6">
                                Order Status : {{$shiperAdvise->order->orderStatus->name}}
                            </div>
                            <div class="col-md-12">
                                Shiper Advise : {{($shiperAdvise->advise) ? $shiperAdvise->advise : ''}}
                            </div>
                            <div class="col-md-12">
                                Customer Address : {{$shiperAdvise->order->consignee_address}}
                            </div>
                        </div>
                        <br><br>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="reply" class="form-control  @error('reply') is-invalid @enderror" value="{{$shiperAdvise->ahl_reply }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Shiper Advise Reply</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Reply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection