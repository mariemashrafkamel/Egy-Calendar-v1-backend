<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Exception;


class FacebookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToFacebook()
    {
        return "test fb";
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
       
        try {
            $user = Socialite::driver('facebook')->user();
            dd($user);
        } catch (\Exception $e) {
            // Handle the exception
            dd($e);
        }

        // Check if the user exists in your database
        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            // User exists, perform necessary actions (e.g., login, update details)
            // For example, you can authenticate the user and redirect them to the home page
            auth()->login($existingUser);

            return redirect('/home')->with('success', 'Successfully logged in.');
        }

        // User doesn't exist, create a new user record
        $newUser = new User();
        $newUser->name = $user->getName();
        $newUser->email = $user->getEmail();
        // ... set other user properties as needed

        // Save the new user to the database
        $newUser->save();

        // Perform necessary actions (e.g., login, redirect)
        // For example, you can authenticate the new user and redirect them to the home page
        auth()->login($newUser);

        return redirect('/home')->with('success', 'Account created and logged in successfully.');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function handleFacebookCallback()
    // {
    //     try {

    //         $user = Socialite::driver('facebook')->user();

    //         $finduser = User::where('facebook_id', $user->id)->first();

    //         if($finduser){

    //             Auth::login($finduser);

    //             return redirect()->intended('dashboard');

    //         }else{
    //             $newUser = User::updateOrCreate(['email' => $user->email],[
    //                     'name' => $user->name,
    //                     'facebook_id'=> $user->id,
    //                     'password' => encrypt('123456dummy')
    //                 ]);

    //             Auth::login($newUser);

    //             return redirect()->intended('dashboard');
    //         }

    //     } catch (Exception $e) {
    //         dd($e->getMessage());
    //     }
    // }
}
