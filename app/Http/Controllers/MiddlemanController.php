<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Order;
use App\Models\User;
use App\Models\AhlWeight;
use App\Models\VendorWeight;
use App\Models\UserCity;
use App\Models\City;
use App\Models\Vendor;
use Auth;
use Carbon\Carbon;

use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TotalParcelsWithCN;
use App\Exports\TodayReport;

class MiddlemanController extends Controller
{
    public function reattemptParcels()
    {
    	$breadcrumbs = [
            'name' => 'Reattempt Parcel Lists', 
        ];

        //only active rider show with cnic
    	/*$riders = User::whereHas(
            'roles', function($q){
                $q->where('name', 'rider');
            }
        )
        ->where('status',1)
        ->get();*/

        $userId = Auth::user()->id;
        $userCity = UserCity::where('user_id',$userId )->pluck('city_id');
        
    	$orders = Order::select('id','vendor_id','order_status','order_reference')
    	->where('order_status',8)->whereIn('consignment_origin_city',$userCity)->with([
    		'vendor' => function($query){
    			$query->select('id','vendor_name');
    		}
    	])
    	->get();

    	return view('middle_man.reattempt-list',compact('breadcrumbs','orders'));
    }

    // Generate PDF
    public function generateReattemptPDF() {
      // retreive all records from db
      $orders = Order::select('id','vendor_id','order_status','order_reference')
        ->where('order_status',8)->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            }
        ])
        ->get();

        $title = 'Reattempt Parcel List';
        // share data to view
        $pdf = PDF::loadView('middle_man.generate-pdf', compact('orders','title'));
        $fileName = date('m-d-y').'-'.'reattempt-parcels';
        // download PDF file with download method
        //return view()->share('middle_man.generate-reattempt-pdf',$orders);
        //return view('middle_man.generate-reattempt-pdf',compact('orders'));
      return $pdf->download($fileName.'.pdf');
    }

    public function cancelledParcels()
    {
    	$breadcrumbs = [
            'name' => 'Cancelled Parcel Lists', 
        ];

        //only active rider show with cnic
    	/*$riders = User::whereHas(
            'roles', function($q){
                $q->where('name', 'rider');
            }
        )
        ->where('status',1)
        ->get();*/

        if(Auth::user()->hasAnyRole('first_man','middle_man'))
        {
            $userId = Auth::user()->id;
            $userCity = UserCity::where('user_id',$userId)->pluck('city_id');

            $orders = Order::select('id','vendor_id','order_status','order_reference')
    	    ->whereIn('consignment_origin_city',$userCity)->where('order_status',9)->with([
    		'vendor' => function($query){
    			$query->select('id','vendor_name');
    		}
    	])
    	->get();
        }
    	

    	return view('middle_man.cancelled-list',compact('breadcrumbs','orders'));
    }

    // Generate PDF
    public function generateCancelledPDF() {
      // retreive all records from db
      $orders = Order::select('id','vendor_id','order_status','order_reference')
        ->where('order_status',9)->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            }
        ])
        ->get();

        $title = 'Cancelled Parcel List';
        // share data to view
        $pdf = PDF::loadView('middle_man.generate-pdf', compact('orders','title'));
        $fileName = date('m-d-y').'-'.'cancelled-parcels';
        // download PDF file with download method
        //return view()->share('middle_man.generate-reattempt-pdf',$orders);
        //return view('middle_man.generate-reattempt-pdf',compact('orders'));
      return $pdf->download($fileName.'.pdf');
    }
    
    public function markVoidLabel(Request $request)
    {
        $parcel_id = $request->id;
        $order = Order::where('id', $parcel_id)->first();
        $order->update(['order_status'=>13]);
        
        return back();
    }
    
    public function addWeight()
    {   
        $userid = Auth()->user()->id;
        $usercity = UserCity::where('user_id',$userid)->pluck('city_id');

        $cities = City::whereIn('id',$usercity)->get();

        return view('middle_man.edit_vendor_weight',compact('cities'));
    }
    
    public function assignVendorWeight(Request $request)
    {
//        dd("truwe");
        $validatedData = [
            //company detail
            'vendor_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $validatedData);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->all());
        }
        
        $vendor_id = $request->vendor_id;
//        dd($vendor_id);
        
        if($request->has('vendorWeights'))
        {
            $addWeightInAHL = $request->vendorWeights;
                
            foreach($addWeightInAHL as $key => $weight)
            {

                $dataOne = [
                    'weight' => $weight,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                    
                $ahlWeight = AhlWeight::create($dataOne);

                $ahlWeightId[] = $ahlWeight->id;
            }
        }

        //Add Vendor Weight
        if($request->has('vendorWeightsPrice'))
        {
            $vendorWeighPrices = $request->vendorWeightsPrice;
            $city = $request->vendorWeightscity;
                
            foreach($vendorWeighPrices as $key => $weightPrice)
            {
                $dataOne = [
                    'vendor_id' => $vendor_id,
                    'ahl_weight_id' => $ahlWeightId[$key],
                    'price' => $weightPrice,
                    'city_id' => $city[$key],
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                    
                $vendorWeight = VendorWeight::create($dataOne);
            }
        }
        
        return redirect()->back();
    }

    public function totalParcelsCN()
    {
        $breadcrumbs = [
            'name' => "Fresh Parcels with their CN", 
        ];
        $today_date = Carbon::now(+5)->format('Y-m-d');
        
        return view('middle_man.total-parcel-cn',compact('breadcrumbs','today_date'));
    }

    public function totalParcelsCNDownload(Request $request)
    {
        $requestDate = $request->date;
        $fileName = 'Total Parcel with their CN ('.$requestDate.')';

        return Excel::download(new TotalParcelsWithCN($requestDate), $fileName.'.xlsx');
    }

    public function midmenTodayReport()
    {
        $breadcrumbs = [
            'name' => "Middle Men Today Report", 
        ];

        $userId = Auth::user()->id;
        if(Auth::user()->hasRole('middle_man'))
        {
            $userCity = UserCity::where('user_id', $userId)->pluck('city_id');

            $atAhl = Order::whereIn('consignee_city', $userCity)->where('order_status', 3)->count();
            $reAttempt = Order::whereIn('consignee_city', $userCity)->where('order_status', 8)->count();
            $cancelled = Order::whereIn('consignee_city', $userCity)->where('order_status', 9)->count();

            $total = Order::whereIn('consignee_city', $userCity)->whereIn('order_status', [3,8,9])->count();
        }
        if(Auth::user()->hasRole('admin'))
        {
            $atAhl = Order::where('order_status', 3)->count();
            $reAttempt = Order::where('order_status', 8)->count();
            $cancelled = Order::where('order_status', 9)->count();

            $total = Order::whereIn('order_status', [3,8,9])->count();
        }
        
        return view('middle_man.midmen-today-report',compact('breadcrumbs','atAhl','reAttempt','cancelled','total'));
    }

    public function TodayReportDownload()
    {
        $fileName = 'Today Report';

        return Excel::download(new TodayReport(), $fileName.'.xlsx');
    }
}
