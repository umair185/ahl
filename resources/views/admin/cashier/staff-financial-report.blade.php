@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Staff Financial Report</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('staffFinancialReport',['staff'=>app('request')->staff])}}" method="POST">
	                   	@csrf
	                    <div class="row">
	                        <div class="col-md-10">
	                            <div class="form-group form-static-label form-default">
	                                <select id="riders" name="staff_id" class="form-control  @error('staff_id') is-invalid @enderror" value="{{ old('staff_id') }}">
	                                    <option selected="" disabled="" hidden="">Select Staff</option>
	                                    @foreach($staffList as $key=> $staff)
	                                        <option value="{{$staff->id}}" value="@if(old('staff_id')) {{ old('staff_id') }} @endif" @if(old('staff_id')) {{ 'selected' }} @endif>{{$staff->name}} ( {{ $staff->userDetail->cnic }} )</option>
	                                    @endforeach
	                                </select>
	                                <span class="form-bar"></span>
	                                @error('staff_id')
	                                    <span class="invalid-feedback" role="alert">
	                                        <strong>{{ $message }}</strong>
	                                    </span>
	                                @enderror
	                            </div>
	                        </div>

	                        <div class="col-md-2">
	                            <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
	                            	Report
	                        	</button>
	                        </div>
	                    </div>
	                </form>
                </div>
            </div>
        </div>
    </div>
    @if(isset($staffFinancialsReport))
    <div class="card">
        <div class="card-header">
            <h5>Staff Financial Report</h5>
            <div class="card-header-right">
                <ul class="list-unstyled card-option">
                    <li><i class="fa fa fa-wrench open-card-option"></i></li>
                    <li><i class="fa fa-window-maximize full-card"></i></li>
                    <li><i class="fa fa-minus minimize-card"></i></li>
                    <li><i class="fa fa-refresh reload-card"></i></li>
                    <li><i class="fa fa-trash close-card"></i></li>
                </ul>
            </div>
        </div>

	    <div class="card-block table-border-style">
	        <div class="table-responsive">
	            <table class="table table-hover" id="example">
	                <thead>
	                    <tr>
	                        <th>#</th>
	                        <th>Staff Name</th>
	                        <th>Cashier</th>
	                        <th>Amount</th>
	                        <th>Note</th>
	                        <th>Created At</th>
	                    </tr>
	                </thead>
	                <tbody>
	                	
	                    @foreach($staffFinancialsReport as $financial)
	                    <tr>
	                        <th scope="row">{{ $loop->iteration }}</th>
	                        <td>{{$financial->staffName->name}}</td>
	                        <td>{{$financial->cashierName->name}}</td>
	                        <td>{{$financial->amount}}</td>
	                        <td>{{$financial->note}}</td>
	                        <td>{{date('d M Y H:i A', strtoTime($financial->created_at))}}</td>
	                    </tr>
	                    @endforeach
	                    
	                </tbody>
	            </table>
	        </div>
	    </div>
	</div>
    @endif
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