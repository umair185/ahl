@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .divider {
        margin-top: 2%; / or whatever /
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
                    <h5>Create Tagline</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="save-tagline">
                        @csrf
                        <h4 class="sub-title divider heading">TAGLINE DETAILS</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="tag_line" class="form-control  @error('tag_line') is-invalid @enderror" value="{{ old('tag_line') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">TagLine</label>
                                    @error('tag_line')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection