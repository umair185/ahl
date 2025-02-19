<?php

namespace app\Helpers;

use File;
use Image;
use Helper;

use Illuminate\Support\Facades\Storage;

//use App\Employ;

class ImageHelper
{
    public static function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    public static function image($data)
    {
        $imageObject = $data['requestFileImage'];
        $desPath = $data['desPath']; 
        $modelName = $data['modelName']; 
        $databaseAttrName = $data['databaseAttrName']; 
        $id = $data['id']; 
        
        //$image = $request->file('profile_img');
        $input['imageName'] = ImageHelper::generateRandomString() . '.' . $imageObject->getClientOriginalExtension();

        //public path
        //$destinationPath = public_path('/uploads/profile');

        //$destinationPath = public_path($desPath);

        //for server save path
        $destinationPath = $desPath;

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $img = Image::make($imageObject->getRealPath());
        $img->save($destinationPath . '/' . $input['imageName']);

        //$imgPath = $desPath . '/' . $input['imageName'];
        $dbImgPath = $desPath . '/' . $input['imageName'];

        //$img = $dbImgPath;
        //$client_id = $this->clientIdTrait($request->bearerToken());
        //$client_id = $id;
        //$User = Client::where('id', $client_id)->first();
        $User = $modelName::where('id', $id)->first();
        // dld img from folder
        $deleteImgPath = 'public/' . $User->$databaseAttrName;
        if (File::exists($deleteImgPath)) {
            File::delete($deleteImgPath);
        }
        //dd($dbImgPath);
        $User->$databaseAttrName = $dbImgPath;
        $User->save();
        //dd($User);
    }

    public static function publicImage($data)
    {
        $imageObject = $data['requestFileImage'];
        $desPath = $data['desPath']; 
        $modelName = $data['modelName']; 
        $databaseAttrName = $data['databaseAttrName']; 
        $orderId = $data['order_id']; 
        
        
        $input['imageName'] = $orderId.'_'.ImageHelper::generateRandomString() . '.' . $imageObject->getClientOriginalExtension();

        //public path
        //$destinationPath = public_path('/uploads/profile');

        //$destinationPath = public_path($desPath);

        //for server save path
        $destinationPath = $desPath;

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        //use in old version
        //$img = Image::make($imageObject->getRealPath());
        //$img->save($destinationPath . '/' . $input['imageName']);
        
        //use in new version
        $imageObject->move($destinationPath,$input['imageName']);

        $dbImgPath = $destinationPath . '/' . $input['imageName'];

        if(isset($data['id'])){
            $id = $data['id'];
            $User = $modelName::where('id', $id)->first();

            $deleteImgPath = 'public/' . $User->$databaseAttrName;
            if (File::exists($deleteImgPath)) {
                File::delete($deleteImgPath);
            }

            $User->$databaseAttrName = $dbImgPath;
            return $User->save();

        }

        return $dbImgPath;
    }

    public static function publicFile($data,$clientFileName)
    {
        $fileObject = $data['requestFileImage'];
        $desPath = $data['desPath']; 
        $modelName = $data['modelName']; 
        $databaseAttrName = $data['databaseAttrName'];
        
        if($clientFileName != ''){
            $input['fileName'] =  $clientFileName. '.' . $fileObject->getClientOriginalExtension();
        }else{
            $input['fileName'] = ImageHelper::generateRandomString() . '.' . $fileObject->getClientOriginalExtension();
        }

        //public path
        //$destinationPath = public_path('/uploads/profile');

        //$destinationPath = public_path($desPath);

        //for server save path
        $destinationPath = $desPath;

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        //$img = Image::make($fileObject->getRealPath());
        $fileObject->move($destinationPath,$input['fileName']);
        //$img->save($destinationPath . '/' . $input['fileName']);


        if($clientFileName != ''){
            $dbImgPath = $desPath . '/' . $input['fileName'];
        }else{
            $dbImgPath = $desPath . '/' . $input['fileName'];
        }

        if(isset($data['id']) && $data['id']){
            $id = $data['id'];

            //dump($id);
            $User = $modelName::where('id', $id)->first();

            $deleteImgPath = 'public/' . $User->$databaseAttrName;
            //dump($deleteImgPath);
            if (File::exists($deleteImgPath)) {
                File::delete($deleteImgPath);
                dump('file delete');
            }

            $User->$databaseAttrName = $dbImgPath;
            //dd($User->$databaseAttrName);
            return $User->save();

        }
        //dump($dbImgPath);
        return $dbImgPath;
    }

    public static function imageStorage($data)
    {
        $imageObject = $data['requestFileImage'];
        $desPath = $data['desPath']; 
        $modelName = $data['modelName']; 
        $databaseAttrName = $data['databaseAttrName']; 
        $id = $data['id']; 
        
        $input['imageName'] = ImageHelper::generateRandomString() . '.' . $imageObject->getClientOriginalExtension();

        //for server save path
        $destinationPath = $desPath;

        if (!Storage::disk('public')->exists($destinationPath)) {
            Storage::makeDirectory($destinationPath, 0775, true);
            //mkdir($destinationPath, 0777, true);
        }


        //$img = Image::make($imageObject->getRealPath());
        //$img->save($destinationPath . '/' . $input['imageName']);
        //storage put image
        Storage::disk('public')->put($desPath,$input['imageName']);

        dd($destinationPath);
        $dbImgPath = $desPath . '/' . $input['imageName'];

        $User = $modelName::where('id', $id)->first();
        
        // dld img from folder
        $deleteImgPath = 'public/' . $User->$databaseAttrName;

        //Storage::delete('file.jpg');
        if (File::exists($deleteImgPath)) {
            File::delete($deleteImgPath);
        }

        $User->$databaseAttrName = $dbImgPath;
        $User->save();
    }
}
