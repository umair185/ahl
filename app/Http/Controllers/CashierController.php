<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\DB;

use App\Models\Vendor;
use App\Models\Order;
use App\Models\VendorFinancial;
use App\Models\StaffFinancial;
use App\Models\RiderCashCollection;
use App\Models\ScanOrder;
use App\Models\OrderAssigned;
use App\Models\User;
use App\Models\UserCity;
use App\Models\City;
use App\Models\Status;
use App\Models\Template;
use App\Models\FlyerRequest;

use Carbon\Carbon;

use Helper;
use AHLHelper;
use PDF;
use Log;

class CashierController extends Controller
{
    //this function userd by AHL admin
    public function vendorFinancials(Request $request)
    {

        $breadcrumbs = [
            'name' => 'Pay Vendor Financials', 
        ];


    	$vendors = Vendor::where('status',1)->get();
        $today_date = Carbon::now(+5)->format('Y-m-d');

        if($request->vendor_id && $request->date_from && $request->date_to){
        	$vendorId = $request->vendor_id;
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;

            $payment_check = VendorFinancial::whereDate('date_from',$dateFrom)->whereDate('date_to',$dateTo)->where('vendor_id', $vendorId)->first();
            if(!empty($payment_check))
            {
                $payment_status = 'Paid';
                $payment_paid = $payment_check->amount;
                $payment_paid_date = date("Y-m-d ", strtotime($payment_check->created_at));
                $class_check = 'disabled';
                $class_check_message = 'Please delete already paid entry';
            }
            else
            {
                $payment_status = 'UnPaid';
                $payment_paid = 0;
                $payment_paid_date = '';
                $class_check = '';
                $class_check_message = '';
            }

            $total_flyer = FlyerRequest::where('vendor_id', $vendorId)->sum('total');
            $total_delivered_flyer = FlyerRequest::where('vendor_id', $vendorId)->where('status', 4)->sum('total');
            $total_received_flyer = VendorFinancial::where('vendor_id', $vendorId)->sum('flyer_amount');
            $remaining_flyer_amount = $total_delivered_flyer - $total_received_flyer;
            
	    	$vendorGst = AHLHelper::vendorGST($vendorId);
            $overallParcelSum = Helper::overallParcelSum($vendorId);
            
            $deliveredParcelSum = Helper::deliveredParcelSum($vendorId,$dateFrom,$dateTo);
            $notFilterDeliveredParcelSum = Helper::deliveredParcelSum($vendorId);
            // dump($deliveredParcelSum);
            // dump($notFilterDeliveredParcelSum);

            $ahlCommissionParcelSum = Helper::ahlCommissionParcelSumNew($vendorId,$dateFrom,$dateTo);
            $notFilterAhlCommissionParcelSum = Helper::ahlCommissionParcelSumNew($vendorId);
            // dd($notFilterAhlCommissionParcelSum);
            
            $returnToVendorParcelSum = Helper::returnToVendorParcelSum($vendorId,$dateFrom,$dateTo);
            
            $data = [
                'deliveredParcelSum' => $deliveredParcelSum,
                'ahlCommissionParcelSum' => $ahlCommissionParcelSum,
                'vendorId' => $vendorId,
                'vendorGst' => $vendorGst,

                'notFilterDeliveredParcelSum' => $notFilterDeliveredParcelSum,
                'notFilterAhlCommissionParcelSum' => $notFilterAhlCommissionParcelSum,
            ];

            $payable = Helper::payableToVendor($data);

            $payableToVendor = $payable['payableToVendor'];
            $total_pay_amount = $payable['total_pay_amount'];
            $total_ahl_commission_deduction = $payable['total_ahl_commission_deduction'];
            $filterPayableToVendor = $payable['filter_payable_to_vendor'] - $remaining_flyer_amount;
            //new
            $notFilterPayableToVendor = $payable['notFilterPayableToVendor'] - $remaining_flyer_amount;
            
            $taxAmount = Helper::ahlGSTCalculation($vendorId,$dateFrom,$dateTo);
            
            $payee = Vendor::where('id',$vendorId)->first();
            // $fuel_adjustment = ($ahlCommissionParcelSum * $payee->fuel)/100;
            // $round_fuel_adjustment = round($fuel_adjustment);
            $round_fuel_adjustment = Helper::ahlFuelCalculation($vendorId,$dateFrom,$dateTo);
            $fuel_adjustment_overall = ($notFilterAhlCommissionParcelSum * $payee->fuel)/100;
            $round_fuel_adjustment_overall = round($fuel_adjustment_overall);
	        
            return view('admin.cashier.vendor-financials',compact('vendors','overallParcelSum','deliveredParcelSum','ahlCommissionParcelSum','returnToVendorParcelSum','payableToVendor','taxAmount','vendorId','payee','total_pay_amount','total_ahl_commission_deduction','notFilterPayableToVendor','filterPayableToVendor','breadcrumbs','total_flyer','total_received_flyer','remaining_flyer_amount','total_delivered_flyer','round_fuel_adjustment','round_fuel_adjustment_overall','payment_status','payment_paid','class_check','class_check_message','today_date','payment_paid_date'));

        }else{
			$overallParcelSum = 0;
	        $deliveredParcelSum = 0;
	        $ahlCommissionParcelSum = 0;
            $returnToVendorParcelSum = 0;
	        $filterPayableToVendor = 0;
            $payableToVendor = 0;
            $notFilterPayableToVendor = 0;
            $taxAmount = 0;
	        $vendorId = 0;
            $payee = 0;
            $total_flyer = 0;
            $total_delivered_flyer = 0;
            $total_received_flyer = 0;
            $remaining_flyer_amount = 0;
            $round_fuel_adjustment = 0;
            $round_fuel_adjustment_overall = 0;
            $payment_status = '';
            $payment_paid = 0;
            $payment_paid_date = '';
            $class_check = '';
            $class_check_message = '';
        }

        return view('admin.cashier.vendor-financials',compact('vendors','overallParcelSum','deliveredParcelSum','ahlCommissionParcelSum','returnToVendorParcelSum','payableToVendor','notFilterPayableToVendor','filterPayableToVendor','taxAmount','vendorId','payee','breadcrumbs','total_flyer','total_received_flyer','remaining_flyer_amount','total_delivered_flyer','round_fuel_adjustment','round_fuel_adjustment_overall','payment_status','payment_paid','class_check','class_check_message','today_date','payment_paid_date'));
    }

    public function newVendorFinancials(Request $request)
    {
        $breadcrumbs = [
            'name' => 'New Pay Vendor Financials', 
        ];

        $rowColor = [
            '1' => 'bg-secondary text-white',
            '0' => 'bg-danger text-white', 
        ];

        
        if($request->date_from && $request->date_to){
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;

            $vendors = Vendor::select('id','gst','vendor_name','status')
            ->where('status',1)
            ->with([
                'vendorFinancials' => function($query){
                    $query->select('vendor_id',
                        DB::raw("SUM(amount) as total_pay_amount"),
                        DB::raw("SUM(ahl_commission) as total_ahl_commission_deduction")
                    )->groupBy("vendor_id");
                },
                /*'vendorOrders' => function($query){
                    $query->select('vendor_id','vendor_weight_id','order_status',
                        DB::raw("SUM(consignment_cod_price) as all_delivered_orders_amount")
                    )->with([
                        'vendorWeight' => function($query){
                            $query->select('id','vendor_id','price');
                        }
                    ])
                    ->groupBy("vendor_id",'vendor_weight_id','order_status')
                    ->whereIn('order_status', [6]);
                }*/
            ])
            ->get();

            //dd($vendors);

            $vendorIds = $vendors->pluck('id');

            $orders = Order::selectRaw('count(id) as count,sum(consignment_cod_price) as delivered_orders_amount,vendor_id, vendor_weight_id,order_status')
            ->groupBy('vendor_id','vendor_weight_id','order_status')
            ->whereIn('order_status', [6,10])//also add return to vendor status in commission
            ->whereIn('vendor_id',$vendorIds)
            ->with([
                'vendorWeight' => function($query){
                    $query->select('id','vendor_id','price');
                },
            ])
            ->whereDate('updated_at','>=',$dateFrom)
            ->whereDate('updated_at','<=',$dateTo)
            ->get();

            if(count($orders)){
                foreach ($vendorIds as $key => $vendorId) {
                    $commission=0;
                    $parcelOrdersAmount=0;
                    foreach ($orders as $key => $order) {
                        if($vendorId == $order->vendor_id){
                            $commission = $commission + ($order->count * $order->vendorWeight->price);
                            $ahlCommission[$order->vendor_id]['ahl_orders_commission'] = $commission;
                            
                            $parcelOrdersAmount = $parcelOrdersAmount + $order->delivered_orders_amount;
                            $ahlCommission[$order->vendor_id]['vendor_delivered_orders_amount'] = $parcelOrdersAmount;
                        }
                    }
                }
            }else{
                $ahlCommission = []; 
            }
            
        }else{
            $vendors = [];
            $ahlCommission = [];
        }

        return view('admin.cashier.new-vendor-financials',compact('breadcrumbs','vendors','ahlCommission','rowColor'));
    }

