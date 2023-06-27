<?php

namespace App\Http\Controllers;

use App\Http\Requests\GmailRequest;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    
    public function users(Request $request)
    {

        $user = User::all();
        return $user;
    }
    public function current(Request $request)
    {
        $user = Auth::user();
        return $user;
    }
    public function signup(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|min:2',
            'last_name'  => 'required|min:2',
            'email'      => 'required|email|unique:users',
           // 'mobile'     => 'required',
            'password'   => 'required|min:6',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' =>  $request->email,
            'password' =>  bcrypt($request->password),
            'mobile' =>  $request->mobile,
        ]);

        $token = $user->createToken('TutsForWeb')->accessToken;



        return response()->json(['token' => $token,'message' => 'User inserted successfully!','status'=>'success' ],200);
    }
    public function login(Request $request){
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if(auth()->attempt($credentials)){
            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
            $user = Auth::user();
            return response()->json(['token'=>$token,'user'=>$user,'message' => 'Successfully','status'=>'success'],200);
        }
        else
        {
            return response()->json(['error'=>'UnAuthorised'],401);
        }

    }
    public function test(Request $request)
    {

        return "ok";
    }

}
