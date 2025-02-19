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
                    <h5>Update Tagline</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('updateTagLine') }}">
                        @csrf
                        <h4 class="sub-title divider heading">TAGLINE DETAILS</h4>

                        <input type="hidden" name="tag_line_id" value="{{$tag_line->id}}">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="tag_line" class="form-control  @error('tag_line') is-invalid @enderror" value="{{$tag_line->tag_line }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Tagline</label>
                                    @error('tag_line')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <select id="status" name="status" class="form-control  @error('status') is-invalid @enderror">
                                        <option>Select Status</option>
                                        <option {{$tag_line->status == 1 ? 'selected' : ''}} value="1" >Active</option>
                                        <option {{$tag_line->status == 0 ? 'selected' : ''}} value="0" >In-Active</option>
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection