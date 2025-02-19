@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
<style type="text/css">

</style>
@endsection
@section('content')

<div class="page-body">
    
    <div class="card">
        <div class="card-header">
            <h5>City List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Area Name</th>
                            <th>City</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($areas as $key => $area)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$area->area_name}}</td>
                            <td>{{$area->city->name}}</td>
                            
                            <td><a href="{{route('editArea',$area->id)}}"><i class="fa fa fa-edit"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
    var table = $('#data-table').DataTable();
});
</SCRIPT>
@endsection