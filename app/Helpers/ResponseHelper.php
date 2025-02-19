<?php

namespace app\Helpers;

//use Illuminate\Support\Facades\Response;

class ResponseHelper
{
    public static function apiResponse($status,$message,$error,$name,$data)
    {
        //status is http status
        //message is string 
        //error if error then provide error object else null array[]
        //data if data active then data object else null data[]
        //ResponseHelper::apiResponse('status','message','error','name','data');
        //$data = new stdClass()
        //(empty($data)) ? $data = (object) array() :  $data;//if null arry then provide object
        (empty($error)) ? $error = (object) array() :  $error;//if null arry then provide object
        
        return response()->json([
            'status' => $status,
            'message'=> $message,
            'error'=> $error,
            $name => $data
        ]);
    }
}
