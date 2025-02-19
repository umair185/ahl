<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PickerController;
use App\Http\Controllers\Api\AssignRequestController;
use App\Http\Controllers\Api\RiderController;
use App\Http\Controllers\Api\ShopifyOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//staff login
Route::post('/test-message', [AuthController::class, 'testMessage']);
Route::get('get-version', [AuthController::class, 'getVersion']);

Route::post('/auth/staff-login', [AuthController::class, 'staffLogin'])->name('login');
Route::post('/shopify-vendor-access-token', [AuthController::class, 'shopifyVendorAccessToken']);
Route::post('/shopify-order-invoice', [ShopifyOrderController::class, 'orderInvoice']);
Route::post('/shopify-order-sms-link', [ShopifyOrderController::class, 'orderSMSLink']);
Route::post('/shopify-tracking-api', [ShopifyOrderController::class, 'trackingApi']);
Route::post('/shopify-detail-tracking-api', [ShopifyOrderController::class, 'detailTrackingApi']);
Route::post('/shopify-status-list', [ShopifyOrderController::class, 'statusList']);
Route::post('/shopify-shipper-advise', [ShopifyOrderController::class, 'shiperParcelAdvice']);

//voice api
Route::post('/get-calling-response', [ShopifyOrderController::class, 'getCallingResponse']);

Route::post('/parcels-qr-api', [ShopifyOrderController::class, 'parcelQR']);

Route::group(['middleware' => ['auth:api','userIsActive']], function () {
	Route::post('/logout', [AuthController::class, 'logout']);

	//Route::get('/change-parcel-status-qr/{order_parcel_id}', [OrderController::class, 'changeParcelStatusTroughQR'])->name('changeParcelStatusTroughQR');
	Route::post('/change-parcel-status-qr', [OrderController::class, 'changeParcelStatusTroughQR'])->name('changeParcelStatusTroughQR');
	Route::post('/change-parcel-status-through-reference', [OrderController::class, 'changeParcelStatusTroughReference'])->name('changeParcelStatusTroughReference');
	Route::post('/change-parcel-status-through-barcode', [OrderController::class, 'changeParcelStatusTroughBarcode'])->name('changeParcelStatusTroughBarcode');
	Route::post('/picker-request-scan-parcel-counter', [OrderController::class, 'pickerRequestScanParcelCounter']);
	Route::post('/change-weight', [OrderController::class, 'changeWeight']);
	Route::post('/save-change-weight', [OrderController::class, 'saveChangeWeight']);
	Route::get('picker-record', [OrderController::class, 'pickerRecord']);
	
	Route::get('/user-profile', [AuthController::class, 'userProfile'])->name('userProfile');
	
	Route::get('/picker-vendor-list', [PickerController::class, 'vendorList']);
	Route::get('/picker-assign-request', [PickerController::class, 'assignRequest']);
	
	/*Picker Assign Request Complete*/
	Route::post('/pickup-request-complete', [AssignRequestController::class, 'complete']);
	Route::get('/picker-complete-request-list', [PickerController::class, 'pickerCompleteRequestList']);
	Route::post('/picker-request-scan-parcel-list', [PickerController::class, 'pickerRequestScanParcelList']);
	Route::get('/picker-financial', [PickerController::class, 'financial']);
	Route::post('/picker-financial-report', [PickerController::class, 'financialReport']);

	/* Rider */
	Route::get('/rider-order-statuses', [RiderController::class, 'orderStatuses']);
	Route::POST('/rider-orders', [RiderController::class, 'orders']);
	Route::POST('/order-delivery', [RiderController::class, 'orderDelivery']);
	Route::get('/rider-commission', [RiderController::class, 'riderCommission']);
	//Route::get('/undelivered-reasons', [RiderController::class, 'undeliveredReasons']);
	Route::get('/order-decline-statuses', [RiderController::class, 'orderDeclineStatuses']);
	Route::get('/order-decline-reasons', [RiderController::class, 'orderDeclineReasons']);
	Route::post('/order-decline', [RiderController::class, 'orderDecline']);
	Route::get('/check-order-start', [RiderController::class, 'checkOrderStart']);

	Route::get('/consignee-relation', [RiderController::class, 'consigneeRelation']);
	Route::post('/rider-history', [RiderController::class, 'history']);
	Route::get('/rider-wallet', [RiderController::class, 'walletNew']);
	Route::get('/rider-wallet-new', [RiderController::class, 'walletNew']);
	Route::get('rider-delivery-record', [OrderController::class, 'riderDeliveryRecord']);
	
	Route::post('/fake-change-status', [AuthController::class, 'fakeChangeStatus']);
});
    /*Shopify Order*/
	Route::post('/shopify-order', [ShopifyOrderController::class, 'order']);
	Route::post('/shopify-order-track', [ShopifyOrderController::class, 'orderTrack']);
	Route::post('/shopify-order-cancel', [ShopifyOrderController::class, 'orderCancel']);
	Route::get('/shopify-order-cities', [ShopifyOrderController::class, 'orderCities']);
	Route::get('/shopify-vendor-detail', [ShopifyOrderController::class, 'vendorDetail']);
	Route::get('/shopify-order-amount-detail', [ShopifyOrderController::class, 'orderAmountDetail']);
	



	
	