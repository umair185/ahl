<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

use Maatwebsite\Excel\Facades\Excel;

use App\Models\OrderAssigned;
use App\Models\VendorFinancial;
use App\Models\Vendor;
use App\Models\City;
use App\Models\UserCity;

use Auth;

use App\Exports\RidersDispatchExport;
use App\Exports\RidersAutomaticDispatchExport;
use App\Exports\OrderCalculate;
use App\Exports\ReturntoVendor;
use App\Exports\DeliveredCalculate;
use App\Helpers\ImageHelper;


class FinancerController extends Controller
{
    public function riderDispatchReport()
    {
        $breadcrumbs = [
            'name' => "Rider's Dispatch Report", 
        ];

        if(Auth::user()->hasAnyRole('admin') ){
            $cities = City::all();
        }

        if(Auth::user()->hasAnyRole('financer','cashier','head_of_account','sales','hub_manager') ){
            $userId = Auth::user()->id;
            $userCity = UserCity::where('user_id',$userId)->pluck('city_id');
            $cities = City::whereIn('id',$userCity)->get();
        }
        
        return view('financer.rider-dispatch-report',compact('breadcrumbs','cities'));
    }

    public function riderDispatchReportDownload(Request $request)
    {
        $requestDate = $request->date;
        $rider_city  = $request->rider_city;
        $fileName = 'AHL Riders Dispatch Report ('.$requestDate.')';

        return Excel::download(new RidersDispatchExport($requestDate,$rider_city), $fileName.'.xlsx');
    }

    public function addVendorFinancialReport(Request $request)
    {
        $breadcrumbs = [
            'name' => "Upload Vendor Financial Report", 
        ];

        $financialId = $request->id;

        $vendorFinancial = VendorFinancial::whereId($financialId)->with([
            'vendorName' => function($query){
                $query->select('id','vendor_name');
            }
        ])->first();

        return view('financer.add-vendor-financial-report',compact('breadcrumbs','vendorFinancial'));
    }

    public function uploadVendorFinancialReport(Request $request)
    {
        //dump($request->financial_report);
        //dump($request->financial_report->getClientOriginalExtension());
        //dump($request->financial_report->getClientOriginalName());

        $validation = Validator::make($request->all(),
            [
                'financial_report' => 'required|mimes:csv,txt,xlsx,xls'
            ]
        );


        if($validation->fails()){
            $error = $validation->errors();
            return back()->withErrors($error)->withInput($request->all());
        }

        $financialId = $request->financial_id;
        $vendorFinancial = VendorFinancial::find($financialId);

        $fileName = explode('.', $request->financial_report->getClientOriginalName());

        if ($request->has('financial_report')) {
            $file = $request->file('financial_report');
            $data = [
                'requestFileImage'=> $file,
                'databaseAttrName'=> 'financial_report',
                'desPath'=> 'uploads/vendor_financial_report',
                //'desPath'=> 'public/profile',
                'modelName'=> 'App\Models\VendorFinancial',
                'id'=> ($vendorFinancial->financial_report) ? $financialId : $financialId = '',
            ];

            //dump($data);

            $fileName = $fileName[0];

            $report = ImageHelper::publicFile($data,$fileName);

            if($vendorFinancial->financial_report == ''){
                $vendorFinancial->financial_report = $report;
                $vendorFinancial->save();
            }
        }

        return redirect()->route('vendorFinancialReport')->with(['success' => 'File Uploaded Successfully']);

        //dd($request->all());
    }

    public function downloadVendorFinancialReport(Request $request)
    {
        $financialId = $request->id;
        $vendorFinancial = VendorFinancial::find($financialId);

        //PDF file is stored under project/public/download/info.pdf
        $file = public_path(). '/' .$vendorFinancial->financial_report;
        //dd($file);
        $headers = array(
          'Content-Type: application/vnd.ms-excel',
        );

        $fileName = explode('uploads/vendor_financial_report/', $vendorFinancial->financial_report);
        //dd($fileName);
        return Response::download($file, $fileName[1], $headers);
    }
    
    public function riderAutomaticDispatchReport()
    {
        $breadcrumbs = [
            'name' => "Vendor's Automatic Dispatch Report", 
        ];
        $vendors = Vendor::where('status',1)->get();

        return view('financer.rider-automatic-dispatch-report',compact('breadcrumbs','vendors'));
    }

    public function riderAutomaticDispatchReportDownload(Request $request)
    {
        $requestFrom = $request->from;
        $requestTo = $request->to;
        $requestVendor = $request->vendor;
        
        $vendor = Vendor::find($requestVendor);
        if(empty($vendor)){
            $fileName = "All Vendors";
        }else{
            $fileName = 'Vendor Parcel Report ('.$vendor->vendor_name.')';
        }

        if($requestFrom && $requestTo && $requestVendor){
            $fileName = 'Vendor Parcel Report ('.$fileName.')';
            
            return Excel::download(new RidersAutomaticDispatchExport($requestFrom,$requestTo,$requestVendor), $fileName.'.xlsx');
        }

        
    }

    public function calculateCommissionOrders(Request $request)
    {
        $requestFrom = $request->from;
        $requestTo = $request->to;
        $vendorId  = $request->vendor_id;

        $vendor = Vendor::where('id', $vendorId)->first();
        $fileName = 'AHL Commission Orders CN ('.$vendor->vendor_name.')';

        return Excel::download(new OrderCalculate($requestFrom,$requestTo,$vendorId), $fileName.'.xlsx');
    }

    public function calculateDeliveredOrders(Request $request)
    {
        $requestFrom = $request->from;
        $requestTo = $request->to;
        $vendorId  = $request->vendor_id;

        $vendor = Vendor::where('id', $vendorId)->first();
        $fileName = 'AHL Delivered Orders CN ('.$vendor->vendor_name.')';

        return Excel::download(new DeliveredCalculate($requestFrom,$requestTo,$vendorId), $fileName.'.xlsx');
    }

    public function calculateRtvOrders(Request $request)
    {
        $requestFrom = $request->from;
        $requestTo = $request->to;
        $vendorId  = $request->vendor_id;

        $vendor = Vendor::where('id', $vendorId)->first();
        $fileName = 'AHL Return to Vendors Orders CN ('.$vendor->vendor_name.')';

        return Excel::download(new ReturntoVendor($requestFrom,$requestTo,$vendorId), $fileName.'.xlsx');
    }

    public function addVendorFinancialPaymentProof(Request $request)
    {
        $breadcrumbs = [
            'name' => "Upload Vendor Financial Payment Proof", 
        ];

        $financialId = $request->id;

        $vendorFinancial = VendorFinancial::whereId($financialId)->with([
            'vendorName' => function($query){
                $query->select('id','vendor_name');
            }
        ])->first();

        return view('financer.add-vendor-financial-payment',compact('breadcrumbs','vendorFinancial'));
    }

    public function uploadVendorFinancialPayment(Request $request)
    {
        $financialId = $request->financial_id;
        $vendorFinancial = VendorFinancial::find($financialId);

        $fileName = explode('.', $request->financial_payment->getClientOriginalName());

        if ($request->has('financial_payment')) {
            $file = $request->file('financial_payment');
            $data = [
                'requestFileImage'=> $file,
                'databaseAttrName'=> 'financial_payment',
                'desPath'=> 'uploads/vendor_financial_payment',
                //'desPath'=> 'public/profile',
                'modelName'=> 'App\Models\VendorFinancial',
                'id'=> ($vendorFinancial->financial_payment) ? $financialId : $financialId = '',
            ];

            //dump($data);

            $fileName = $fileName[0];

            $report = ImageHelper::publicFile($data,$fileName);

            if($vendorFinancial->financial_payment == ''){
                $vendorFinancial->financial_payment = $report;
                $vendorFinancial->save();
            }
        }

        return redirect()->route('vendorFinancialReport')->with(['success' => 'Proof Uploaded Successfully']);

        //dd($request->all());
    }
}
