<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class GoogleController extends Controller
{
    public function googleLoginUrl(Request $request)
    {
        //return Socialite::driver('google')->redirect();

        // return Response::json([
        //     'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        //     //'url' => Socialite::driver('google')->redirect()->getTargetUrl(),
        // ]);

        $redirectUrl = url('/login/google/callback');
        $url = Socialite::driver('google')
            ->with(["redirect_uri" => $redirectUrl])
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return $url;


    }


    public function loginCallback(Request $request)
    {
        $accessToken = $request->googleToken;

        $url = 'https://people.googleapis.com/v1/people/me?personFields=names,emailAddresses';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);


        if (curl_errno($ch)) {
            // Handle curl error
            $error = curl_error($ch);
            curl_close($ch);
            // Handle the error gracefully
            // ...
        }


        curl_close($ch);

        // Process the API response
        $userData = json_decode($response, true);

        //return $userData;
        // Access the user data

        $name = $userData['names'][0]['displayName'];
        if ( $userData['emailAddresses']) {
            $email = $userData['emailAddresses'][0]['value'];

        }
        else {
            return response()->json(['message'=>'use another gmail account this account has a problem with sharing email with third party','status'=>'failed' ],200);

        }

        // Check if the user already exists in your database
        $existingUser = User::where('email', $email)->first();
        $user = [
        'name'=>$name,
        'email'=>$email
       ];
        if ($existingUser) {
            // User exists, perform necessary actions (e.g., login, update details)
            // For example, you can authenticate the user and redirect them to the home page
            auth()->login($existingUser);

            return response()->json(['message'=>'successfully logged in','status'=>'success' ,'user'=>$user],200);
        }
        else {
            $name = explode(" ", $name);
            // Generate a random password
            //$password = Str::random(10); // Generates a random string of length 10

            // Alternatively, you can use the following to generate a password with specific requirements:
             $password = Str::random(8).'!9Aa'; // Generates an 8-character password with at least one uppercase, one lowercase, one number, and one special character
             $hashedPassword = Hash::make($password);
              // User doesn't exist, create a new user record
              $newUser = new User();
              $newUser->first_name = $name[0];
              $newUser->last_name = $name[1];
              $newUser->email = $email;
              $newUser->password = $hashedPassword;
              // ... set other user properties as needed

              // Save the new user to the database
              $newUser->save();

              // Perform necessary actions (e.g., login, redirect)
              // For example, you can authenticate the new user and redirect them to the home page
              auth()->login($newUser);

              return response()->json(['message'=>'successfully logged in','status'=>'success' ,'user'=>$newUser],200);
        }


        return $name;
        // //dd($response);
        //  try {
        //    // dd($request->all());
        //         $user = Socialite::driver('google')->stateless()->user();
        //     } catch (\Exception $e) {
        //         dd($e->getMessage());
        //         // Handle any exceptions that occur during the authentication process
        //         return response()->json(['message'=>'Google authentication failed.','status'=>'failed'],401);
        //         //return redirect('/login')->with('error', 'Google authentication failed.');
        //     }

        //     // Check if the user already exists in your database
        //     $existingUser = User::where('email', $user->getEmail())->first();

        //     if ($existingUser) {
        //         // User exists, perform necessary actions (e.g., login, update details)
        //         // For example, you can authenticate the user and redirect them to the home page
        //         auth()->login($existingUser);

        //         return redirect('/home')->with('success', 'Successfully logged in.');
        //     }

        //     // User doesn't exist, create a new user record
        //     $newUser = new User();
        //     $newUser->name = $user->getName();
        //     $newUser->email = $user->getEmail();
        //     // ... set other user properties as needed

        //     // Save the new user to the database
        //     $newUser->save();

        //     // Perform necessary actions (e.g., login, redirect)
        //     // For example, you can authenticate the new user and redirect them to the home page
        //     auth()->login($newUser);

        //     return redirect('/home')->with('success', 'Account created and logged in successfully.');
            }
}
