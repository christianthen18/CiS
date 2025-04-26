<?php

namespace App\Http\Controllers;

use App\Models\PosSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //

    public function login()
    {
        return view('login');
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            $possession = PosSession::firstOrCreate(
                [
                    'users_id' => $user->id,
                    'Date' => Carbon::now()->toDateString()
                ],
                [
                    'cash_in' => 0,
                    'cash_out' => 0,
                    'session_status' => 'open',
                    'total_income' => 0
                ]
            );

            if (!$possession->wasRecentlyCreated) {
                $possession->update(['session_status' => 'open']);
            }

            return redirect('/')->with('success', 'Berhasil login');
        }

        return redirect()->back()->withErrors('Ops, harap cek email atau password Anda');
    }

    public function register()
    {
        return view('register');
    }

    public function registerPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255', // We'll use this as the username
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);
    
        $userCount = User::count();
        $roleId = ($userCount === 0) ? 2 : 1;
        
        $user = User::create([
            'username' => $request->name, // Set username from name input
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status_active' => 1, // Assuming you want to activate the user
            'roles_id' => $roleId // Assign role based on whether this is the first user
        ]);
    
        // Automatically log in the user after registration
        Auth::login($user);
    
        // Create initial POS session
        PosSession::create([
            'users_id' => $user->id,
            'Date' => Carbon::now()->toDateString(),
            'cash_in' => 0,
            'cash_out' => 0,
            'session_status' => 'open',
            'total_income' => 0
        ]);
    
        return redirect('/')->with('success', 'Registrasi berhasil');
    }

    public function logout()
    {
        if (Auth::check()) {
            $user = Auth::user();
            PosSession::where('users_id', $user->id)
                ->where('session_status', 'open')
                ->update(['session_status' => 'close']);

            Auth::logout();
        }
        return redirect('/login')->with('success', 'Berhasil logout');
    }
}
