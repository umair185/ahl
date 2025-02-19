<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ManageRoleController extends Controller
{
    public function manageRole()
    {
        //dd('manage-role');
        //Role's
        /*$role = Role::create(['name' => 'admin']);//mindech or AHL
        $role = Role::create(['name' => 'vendor_admin']);//company owner
        $role = Role::create(['name' => 'vendor_editor']);//vendor editor
        $role = Role::create(['name' => 'middle_man']);//warehouse middle man
        $role = Role::create(['name' => 'supervisor']);//manage warehouse
        $role = Role::create(['name' => 'picker']);//
        $role = Role::create(['name' => 'rider']);//
        $role = Role::create(['name' => 'cashier']);//
        $role = Role::create(['name' => 'first_man']);//*/
        $role = Role::create(['name' => 'financer']);//

        //AHL and Mindtech Admin
        //$role = Role::findByName('admin');
        
        //$mindtechAdmin = User::whereEmail('mindtech@admin.com')->first();
        //$mindtechAdmin->assignRole('admin');
        //$mindtechAdmin->role('admin')->get();

        //$ahlAdmin = User::whereEmail('ahl@admin.com')->first();
        //$ahlAdmin->assignRole('admin');
        //$ahlAdmin->role('admin')->get();
        
        dump('done');
        
        //$superAdmin = Auth::user();
        //$superAdminId = $superAdmin->id;
    }
}
