<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    Auth\LoginController,
    Auth\RegisterController,
    Auth\VerificationController,
    Auth\SumSubController,
    SubscriptionController,
    HomeController,
    DocumentController
};
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

Route::prefix('auth')->group(function () {
    Route::post('register', [RegisterController::class, 'createUser']);
    Route::post('login', [LoginController::class, 'loginUser']);
});

Route::post('subscription/store', [SubscriptionController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::prefix('email/verify')->group(function () {
        Route::post('send', [VerificationController::class, 'sendEmailVerifyCode']);
        Route::post('check', [VerificationController::class, 'checkEmailVerifyCode']);
    });

    Route::prefix('phone/verify')->group(function () {
        Route::post('send', [VerificationController::class, 'sendPhoneVerifyCode']);
        Route::post('check', [VerificationController::class, 'checkPhoneVerifyCode']);
        Route::post('test', [VerificationController::class, 'sendPhoneVerifyCodeTest']);
    });

    Route::prefix('auth/register')->group(function () {
        Route::post('update', [RegisterController::class, 'updateUser']);
        Route::get('company_details/get', [RegisterController::class, 'companyDetailsGet']);
        Route::post('company_details/update', [RegisterController::class, 'companyDetailsUpdate']);
        Route::post('company_address/update', [RegisterController::class, 'companyAddressUpdate']);
        Route::post('company_registration_address/update', [RegisterController::class, 'companyRegistrationAddressUpdate']);
        Route::get('legal_representatives/get', [RegisterController::class, 'legalRepresentativesGet']);
        Route::post('legal_representatives/store', [RegisterController::class, 'legalRepresentativesStore']);
        Route::post('legal_representatives/update', [RegisterController::class, 'legalRepresentativesUpdate']);
        Route::post('legal_representatives/destroy', [RegisterController::class, 'legalRepresentativesDestroy']);
        Route::get('identity_verification/get', [VerificationController::class, 'identityVerification']);
    });

    Route::get('home', [HomeController::class, 'home']);
    
    Route::prefix('home')->group(function () {
        Route::prefix('company_verification')->group(function () {
            Route::get('documents', [DocumentController::class, 'show']);
            Route::post('documents/store', [DocumentController::class, 'store']);
            Route::get('documents/required', [DocumentController::class, 'required']);
            Route::post('documents/destroy', [DocumentController::class, 'destroy']);
        });
    });
    // Route::middleware('isVerified')->group(function () {
    //     // Routes that require verification
    // });
});

Route::get('sumsub/token', [SumsubController::class, 'getAccessToken']);
Route::get('sumsub/createApplicant', [SumsubController::class, 'createApplicant']);
