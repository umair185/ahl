@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .divider {
        margin-top: 2%; /* or whatever */
    }
    .heading
    {
        font-weight: bold !important;
    }
</style>
@endsection
@section('content')

<div class="page-body">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Vendor</h5>
                    <h5 style="float:right;">Additional Kg Price <span id="additional_weight"> </span></h5>
                </div>
                <div class="card-block">
                
                    <form method="POST" class="form-material" action="{{route('assignVendorWeight')}}">
                        @csrf
                        <h4 class="sub-title divider heading">Vendor Weights</h4>
                        <div class="col-md-12">
                            <div class="form-group form-static-label form-primary">
                                <select id="vendor_id" name="vendor_id" class="form-control">
                                    <option selected="" disabled="" hidden="">Select Vendor</option>
                                    @foreach(Helper::getActiveVendors() as $key=> $state)
                                    <option value="{{$state->id}}" data-weight="{{$state->addational_kgs}}">{{$state->vendor_name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('vendor_id'))
                                    <div class="alert alert-danger" role="alert">
                                      Add At least One Vendor
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <button type="button" onclick="newVendorWeight();" class="btn" style="background-color: #448AFF; color: white; font-weight: bold"> <i class="fa fa-plus"></i> Add Vendor Weight </button>
                                </div>
                            </div>
                        </div>
                        <div id="vendor-weight-container"></div>
                        <hr>
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Weight</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('custom-js')
<script>

        var weightRowInc = 0;
        function newVendorWeight()
        {
            weightRowInc++;
            var newVendorWeight = '<div style="margin-top: 10px" id="row-weight' + weightRowInc + '" class="row">' +
                '<div class="col-md-3">' +
                '<input type="text" required name="vendorWeights[]" class="form-control" placeholder="Enter Vendor Weight">' +
                '</div>' +
                '<div class="col-md-3">' +
                '<input type="number" required name="vendorWeightsPrice[]" class="form-control" placeholder="Enter Vendor Weight Price">' +
                '</div>' +
                '<div class="col-md-3">' +
                '<select name="vendorWeightscity[]" class="form-control" required><option value="">Select City</option>@foreach($cities as $city)<option value={{$city->id}}>{{$city->name}}</option>@endforeach</select>' 
                +'</div>' +
                '<div class="col-md-3">' +
                '<div class="input-group-btn">' +
                '<button class="btn" style="background-color: #448AFF; color: white" onclick="removeAddress(' + weightRowInc + ');" type="button"> <i class="fas fa-minus"></i> </button>' +
                '</div>' +
                '</div>' +
                '</div>';

            $("#vendor-weight-container").append(newVendorWeight);
        }

        function removeVendorWeight(rowId) {
            $("#row-weight" + rowId).remove();
        }

        $('#vendor_id').on("change",function(){
            var weight = $("#vendor_id option:selected").attr('data-weight');
            console.log(weight);
            $('#additional_weight').text(weight);
        });

</script>
@endsection