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
            <h5>Packing List</h5>
            <div class="card-header-right">
                <a href="{{route('createPacking')}}"><button class="btn waves-effect waves-light btn-primary">New Packing</button></a>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Packing Name</th>
                            <th>Packing Status</th>
                            <th>Created At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packing as $key => $pickup_request)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$pickup_request->name}}</td>
                            <td>{{$pickup_request->status}}</td>
                            <td>{{date('d M Y H:i A', strtoTime($pickup_request->created_at))}}</td>
                            <td><a href="{{route('editPacking', $pickup_request->id)}}"><i class="fa fa fa-edit"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection