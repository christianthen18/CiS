<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($id);
        $newRoleId = $request->role_id;

        // Check if trying to assign Owner role (ID 2)
        if ($newRoleId == 2) {
            // Check if any other user already has the Owner role
            $ownerExists = DB::table('users')
                ->where('roles_id', 2)
                ->where('id', '!=', $id)
                ->exists();

            if ($ownerExists) {
                return redirect()->back()->with('error', 'Owner role can only be assigned to one user.');
            }
        }

        $user->roles_id = $newRoleId;
        $user->save();

        return redirect()->back()->with('status', 'User role has been updated successfully.');
    }
}
