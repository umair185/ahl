@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    /*.btn {
        padding: .375rem .75rem;
    }*/
</style>
@endsection
@section('content')

<div class="page-body">
    
    <div class="card">
        <div class="card-header">
            <h5>Time Slots List</h5>
            <div class="card-header-right">
                <a href="{{route('createTiming')}}"><button class="cardHeaderBtn btn waves-effect waves-light btn-primary">New Timing</button></a>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Time Slots</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timings as $key => $pickup_request)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$pickup_request->timings}}</td>
                            <td><a href="{{route('editTiming', $pickup_request->id)}}"><i class="fa fa fa-edit"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection