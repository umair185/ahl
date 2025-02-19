@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body display" id="printableArea">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Order Hold Status</h5>
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <span>Reference No #</span>
                            <strong>{{$order->order_reference}}</strong>
                        </div>    

                        <div class="col-xl-3 col-md-6">
                            <span>Vendor</span>
                            <strong>{{$order->vendor->vendor_name}}</strong>
                        </div>    
                    </div>    
                </div>
                <div class="card-block table-border-style">
                    <form action="{{route('updateholdStatus')}}" method="post">
                        @csrf
                        <input type="hidden" name="order_id" value="{{$order->id}}">
                        <label for="select_hold">Select Status</label>
                        <select name="select_hold" id="hold_id" class="form-control fprm-select">
                            <option value="">Select Status</option>
                            <option value="1">Hold</option>
                            <option value="0">Un-hold</option>
                        </select>
                        <br>

                        <div class="form-group form-static-label form-default">
                            <label for="select_hold">Hold Reason</label>
                            <input type="text" class="form-control fill" name="reason" id="reason" placeholder="Enter hold reason">
                            
                        </div>
                        
                        <button type="submit" class="btn btn-primary mt-2">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<SCRIPT language="javascript">

</SCRIPT>
@endsection