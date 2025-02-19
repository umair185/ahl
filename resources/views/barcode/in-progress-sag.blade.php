@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>In-Progress Sags List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sag Number</th>
                            <th>Manual Seal Number</th>
                            <th>Create By</th>
                            <th>Create In</th>
                            <th>No. of Parcels</th>
                            <th>Bilty Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sags as $key => $sag)
                        <tr>
                            <th>{{ ++$key }}</th>
                            <td>{{ $sag->sag_number }}</td>
                            <td>{{ $sag->manual_seal_number }}</td>
                            <td>{{ $sag->closeBy->name }}</td>
                            <td>{{ $sag->fromCity->name }}</td>
                            <td>{{ count($sag->orders) }}</td>
                            <td>{{ $sag->bilty_status == 1 ? 'Yes' : 'No' }}</td>
                            <td><a href="{{route('inProgressSagParcelList', $sag->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>View Sag Parcels</button></a></td>
                            
                        </tr>
                       @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection