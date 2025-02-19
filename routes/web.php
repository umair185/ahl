<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManageRoleController;
use App\Http\Controllers\AssignRequestController;

use App\Http\Controllers\PackingController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\RiderController;
use App\Http\Controllers\MiddlemanController;
use App\Http\Controllers\AhlWeightController;
use App\Http\Controllers\FirstManController;
use App\Http\Controllers\FinancerController;
use App\Http\Controllers\FlyerController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\SubAreaController;
use App\Http\Controllers\ComplainController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*clear cache and config*/



/* Migration */
Route::group(['middleware' => 'ipcheck'], function () {
   
    Route::get('/clear', function () {
    Artisan::call('cache:clear');
    dump('Cache Clear!');
    Artisan::call('config:clear');
    dump('Config Clear!');
    Artisan::call('route:clear');
    dump('Route Clear!');
    
    //when upload new project and if 419 page expired issue on server
    //Artisan::call('config:cache');
    //dump('Config Cache Clear!');
    
    Artisan::call('view:clear');
    dump('View Clear!');
    
    //Artisan::call('optimize:clear');
    //Artisan::call('optimize');
    //dump('Optimize');
    return back();
});
    Route::get('/migrate', function () {
        
        Artisan::call('migrate');
        dump('Migrate');
        return back();
    });
    /*Barcode Testing */
    Route::get('qrcode', function () {
    return view('vendor/parcels-qr');
    return QrCode::size(100)
        ->generate('MyNotePaper');
});
    /*Barcode*/
    Route::view('/barcode-view', 'vendor/barcode-view');
    Route::post('/barcode', [VendorController::class, 'barcode'])->name('barcode');
    Route::get('barcodes', function(){
        echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG('1617092555899', 'C128A',1,33,array(1,1,1),true) . '" alt="barcode"   />';
    });

});
/*Login*/
Route::group(['middleware' => ['guest' , 'ipcheck'] ], function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authLogin'])->name('authLogin');
    Route::get('/', function () {
        return view('/login');
    });

    //MANAGE ROLE
    //Route::get('/manage-role', [ManageRoleController::class, 'manageRole'])->name('manageRole');
});

Route::group(['middleware' => ['auth','userIsActive', 'ipcheck'] ], function () {
    Route::get('/check-otp', [AuthController::class, 'checkOTP'])->name('checkOTP');
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP'])->name('verifyOTP');
    Route::get('/resend-otp', [AuthController::class, 'resendOTP'])->name('resendOTP');
});

Route::get('/call-response', [BarcodeController::class, 'getCall']);

Route::group(['middleware' => ['auth','userIsActive','otpcheck','ipcheck','otpexpiry'] ], function () {
    Route::get('/dashboard', [VendorController::class, 'dashboard'])->name('index');
    Route::get('/admin-dashboard', [HomeController::class, 'adminDashboard'])->name('adminDashboard');
});


//Auth
Route::group(['middleware' => ['auth','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/track-order', function () {
        //
    });

    //sag list
    Route::get('/in-progress-sag', [BarcodeController::class, 'inProgressSag'])->name('inProgressSag');
    Route::get('/sag-parcel-list/{id}', [BarcodeController::class, 'inProgressSagParcelList'])->name('inProgressSagParcelList');
    Route::get('/closed-sag', [BarcodeController::class, 'closedSag'])->name('closedSag');

    //bilty list
    Route::get('/in-progress-bilty', [BarcodeController::class, 'inProgressBilty'])->name('inProgressBilty');
    Route::get('/bilty-sag-list/{id}', [BarcodeController::class, 'biltySagList'])->name('biltySagList');
    Route::get('/closed-bilty', [BarcodeController::class, 'closedBilty'])->name('closedBilty');

    Route::get('/password-update/{id}', [StaffController::class, 'passwordUpdate'])->name('passwordUpdate');
    Route::post('/password-update', [StaffController::class, 'updatePassword'])->name('updatePassword');

    Route::post('/get-rider-info', [RiderController::class, 'getRiderInfo'])->name('get-rider-info');

    //Parcel Limit management
    Route::get('create-parcel-limit', [ComplainController::class, 'createParcelLimit'])->name('createParcelLimit');
    Route::post('save-parcel-limit', [ComplainController::class, 'saveParcelLimit'])->name('saveParcelLimit');
    Route::get('parcel-limit-list', [ComplainController::class, 'listParcelLimit'])->name('listParcelLimit');
    Route::get('edit-parcel-limit/{id}', [ComplainController::class, 'editParcelLimit'])->name('editParcelLimit');
    Route::post('update-parcel-limit', [ComplainController::class, 'updateParcelLimit'])->name('updateParcelLimit');

    //shipper advise report
    Route::get('shipper-advise-report', [FirstManController::class, 'shipperAdviseReport'])->name('shipperAdviseReport');

    //Agging Report
    Route::get('parcel-agging-report', [HomeController::class, 'parcelAggingReport'])->name('parcelAggingReport');

    //complain management
    //vendor side
    Route::get('create-complain', [ComplainController::class, 'createVendorComplain'])->name('createVendorComplain');
    Route::post('save-complain', [ComplainController::class, 'saveVendorComplain'])->name('saveVendorComplain');
    Route::get('pending-complain', [ComplainController::class, 'pendingVendorComplain'])->name('pendingVendorComplain');
    Route::get('delete-complain/{id}', [ComplainController::class, 'deleteVendorComplain'])->name('deleteVendorComplain');
    Route::get('in-progress-complain', [ComplainController::class, 'inProgressVendorComplain'])->name('inProgressVendorComplain');
    Route::get('resolved-complain', [ComplainController::class, 'resolvedVendorComplain'])->name('resolvedVendorComplain');

    //admin side
    Route::get('all-pending-complain', [ComplainController::class, 'pendingComplain'])->name('pendingComplain');
    Route::get('action-complain/{id}', [ComplainController::class, 'actionComplain'])->name('actionComplain');
    Route::post('save-action-complain', [ComplainController::class, 'saveActionComplain'])->name('saveActionComplain');
    Route::get('all-in-progress-complain', [ComplainController::class, 'inProgressComplain'])->name('inProgressComplain');
    Route::get('resolve-progress-complain/{id}', [ComplainController::class, 'saveResolvedComplain'])->name('saveResolvedComplain');
    Route::get('all-resolved-complain', [ComplainController::class, 'resolvedComplain'])->name('resolvedComplain');
    Route::get('revert-complain/{id}', [ComplainController::class, 'revertComplain'])->name('revertComplain');

    Route::get('/awaiting-parcels-list', [OrderController::class, 'awaitingParcelList'])->name('awaitingParcelList');
    Route::post('/awaiting-parcels-list-cancel', [OrderController::class, 'awaitingParcelListCancel'])->name('awaitingParcelListCancel');
    Route::post('/bulk-invoice-delete', [CashierController::class, 'bulkInvoiceDelete'])->name('bulkInvoiceDelete');

    Route::get('/delayed-order-status', [RiderController::class, 'delayedOrders'])->name('delayedOrders');

    Route::post('/getCities', [VendorController::class, 'getCities'])->name('getCities');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*for vendor and admin*/
    Route::get('/vendor-complete-pickup-requests', [VendorController::class, 'completePickupRequest'])->name('completePickupRequest');
    Route::get('/pickup-request-scan-parcel-lists/{id}', [VendorController::class, 'pickupRequestScanParcelList'])->name('pickupRequestScanParcelList');
    Route::get('/pickup-request-scan-parcel-lists-pdf/{id}', [VendorController::class, 'pickupRequestScanParcelListpdf'])->name('pickupRequestScanParcelListpdf');

    Route::post('/cancel-by', [HomeController::class, 'cancelBy'])->name('cancelBy');

    Route::get('/scan-history', [HomeController::class, 'scanHistory'])->name('scanHistory')->middleware(['role:middle_man|hub_manager|admin|data_analyst']);
    Route::get('/scan-order-detail', [HomeController::class, 'scanOrderDetail'])->name('scanOrderDetail')->middleware(['role:middle_man|supervisor|lead_supervisor|admin|data_analyst']);
    Route::post('/rack-cancel-parcels', [HomeController::class, 'rackCancelParcel'])->name('rack-cancel-parcels')->middleware(['role:middle_man|hub_manager|admin|data_analyst']);
    Route::post('/rack-reattempt-parcels', [HomeController::class, 'rackReattemptParcel'])->name('rack-reattempt-parcels')->middleware(['role:middle_man|hub_manager|admin|data_analyst']);
    Route::post('/save-reattempt-rack-parcel', [HomeController::class, 'reattemptRackParcel'])->name('save-reattempt-rack-parcel')->middleware(['role:middle_man|hub_manager|admin|data_analyst']);
    Route::post('/save-cancel-rack-parcel', [HomeController::class, 'cancelRackParcel'])->name('save-cancel-rack-parcel')->middleware(['role:middle_man|hub_manager|admin|data_analyst']);
    Route::get('/scan-history-report', [HomeController::class, 'scanHistoryReport'])->name('scanHistoryReport')->middleware(['role:middle_man|hub_manager|admin|data_analyst']);
    Route::post('/rack-balance-remarks', [HomeController::class, 'rackBalanceRemarks'])->name('rack-balance-remarks')->middleware(['role:middle_man|hub_manager|admin|data_analyst']);
    
    Route::post('/search-parcel', [HomeController::class, 'searchParcel'])->name('searchParcel');
    Route::get('/search-order-view', [HomeController::class, 'searchOrderView'])->name('searchOrderView');
    Route::post('/search-order', [HomeController::class, 'searchParcelOrder'])->name('searchParcelOrder');
    Route::post('/search-parcel-through-mobile', [HomeController::class, 'searchParcelThroughMobile'])->name('searchParcelThroughMobile');

    /*Status Report */
    Route::get('/current-status-report', [HomeController::class, 'currentStatusReport'])->name('currentStatusReport');
    Route::get('/status-report/{status?}', [HomeController::class, 'statusReport'])->name('statusReport');
    Route::post('/bulk-cancel-by-vendor', [HomeController::class, 'bulkCancelByVendor'])->name('bulkCancelByVendor');
    Route::get('/warehouse-status-report/{status?}', [HomeController::class, 'warehouseStatusReport'])->name('warehouseStatusReport');
    Route::get('/delivered-status-report/{status?}', [HomeController::class, 'deliveredStatusReport'])->name('deliveredStatusReport');
    Route::get('/cancel-status-report/{status?}', [HomeController::class, 'cancelStatusReport'])->name('cancelStatusReport');
    Route::get('/return-status-report/{status?}', [HomeController::class, 'returnStatusReport'])->name('returnStatusReport');
    Route::get('/return-in-progress-status-report/{status?}', [HomeController::class, 'returnInProgressStatusReport'])->name('returnInProgressStatusReport');
    Route::post('/status-report', [HomeController::class, 'statusReportData'])->name('statusReportData');
    Route::get('/order-parcel-detail/{parcel}', [OrderController::class, 'parcelDetail'])->name('parcelDetail');
    Route::post('/upload-photo/{id}', [OrderController::class, 'uploadPhoto'])->name('uploadPhoto');
    

    /* Admin Status Report */
    Route::get('/awaiting-admin-report/{status?}', [HomeController::class, 'awaitingAdminReport'])->name('awaitingAdminReport');
    Route::get('/pickup-admin-report/{status?}', [HomeController::class, 'pickupAdminReport'])->name('pickupAdminReport');
    Route::get('/at-ahl-admin-report/{status?}', [HomeController::class, 'atAhlAdminReport'])->name('atAhlAdminReport');
    Route::get('/at-ahl-delayed-admin-report/{status?}', [HomeController::class, 'atAhlDelayedAdminReport'])->name('atAhlDelayedAdminReport');
    Route::get('/supervisor-admin-report/{status?}', [HomeController::class, 'supervisorAdminReport'])->name('supervisorAdminReport');
    Route::get('/dispatched-admin-report/{status?}', [HomeController::class, 'dispatchedAdminReport'])->name('dispatchedAdminReport');
    Route::get('/delivered-admin-report/{status?}', [HomeController::class, 'deliveredAdminReport'])->name('deliveredAdminReport');
    Route::get('/request-reattempt-admin-report/{status?}', [HomeController::class, 'requestforReattemptAdminReport'])->name('requestforReattemptAdminReport');
    Route::get('/reattempt-admin-report/{status?}', [HomeController::class, 'ReattemptAdminReport'])->name('ReattemptAdminReport');
    Route::get('/cancel-admin-report/{status?}', [HomeController::class, 'cancelAdminReport'])->name('cancelAdminReport');
    Route::get('/return-admin-report/{status?}', [HomeController::class, 'returnAdminReport'])->name('returnAdminReport');
    Route::get('/return-in-progress-admin-report/{status?}', [HomeController::class, 'returnInProgressAdminReport'])->name('returnInProgressAdminReport');
    Route::get('/cancel-ahl-admin-report/{status?}', [HomeController::class, 'cancelAhlAdminReport'])->name('cancelAhlAdminReport');
    Route::get('/cancel-vendor-admin-report/{status?}', [HomeController::class, 'cancelVendorAdminReport'])->name('cancelVendorAdminReport');
    Route::get('/void-admin-report/{status?}', [HomeController::class, 'voidAdminReport'])->name('voidAdminReport');
    Route::get('/replace-admin-report/{status?}', [HomeController::class, 'replaceAdminReport'])->name('replaceAdminReport');
    Route::get('/rider-reattempt-admin-report/{status?}', [HomeController::class, 'riderReattemptAdminReport'])->name('riderReattemptAdminReport');
    Route::get('/cancel-by-rider-admin-report/{status?}', [HomeController::class, 'cancelbyRiderAdminReport'])->name('cancelbyRiderAdminReport');
    Route::get('/cancel-by-supervisor-admin-report/{status?}', [HomeController::class, 'cancelbySupervisorAdminReport'])->name('cancelbySupervisorAdminReport');


    /*Change Staff Status Only Admin, Supervisor, Vendor Admin*/
    Route::get('/staff-status-change/{id}', [AuthController::class, 'staffStatusChange'])->name('staffStatusChange')->middleware(['role:supervisor|lead_supervisor|admin|vendor_admin|first_man|hr|hub_manager']);

    //Edit parcel by admin
    Route::get('/edit-parcel/{id}', [FirstManController::class, 'editParcel'])->name('editParcel');
    Route::post('/edit-parcel', [FirstManController::class, 'updateParcel'])->name('updateParcel');

    Route::get('/warehouse-parcel/{id}', [FirstManController::class, 'moveToWarehouse'])->name('moveToWarehouse');
    
    //awaiting Parcel
    Route::get('/vendor-parcels-report', [HomeController::class, 'vendorParcelsReport'])->name('vendorParcelsReport');
    Route::get('/awaiting-parcels-count', [HomeController::class, 'vendorParcelCount'])->name('vendorParcelCount');
    Route::get('/pickup-parcels-count', [HomeController::class, 'pickupParcelCount'])->name('pickupParcelCount');
    Route::get('/pickup-vendor-parcels-count', [HomeController::class, 'pickupVendorParcelCount'])->name('pickupVendorParcelCount');
    Route::post('/pickup-vendor-parcels-count-download', [HomeController::class, 'pickupVendorParcelCountDownload'])->name('pickupVendorParcelCountDownload');
    Route::get('/cashier-collection-report', [HomeController::class, 'cashierCollectionReport'])->name('cashierCollectionReport');
    Route::get('/rider-cash-report', [HomeController::class, 'riderCashReport'])->name('riderCashReport');
    Route::match(['get', 'post'],'/delivery-ratio', [BookingController::class, 'deliveryRatio'])->name('deliveryRatio');
    
    //additional note
    Route::get('/edit-additional_note/{id}', [OrderController::class, 'editAdditionalNote'])->name('editAdditionalNote');
    Route::post('/save-additional-note', [OrderController::class, 'saveAdditionalNote'])->name('saveAdditionalNote');
});
Route::group(['middleware' => ['auth','role:admin|financer|head_of_account','userIsActive' ,'ipcheck']], function () {
    Route::match(['get', 'post'],'/vendor-financials', [CashierController::class, 'vendorFinancials'])->name('vendorFinancials');
    Route::post('/pay-vendor-financials', [CashierController::class, 'payVendorFinancials'])->name('payVendorFinancials');
    Route::get('/download-commission-orders', [FinancerController::class, 'calculateCommissionOrders'])->name('calculateCommissionOrders');
    Route::get('/download-delivered-orders', [FinancerController::class, 'calculateDeliveredOrders'])->name('calculateDeliveredOrders');
    Route::get('/download-rtv-orders', [FinancerController::class, 'calculateRtvOrders'])->name('calculateRtvOrders');
    Route::get('edit-vendor-financials/{id}', [CashierController::class, 'editVendorFinancials'])->name('editVendorFinancials');
    Route::post('/pay-vendor-financials-update', [CashierController::class, 'payVendorFinancialsUpdate'])->name('payVendorFinancialsUpdate');
});

Route::group(['middleware' => ['auth','role:admin|hr|lead_supervisor|hub_manager','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/supervisor-staff-list', [VendorController::class, 'supervisorStaffList'])->name('supervisorStaffList');
    Route::get('/assign-supervisor/{id}', [VendorController::class, 'assignSupervisor'])->name('assignSupervisor');
    Route::post('/save-assign-supervisor', [VendorController::class, 'saveAssignSupervisor'])->name('saveAssignSupervisor');

    Route::get('/picker-staff-list', [VendorController::class, 'pickerStaffList'])->name('pickerStaffList');
    Route::get('/assign-picker-staff/{id}', [VendorController::class, 'assignPickerStaff'])->name('assignPickerStaff');
    Route::post('/save-assign-picker-staff', [VendorController::class, 'saveAssignPickerStaff'])->name('saveAssignPickerStaff');
});

Route::group(['middleware' => ['auth','role:supervisor','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/supervisor-assigned-riders', [VendorController::class, 'supervisorAssignedRiders'])->name('supervisorAssignedRiders');
});

Route::group(['middleware' => ['auth','role:first_man','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/picker-assigned-riders', [VendorController::class, 'pickupAssignedRiders'])->name('pickupAssignedRiders');
});

//ROLE ADMIN
Route::group(['middleware' => ['auth','role:admin|sales|bd|bdm|hr|supervisor|lead_supervisor|first_man','userIsActive','otpcheck','ipcheck']], function () {

    //|first_man assign parcel to rider
    //Route::get('/create-booking', [BookingController::class, 'createBooking'])->name('createBooking');
    //Route::get('/create-select-booking', [BookingController::class, 'createSelectBooking'])->name('createSelectBooking');
    
    Route::get('/get-vendor-parcel', [BookingController::class, 'vendorParcel'])->name('get-vendor-parcel');
    Route::get('/get-select-vendor-parcel', [BookingController::class, 'vendorSelectParcel'])->name('get-select-vendor-parcel');
    
    Route::get('/assignrider/{id}', [BookingController::class, 'assignRider'])->name('assignRider');
    Route::post('/save-assign-rider', [BookingController::class, 'saveAssignRider'])->name('saveAssignRider');
    
    
    //Vendor Assign POC n CSR
    Route::get('/sales-staff-list', [VendorController::class, 'salesStaffList'])->name('salesStaffList');
    Route::get('/assign-sale/{id}', [VendorController::class, 'assignSale'])->name('assignSale');
    Route::post('/save-assign-sale', [VendorController::class, 'saveAssignSale'])->name('saveAssignSale');

    Route::get('/csr-staff-list', [VendorController::class, 'csrStaffList'])->name('csrStaffList');
    Route::get('/assign-csr/{id}', [VendorController::class, 'assignCSR'])->name('assignCSR');
    Route::post('/save-assign-csr', [VendorController::class, 'saveAssignCSR'])->name('saveAssignCSR');

    //Vendor
    Route::get('/vendors', [VendorController::class, 'vendorList'])->name('vendorList');
    Route::get('/create-vendor', [VendorController::class, 'createVendor'])->name('createVendor');
    Route::post('/save-vendor', [VendorController::class, 'saveVendor'])->name('saveVendor');
    Route::get('/edit-vendor/{id}', [VendorController::class, 'editVendor'])->name('editVendor');
    Route::post('/update-vendor/{id}', [VendorController::class, 'updateVendor'])->name('updateVendor');
    Route::post('/upload-vendor-photo/{id}', [OrderController::class, 'uploadVendorPhoto'])->name('uploadVendorPhoto');
        
    Route::get('/vendors-user/{id}', [VendorController::class, 'vendorUsersList'])->name('vendorUsersList');
    Route::get('/create-editor/{id}', [VendorController::class, 'createEditor'])->name('createEditor');
    Route::post('/save-editor', [VendorController::class, 'saveVendorEditor'])->name('saveVendorEditor');

    Route::get('/update-editor/{id}', [VendorController::class, 'updateEditor'])->name('updateEditor');
    Route::post('/update-editor', [VendorController::class, 'updateVendorEditor'])->name('updateVendorEditor');
    
    /*Pay vendor Financials and report  */
    
    /*New Vendor Financials*/
    Route::match(['get', 'post'],'/new-vendor-financials', [CashierController::class, 'newVendorFinancials'])->name('newVendorFinancials');
    Route::post('/new-pay-vendor-financials', [CashierController::class, 'newPayVendorFinancials'])->name('payVendor');
    
    //Route::get('/admin-update/{id}', [AuthController::class, 'adminUpdate'])->name('adminUpdate');
    //Route::post('/admin-update', [AuthController::class, 'updateAdmin'])->name('updateAdmin');

    /*City*/
    Route::get('/add-city', [CityController::class, 'index'])->name('city');
    Route::post('/saveCity', [CityController::class, 'createCity'])->name('saveCity');
    Route::get('/city-list', [CityController::class, 'cityList'])->name('cityList');
    Route::get('/edit-city/{id}', [CityController::class, 'editCity'])->name('editCity');
    Route::post('/update-city', [CityController::class, 'updateCity'])->name('updateCity');

    /*Tagline*/
    Route::get('/add-tagline', [CityController::class, 'createTagLine'])->name('createTagLine');
    Route::post('/save-tagline', [CityController::class, 'saveTagLine'])->name('saveTagLine');
    Route::get('/tagline-list', [CityController::class, 'TagLineList'])->name('TagLineList');
    Route::get('/edit-tagline/{id}', [CityController::class, 'editTagLine'])->name('editTagLine');
    Route::post('/update-tagline', [CityController::class, 'updateTagLine'])->name('updateTagLine');

     /*Sub Area*/
     Route::get('/add-area', [SubAreaController::class, 'index'])->name('area');
     Route::post('/save-area', [SubAreaController::class, 'createArea'])->name('saveArea');
     Route::get('/area-list', [SubAreaController::class, 'areaList'])->name('areaList');
     Route::get('/edit-area/{id}', [SubAreaController::class, 'editArea'])->name('editArea');
     Route::post('/update-area', [SubAreaController::class, 'updateArea'])->name('updateArea');

    /*Packing*/
    Route::get('/packing', [PackingController::class, 'index'])->name('packing');
    Route::get('/create-packing', [PackingController::class, 'create'])->name('createPacking');
    Route::post('/save-packing', [PackingController::class, 'savePacking'])->name('savePacking');
    Route::get('/edit-packing/{id}', [PackingController::class, 'edit'])->name('editPacking');
    Route::post('/update-packing', [PackingController::class, 'saveEditPacking'])->name('saveEditPacking');
    
    /*Timing*/
    Route::get('/timing', [PackingController::class, 'timeIndex'])->name('timeIndex');
    Route::get('/create-timing', [PackingController::class, 'createTiming'])->name('createTiming');
    Route::post('/save-timing', [PackingController::class, 'saveTiming'])->name('saveTiming');
    Route::get('/edit-timing/{id}', [PackingController::class, 'editTiming'])->name('editTiming');
    Route::post('/update-timing', [PackingController::class, 'saveEditTiming'])->name('saveEditTiming');

    /*AHL WEIGHT*/
    Route::get('/ahl-weights', [AhlWeightController::class, 'index'])->name('weightIndex');
    Route::get('/ahl-weight-create', [AhlWeightController::class, 'create'])->name('ahlWeightCreate');
    Route::post('/ahl-weight-store', [AhlWeightController::class, 'store'])->name('ahlWeightStore');

    Route::get('/ahl-weight-edit/{id}', [AhlWeightController::class, 'edit'])->name('ahlWeightEdit');
    Route::post('/ahl-weight-update', [AhlWeightController::class, 'update'])->name('ahlWeightUpdate');

    /*Vendor Status Change*/
    Route::get('/vendor-status-change/{id}', [AuthController::class, 'vendorStatusChange'])->name('vendorStatusChange');

    Route::get('/vendor-commission-change/{id}', [AuthController::class, 'vendorCommissionChange'])->name('vendorCommissionChange');

    Route::get('/vendor-tax-invoice', [HomeController::class, 'vendorTaxInvoice'])->name('vendorTaxInvoice');
    Route::post('/vendor-tax-invoice-download', [HomeController::class, 'vendorTaxInvoiceDownload'])->name('vendorTaxInvoiceDownload');
    
});

Route::group(['middleware' => ['auth','role:vendor_admin|admin|financer|head_of_account','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/vendor-financial-invoice/{id}', [CashierController::class, 'indiviualTaxInvoice'])->name('indiviualTaxInvoice');
    Route::get('/vendor-financial-invoice-delete/{id}', [CashierController::class, 'VendorFinancialDelete'])->name('VendorFinancialDelete');
});

Route::group(['middleware' => ['auth','role:vendor_admin|vendor_editor','userIsActive','otpcheck','ipcheck']], function () {
    
    Route::get('/manual-order', [OrderController::class, 'manualOrder'])->name('manualOrder');
    Route::get('/profile-view', [VendorController::class, 'viewProfile'])->name('viewProfile');
    Route::post('/select-city-data', [OrderController::class, 'selectCitydata'])->name('selectCitydata');
    Route::post('/save-manual-order', [OrderController::class, 'saveManualOrder'])->name('saveManualOrder');
    Route::post('/save-printing-slip', [VendorController::class, 'savePrintingSlip'])->name('savePrintingSlip');
    Route::get('/bulk-order', [OrderController::class, 'bulkOrder'])->name('bulkOrder');
    Route::post('/save-bulk-order', [OrderController::class, 'saveBulkOrder'])->name('saveBulkOrder');
    Route::get('/pickup-request', [VendorController::class, 'pickupRequestList'])->name('pickupRequest');
    Route::get('/create-pickup-request', [VendorController::class, 'createPickup'])->name('createPickupRequest');
    Route::post('/generate-pickup', [VendorController::class, 'generatePickupRequest'])->name('generatePickupRequest');

    Route::get('/export-bulk-format', [OrderController::class, 'exportBulkFormat'])->name('exportBulkFormat');

    Route::get('/parcels', [OrderController::class, 'parcelList'])->name('parcelList');
    Route::post('/parcels-qr', [OrderController::class, 'parcelQR'])->name('parcelQR');
    //Route::get('/print-parcels-qr/{id}', [OrderController::class, 'printParcelQR'])->name('printParcelQR');

    Route::get('/update-vendor-editor/{id}', [VendorController::class, 'vendorUpdateEditor'])->name('vendorUpdateEditor');
    Route::post('/update-vendor-editor', [VendorController::class, 'updateVendorSideEditor'])->name('updateVendorSideEditor');

    Route::get('/vendor-editor-list', [VendorController::class, 'vendorEditorsList'])->name('vendorEditorsList');
    Route::get('/create-vendor-editor', [VendorController::class, 'createVendorEditor'])->name('createVendorEditor');
    Route::post('/save-vendor-staff', [VendorController::class, 'saveVendorEditorStaff'])->name('saveVendorEditorStaff');
    Route::get('/cities-ids-for-bulk-order', [VendorController::class, 'citiesIdList'])->name('citiesIdList');
    Route::get('/city-areas/{id}', [VendorController::class, 'cityarea'])->name('cityarea');
    Route::post('/cancel-by-vendor', [VendorController::class, 'cancelByVendor'])->name('cancelByVendor');
    
    Route::get('/ahl-pay-report', [VendorController::class, 'ahlPayReport'])->name('ahlPayReport');

    Route::get('/generate-tax-invoice', [VendorController::class, 'generateTaxInvoice'])->name('generateTaxInvoice');
    Route::post('/tax-invoice-download', [VendorController::class, 'taxInvoiceDownload'])->name('taxInvoiceDownload');

    /*vendor shiper advisor*/
    Route::get('/vendor-shiper-adviser', [VendorController::class, 'shiperAdviser'])->name('vendorShiperAdviser');

    Route::match(['get', 'post'],'/vendor-shiper-parcel-advice/{id}', [VendorController::class, 'shiperParcelAdvice'])->name('vendorShiperParcelAdvice');
    Route::match(['get', 'post'],'/vendor-shiper-parcel-advice-edit/{advise_id}', [VendorController::class, 'shiperParcelAdviceEdit'])->name('vendorShiperParcelAdviceEdit');
    
    });

/* Middleman */
Route::group(['middleware' => ['auth','role:middle_man','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/scan-barcode-parcel-list', [BarcodeController::class, 'scanParcelList'])->name('scanParcelList');
    Route::post('/add-barcode-parcel', [BarcodeController::class, 'addParcel'])->name('addBarcodeParcel');
    Route::get('/scan-reattempt-parcel-list', [BarcodeController::class, 'reattemptParcelList'])->name('reattemptParcelList');
    Route::post('/reattempt-barcode-parcel', [BarcodeController::class, 'reattemptParcel'])->name('reattemptParcel');
    Route::get('/scan-cancel-parcel-by-rider-list', [BarcodeController::class, 'cancelByRiderParcelList'])->name('cancelByRiderParcelList');
    Route::post('/cancel-barcode-parcel', [BarcodeController::class, 'cancelScanParcel'])->name('cancelScanParcel');

    Route::post('/get-vendor-weight', [BarcodeController::class, 'getVendorWeight'])->name('getVendorWeight');
    Route::post('/bulk-vendor-weight', [BarcodeController::class, 'bulkVendorWeight'])->name('bulkVendorWeight');

    Route::get('/enroute-scan-parcel-list', [BarcodeController::class, 'enRouteScanParcelList'])->name('enRouteScanParcelList');
    Route::post('/enroute-add-barcode-parcel', [BarcodeController::class, 'addEnRouteParcel'])->name('enRouteaddBarcodeParcel');
    Route::post('/get-sag-number', [BarcodeController::class, 'getSagNumber'])->name('getSagNumber');
    Route::post('/close-sag-number', [BarcodeController::class, 'closeSagNumber'])->name('closeSagNumber');
    Route::get('/check-sag', [BarcodeController::class, 'checkSag'])->name('checkSag');
    Route::post('/open-sag-number', [BarcodeController::class, 'openSagNumber'])->name('openSagNumber');
    Route::post('/check-sag-parcel', [BarcodeController::class, 'checkSagParcels'])->name('checkSagParcels');
    Route::get('/generate-enroute-pdf/{sag?}', [BarcodeController::class, 'enroutePDF'])->name('enroutePDF');

    //bilty
    Route::get('/create-new-bilty', [BarcodeController::class, 'createBilty'])->name('createBilty');
    Route::post('/get-bilty-number', [BarcodeController::class, 'getBiltyNumber'])->name('getBiltyNumber');
    Route::post('/close-bilty-number', [BarcodeController::class, 'closeBiltyNumber'])->name('closeBiltyNumber');
    Route::get('/check-bilty', [BarcodeController::class, 'checkBilty'])->name('checkBilty');
    Route::post('/open-bilty-number', [BarcodeController::class, 'openBiltyNumber'])->name('openBiltyNumber');
    Route::post('/received-bilty-number', [BarcodeController::class, 'receivedBiltyNumber'])->name('receivedBiltyNumber');
    Route::get('/generate-bilty-pdf/{bilty?}', [BarcodeController::class, 'biltyPDF'])->name('biltyPDF');

    Route::get('/change-weight/{id}', [BarcodeController::class, 'changeWeight'])->name('changeWeight');
    Route::post('/update-change-weight', [BarcodeController::class, 'saveChangeWeight'])->name('saveChangeWeight');
    Route::get('/reattempt-parcels', [MiddleManController::class, 'reattemptParcels'])->name('reattemptParcels');
    Route::get('/generate-reattempt-pdf', [MiddleManController::class, 'generateReattemptPDF'])->name('generateReattemptPDF');
    //Route::post('/reattempt-parcels', [MiddleManController::class, 'reattempt'])->name('reattempt');
    Route::get('/generate-cancelled-pdf', [MiddleManController::class, 'generateCancelledPDF'])->name('generateCancelledPDF');
    //Route::post('/cancelled-parcels', [MiddleManController::class, 'cancelled'])->name('cancelled');
    Route::get('/midmen-today-report-report', [MiddleManController::class, 'midmenTodayReport'])->name('midmenTodayReport');
    Route::get('/midmen-today-report-cn-download', [MiddleManController::class, 'TodayReportDownload'])->name('TodayReportDownload');
});

Route::group(['middleware' => ['auth','role:middle_man|hub_manager','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/total-parcel-cn-report', [MiddleManController::class, 'totalParcelsCN'])->name('totalParcelsCN');
    Route::post('/total-parcel-cn-report-download', [MiddleManController::class, 'totalParcelsCNDownload'])->name('totalParcelsCNDownload');
});

Route::group(['middleware' => ['auth','role:supervisor|lead_supervisor|hub_manager','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/supervisor-scan-history', [SupervisorController::class, 'supervisorScanHistory'])->name('supervisorScanHistory');
    Route::get('/supervisor-scan-history-download', [SupervisorController::class, 'supervisorScanHistoryDownload'])->name('supervisorScanHistoryDownload');
});


/* Supervisor */
Route::group(['middleware' => ['auth','role:supervisor|lead_supervisor|hub_manager','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/supervisor-scan-barcode-parcel-list', [SupervisorController::class, 'scanParcelList'])->name('supervisorScanParcelList');
    Route::post('/check-by-supervisor', [SupervisorController::class, 'checkBySupervisor'])->name('checkBySupervisor');
    Route::post('/dispatch-to-rider', [SupervisorController::class, 'dispatchToRider'])->name('dispatchToRider');
    
    /*deparicated routes start*/
    Route::get('/request-reattempt', [SupervisorController::class, 'requestReattempt'])->name('requestReattempt');
    Route::post('/request-reattempt', [SupervisorController::class, 'reattempt'])->name('reattempt');
    Route::get('/cancelled-parcel', [SupervisorController::class, 'cancelledParcel'])->name('cancelledParcel');
    Route::post('/canelled-parcel-reattempt', [SupervisorController::class, 'cancelledParcelReattempt'])->name('cancelledParcelReattempt');
    /*deparicated routes end*/

    Route::post('/force-status-change', [SupervisorController::class, 'forceStatusChange'])->name('forceStatusChange');
    Route::get('/generate-dispatch-pdf/{parcels}/{rider?}', [SupervisorController::class, 'generateDispatchPDF'])->name('generateDispatchPDF');

    Route::match(['get', 'post'],'/change-bulk-status', [SupervisorController::class, 'bulkStatusView'])->name('bulkStatusView');
    Route::post('/mark-bulk-status', [SupervisorController::class, 'markDelivered'])->name('markDelivered');
    Route::get('/hold-status/{id}', [SupervisorController::class, 'holdStatusForm'])->name('holdStatus');
    Route::post('/update-status', [SupervisorController::class, 'updateholdStatus'])->name('updateholdStatus');
});

/* Cashier and Admin */
Route::group(['middleware' => ['auth','role:cashier|head_of_account|admin','userIsActive','otpcheck','ipcheck']], function () {
    Route::match(['get', 'post'],'/staff-financial-report/{staff}', [CashierController::class, 'staffFinancialReport'])->name('staffFinancialReport');

    Route::match(['get', 'post'],'/staff-financials/{staff}', [CashierController::class, 'staffFinancials'])->name('staffFinancials');
    Route::post('/pay-staff-financials', [CashierController::class, 'payStaffFinancials'])->name('payStaffFinancials');

    Route::post('/cash-collect', [CashierController::class, 'cashCollect'])->name('cashCollect');
    Route::get('/rider-cash-collection-list', [CashierController::class, 'riderCashCollectionList'])->name('riderCashCollectionList');
});

/*Admin and Supervisor and first man*/
Route::group(['middleware' => ['auth','role:supervisor|lead_supervisor|admin|first_man|hr|hub_manager','userIsActive','otpcheck','ipcheck']], function () {
    /*staff*/
    //Access To Supervisor and Admin and First Man
    Route::get('/create-staff', [VendorController::class, 'createStaff'])->name('createStaff');
    Route::post('/save-staff', [VendorController::class, 'saveStaff'])->name('saveStaff');
    Route::get('/staff-list', [StaffController::class, 'list'])->name('staffList');
    Route::get('/block-staff-list', [StaffController::class, 'blockStaffList'])->name('blockStaffList');
    Route::get('/staff-vendor-record/{id}/staff-role/{role}', [StaffController::class, 'vendorRecord'])->name('staffVendorRecord');
    Route::get('/assign-city/{id}',[StaffController::class,'finduser'])->name('finduser');
    Route::post('/save-city',[StaffController::class,'saveuserCity'])->name('saveusercity');
    Route::get('/staff-update/{id}', [StaffController::class, 'staffUpdate'])->name('staffUpdate');
    Route::post('/staff-update', [StaffController::class, 'updateStaff'])->name('updateStaff');
    
    // Replace Parcel
    Route::get('/replace-parcel/{id}', [OrderController::class, 'replaceParcel'])->name('replaceParcel');
    
    // Staff verification
    Route::get('/staff-verification/{id}', [RiderController::class, 'staffVerification'])->name('staffVerification');
    Route::post('/save-staff-verification/{id}', [RiderController::class, 'saveStaffVerification'])->name('saveStaffVerification');
    Route::get('/view-verification/{id}', [RiderController::class, 'viewVerification'])->name('viewVerification');

    //Pickup assign to vendor
    Route::get('/pickup-staff-list', [VendorController::class, 'pickupStaffList'])->name('pickupStaffList');
    Route::get('/assign-pickup/{id}', [VendorController::class, 'assignPickup'])->name('assignPickup');
    Route::post('/save-assign-pickup', [VendorController::class, 'saveAssignPickup'])->name('saveAssignPickup');

    //first_man vendors list
    Route::get('/pickup-assigned-vendors', [VendorController::class, 'pickupAssignedVendors'])->name('pickupAssignedVendors');
});

/*Supervisor and Cashier*/
Route::group(['middleware' => ['auth','role:supervisor|lead_supervisor|cashier|head_of_account|hub_manager|admin|csr|data_analyst','ipcheck']], function () {
    Route::match(['get', 'post'],'/cash-collection', [CashierController::class, 'cashCollection'])->name('cashCollection');
    
    Route::match(['get', 'post'],'/rider-parcels-report', [SupervisorController::class, 'riderParcelsReport'])->name('riderParcelsReport');
    Route::get('/rider-load-sheet', [SupervisorController::class, 'riderLoadSheet'])->name('riderLoadSheet');

    //control tower
    Route::get('/pending-remarks-list', [ComplainController::class, 'pendingRemarksList'])->name('pendingRemarksList');
    Route::get('/pending-remarks/{id}', [ComplainController::class, 'pendingRemark'])->name('pendingRemark');
    Route::post('/save-pending-remarks', [ComplainController::class, 'savePendingRemark'])->name('savePendingRemark');
    Route::get('/complete-remarks-list', [ComplainController::class, 'completeRemarksList'])->name('completeRemarksList');
});

/*Supervisor and Admin*/
Route::group(['middleware' => ['auth','role:supervisor|lead_supervisor|admin','ipcheck']], function () {
    Route::get('/assign-rider-city', [RiderController::class, 'assignCity'])->name('assignCity');
    Route::post('/assign-city-to-rider', [RiderController::class, 'assignCityToRider'])->name('assignCityToRider');

    Route::match(['get', 'post'],'/rider-parcels', [SupervisorController::class, 'riderParcels'])->name('riderParcels');
});

/*First Man or Admin*/
Route::group(['middleware' => ['auth','role:first_man|admin|csr','userIsActive','otpcheck','ipcheck']], function () {
        
    //Pickup Request
    Route::get('/all-pickup-requests', [VendorController::class, 'vendorPickupRequestList'])->name('vendorPickupRequestList');
    Route::get('/pickup-request-history', [FirstManController::class, 'pickupHistory'])->name('pickupHistory');

    //Assign Request to Picker
    Route::get('/assign-request/{id}', [AssignRequestController::class, 'assignRequest'])->name('assignRequest');
    Route::post('/save-assign-request', [AssignRequestController::class, 'saveAssignPickerRequest'])->name('saveAssignPickerRequest');

    //Vendor Assign
    Route::get('/picker-list', [HomeController::class, 'pickerRider'])->name('pickerRider');
    Route::get('/assign-picker/{id}', [HomeController::class, 'assignVendor'])->name('assignVendor');
    Route::post('/save-assign-picker', [HomeController::class, 'saveAssignVendor'])->name('saveAssignVendor');

    //return to vendor
    Route::get('/return-to-vendor-list', [FirstManController::class, 'returnToVendorList'])->name('returnToVendorList');
    Route::post('/return-to-vendor', [FirstManController::class, 'returnToVendor'])->name('returnToVendor');
    Route::get('/return-to-vendor-update/{id}', [FirstManController::class, 'returnToVendorUpdate'])->name('returnToVendorUpdate');
    Route::post('/bulk-return-to-vendor', [FirstManController::class, 'bulkReturnToVendor'])->name('bulkReturnToVendor');
    Route::get('/firstman-pickup-request', [FirstManController::class, 'firstManPickUp'])->name('firstManPickUp');
    Route::get('/select-vedor-data/{id}', [FirstManController::class, 'selectVendordata'])->name('selectVendordata');
    Route::post('/save-pickup-request', [FirstManController::class, 'saveFirstManPickUp'])->name('saveFirstManPickUp');
    
    //forcefully delete and complete request
    Route::get('/force-request-delete/{id}', [FirstManController::class, 'forceRequestDelete'])->name('forceRequestDelete');
    Route::get('/assigned-request-list', [AssignRequestController::class, 'assignedRequestList'])->name('assignedRequestList');
    Route::get('/force-request-complete/{assign_id}/{pickup_request_id}', [AssignRequestController::class, 'forceRequestComplete'])->name('forceRequestComplete');
    Route::post('/bulk-force-request-complete', [AssignRequestController::class, 'BulkorceRequestComplete'])->name('BulkorceRequestComplete');

    //update vendor address
    Route::post('/delete-vendor-location', [FirstManController::class, 'deleteVendorLocation'])->name('deleteVendorLocation');
    Route::post('/update-vendor-location', [FirstManController::class, 'updateVendorLocation'])->name('updateVendorLocation');
    Route::post('/insert-vendor-location', [FirstManController::class, 'insertVendorLocation'])->name('insertVendorLocation');

    //update vendor weights
    Route::post('/delete-vendor-weight', [FirstManController::class, 'deleteVendorWeight'])->name('deleteVendorWeight');
    Route::post('/update-vendor-weight', [FirstManController::class, 'updateVendorWeight'])->name('updateVendorWeight');
    Route::post('/insert-vendor-weight', [FirstManController::class, 'insertVendorWeight'])->name('insertVendorWeight');

    /*ahl shiper advisor*/
    Route::get('/ahl-shiper-advise', [FirstManController::class, 'shiperAdvise'])->name('ahlShiperAdvise');

    /*Ahl Shipper Advise Reply*/
    Route::get('/shiper-reply/{id}', [FirstManController::class, 'shiperReply'])->name('shiperReply');
    Route::post('/save-shiper-reply', [FirstManController::class, 'saveReply'])->name('saveReply');

    //csr vendors list
    Route::get('/csr-assigned-vendors', [VendorController::class, 'csrAssignedVendors'])->name('csrAssignedVendors');

});

/*First Man and Middle Man */
Route::group(['middleware' => ['auth','role:first_man|middle_man','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/cancelled-parcels', [MiddleManController::class, 'cancelledParcels'])->name('cancelledParcels');
    Route::get('/mark-void-label/{id}', [MiddleManController::class, 'markVoidLabel'])->name('markVoidLabel');
    Route::get('add-vendor-weight', [MiddlemanController::class, 'addWeight'])->name('addWeight');
    Route::post('assign-vendor-weight', [MiddlemanController::class, 'assignVendorWeight'])->name('assignVendorWeight');
    Route::get('/scan-cancel-parcel-list', [BarcodeController::class, 'scanCancelledParcelList'])->name('scanCancelledParcelList');
    Route::post('/add-cancel-parcel', [BarcodeController::class, 'addCancelledParcel'])->name('addCancelledParcel');
    Route::get('/return-to-vendor-in-progress-list', [FirstManController::class, 'returnToVendorInProgressList'])->name('returnToVendorInProgressList');
});

/*Financer and Admin, sales, middle_man*/
Route::group(['middleware' => ['auth','role:admin|financer|sales|middle_man|bd|bdm|csr|hr|hub_manager|cashier|head_of_account|data_analyst','userIsActive','otpcheck','ipcheck']], function () {
    Route::match(['get', 'post'],'/get-vendor-dispatch-parcel', [HomeController::class, 'vendorDispatchSheet'])->name('vendorDispatchSheet');
    Route::match(['get', 'post'],'/pra-report', [HomeController::class, 'reportPRA'])->name('reportPRA');
    Route::get('/automatic-dispatch-sheet/{id}', [CashierController::class, 'automaticDispatchSheet'])->name('automaticDispatchSheet');
    
    Route::match(['get', 'post'],'/vendor-financial-report', [CashierController::class, 'vendorFinancialReport'])->name('vendorFinancialReport');
    
    Route::get('/add-vendor-financial-report/{id}', [FinancerController::class, 'addVendorFinancialReport'])->name('addVendorFinancialReport');
    Route::post('/upload-vendor-financial-report', [FinancerController::class, 'uploadVendorFinancialReport'])->name('uploadVendorFinancialReport');
    Route::get('/add-vendor-financial-payment/{id}', [FinancerController::class, 'addVendorFinancialPaymentProof'])->name('addVendorFinancialPaymentProof');
    Route::post('/upload-vendor-financial-payment', [FinancerController::class, 'uploadVendorFinancialPayment'])->name('uploadVendorFinancialPayment');
    
});

/*First Man and Admin or vendor admin */
Route::group(['middleware' => ['auth','role:first_man|admin|vendor_admin|financer|head_of_account|vendor_editor','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/download-vendor-financial-report/{id}', [FinancerController::class, 'downloadVendorFinancialReport'])->name('downloadVendorFinancialReport');
    Route::match(['get', 'post'],'/get-vendor-side-dispatch-parcel', [HomeController::class, 'vendorSideDispatchSheet'])->name('vendorSideDispatchSheet');
});

/*admin,financer,cashier*/
Route::group(['middleware' => ['auth','role:admin|financer|cashier|head_of_account|sales|hub_manager','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/rider-dispatch-report', [FinancerController::class, 'riderDispatchReport'])->name('riderDispatchReport');
    Route::post('/rider-dispatch-report-download', [FinancerController::class, 'riderDispatchReportDownload'])->name('riderDispatchReportDownload');
    Route::get('/rider-automatics-dispatch-report', [FinancerController::class, 'riderAutomaticDispatchReport'])->name('riderAutomaticDispatchReport');
    Route::post('/rider-automatic-dispatch-report-download', [FinancerController::class, 'riderAutomaticDispatchReportDownload'])->name('riderAutomaticDispatchReportDownload');
    Route::get('/vendor-payment-report', [CashierController::class, 'vendorPaymentReport'])->name('vendorPaymentReport');
});

Route::group(['middleware' => ['auth','role:bd|bdm','userIsActive','otpcheck','ipcheck']], function () {
    //sales vendors list
    Route::get('/sales-assigned-vendors', [VendorController::class, 'salesAssignedVendors'])->name('salesAssignedVendors');
});

Route::group(['middleware' => ['auth','role:admin|first_man|hr|csr|hub_manager','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/index-flyer', [FlyerController::class, 'flyerIndex'])->name('flyerIndex');
    Route::get('/create-flyer', [FlyerController::class, 'flyerCreate'])->name('flyerCreate');
    Route::post('/save-flyer', [FlyerController::class, 'saveNewFlyer'])->name('saveNewFlyer');
    Route::get('/edit-flyer/{id}', [FlyerController::class, 'editFlyer'])->name('editFlyer');
    Route::post('/update-flyer', [FlyerController::class, 'updateFlyer'])->name('updateFlyer');
    Route::get('/pending-flyer-request', [FlyerController::class, 'pendingFlyerRequest'])->name('pendingFlyerRequest');
    Route::get('/accepted-flyer-request', [FlyerController::class, 'acceptedFlyerRequest'])->name('acceptedFlyerRequest');
    Route::get('/dispatched-flyer-request', [FlyerController::class, 'dispatchFlyerRequest'])->name('dispatchFlyerRequest');
    Route::get('/delivered-flyer-request', [FlyerController::class, 'delvieredFlyerRequest'])->name('delvieredFlyerRequest');
    Route::get('/flyer-request-status/{id}', [FlyerController::class, 'flyerRequestStatusChange'])->name('flyerRequestStatusChange');

    //flyer inventory
    Route::get('/add-inventory/{id}', [FlyerController::class, 'addInventory'])->name('addInventory');
    Route::post('/save-flyer-inventory', [FlyerController::class, 'saveInventory'])->name('saveInventory');
    Route::get('/view-inventory/{id}', [FlyerController::class, 'viewInventory'])->name('viewInventory');

    Route::get('/admin-pending-reverse-pickup', [OrderController::class, 'pendingReversePickupRequest'])->name('pendingReversePickupRequest');
    Route::get('/admin-pending-reverse-pickup-remarks/{id}', [OrderController::class, 'ReversePickupRemarks'])->name('ReversePickupRemarks');
    Route::post('/save-admin-pending-reverse-pickup-remarks/{id}', [OrderController::class, 'saveReversePickupRemarks'])->name('saveReversePickupRemarks');
    Route::get('/admin-received-reverse-pickup', [OrderController::class, 'receivedReversePickupRequest'])->name('receivedReversePickupRequest');
    Route::get('/admin-dispatched-reverse-pickup', [OrderController::class, 'dispatchedReversePickupRequest'])->name('dispatchedReversePickupRequest');
    Route::get('/admin-delivered-reverse-pickup', [OrderController::class, 'deliveredReversePickupRequest'])->name('deliveredReversePickupRequest');
    Route::get('/admin-cancel-reverse-pickup', [OrderController::class, 'cancelReversePickupRequest'])->name('cancelReversePickupRequest');

    Route::get('/scan-barcode-reverse-parcel-list', [BarcodeController::class, 'scanReverseParcelList'])->name('scanReverseParcelList');
    Route::post('/add-barcode-reverse-parcel', [BarcodeController::class, 'addReverseParcel'])->name('addBarcodeReverseParcel');

    Route::post('/print-parcels-qr', [OrderController::class, 'printParcelQR'])->name('printParcelQR');

    Route::get('/firstman-scan-barcode-parcel-list', [SupervisorController::class, 'scanReverseParcelList'])->name('firstmanScanParcelList');
    Route::post('/check-by-firstman', [SupervisorController::class, 'checkByFirstman'])->name('checkByFirstman');
    Route::post('/reverse-dispatch-to-rider', [SupervisorController::class, 'reverseDispatchToRider'])->name('reverseDispatchToRider');
    Route::get('/generate-reverse-dispatch-pdf/{parcels}/{rider?}', [SupervisorController::class, 'generateReverseDispatchPDF'])->name('generateReverseDispatchPDF');

});
Route::group(['middleware' => ['auth','role:vendor_admin|vendor_editor','userIsActive','otpcheck','ipcheck']], function () {
    Route::get('/index-flyer-request', [FlyerController::class, 'flyerRequestIndex'])->name('flyerRequestIndex');
    Route::get('/create-flyer-request', [FlyerController::class, 'createFlyerRequest'])->name('createFlyerRequest');
    Route::post('/save-flyer-request', [FlyerController::class, 'saveFlyerRequest'])->name('saveFlyerRequest');
    Route::get('/complete-flyer-request', [FlyerController::class, 'completedFlyerRequestIndex'])->name('completedFlyerRequestIndex');
    Route::get('/reverse-pickup-request', [FlyerController::class, 'reversePickupRequest'])->name('reversePickupRequest');
    Route::get('/in-progress-reverse-pickup-request', [FlyerController::class, 'progressReversePickupRequest'])->name('progressReversePickupRequest');
    Route::get('/complete-reverse-pickup-request', [FlyerController::class, 'completeReversePickup'])->name('completeReversePickup');
    Route::get('/cancel-reverse-pickup-request', [FlyerController::class, 'cancelReversePickup'])->name('cancelReversePickup');
    Route::get('/cancel-reverse-pickup-parcel/{id}', [FlyerController::class, 'cancelReversePickupParcel'])->name('cancelReversePickupParcel');
});

Route::group(['middleware' => ['auth','role:vendor_admin|vendor_editor|admin|first_man|hr','userIsActive','otpcheck','ipcheck']], function () {
    Route::post('/cancel-Flyer/{id}',[FlyerController::class, 'cancelFlyer'])->name('cancleFlyer');
});