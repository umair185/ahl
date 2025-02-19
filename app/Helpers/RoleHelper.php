<?php

namespace app\Helpers;

use Illuminate\Support\Facades\Auth;

class RoleHelper
{
    public static function getUserRoleName($user = '')
    {
    	if(empty($user)){
    		$user = Auth::user();
    	}
		
		$roleNames  = $user->roles->pluck('name');

		foreach ($roleNames as $key => $name) {
			$strReplace = str_replace("-"," ",$name);
			$roleName[] = ucwords($strReplace);
		}

		$userRoleName = implode(" ",$roleName);
		return $userRoleName;
    }

    public static function showRoleName($roleName)
    {
        $strReplace = str_replace("_"," ",$roleName);
        $userRoleName = ucwords($strReplace);

        return $userRoleName;
    }

    public static function parcelStatusByRole($role)
    {
    	switch ($role) {
    		case 'picker':
    			//picker picked the parcel from vendor
    			$status = 2;
    			break;
    		case 'middle_man':
    			//At AHL Wearhouse
    			$status = 3;
    			break;
    		case 'supervisor':
    			//Scan By Supervisor
    			$status = 4;
    			break;
    		case 'rider':
    			//Dispatch
    			$status = 5;
    			break;
    		
    		default:
    			# code...
    			break;
    	}

    	return $status;
    }
}
