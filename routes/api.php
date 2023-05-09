<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Middleware\EnsureUserIsVerified;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('auth/register', [RegisterController::class, 'createUser']);
Route::post('auth/login', [LoginController::class, 'loginUser']);

//Saving emails in list
Route::post('subscription/store', [SubscriptionController::class, 'store']);

Route::group(['middleware' => ['auth:sanctum']], function() {
    //Send&Check Email Verification Code
    Route::post('email/verify/send', [VerificationController::class, 'sendEmailVerifyCode']);
    Route::post('email/verify/check', [VerificationController::class, 'checkEmailVerifyCode']);
    
    Route::post('phone/verify/send', [VerificationController::class, 'sendPhoneVerifyCode']);
    Route::post('phone/verify/check', [VerificationController::class, 'checkPhoneVerifyCode']);
    Route::post('phone/verify/test', [VerificationController::class, 'sendPhoneVerifyCodeTest']);
    Route::post('auth/register/update', [RegisterController::class, 'updateUser']);

    //Register steps
    Route::get('auth/register/company_details/get', [RegisterController::class, 'companyDetailsGet']);
    Route::post('auth/register/company_details/update', [RegisterController::class, 'companyDetailsUpdate']);
    
    Route::post('auth/register/company_address/update', [RegisterController::class, 'companyAddressUpdate']);
    Route::post('auth/register/company_registration_address/update', [RegisterController::class, 'companyRegistrationAddressUpdate']);
    
    Route::get('auth/register/legal_representatives/get', [RegisterController::class, 'legalRepresentativesGet']);
    Route::post('auth/register/legal_representatives/store', [RegisterController::class, 'legalRepresentativesStore']);
    Route::post('auth/register/legal_representatives/update', [RegisterController::class, 'legalRepresentativesUpdate']);
    Route::post('auth/register/legal_representatives/destroy', [RegisterController::class, 'legalRepresentativesDestroy']);
    //Identity Verification
    Route::get('auth/register/identity_verification/get', [VerificationController::class, 'identityVerification']);
    
    //Home
    Route::get('home', [HomeController::class, 'home']);
    
    Route::group(['middleware' => ['isVerified']], function() {
        
    });  
});