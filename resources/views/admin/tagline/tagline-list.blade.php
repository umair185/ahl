@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
<style type="text/css">

</style>
@endsection
@section('content')

<div class="page-body">
    
    <div class="card">
        <div class="card-header">
            <h5>Tagline List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tagline</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tag_lines as $key => $tag_line)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$tag_line->tag_line}}</td>
                            <?php 
                            if($tag_line->status == 1)
                            {
                                $tag_line_status = 'Active';
                            }
                            else
                            {
                                $tag_line_status = 'In-Active';
                            }
                            ?>
                            <td>{{$tag_line_status}}</td>
                            
                            <td><a href="{{route('editTagLine',$tag_line->id)}}"><i class="fa fa fa-edit"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">

$(function () {
    /* Data Table */
    var table = $('#data-table').DataTable();
});
</SCRIPT>
@endsection