@extends('layouts.app')

@section('custom-css')
<style type="text/css">

</style>
@endsection
@section('content')

<div class="page-body">
    
    <div class="card">
        <div class="card-header">
            <h5>Weight List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Weight Slots</th>
                            <th>Weight City</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ahlWeights as $key => $weight)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$weight->weight}}</td>
                            <td>{{$weight->weightCity->city[0]->name ?? ''}}</td>
                            <td>{{date('d M Y', strtoTime($weight->created_at))}}</td>
                            <td><a href="{{route('ahlWeightEdit', $weight->id)}}"><i class="fa fa fa-edit"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection