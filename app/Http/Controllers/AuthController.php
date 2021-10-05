<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller{
    public function register(Request $request){
        $this->validate($request,[
            'name' => 'required|string',
            'user_name' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        try {
            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->userName = $request->input('user_name');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);
            $user->save();
            return response()->json(['user' => $user, 'message' => 'CREATED'],201 );
        }catch (\Exception $e){
            return dd($user);
            return response()->json(['message' => 'User registration Failed!!'], 409);
        }
    }
}
