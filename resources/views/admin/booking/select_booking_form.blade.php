@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .divider {
        margin-top: 5%;
    }
</style>
@endsection
@section('content')

<div class="page-body">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Assign Parcels to Riders</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-static-label form-default">
                                <select name="vendor_id" id="vendor-id" onchange="getParcel()" class="form-control  @error('vendor_id') is-invalid @enderror" value="{{ old('vendor_id') }}">
                                    <option selected="" disabled="" hidden="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{$vendor->id}}" {{ (isset($vendorId) && $vendorId == $vendor->id) ? 'selected' : '' }}>{{$vendor->vendor_name}}</option>
                                    @endforeach
                                </select>
                                <span class="form-bar"></span>
                                <!--<label class="float-label">Name</label>-->
                                @error('vendor_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <th>#</th>
                                <th>Parcel ID</th>
                                <th>Consignee Address</th>
                                <th>Consignment City</th>
                                <th>Assign to Rider</th>
                                </thead>
                                <tbody>
                                    @foreach($parcelList as $key => $parcelLists)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td>{{$parcelLists->consignment_order_id}}</td>
                                        <td>{{$parcelLists->consignee_address}}</td>
                                        <td>{{$parcelLists->customerCity->name}}</td>
                                        <td><button class="btn waves-effect waves-light btn-success"><i class="fa fa-arrow-circle-o-right"></i>Assign Rider</button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>  
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function getParcel()
    {
        var vendorId = $('#vendor-id').val();
        $.ajax({
            'type': 'GET',
            'url': 'get-select-vendor-parcel',
            'data': {
                vendor_id: vendorId
            },
            success: function (response) {
                window.location.replace(response);
            },
            error: function (response) {
                console.log(response);
            }
        });
    }
</script>


@endsection
