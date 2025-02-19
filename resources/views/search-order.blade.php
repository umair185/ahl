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
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Search Parcel by Order Id</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('searchParcelOrder') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="order_id" class="form-control  @error('weight') is-invalid @enderror" value="{{ old('weight') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Enter Order Id</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Search</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection