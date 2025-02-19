@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>City List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Area Name</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subareas as $key=> $area)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{$area->area_name}}</td>
                            </tr>
                        @endforeach    
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection