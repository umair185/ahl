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
                    <h5>Add Additional Note</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveAdditionalNote') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" name="note_id" value="{{$orders_decline->id}}">
                                    <input type="text" name="additional_note" class="form-control  @error('additional_note') is-invalid @enderror" value="{{ $orders_decline->additional_note }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Enter Additional Note</label>
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