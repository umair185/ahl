<?php

namespace app\Helpers;

use App\Models\User;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Auth;

class NotificationHelper
{
    public static function sendMultiFCMNotification($notificationData) {
        // $obj = {"multicast_id":3524764761887012646,"success":1,"failure":0,"canonical_ids":0,"results":[{"message_id":"0:1592040420129243%7031b2e6f9fd7ecd"}]};
        // dd($obj);

        //dd($notificationData);
        //$url = 'https://fcm.googleapis.com/fcm/send';
        $url = 'https://fcm.googleapis.com/fcm/send';
        //$token = $token;

        $notification = [
            'title' => $notificationData['body']['name'],
            'body' => $notificationData['body']['message'],
            'sound' => true,
        ];

        //$token = 'eZ7qQgcOTgCM4IxEJhUmlM:APA91bEnSoG8Rv37EEMeRaArVH3Dm64FI4EWEGFwggEY8StBvKLU0574pu14WNLOVHsDj0TI8qW6Vfgq-NJj4XvGlku1WHGJWEbimzXwbxMeDO7QwYu6DIPGNaK6UQePbJezpwta0Fnk';
        
        //$extraNotificationData = ["message" => $notification,"moredata" =>'dd'];

        $fcmNotification = [
            //'registration_ids' => $tokenList, //multple token array
            'to' => $notificationData['token'], //single token
            'notification' => $notification,
            //'data' => $extraNotificationData
        ];

        //dd($fcmNotification);
        $headers = [
            'Authorization: key=' . 'AAAAqVJdzGU:APA91bHWmyotLOYDe7yc24NH6WtrlbBjE5VtporQn2emj1KbTEU0vfjb-Gk3FEqNhczJ65nB_BeL_Js2Kyw0T7SPPlxunsz9OFVlNuNKj6vIUu-gadxjbCBIF8ic55Un25wkkuvTH8so',
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));

        // Execute post
        $result = curl_exec($ch);
        //dd($result);

        if ($result === false) {
            $logs = new NotificationLog();
            $logs->to_user_id = $notificationData['to_user_id'];
            $logs->user_type = $notificationData['user_type'];
            $logs->text = $notificationData['body']['message'];
            $logs->curl_status = curl_error($ch);
            $logs->token = $notificationData['token'];
            $logs->message_status = $result;
            $logs->save();
            //dd($logs);
            return 'Curl failed: ' . curl_error($ch);
        }
        // Close connection
        curl_close($ch);
        $logs = new NotificationLog();
        $logs->to_user_id = $notificationData['to_user_id'];
        $logs->user_type = $notificationData['user_type'];
        $logs->text = $notificationData['body']['message'];
        $logs->token = $notificationData['token'];
        //$logs->curl_status = $result;
        $logs->message_status = $result;
        $logs->save();
    }
}
