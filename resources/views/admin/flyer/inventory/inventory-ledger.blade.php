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
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Flyer Name</th>
                            <th>Flyer Quantity Added</th>
                            <th>Remarks</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flyer_inventories as $key => $flyer_inventory)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$flyer_inventory->flyerName->name}}</td>
                            <td>{{$flyer_inventory->qty}}</td>
                            <td>{{$flyer_inventory->remarks}}</td>
                            <td>{{date('d M Y h:m a', strtoTime($flyer_inventory->created_at))}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection