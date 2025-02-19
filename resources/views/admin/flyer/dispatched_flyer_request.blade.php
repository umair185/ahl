@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .btn {
        padding: .375rem .75rem;
    }
</style>
@endsection
@section('content')

<div class="page-body">
    
    <div class="card">
        <div class="card-header">
            <h5>Dispatched Flyers Request</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendor Name</th>
                            <th>Flyers Detail</th>
                            <th>Flyers Total Price</th>
                            <th>Current Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flyer_requests as $key => $flyer_request)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td class="font-weight-bold">{{$flyer_request->vendor->vendor_name}}</td>
                            <td>
                                @foreach($flyer_request->flyerDetail as $key => $flyers)
                                <p>{{$flyers['quantity']}} Pcs of {{$flyers->flyerName->name}} Flyer</p>
                                @endforeach
                            </td>
                            <td>PKR {{number_format($flyer_request->total)}}</td>
                            <td>
                                @if($flyer_request->status == 1)
                                Pending
                                @elseif($flyer_request->status == 2)
                                Accepted
                                @elseif($flyer_request->status == 3)
                                En-Route
                                @elseif($flyer_request->status == 4)
                                Delivered
                                @else
                                Not Specified
                                @endif
                            </td>
                            <td>
                                <a href="{{route('flyerRequestStatusChange', $flyer_request->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Mark as Delivered</button></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection