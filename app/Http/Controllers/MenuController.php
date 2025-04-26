<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Get all menu items that the authenticated user has access to
     */
    public static function getAuthorizedMenus()
    {
        $user = Auth::user();
        
        if (!$user) {
            return collect();
        }
        
        // Get user role
        $roleId = $user->roles_id;
        
        // Get all menu items that the role has access to
        $menuItems = DB::table('menu')
            ->join('roles_has_menu', 'menu.id', '=', 'roles_has_menu.menu_id')
            ->where('roles_has_menu.roles_id', $roleId)
            ->where('menu.statusActive', 1)
            ->orderBy('menu.id')
            ->select('menu.*')
            ->get();
        
        return $menuItems;
    }
    
    /**
     * Check if user has access to a specific menu
     */
    public static function hasAccess($menuId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Get user role
        $roleId = $user->roles_id;
        
        // Check if role has access to the menu
        $hasAccess = DB::table('roles_has_menu')
            ->where('roles_id', $roleId)
            ->where('menu_id', $menuId)
            ->exists();
        
        return $hasAccess;
    }
    
    /**
     * Check if user has role-specific access to configurations
     * This function checks if the user has a specific role
     */
    public static function hasConfigAccess()
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Only role id 2 can access configurations
        return $user->roles_id == 2;
    }
}