    public static function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    public function payVendorFinancials(Request $request)
    {
        $cashierId = Auth::user()->id;
        $vendorId = $request->vendor_id;
        $payAmount = $request->pay_amount;
        $payableToVendor = $request->payable_to_vendor;
        $ahlCommission = $request->ahl_commission;
        $ahlGST= $request->ahl_gst;
        $ahlRemarks= $request->remarks;
        $totalPayAmount = $request->total_pay_amount;
        $totalAhlCommissionDeduction = $request->total_ahl_commission_deduction;
        $requestDateFrom = $request->date_from;
        $requestDateTo = $request->date_to;
        $flyer_amount = $request->flyer_amount;
        $deduction_amount = $request->deduction_amount;
        $fuel_adjustment = $request->fuel_adjustment;
        $extra_phone_number = $request->extra_phone_number;
        $pay_date = $request->pay_date;
        $invoice_type = $request->invoice_type;
        $advance_amount = $request->advance_amount;
        $deduction_remarks = $request->deduction_remarks;
        $today_date = Carbon::now(+5)->format('Y-m-d');
        $invoice_number = 0;
        $paid_number = 0;

        if($invoice_type == 'IBFT')
        {
            $ibft_invoice_paid = VendorFinancial::where('invoice_type', '=', 'IBFT')->orderBy('id','DESC')->first();
            $paid_number = $ibft_invoice_paid->paid_number + 1;
            $invoice_number = "I-".$paid_number;
        }
        else
        {
            $cash_invoice_paid = VendorFinancial::where('invoice_type', '=', 'CASH')->orderBy('id','DESC')->first();
            $paid_number = $cash_invoice_paid->paid_number + 1;
            $invoice_number = "C-".$paid_number;
        }

        if(empty($pay_date))
        {
            $payment_date = $today_date.' 10:00:00';
        }
        else
        {
            $payment_date = $pay_date.' 10:00:00';
        }
        
        $rules = [
            'vendor_id' => 'required',
            'pay_amount' => 'required',
            'payable_to_vendor' => 'required',
            'ahl_commission' => 'required',
            'ahl_gst' => 'required',
            'flyer_amount' => 'required',
            'deduction_amount' => 'required',
            'total_pay_amount' => 'required',
            'total_ahl_commission_deduction' => 'required',
            'fuel_adjustment' => 'required',
            'financial_report' => 'required',
        ];

        $validation = Validator::make($request->all(),$rules);

        if($validation->fails()){
            $error = $validation->errors();
            return back()->withErrors($error)->withInput($request->all());
        }

        if($payAmount <= $payableToVendor){

            $vendorFinancial = [
                'vendor_id' => $vendorId,
                'cashier_id' => $cashierId,
                'amount' => $payAmount,
                'ahl_commission' => $ahlCommission,
                'ahl_gst' => $ahlGST,
                'flyer_amount' => $flyer_amount,
                'deduction_amount' => $deduction_amount,
                'remarks' => $ahlRemarks,
                'date_from' => $requestDateFrom,
                'date_to' => $requestDateTo,
                'fuel_adjustment' => $fuel_adjustment,
                'invoice_number' => $invoice_number,
                'invoice_type' => $invoice_type,
                'advance_amount' => $advance_amount,
                'deduction_remarks' => $deduction_remarks,
                'paid_number' => $paid_number,
                'created_at' => $payment_date,
                'updated_at' => $payment_date,
            ];
            
            $vendor_finance = VendorFinancial::create($vendorFinancial);
            if($vendor_finance)
            {
                $find_paid_vendor = Vendor::find($vendorId);
                $final_advance = 0;
                if($deduction_remarks == 'normal')
                {
                    $final_advance = $find_paid_vendor->advance + $advance_amount;
                }
                else
                {
                    $final_advance = $find_paid_vendor->advance - $deduction_amount;
                }
                $find_paid_vendor->update(['advance' => $final_advance]);

                $find_vendor_finance = VendorFinancial::find($vendor_finance->id);
                if($request->financial_report)
                {
                    $report = $request->file('financial_report');
                    $report_name = $this->generateRandomString() . '.' . $report->getClientOriginalName();

                    $upload_dir = 'uploads/vendor_financial_report';
                    if(!is_dir($upload_dir)) 
                        mkdir($upload_dir, 0755, true);
                        $path = $upload_dir.'/'.$report_name;
                        $report->move($upload_dir,$report_name);
                    
                        $data = [
                            'financial_report' => $path,
                        ];
                        $find_vendor_finance->update($data);
                }
            }
            
            $number = $vendor_finance->vendorName->vendor_phone;
            $name = $vendor_finance->vendorName->vendor_name;
            $paidAmount = $vendor_finance->amount;
            $date_from = date('d M Y', strtoTime($vendor_finance->date_from));
            $date_to = date('d M Y', strtoTime($vendor_finance->date_to));

            $message = Template::find(3);
            $body = str_replace('{{VENDOR_NAME}}', $name, $message->message);
            $body = str_replace('{{PAID_AMOUNT}}', $paidAmount, $body);
            $body = str_replace('{{DATE_FROM}}', $date_from, $body);
            $body = str_replace('{{DATE_TO}}', $date_to, $body);
            $body = str_replace('<p>', '%20', $body);
            $body = str_replace('</p>', '%20', $body);
            $body = str_replace(' ', '%20', $body);
            $message_data = [
                'number' => $number,
                'message' => $body
            ];

            Helper::sendMessage($message_data);

            if(!empty($extra_phone_number))
            {
                $extra_number = $extra_phone_number;
                $extra_name = $vendor_finance->vendorName->vendor_name;
                $extra_paidAmount = $vendor_finance->amount;
                $extra_date_from = date('d M Y', strtoTime($vendor_finance->date_from));
                $extra_date_to = date('d M Y', strtoTime($vendor_finance->date_to));

                $extra_message = Template::find(3);
                $extra_body = str_replace('{{VENDOR_NAME}}', $extra_name, $extra_message->message);
                $extra_body = str_replace('{{PAID_AMOUNT}}', $extra_paidAmount, $extra_body);
                $extra_body = str_replace('{{DATE_FROM}}', $extra_date_from, $extra_body);
                $extra_body = str_replace('{{DATE_TO}}', $extra_date_to, $extra_body);
                $extra_body = str_replace('<p>', '%20', $extra_body);
                $extra_body = str_replace('</p>', '%20', $extra_body);
                $extra_body = str_replace(' ', '%20', $extra_body);
                $extra_message_data = [
                    'number' => $extra_number,
                    'message' => $extra_body
                ];

                Helper::sendMessage($extra_message_data);
            }

        }else{
            $rulesError = ['pay_amount' => 'Not greater than payable to vendor'];
            $validatorNewErrors = new MessageBag($rulesError);
            return back()->withErrors($validatorNewErrors)->withInput($request->all());
        }

        return back()->with('sucess', 'Payed Sucessfully');
    }

    public function newPayVendorFinancials(Request $request)
    {
        $cashierId = Auth::user()->id;
        $vendorId = $request->vendor_id;
        
        $requestDateFrom = $request->date_from;
        $requestDateTo = $request->date_to;

        //$payAmount = $request->pay_amount;
        $totalPayAmount = $request->pay_amount;
        $reason = $request->reason;
        
        $payableToVendor = $request->payable_amount;
        $totalAhlCommissionDeduction = $request->total_ahl_commission_deduction;
        $ahlCommission = $request->ahl_order_commission;
        //dd($ahl_order_commission);
        if($totalPayAmount <= $payableToVendor && $totalPayAmount != 0){

            if($totalPayAmount != 0){
                $ahlCommission = $ahlCommission - $totalAhlCommissionDeduction;
                $remaining = $payableToVendor - $totalPayAmount;
            }


            //dd($ahlCommission);

            $vendorFinancial = [
                'vendor_id' => $vendorId,
                'cashier_id' => $cashierId,
                'amount' => $totalPayAmount,
                'ahl_commission' => $ahlCommission,
                'date_from' => $requestDateFrom,
                'date_to' => $requestDateTo,
            ];

            VendorFinancial::create($vendorFinancial);

        }else{
            return response()->json([
                'status' => 0, 
                'message' => 'Not greater than payable to vendor', 
            ]);
        }

        return response()->json([
            'status' => 1, 
            'message' => 'Amount Payed Successfully', 
            'remaining_amount' => $remaining, 
        ]);
    }

    public function vendorFinancialReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Vendor Financial Report', 
        ];

        $vendors = Vendor::where('status',1)->get();
        if($request->date_from && $request->date_to && $request->vendor_id == 'any'){

            $VendorFinancialsReport = VendorFinancial::whereDate('created_at','>=', $request->date_from)->whereDate('created_at', '<=',$request->date_to)->with([
                'vendorName' => function($query){
                    $query->select('id','vendor_name');
                },
                'cashierName' => function($query){
                    $query->select('id','name');
                }
            ])
            ->orderBy('created_at','desc')
            ->get();
            //dd($VendorFinancialsReport);
            return view('admin.cashier.vendor-financial-report',compact('vendors','VendorFinancialsReport','breadcrumbs'));
        }

        if($request->date_from && $request->date_to && $request->vendor_id <> 'any'){
            $vendorId = $request->vendor_id;
            //$selectedVendor = Vendor::where('id',$vendorId)->first();
            $VendorFinancialsReport = VendorFinancial::where('vendor_id',$vendorId)->whereDate('created_at','>=', $request->date_from)->whereDate('created_at', '<=',$request->date_to)->with([
                'vendorName' => function($query){
                    $query->select('id','vendor_name');
                },
                'cashierName' => function($query){
                    $query->select('id','name');
                }
            ])
            ->orderBy('created_at','desc')
            ->get();
            //dd($VendorFinancialsReport);
            return view('admin.cashier.vendor-financial-report',compact('vendors','VendorFinancialsReport','breadcrumbs'));
        }

        if($request->vendor_id <> 'any'){
            $vendorId = $request->vendor_id;
            //$selectedVendor = Vendor::where('id',$vendorId)->first();
            $VendorFinancialsReport = VendorFinancial::where('vendor_id',$vendorId)->with([
                'vendorName' => function($query){
                    $query->select('id','vendor_name');
                },
                'cashierName' => function($query){
                    $query->select('id','name');
                }
            ])
            ->orderBy('created_at','desc')
            ->get();
            //dd($VendorFinancialsReport);
            return view('admin.cashier.vendor-financial-report',compact('vendors','VendorFinancialsReport','breadcrumbs'));
        }

        $VendorFinancialsReport = VendorFinancial::with([
            'vendorName' => function($query){
                $query->select('id','vendor_name');
            },
            'cashierName' => function($query){
                $query->select('id','name');
            }
        ])
        ->orderBy('created_at','desc')
        ->get();
        
        return view('admin.cashier.vendor-financial-report',compact('vendors','VendorFinancialsReport','breadcrumbs'));
    }

    public function staffFinancials(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Pay Staff Financials', 
        ];

        if($request->staff == 'picker'){
            $staff = [6];
        }else{
            $staff = [7];
        }

        $staffList = User::whereHas(
            'roles', function($q) use($staff){
                $q->whereIn('id', $staff);
            }
        )
        ->with([
            'userDetail' => function($query){
                $query->select('id','user_id','cnic');
            },
        ])
        ->where('status',1)
        ->get();
        
        if($request->staff_id){
            $staffId = $request->staff_id;
            $staffData = User::where('id',$staffId)->with('userDetail')->first();

            $staffCommission = Helper::staffCommission($staffData);
            
            $totalOrder = $staffCommission['totalOrder'];
            $totalCommission = $staffCommission['totalCommission'];
            $totalPaidCommission = $staffCommission['totalPaidCommission'];
            $remaingCommission = $staffCommission['remaingCommission'];

            
        }else{
            $staffId = 0;
            $staffData = 0;
            $totalOrder = 0;
            $totalCommission = 0;
            $totalPaidCommission = 0;
            $remaingCommission = 0;
        }

    	return view('admin.cashier.staff-financials',compact('staffId','staffList','staffData','totalOrder','totalCommission','totalPaidCommission','remaingCommission','breadcrumbs'));
    }

    public function payStaffFinancials(Request $request)
    {
    	$cashierId = Auth::user()->id;
        $staffId = $request->staff_id;
        $payAmount = $request->pay_amount;
        $payNote = $request->pay_note;

        //$totalOrder = $request->total_order;
        //$totalCommission = $request->total_commission;
        //$totalPaidCommission = $request->total_paid_commission;
        $remaingCommission = $request->remaing_commission;
        
        $rules = [
            'staff_id' => 'required',
            'pay_amount' => 'required',
            'total_order' => 'required',
            'total_commission' => 'required',
            'total_paid_commission' => 'required',
            'remaing_commission' => 'required',
        ];

        $validation = Validator::make($request->all(),$rules);

        if($validation->fails()){
            $error = $validation->errors();
            return back()->withErrors($error)->withInput($request->all());
        }

        if($payAmount <= $remaingCommission && $payAmount != 0){

            $staffFinancial = [
                'staff_id' => $staffId,
                'cashier_id' => $cashierId,
                'amount' => $payAmount,//staff commission paid
                'note' => $payNote,//staff commission paid
            ];

            StaffFinancial::create($staffFinancial);

        }else{
            $rulesError = ['pay_amount' => 'Not greater than payable to vendor'];
            $validatorNewErrors = new MessageBag($rulesError);
            return back()->withErrors($validatorNewErrors)->withInput($request->all());
        }

        return back()->with('sucess', 'Payed Sucessfully');
    }

    public function staffFinancialReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Staff Financial Report', 
        ];

        if($request->staff == 'picker'){
            $staff = [6];
        }else{
            $staff = [7];
        }

        $staffList =  User::whereHas(
            'roles', function($q) use($staff){
                $q->whereIn('id', $staff);
            }
        )
        ->with([
            'userDetail' => function($query){
                $query->select('id','user_id','cnic');
            },
        ])
        ->where('status',1)
        ->get();

        $staffId = $request->staff_id;
        
        if($staffId){
            $staff = User::where('id',$staffId)->first();
            
            $staffFinancialsReport = StaffFinancial::where('staff_id',$staffId)->with([
                'staffName' => function($query){
                    $query->select('id','name');
                },
                'cashierName' => function($query){
                    $query->select('id','name');
                }
            ])
            ->orderBy('id','desc')
            ->get();

            return view('admin.cashier.staff-financial-report',compact('staffList','staff','staffFinancialsReport','breadcrumbs'));
        }
            return view('admin.cashier.staff-financial-report',compact('staffList','breadcrumbs'));


    }

    public function cashCollection(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Cash Collection', 
        ];
        
        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $staffList = User::whereHas(
            'roles', function($q){
                $q->whereIn('id', [7]);
            }
        )->whereHas('usercity',function($query) use($usercity){
            $query->whereIn('city_id',$usercity);
        })
        ->with('userDetail')
        ->where('status',1)
        ->get();
        
        if($request->staff_id && $request->date){
            //supervisor and cashier comman
            $staffId = $request->staff_id;
            $requestDate = $request->date;

            $staffData = User::where('id',$staffId)->with('userDetail')->first();
            $staffCashCollection = Helper::riderCashCollection($staffData,$requestDate);
            $staffCommission = $staffData->userDetail->commission;
            $orderAssignedIds = OrderAssigned::select('id','order_id','created_at','rider_id','status')
            ->whereDate('created_at',$requestDate)
            ->where(['rider_id'=>$staffId,'force_status'=>1])
            ->get()
            ->pluck('order_id');
            
            $rackBalancing = AHLHelper::rackBalancing($orderAssignedIds);
            
            if($rackBalancing['deliveredParcels'] == 0){
                $totalDeliveredParcels = 0;
            }else{
                $totalDeliveredParcels = count($rackBalancing['deliveredParcels']);
            }
            $deliveredParcelACommission =  $totalDeliveredParcels * $staffCommission;
            //supervisor
            if(Auth::user()->isSupervisor() || Auth::user()->isHubManager()){
                $allStatuses = Status::select('id','name')->whereIn('id',[6,7,18])->get();
                foreach($allStatuses as $status){
                    $statuses[$status->id] = $status->name;
                }
            }else{
                $statuses = 0;
            }
            
            $defaultOrders = Order::whereIn('id', $orderAssignedIds)->with('countOrderAssigned', 
                function($query) use($requestDate,$staffId){
                    $query->whereDate('created_at', $requestDate);
                    $query->where('rider_id', $staffId);
                })->whereIn('consignee_city',$usercity)->get();
            // $unactionedParcels = Order::whereIn('id', $orderAssignedIds)->whereIn('consignee_city',$usercity)->whereIn('order_status', [1,4,5,16,17])->count();
            $unactionedParcels = OrderAssigned::whereDate('created_at',$requestDate)->where(['rider_id'=>$staffId])
            ->whereIn('trip_status_id', [1,2])->where('status', 1)->count();
            
            $orders = OrderAssigned::where('rider_id', $staffId)->where('trip_status_id',4)->where('status', 1)->pluck('order_id');
            $initial_sum = 0;
            foreach($orders as $order)
            {
                $order_details_arr = Order::where('id', $order)->where('order_status', 6)->where('consignment_order_type', 1)->sum('consignment_cod_price');
                $initial_sum = round($initial_sum + $order_details_arr);

            }
            $totalCollectCashFromRider = RiderCashCollection::where('rider_id',$staffId)->sum('amount');
            
            $remainingCash = round($initial_sum-$totalCollectCashFromRider);

            $total_parcels = Order::whereIn('id', $orderAssignedIds)->count();
            $confirm_delivered = OrderAssigned::where('rider_id', $staffId)->whereDate('created_at',$requestDate)->where('trip_status_id', 4)->where('status',1)->count();
            $confirm_cancel = OrderAssigned::where('rider_id', $staffId)->whereDate('created_at',$requestDate)->where('trip_status_id', 5)->where('status',0)->count();
            $confirm_reattempt = OrderAssigned::where('rider_id', $staffId)->whereDate('created_at',$requestDate)->where('trip_status_id', 6)->where('status',0)->count();
            $confirm_inprogress = OrderAssigned::where('rider_id', $staffId)->whereDate('created_at',$requestDate)->whereNotIn('trip_status_id', [4,5,6])->where('status',1)->count();
            if($total_parcels > 0)
            {
                $wining_ratio = ($confirm_delivered/$total_parcels)*100;
            }
            else
            {
                $wining_ratio = 0;
            }
            
        }else{
            $staffId = 0;
            $staffData = 0;
            $rackBalancing = 0;
            $statuses = 0;
            $deliveredParcelACommission = 0;
            $staffCashCollection = [
                'todayOrder' => 0,
                'remaingOrder' => 0,
                'totalCashByRider' => 0,
                'totalCollectCashFromRider' => 0,
                'remainingCash' => 0,
            ];
            $defaultOrders = [];
            $unactionedParcels = 0;
            $remainingCash = 0;
            $total_parcels = 0;
            $confirm_delivered = 0;
            $confirm_cancel = 0;
            $wining_ratio = 0;
            $confirm_reattempt = 0;
            $confirm_inprogress = 0;
        }

        return view('admin.cashier.cash-collection',compact('staffId','staffList','staffData','staffCashCollection',
                'deliveredParcelACommission','statuses','rackBalancing','breadcrumbs','defaultOrders','remainingCash',
                'unactionedParcels','total_parcels','confirm_delivered','confirm_cancel','wining_ratio','confirm_reattempt','confirm_inprogress'));
    }

    public function cashCollect(Request $request)
    {
        $cashierId = Auth::user()->id;
        $staffId = $request->collect_staff_id;
        $additionalNote = $request->note;
        $collectAmount = (int) $request->collect_amount;
        $in_cash_collection = (int) $request->in_cash_collection;
        $ibft_collection = (int) $request->ibft_collection;
        $ibft_comment = $request->ibft_comment;

        $remainingCash = (int) $request->remaining_cash;
        
        $date = $request->cash_date;
        
        $rules = [
            'collect_staff_id' => 'required',
            'collect_amount' => 'required',
            'remaining_cash' => 'required',
            'cash_date' => 'required',
        ];
        
        $validation = Validator::make($request->all(),$rules);

        if($validation->fails()){
            $error = $validation->errors();
            return back()->withErrors($error)->withInput($request->all());
        }
        if($collectAmount != 0){
            //after collect cash remaining amount is 
            $remainingAmount = $remainingCash - $collectAmount;
            $staffFinancial = [
                'rider_id' => $staffId,
                'cashier_id' => $cashierId,
                'amount' => $collectAmount,
                'remaining_amount' => $remainingAmount,
                'in_cash_collection' => $in_cash_collection,
                'ibft_collection' => $ibft_collection,
                'ibft_comment' => $ibft_comment,
                'note' => $additionalNote,
                'created_at' => $date,
                'updated_at' => $date,
            ];

            RiderCashCollection::create($staffFinancial);

            $find_rider = User::where('id', $staffId)->first();

            $number = $find_rider->userDetail ? $find_rider->userDetail->phone : '03004407411';
            $rider_name = $find_rider->name;
            $cashier_name = Auth::user()->name;

            $message = Template::find(6);
            $body = str_replace('{{RIDER_NAME}}', $rider_name, $message->message);
            $body = str_replace('{{ORDER_AMOUNT}}', $collectAmount, $body);
            $body = str_replace('{{CASHIER_NAME}}', $cashier_name, $body);
            $body = str_replace('<p>', '%20', $body);
            $body = str_replace('</p>', '%20', $body);
            $body = str_replace(' ', '%20', $body);
            $message_data = [
                'number' => $number,
                'message' => $body
            ];

            Helper::sendMessage($message_data);

        }else{
            $rulesError = ['collect_amount' => 'Not greater than remaining to amount'];
            $validatorNewErrors = new MessageBag($rulesError);
            return back()->withErrors($validatorNewErrors)->withInput($request->all());
        }

        return back()->with('sucess', 'Amount Collect Sucessfully');
    }

    public function riderCashCollectionList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Rider Cash Collection List', 
        ];

        if(Auth::user()->hasAnyRole('admin')){
            if($request->date && $request->to)
            {
                $today = $request->date;
                $to = $request->to;

                $riderCashCollection = RiderCashCollection::whereDate('created_at','>=',$today)->whereDate('created_at','<=', $to)->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'cashier' => function($query){
                        $query->select('id','name');
                    }
                ])
                ->orderBy('id', 'desc')
                ->get();
            }
            else
            {
                $riderCashCollection = RiderCashCollection::with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'cashier' => function($query){
                        $query->select('id','name');
                    }
                ])
                ->orderBy('id', 'desc')
                ->get();
            }
        }
        elseif(Auth::user()->hasAnyRole('cashier')){
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

            if($request->date && $request->to)
            {
                $today = $request->date;
                $to = $request->to;

                $riderCashCollection = RiderCashCollection::whereDate('created_at','>=',$today)->whereDate('created_at','<=', $to)->whereHas('rider.usercity',function($query) use($usercity){
                    $query->whereIn('city_id',$usercity);
                })->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'cashier' => function($query){
                        $query->select('id','name');
                    }
                ])
                ->orderBy('id', 'desc')
                ->get();
            }
            else
            {
                $riderCashCollection = RiderCashCollection::whereHas('rider.usercity',function($query) use($usercity){
                    $query->whereIn('city_id',$usercity);
                })->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'cashier' => function($query){
                        $query->select('id','name');
                    }
                ])
                ->orderBy('id', 'desc')
                ->get();
            }
        }
        
        
        return view('admin.cashier.rider-cash-collection-list',compact('breadcrumbs','riderCashCollection'));
    }
    
    public function indiviualTaxInvoice(Request $request)
    {
        $financial_id = VendorFinancial::where('id', $request->id)->first();
//        dd($financial_id);
        $vendor_detail = Vendor::where('id', $financial_id->vendor_id)->first();
        
       // return view('admin.cashier.vendor-financial-invoice-pdf', compact('financial_id'));
        
        $pdf = PDF::loadView('admin.cashier.vendor-financial-invoice-pdf', compact('financial_id'));
        $pdf_name =$vendor_detail->vendor_name."_Payment_invoice_".$financial_id->id."_".date("Y_m_d_h_i_s").".pdf";
//        dd($pdf_name);
        return $pdf->download($pdf_name);
    }
    
    public function automaticDispatchSheet(Request $request)
    {
        $finance_id = VendorFinancial::where('id', $request->id)->first();

        $from = $finance_id->date_from;
        $to = $finance_id->date_to;
        $vendor = $finance_id->vendor_id;

        $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('vendor_id', $vendor)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
        $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('vendor_id', $vendor)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
        $collection = collect([$parcels,$returned_parcels]);
        $collapsed = $collection->collapse();
        $orders_assigned = $collapsed->all();

        $orders = Order::where('parcel_nature',1)->whereIn('id', $orders_assigned)->pluck('id');
        $group_order = OrderAssigned::whereIn('order_id', $orders_assigned)->groupBy('order_id')->pluck('id');
                
        $orders_delivered =  OrderAssigned::whereIn('id', $group_order)
            ->with([
                'rider' => function($query){
                    $query->select('id','name');
                },
                'riderVendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'order' => function($query){
                    $query->select('id','order_reference','consignee_first_name','consignee_last_name','consignment_cod_price','consignment_order_id','consignment_order_type','vendor_weight_id','consignee_address','consignee_phone','dispatch_date','order_status','vendor_weight_price','vendor_tax_price','vendor_fuel_price')->with([
                        'vendorWeight' => function($query){
                            $query->select('id','ahl_weight_id','price','city_id')->with([
                                'ahlWeight' =>  function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        }
                    ]);
                },
                'tripStatus' => function($query){
                    $query->select('id','description');
                }
            ])
            ->get();
        
        return view('admin.cashier.automatic-dispatch-sheet', compact('orders_delivered','finance_id'));
    }

    public function VendorFinancialDelete(Request $request)
    {
        $financial_id = VendorFinancial::where('id', $request->id)->first();

        $find_paid_vendor = Vendor::find($financial_id->vendor_id);
        $final_advance = 0;
        if($financial_id->deduction_remarks == 'normal')
        {
            $final_advance = $find_paid_vendor->advance - $financial_id->advance_amount;
        }
        else
        {
            $final_advance = $find_paid_vendor->advance + $financial_id->deduction_amount;
        }
        $find_paid_vendor->update(['advance' => $final_advance]);

        VendorFinancial::destroy($financial_id->id);

        return back();
    }

    public function bulkInvoiceDelete(Request $request)
    {
        // dd($request->invoices);
        $invoices = $request->invoices;
        foreach($invoices as $invoice)
        {
            $financial_id = VendorFinancial::where('id', $invoice)->first();

            $find_paid_vendor = Vendor::find($financial_id->vendor_id);
            $final_advance = 0;
            if($financial_id->deduction_remarks == 'normal')
            {
                $final_advance = $find_paid_vendor->advance - $financial_id->advance_amount;
            }
            else
            {
                $final_advance = $find_paid_vendor->advance + $financial_id->deduction_amount;
            }
            $find_paid_vendor->update(['advance' => $final_advance]);

            VendorFinancial::destroy($financial_id->id);
        }
        
        return response()->json([
            'status' => 1, 
        ]);
    }

    public function editVendorFinancials($id)
    {
        $get_detail = VendorFinancial::find($id);
        $today_date = Carbon::now(+5)->format('Y-m-d');
        // dd($get_detail);

        return view('admin.cashier.edit-vendor-financials',compact('get_detail','today_date'));
    }

    public function payVendorFinancialsUpdate(Request $request)
    {
        $financial_id = $request->financial_id;
        $payAmount = $request->pay_amount;
        $ahlCommission = $request->ahl_commission;
        $ahlGST= $request->ahl_gst;
        $advance_amount = $request->advance_amount;
        $fuel_adjustment = $request->fuel_adjustment;
        $flyer_amount = $request->flyer_amount;
        $deduction_amount = $request->deduction_amount;
        $deduction_remarks = $request->deduction_remarks;
        $ahlRemarks= $request->remarks;
        $date_from= $request->date_from;

        $find_vendor_finance = VendorFinancial::find($financial_id);

        $created_at = '';
        if(!empty($date_from))
        {
            $created_at = $date_from;
        }
        else
        {
            $created_at = $find_vendor_finance->created_at;
        }

        $vendorFinancial = [
            'amount' => $payAmount,
            'ahl_commission' => $ahlCommission,
            'ahl_gst' => $ahlGST,
            'flyer_amount' => $flyer_amount,
            'deduction_amount' => $deduction_amount,
            'remarks' => $ahlRemarks,
            'fuel_adjustment' => $fuel_adjustment,
            'advance_amount' => $advance_amount,
            'deduction_remarks' => $deduction_remarks,
            'created_at' => $created_at,
        ];

        $find_vendor_finance->update($vendorFinancial);
            
        if($request->financial_report)
        {
            $report = $request->file('financial_report');
            $report_name = $this->generateRandomString() . '.' . $report->getClientOriginalName();

            $upload_dir = 'uploads/vendor_financial_report';
            if(!is_dir($upload_dir))
                mkdir($upload_dir, 0755, true);
                $path = $upload_dir.'/'.$report_name;
                $report->move($upload_dir,$report_name);
                    
                $data = [
                    'financial_report' => $path,
                ];
                $find_vendor_finance->update($data);
        }

        return back()->with('sucess', 'Financials Updated Sucessfully');
    }

    public function vendorPaymentReport(Request $request)
    {
        // dd('yes');

        if($request->date && $request->to)
        {
            $dateFrom = $request->date;
            $dateTo = $request->to;

            $vendors = Vendor::where('status', 1)->get();

            $delivered = [];
            $commission = [];
            $fuel = [];
            $tax = [];
            $flyer = [];
            $balance = [];

            foreach($vendors as $key => $vendor)
            {
                $vendorId = $vendor->id; //vendor id

                $deliveredParcelSum = Helper::deliveredParcelSum($vendorId,$dateFrom,$dateTo); //delivered
                $ahlCommissionParcelSum = Helper::ahlCommissionParcelSumNew($vendorId,$dateFrom,$dateTo); //commission
                $round_fuel_adjustment = Helper::ahlFuelCalculation($vendorId,$dateFrom,$dateTo); //fuel
                $taxAmount = Helper::ahlGSTCalculation($vendorId,$dateFrom,$dateTo); //GST

                //flyer
                $total_delivered_flyer = FlyerRequest::where('vendor_id', $vendorId)->where('status', 4)->sum('total');
                $total_received_flyer = VendorFinancial::where('vendor_id', $vendorId)->sum('flyer_amount');
                $remaining_flyer_amount = $total_delivered_flyer - $total_received_flyer;

                //overall
                $overalldeliveredParcelSum = Helper::deliveredParcelSum($vendorId); //Overall delivered
                $overallahlCommissionParcelSum = Helper::ahlCommissionParcelSumNew($vendorId); //Overall commission
                $overallround_fuel_adjustment = Helper::ahlFuelCalculation($vendorId); //Overall fuel
                $overalltaxAmount = Helper::ahlGSTCalculation($vendorId); //Overall GST

                $total_finance_amount = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');

                $combine = $overalltaxAmount + $overallahlCommissionParcelSum + $total_finance_amount;

                $balanceDue = $overalldeliveredParcelSum - $combine - $remaining_flyer_amount;

                $delivered[$key] = $deliveredParcelSum;
                $commission[$key] = $ahlCommissionParcelSum;
                $fuel[$key] = $round_fuel_adjustment;
                $tax[$key] = $taxAmount;
                $flyer[$key] = $remaining_flyer_amount;
                $balance[$key] = $balanceDue;
            }
        }
        else
        {
            $dateFrom = \Carbon\Carbon::now();
            $dateTo = \Carbon\Carbon::now();

            $vendors = [];

            $delivered = [];
            $commission = [];
            $fuel = [];
            $tax = [];
            $flyer = [];
            $balance = [];
        }
        
        return view('financer-report', compact('vendors','dateFrom','dateTo','delivered','commission','fuel','tax','flyer','balance'));
    }
    
}
