<nav class="pcoded-navbar">
    <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">
        <div class="">
            <div class="main-menu-header">
                <img class="img-80 img-radius" src="{{ asset('assets/images/profile.png')}}" alt="User-Profile-Image">
                <div class="user-details">
                    <span id="more-details">{{Auth::user() ? Auth::user()->name : ''}}<i class="fa fa-caret-down"></i></span>
                </div>
            </div>
            <div class="main-menu-content">
                <ul>
                    <li class="more-details">
                        <a href="{{ route('passwordUpdate',['id'=>Helper::encrypt(Auth::user()->id)]) }}"><i class="ti-user"></i>Change Password</a>
                        <!-- <a href="#"><i class="ti-settings"></i>Settings</a> -->                        
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                         document.getElementById('logout-form').submit();"><i class="ti-layout-sidebar-left"></i>Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        <div class="p-15 p-b-0">
            <form class="form-material">
                <div class="form-group form-primary form-static-label">
                    <input type="text" name="left_search_parcel" id="left_search_parcel" class="form-control" placeholder="#LHR1617092555800">
                    <span class="form-bar"></span>
                    <label class="float-label"><i class="fa fa-search m-r-10"></i>Search by Tracking Number</label>
                </div>
            </form>
        </div>
        <div class="p-15 p-b-0">
            <form class="form-material">
                <div class="form-group form-primary form-static-label">
                    <input type="text" name="id_search_parcel" id="id_search_parcel" class="form-control" placeholder="SF-1059">
                    <span class="form-bar"></span>
                    <label class="float-label"><i class="fa fa-search m-r-10"></i>Search by Order ID</label>
                </div>
            </form>
        </div>
        <div class="p-15 p-b-0">
            <form class="form-material">
                <div class="form-group form-primary form-static-label">
                    <input type="text" name="mobile_search_parcel" id="mobile_search_parcel" class="form-control" placeholder="03xx-xxxxxxx">
                    <span class="form-bar"></span>
                    <label class="float-label"><i class="fa fa-search m-r-10"></i>Search by Mobile Number</label>
                </div>
            </form>
        </div>
        @hasanyrole('vendor_admin|vendor_editor')
        <div class="pcoded-navigation-label">Vendor</div>
        <ul class="pcoded-item pcoded-left-item">
            
            <li class="active">
                <a href="{{route('index')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                    <span class="pcoded-mtext">AHL Dashboard</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @hasrole('vendor_admin')
            <li class="">
                <a href="{{route('viewProfile')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-settings"></i><b>D</b></span>
                    <span class="pcoded-mtext">Profile</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasrole
            <div class="pcoded-navigation-label">Booking</div>
            <li class="">
                <a href="{{route('manualOrder')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                    <span class="pcoded-mtext">Create Booking</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li class="">
                <a href="{{route('bulkOrder')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                    <span class="pcoded-mtext">Bulk Booking</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li class="">
                <a href="{{route('citiesIdList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Cities ID List's</span>
                </a>
            </li>
            <div class="pcoded-navigation-label">Requests</div>
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Pickup Requests</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('pickupRequest')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Pending Pickup Request</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('createPickupRequest')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create Pickup Request</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>

                    <li class=" ">
                        <a href="{{route('completePickupRequest')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">AHL Load Sheet</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Flyer Requests</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('createFlyerRequest')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create Flyer Request</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('flyerRequestIndex')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Flyer Requests</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>

                    <li class=" ">
                        <a href="{{route('completedFlyerRequestIndex')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Received Flyers Request</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Complain Section</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('createVendorComplain')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create Complain</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('pendingVendorComplain')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Pending Complains</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('inProgressVendorComplain')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">In-Progress Complains</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('resolvedVendorComplain')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Resolved Complains</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Reverse Pickup</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('reversePickupRequest')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Pending Reverse Pickup</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('progressReversePickupRequest')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">In-Progress Reverse Pickup</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('completeReversePickup')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Complete Reverse Pickups</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('cancelReversePickup')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Cancel Reverse Pickups</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
            <div class="pcoded-navigation-label">Parcel Management</div>
            <li class="">
                <a href="{{route('parcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Print QR</span>
                </a>
            </li>
            <li class=" ">
                <a href="{{route('currentStatusReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"><b>BC</b></i></span>
                    <span class="pcoded-mtext">Current Parcels Status</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('vendorShiperAdviser')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Shiper Adviser <label class="badge badge-inverse-danger"> {{ $advise_parcels_count }} <!-- from view composer --> </label></span>
                </a>
            </li>
            <div class="pcoded-navigation-label">Admin Management</div>
            <li class="">
                <a href="{{route('vendorEditorsList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Staff Management</span>
                </a>
            </li>
            <div class="pcoded-navigation-label">Payment Management</div>
            <li class="">
                <a href="{{route('ahlPayReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">AHL Pay Report</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('vendorSideDispatchSheet')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Quality Service Report (QSR)</span>
                </a>
            </li>
            <li>
                <a href="{{route('shipperAdviseReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Shipper Advise Report</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <!--li class="">
                <a href="{{route('generateTaxInvoice')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Tax Invoice</span>
                </a>
            </li-->
        </ul>
        @endhasrole
        @hasanyrole('admin|middle_man|supervisor|lead_supervisor|first_man|financer|sales|csr|bd|bdm|hr|hub_manager|data_analyst|head_of_account')
        <div class="pcoded-navigation-label">System</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="active">
                <a href="{{route('adminDashboard')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                    <span class="pcoded-mtext">AHL Dashboard</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @hasanyrole('admin')
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Limit Management</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('createParcelLimit')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create Limit</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('listParcelLimit')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Limit List</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole
            @hasanyrole('admin|csr|data_analyst')
            <li>
                <a href="{{route('shipperAdviseReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Shipper Advise Report</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|supervisor|lead_supervisor|sales|csr|hub_manager|data_analyst')
            <li>
                <a href="{{route('parcelAggingReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Parcel Agging Report</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|first_man|hub_manager|middle_man')
            <li class="">
                <a href="{{route('vendorParcelsReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Daily Vendor Booking & Pickup Report</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|first_man|bd|bdm|hub_manager')
            <li class="">
                <a href="{{route('vendorParcelCount')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Vendor Parcel Count</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|first_man|bd|bdm|hub_manager|data_analyst')
            <li class="">
                <a href="{{route('pickupParcelCount')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Picker Pickup Count</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|first_man|bd|bdm|data_analyst')
            <li class="">
                <a href="{{route('pickupVendorParcelCount')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Vendor Pickup Count</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|hub_manager|bd|bdm|csr|data_analyst')
            <li class="">
                <a href="{{route('deliveryRatio')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Delivery Ratio</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|csr')
            <li class="">
                <a href="{{route('awaitingParcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Cancel All Parcels</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Complain Section</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('pendingComplain')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Pending Complains <label class="badge badge-inverse-danger"> {{ App\Models\Complain::where('status', 1)->count() }}</label></span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('inProgressComplain')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">In-Progress Complains <label class="badge badge-inverse-danger"> {{ App\Models\Complain::where('status', 2)->count() }}</label></span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('resolvedComplain')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Resolved Complains</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole
            @hasanyrole('admin|supervisor|lead_supervisor|csr|hub_manager')
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Control Tower Section</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('pendingRemarksList')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Pending Remarks Parcels <label class="badge badge-inverse-danger"> {{ App\Models\OrderAssigned::whereDate('created_at', now())->where('remarks_status',0)->whereIn('trip_status_id', [5,6])->where('status', 0)->whereHas('orderDetail',function($query) {
                                $query->whereIn('order_status',[16,17]);
                            })->count() }}</label></span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('completeRemarksList')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Complete Remarks Parcels</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole
            @hasanyrole('admin|first_man|csr')
            @hasanyrole('admin|first_man')
            <li class="">
                <a href="{{route('vendorPickupRequestList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <!-- <label class="badge badge-inverse-danger"> {{ $request_count }} </label> -->
                    <span class="pcoded-mtext">Pickup Requests <label class="badge badge-inverse-danger"> {{ $request_count }} <!-- from view composer --> </label></span>
                </a>
            </li>
            <li class="">
                <a href="{{route('pickerRider')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Assign Picker To Vendor</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|first_man')
            <li class="">
                <a href="{{route('assignedRequestList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Picker Assigned Request</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|csr')
            <li class="">
                <a href="{{route('ahlShiperAdvise')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Shiper Advise <label class="badge badge-inverse-danger"> {{ $advise_parcels_count }} <!-- from view composer --> </label></span>
                </a>
            </li>
            @endhasanyrole
            @role('first_man')
            <li class="">
                <a href="{{route('scanCancelledParcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Return to Vendor In-Progress Scanner</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('returnToVendorInProgressList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Return to Vendor In-Progress List</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('cancelledParcels')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Cancelled Parcels</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('returnToVendorList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Return To Vendor</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('firstManPickUp')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Geneate Pickup Request</span>
                </a>
            </li>
            @endrole
            @role('admin')
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Parcels Status</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('currentStatusReport')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Current Parcels Status </span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>            
                </ul>
            </li>
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Tagline Management</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('createTagLine')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create New Tagline </span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('TagLineList')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Tagline List </span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>            
                </ul>
            </li>
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">City Management</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('city')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create New City </span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('cityList')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">City List </span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>            
                </ul>
            </li>
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Sub Area Management</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('area')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create New Area </span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('areaList')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Area List </span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>            
                </ul>
            </li>
            <li>
                <a href="{{route('packing')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Packing Management</span>
                </a>
            </li>
            <li>
                <a href="{{route('timeIndex')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Time Management</span>
                </a>
            </li>
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Weight Management</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('weightIndex')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">AHL Weights</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('ahlWeightCreate')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>   
                </ul>
            </li>
            @endrole
            @endhasanyrole
        </ul>
        @endhasanyrole
        @hasanyrole('admin|bdm|hub_manager|lead_supervisor|first_man|supervisor|bd|csr|hr')
        <div class="pcoded-navigation-label">Staff Management</div>
        <ul class="pcoded-item pcoded-left-item">
            @hasanyrole('admin|bdm')
            <li class="">
                <a href="{{route('salesStaffList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Assign Sale to Vendor</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li class="">
                <a href="{{route('csrStaffList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Assign CSR to Vendor</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|hub_manager')
            <li class="">
                <a href="{{route('pickupStaffList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Assign P.Supervisor to Vendor</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|hub_manager|lead_supervisor')
            <li class="">
                <a href="{{route('supervisorStaffList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Riders to Supervisor</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('admin|hub_manager')
            <li class="">
                <a href="{{route('pickerStaffList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Picker to Firstman</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('first_man')
            <li class="">
                <a href="{{route('pickupAssignedVendors')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Assigned Vendors List</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li class="">
                <a href="{{route('pickupAssignedRiders')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Assigned Pickup Rider</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('supervisor')
            <li class="">
                <a href="{{route('supervisorAssignedRiders')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Assigned Riders List</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('csr')
            <li class="">
                <a href="{{route('csrAssignedVendors')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Assigned Vendors List</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('bd|bdm')
            <li class="">
                <a href="{{route('salesAssignedVendors')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Assigned Vendors List</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('supervisor|lead_supervisor|admin|first_man|bdm|hr|hub_manager')
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">AHL Staff Management</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <!-- <li class=" ">
                        <a href="{{route('createVendor')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create Vendor Editor</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li> -->
                    @hasanyrole('hub_manager|admin|first_man|bdm|hr')
                    <li class=" ">
                        <a href="{{route('createStaff')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create Staff</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    @endhasanyrole
                    @hasanyrole('supervisor|lead_supervisor|admin')
                    <li class=" ">
                        <a href="{{route('assignCity')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Assign City To Rider</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    @endhasanyrole
                    <li class=" ">
                        <a href="{{route('staffList')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Staff Lists</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('blockStaffList')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Block Staff Lists</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole
        </ul>
        @endhasanyrole
        @hasanyrole('admin|middle_man|supervisor|lead_supervisor|first_man|sales|bd|bdm|hr|hub_manager|csr|data_analyst')
        <div class="pcoded-navigation-label">Admin System</div>
        <ul class="pcoded-item pcoded-left-item">
            @hasanyrole('middle_man|admin|hub_manager|data_analyst')
            <li class="">
                <a href="{{route('scanHistory')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Rack Balancing</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('scanHistoryReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Rack Balancing Report</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('middle_man')
            <li class="">
                <a href="{{route('scanParcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Middle Man Scaner</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('enRouteScanParcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">En-Route Scanner</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('reattemptParcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Reattempt Parcel Scanner</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('cancelByRiderParcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Cancel Parcel Scanner</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('reattemptParcels')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Reattempt Parcels</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('cancelledParcels')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Cancelled Parcels</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('addWeight')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Add New Weights</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('supervisor|lead_supervisor')
            <li class="">
                <a href="{{route('riderParcels')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Rider Parcel List</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('hub_manager|supervisor|lead_supervisor')
            <li class="">
                <a href="{{route('supervisorScanParcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Supervisor Scaner</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('supervisorScanHistory')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Supervisor Scan History</span>
                </a>
            </li>
            <!-- <li class="">
                <a href="{{route('requestReattempt')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Reattempt Request</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('cancelledParcel')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Cancelled Parcel</span>
                </a>
            </li> -->

            <li class="">
                <a href="{{route('cashCollection')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Cash Collection</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('supervisor|lead_supervisor|hub_manager|admin|csr|data_analyst')
            <li class="">
                <a href="{{route('riderParcelsReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Riders Parcel Report</span>
                </a>
            </li>
            <!--<li class="">-->
            <!--    <a href="{{route('bulkStatusView')}}" class="waves-effect waves-dark">-->
            <!--        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>-->
            <!--        <span class="pcoded-mtext">Bulk Delivered Parcels</span>-->
            <!--    </a>-->
            <!--</li>-->
            @endhasanyrole
            @hasanyrole('supervisor|lead_supervisor|admin|first_man|sales|bd|bdm|hr|hub_manager')
            @hasanyrole('admin|sales|bd|bdm')
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Vendor Management</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="{{route('createVendor')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Create Vendor</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" ">
                        <a href="{{route('vendorList')}}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Vendors Lists</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole
            @endhasanyrole
        </ul>
        @endhasanyrole
        @hasanyrole('first_man|csr|hub_manager')
        <div class="pcoded-navigation-label">Reverse Pickup Management</div>
        <ul class="pcoded-item pcoded-left-item">
            <li>
                <a href="{{route('pendingReversePickupRequest')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Pending Reverse <label class="badge badge-inverse-danger"> {{ App\Models\Order::where('order_status', 1)->where('parcel_nature', 2)->count() }} <!-- from view composer --> </label></span>
                </a>
            </li>
            <li>
                <a href="{{route('scanReverseParcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Scan Received Reverse</span>
                </a>
            </li>
            <li>
                <a href="{{route('receivedReversePickupRequest')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Received Reverse <label class="badge badge-inverse-danger"> {{ App\Models\Order::where('order_status', 3)->where('parcel_nature', 2)->count() }} <!-- from view composer --> </label></span>
                </a>
            </li>
            <li>
                <a href="{{route('firstmanScanParcelList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Scan Dispatch Reverse</span>
                </a>
            </li>
            <li>
                <a href="{{route('dispatchedReversePickupRequest')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Dispatched Reverse <label class="badge badge-inverse-danger"> {{ App\Models\Order::where('order_status', 5)->where('parcel_nature', 2)->count() }} <!-- from view composer --> </label></span>
                </a>
            </li>
            <li>
                <a href="{{route('deliveredReversePickupRequest')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Delivered Reverse Pickup</span>
                </a>
            </li>
            <li>
                <a href="{{route('cancelReversePickupRequest')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Cancel Reverse Pickup</span>
                </a>
            </li>
        </ul>
        @endhasanyrole
        @hasanyrole('admin|first_man|hr')
        <div class="pcoded-navigation-label">Flyer Request Management</div>
        <ul class="pcoded-item pcoded-left-item">
            <li>
                <a href="{{route('flyerIndex')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Flyer Management</span>
                </a>
            </li>
            <li>
                <a href="{{route('pendingFlyerRequest')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Flyer Requests <label class="badge badge-inverse-danger"> {{ App\Models\FlyerRequest::where('status', 1)->count() }} <!-- from view composer --> </label></span>
                </a>
            </li>
            <li>
                <a href="{{route('acceptedFlyerRequest')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Accepted Requests <label class="badge badge-inverse-danger"> {{ App\Models\FlyerRequest::where('status', 2)->count() }} <!-- from view composer --> </label></span>
                </a>
            </li>
            <li>
                <a href="{{route('dispatchFlyerRequest')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">En-Route Requests <label class="badge badge-inverse-danger"> {{ App\Models\FlyerRequest::where('status', 3)->count() }} <!-- from view composer --> </label></span>
                </a>
            </li>
            <li>
                <a href="{{route('delvieredFlyerRequest')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Delivered Flyer Requests</span>
                </a>
            </li>
        </ul>
        @endhasanyrole
        @hasanyrole('cashier|admin|sales')
        <div class="pcoded-navigation-label">Financials</div>
        <ul class="pcoded-item pcoded-left-item">
            @role('cashier')
            <li class="active">
                <a href="{{route('adminDashboard')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                    <span class="pcoded-mtext">Cashier Dashboard</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li class="">
                <a href="{{route('cashierCollectionReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Cashier Collection Report</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li class="">
                <a href="{{route('riderCashReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>D</b></span>
                    <span class="pcoded-mtext">Rider Cash Report</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>

            <!-- <li class="">
                <a href="route('staffFinancials')" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Pay Staff Financials</span>
                </a>
            </li> -->

            <li class="">
                <a href="{{route('staffFinancials',['staff'=>'picker'])}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Pay Picker Financials</span>
                </a>
            </li>

            <li class="">
                <a href="{{route('staffFinancials',['staff'=>'rider'])}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Pay Rider Financials</span>
                </a>
            </li>

            <li class="">
                <a href="{{route('cashCollection')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Cash Collection</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('riderParcelsReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Riders Parcel Report</span>
                </a>
            </li>
            @endrole
            @hasanyrole('financer|admin|head_of_account')
            <li class="">
                <a href="{{route('vendorFinancials')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Pay Vendor Financials</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('financer|admin|sales')
            <li class="">
                <a href="{{route('riderAutomaticDispatchReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Vendor Parcel Report</span>
                </a>
            </li>
            @endhasanyrole
            @role('admin')

            <!-- <li class="">
                <a href="{{route('newVendorFinancials')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">New Pay Vendor Financials</span>
                </a>
            </li> -->

            <!-- <li class="">
                <a href="{{route('vendorFinancialReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Vendor Financial Report</span>
                </a>
            </li> -->

            <!--li class="">
                <a href="{{route('vendorTaxInvoice')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Tax Invoice</span>
                </a>
            </li-->
            @endrole
            
            <!-- <li class="">
                <a href="route('staffFinancialReport')" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Staff Financial Report</span>
                </a>
            </li> -->
            @hasanyrole('cashier|admin')
            <li class="">
                <a href="{{route('riderCashCollectionList')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Rider Cash List</span>
                </a>
            </li>

            <li class="">
                <a href="{{route('staffFinancialReport',['staff'=>'picker'])}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Picker Financial Report</span>
                </a>
            </li>

            <li class="">
                <a href="{{route('staffFinancialReport',['staff'=>'rider'])}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Rider Financial Report</span>
                </a>
            </li>
            @endhasanyrole
        </ul>
        @endhasanyrole

        @hasanyrole('financer|admin|sales|hr|hub_manager|cashier|head_of_account')
        <div class="pcoded-navigation-label">Financer</div>
        <ul class="pcoded-item pcoded-left-item">
            @hasanyrole('financer|head_of_account')
            <li class="">
                <a href="{{route('vendorFinancials')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Pay Vendor Financials</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('riderAutomaticDispatchReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Vendor Parcel Report</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('financer|admin|sales|head_of_account')
            <li class="">
                <a href="{{route('vendorFinancialReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Vendor Financial Report</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('financer|admin|head_of_account')
            <li class="">
                <a href="{{route('reportPRA')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">PRA Report</span>
                </a>
            </li>
            <li class="">
                <a href="{{route('vendorPaymentReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Vendor Payment Report</span>
                </a>
            </li>
            @endhasanyrole
            @hasanyrole('financer|admin|cashier|head_of_account|sales|hub_manager')
            <li class="">
                <a href="{{route('riderDispatchReport')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Rider Dispatch Report</span>
                </a>
            </li>
            @endhasanyrole
            <li class="">
                <a href="{{route('vendorDispatchSheet')}}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                    <span class="pcoded-mtext">Quality Service Report (QSR)</span>
                </a>
            </li>
        </ul>
        @endhasanyrole
        @hasanyrole('middle_man')
            <div class="pcoded-navigation-label">Sag Management</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{route('checkSag')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-search"></i><b>BC</b></span>
                        <span class="pcoded-mtext">Open Received Sag</span>
                    </a>
                </li>
                <li class="">
                    <a href="{{route('inProgressSag')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                        <span class="pcoded-mtext">In-Progress Sag</span>
                    </a>
                </li>
                <li class="">
                    <a href="{{route('closedSag')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                        <span class="pcoded-mtext">Closed Sag</span>
                    </a>
                </li>
            </ul>
            <div class="pcoded-navigation-label">Bilty Management</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{route('createBilty')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                        <span class="pcoded-mtext">Create New Bilty</span>
                    </a>
                </li>
                <li class="">
                    <a href="{{route('checkBilty')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-search"></i><b>BC</b></span>
                        <span class="pcoded-mtext">Open Received Bilty</span>
                    </a>
                </li>
                <li class="">
                    <a href="{{route('inProgressBilty')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                        <span class="pcoded-mtext">In-Progress Bilty</span>
                    </a>
                </li>
                <li class="">
                    <a href="{{route('closedBilty')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                        <span class="pcoded-mtext">Closed Bilty</span>
                    </a>
                </li>
            </ul>
        @endhasanyrole
        @hasanyrole('middle_man|sales|bd|bdm|csr|data_analyst')
        <div class="pcoded-navigation-label">Dispatch Sheet</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{route('vendorDispatchSheet')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                        <span class="pcoded-mtext">Quality Service Report (QSR)</span>
                    </a>
                </li>
            </ul>
        @endhasanyrole
        @hasanyrole('middle_man|hub_manager')
            <div class="pcoded-navigation-label">Reporting</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{route('totalParcelsCN')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                        <span class="pcoded-mtext">Total Parcels with CN</span>
                    </a>
                </li>
                @hasanyrole('middle_man')
                <li class="">
                    <a href="{{route('midmenTodayReport')}}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                        <span class="pcoded-mtext">Middle Men Rack Scanning</span>
                    </a>
                </li>
                @endhasanyrole
            </ul>
        </div>
        @endhasanyrole
    </div>
</nav>

@section('search-custom-js')
<script type="text/javascript">
    $(document).ready(function() {
        $('#left_search_parcel').on('keypress', function (event) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            
            var searchParcel = $(this).val();
            // alert(searchParcel);
            var referenceLength = searchParcel.length;
            // alert(referenceLength);
            
            var flag = 'true';
            if(referenceLength > 17 || referenceLength < 17 ){
                var flag = 'false';
                //alert('Invalid Reference Number Format');
            }
            // alert('yes');
            
            if(keycode == '13' && flag == 'true'){ 
                event.preventDefault();
                if(searchParcel != ''){
                    // alert(searchParcel);
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                    $.post( "/search-parcel", {_token: CSRF_TOKEN, order_reference: searchParcel})
                      .done(function( data ) {
                            console.log(data);
                            //var w = window.open("http://127.0.0.1:8000/track-order", "_blank","toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=800,height=800");
                            var w = window.open('/track-order','track-order');
                            //var w = window.open("http://127.0.0.1:8000/track-order", "_blank");
                            //w.document.open();
                            w.document.write(data);
                            w.document.close();
                      });
                }else{
                    alert('Please Add Parcel Reference Number ');
                }
            }/*else{
                alert('Invalid Reference Number Format.');
                return false;
                event.preventDefault();
            }*/
        });
    });
    $(document).ready(function() {
        $('#id_search_parcel').on('keypress', function (event) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            
            var searchParcelID = $(this).val();
            var referenceLength = searchParcelID.length;
            
            var flag = 'true';
            if(referenceLength < 0 ){
                var flag = 'false';
                //alert('Invalid Reference Number Format');
            }
            
            if(keycode == '13' && flag == 'true'){ 
                event.preventDefault();
                if(searchParcelID != ''){
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                    $.post( "/search-order", {_token: CSRF_TOKEN, order_id: searchParcelID})
                      .done(function( data ) {
                            console.log(data);
                            var w = window.open('/track-order','track-order');
                            w.document.write(data);
                            w.document.close();
                      });
                }else{
                    alert('Please Add Parcel Order ID ');
                }
            }
        });
    });
    $(document).ready(function() {
        $('#mobile_search_parcel').on('keypress', function (event) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            
            var mobileNumber = $(this).val();
            var referenceLength = mobileNumber.length;
            
            var flag = 'true';
            if(referenceLength < 0 ){
                var flag = 'false';
                // alert('Invalid Mobile Number Format');
            }
            
            if(keycode == '13' && flag == 'true'){ 
                event.preventDefault();
                if(mobileNumber != ''){
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                    $.post( "/search-parcel-through-mobile", {_token: CSRF_TOKEN, mobile: mobileNumber})
                      .done(function( data ) {
                            console.log(data);
                            var w = window.open('/track-order','track-order');
                            w.document.write(data);
                            w.document.close();
                      });
                }else{
                    alert('Please Add Mobile Number ');
                }
            }
        });
    });
</script>
@endsection