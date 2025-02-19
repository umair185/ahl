@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .btn {
        padding: .375rem .75rem;
    }
</style>
@endsection
@section('content')
@if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
@endif
<div class="page-body">
    
    <div class="card">
        <div class="card-header">
            <h5>Flyer List</h5>
            <div class="card-header-right">
                <a href="{{route('flyerCreate')}}"><button class="btn waves-effect waves-light btn-primary">New Flyer</button></a>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Flyer Name</th>
                            <th>Flyer Price</th>
                            <th>Current Stock</th>
                            <th>Edit</th>
                            <th>Add Inventory</th>
                            <th>View Inventory</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flyers as $key => $flyer)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$flyer->name}}</td>
                            <td>{{$flyer->price}}</td>
                            <td>{{$flyer->current_stock}}</td>
                            <td><a href="{{route('editFlyer', $flyer->id)}}"><i class="fa fa fa-edit"></i></a></td>
                            <td><a href="{{route('addInventory', $flyer->id)}}"><i class="fa fa fa-plus"></i></a></td>
                            <td><a href="{{route('viewInventory', $flyer->id)}}"><i class="fa fa fa-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection