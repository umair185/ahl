@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="card-block">
    <div class="page-body display" id="printableArea">
        @if (session('status'))
            <div class="alert alert-info">
                {{ session('status') }}
            </div>
        @endif
        <div class="card">
            <div class="card-header">
                <h5>Cancelled Parcels</h5>
                <div class="card-header-right">
                    <a href="{{route('generateCancelledPDF')}}"><button class="cardHeaderBtn btn waves-effect waves-light btn-primary">Export to PDF</button></a>
                </div>
            </div>
            <div class="card-block table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Parcel Reference Number</th>
                                <th>Vendor</th>
                                @role('first_man')
                                <th>Action</th>
                                <th>Void Label</th>
                                @endrole
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($orders) > 0)
                                @foreach($orders as $key => $order)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>{{$order->order_reference}}</td>
                                        <td>{{$order->vendor->vendor_name}}</td>
                                        @role('first_man')
                                        <td>
                                            <a href="{{ route('returnToVendor') }}"
                                               onclick="event.preventDefault();
                                             document.getElementById('return-form-{{$loop->iteration}}').submit();"><i class="ti-layout-sidebar-left"></i>Return</a>

                                            <form id="return-form-{{$loop->iteration}}" action="{{ route('returnToVendor') }}" method="POST" class="d-none">
                                                @csrf
                                                <input type="hidden" value="{{ $order->id }}" name="order_id">
                                            </form>
                                        </td>
                                        <td><a href="{{route('markVoidLabel', $order->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Void Label</button></a></td>
                                        @endrole
                                    </tr>
                                @endforeach
                            @endif
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

$(function () {
    /* Data Table */
    var table = $('#example').DataTable();
});
</SCRIPT>
@endsection