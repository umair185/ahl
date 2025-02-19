@extends('layouts.app')

@section('content')

<div class="page-body"> 
	@if (session('sucess'))
	    <div class="alert alert-success">
	        {{ session('sucess') }}
	    </div>
	@endif
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Pay To {{ $get_detail->vendorName ? $get_detail->vendorName->vendor_name : '' }}</h5>
                    <br>
                    <br>
                    <br>
                    <h2>Paid at {{date('d M Y H:i A', strtoTime($get_detail->created_at))}}</h2>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('payVendorFinancialsUpdate') }}" enctype="multipart/form-data">
                        @csrf
                    	<input type="hidden" id="financial_id" name="financial_id" value="{{$get_detail->id}}">                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <select name="invoice_type" class="form-control  @error('invoice_type') is-invalid @enderror" value="{{ old('invoice_type') }}" required>
                                        <option value="">Select Invoice Type</option>
                                        <option value="IBFT" {{$get_detail->invoice_type == 'IBFT'  ? 'selected' : ''}}>IBFT</option>
                                        <option value="CASH" {{$get_detail->invoice_type == 'CASH'  ? 'selected' : ''}}>CASH</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="pay_amount" class="form-control  @error('pay_amount') is-invalid @enderror" value="{{ $get_detail->amount }}" required>
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">Amount</label>
                                    @error('pay_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="ahl_commission" class="form-control  @error('ahl_commission') is-invalid @enderror" value="{{$get_detail->ahl_commission}}" required="">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">AHL Commission</label>
                                    @error('ahl_commission')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="ahl_gst" class="form-control  @error('ahl_gst') is-invalid @enderror"  required="" value="{{$get_detail->ahl_gst}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">AHL GST</label>
                                    @error('ahl_gst')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="advance_amount" class="form-control  @error('advance_amount') is-invalid @enderror" value="{{$get_detail->advance_amount}}" required="">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">ADVANCE PAYMENT</label>
                                    @error('advance_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="fuel_adjustment" class="form-control  @error('fuel_adjustment') is-invalid @enderror" required="" value="{{$get_detail->fuel_adjustment}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">FUEL ADJUSTMENT</label>
                                    @error('fuel_adjustment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="flyer_amount" class="form-control  @error('flyer_amount') is-invalid @enderror" required="" value="{{$get_detail->flyer_amount}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">FLYERS AMOUNT</label>
                                    @error('flyer_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="deduction_amount" class="form-control  @error('deduction_amount') is-invalid @enderror" required="" value="{{$get_detail->deduction_amount}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">DEDUCTION</label>
                                    @error('deduction_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <select name="deduction_remarks" class="form-control  @error('deduction_remarks') is-invalid @enderror" value="{{ old('deduction_remarks') }}" required>
                                        <option value="">Select DEDUCTION REMARKS</option>
                                        <option value="normal" {{$get_detail->deduction_remarks == 'normal'  ? 'selected' : ''}}>NORMAL</option>
                                        <option value="advance_deduction" {{$get_detail->deduction_remarks == 'advance_deduction'  ? 'selected' : ''}}>ADVANCE DEDUCTION</option>
                                    </select>
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">DEDUCTION REMARKS</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="remarks" class="form-control  @error('remarks') is-invalid @enderror" value="{{$get_detail->remarks}}">
                                    <span class="form-bar"></span>
                                    <label class="float-label" style="color:black">Remarks</label>
                                    @error('remarks')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="financial_report" class="form-control  @error('financial_report') is-invalid @enderror">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Upload Excel or CSV file</label>
                                    @error('financial_report')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date_from" id="date_from" max="{{$today_date}}" class="form-control">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Invoice Date</label>
                                    @error('financial_report')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                        		<button type="submit" class="btn btn-primary waves-effect waves-light">Pay</button>
                        	</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection