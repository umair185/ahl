<!-- Page-header start -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{ $breadcrumbs['name'] }}</h5>
                    <p class="m-b-0">Welcome to {{ config('app.name', 'Laravel') }} </p>
                </div>
            </div>
            <div class="col-md-4">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{route('index')}}"> <i class="fa fa-home"></i> </a>
                    </li>
                    <!-- <li class="breadcrumb-item"><a href="#!">Dashboard</a>
                    </li> -->
                    <li class="breadcrumb-item"><a href="#!">{{ $breadcrumbs['name'] }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Page-header end -->