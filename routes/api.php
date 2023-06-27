<?php

use App\Http\Controllers\Api\Auth\GoogleController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Api\Auth\FacebookController;
use App\Http\Controllers\LinkedinController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('/users/login', [UserController::class, 'login']);
Route::post('/users/signup', [UserController::class, 'signup']);
Route::get('/test', [UserController::class, 'test']);

Route::group(['prefix' => 'users' , 'middleware' => 'auth:api'], function () {

    Route::post('', [UserController::class, 'users']);
    Route::post('/current', [UserController::class, 'current']);
    //Route::post('/login', [UserController::class, 'login']);
    //Route::post('/signup', [UserController::class, 'signup']);
    //Route::put('{user?}', [UsersController::class, 'put']);

});
Route::group(['prefix' => 'events'], function () {

    Route::get('', [EventController::class, 'index']);
    Route::get('/{event}', [EventController::class, 'show']);
    Route::post('/add', [EventController::class, 'create']);


});
Route::group(['prefix' => 'ticket', 'middleware' => 'auth:api'], function () {
    Route::post('/payment',[PaymentController::class,'firstStep']);
    Route::post('/payment/verification',[PaymentController::class,'paymentVerification']);


});




Route::get('auth/linkedin', [LinkedinController::class, 'linkedinRedirect']);
Route::get('auth/linkedin/callback', [LinkedinController::class, 'linkedinCallback']);

// Route::controller(FacebookController::class)->group(function(){
//     Route::get('auth/facebook', 'redirectToFacebook')->name('auth.facebook');
//     Route::get('auth/facebook/callback', 'handleFacebookCallback');
// });

Route::get('/auth/google', [GoogleController::class, 'googleLoginUrl']);
Route::get('/auth/google/callback', [GoogleController::class, 'loginCallback']);

Route::get('/login/facebook/callback',[FacebookController::class,'handleFacebookCallback']);
