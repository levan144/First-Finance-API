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
    DocumentController,
    BankController,
    BankAccountController,
    TransactionController,
    UserNotificationController,
    UserController,
    TicketController,
    TopicController

};

use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\AttachmentController;


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
    Route::get('user', [UserController::class, 'show']);
    
    Route::prefix('home')->group(function () {
        Route::prefix('company_verification')->group(function () {
            Route::get('documents', [DocumentController::class, 'show']);
            Route::post('documents/store', [DocumentController::class, 'store']);
            Route::get('documents/required', [DocumentController::class, 'required']);
            Route::post('documents/destroy', [DocumentController::class, 'destroy']);
        });
        
        Route::middleware('isVerified')->group(function () {
            Route::group(['prefix' => 'banks'], function () {
                Route::get('/', [BankController::class, 'index']);
                Route::get('/{id}', [BankController::class, 'show']);
                Route::get('/{id}/accounts', [BankAccountController::class, 'getBankAccountsByBankId']);
                // Add other bank routes as needed
            });
            
            Route::group(['prefix' => 'bank-accounts'], function () {
                Route::get('/{id}', [BankAccountController::class, 'show']);
                // Add other bank account routes as needed
            });
                
            Route::get('/rate', [TransactionController::class, 'exchangeRate']);    
                
            Route::group(['prefix' => 'transactions'], function () {
                
                Route::get('/bank-accounts/{bankAccount}', [TransactionController::class, 'getBankAccountTransactions']);
                Route::get('/bank/{id}', [TransactionController::class, 'getBankTransactions']);
                Route::post('/transfer', [TransactionController::class, 'transfer']);
                Route::post('/exchange', [TransactionController::class, 'exchange']);
                Route::post('/calculate-exchange', [TransactionController::class, 'calculateExchange']);
                Route::post('/calculate-fee', [TransactionController::class, 'calculateFee']);
                Route::get('/', [TransactionController::class, 'index']);
                
                
                Route::get('/{id}', [TransactionController::class, 'show']);
                
                Route::get('/{id}/offers', [TransactionController::class, 'exchange_offers']);
                Route::get('/offers/{id}', [TransactionController::class, 'exchange_offer_show']);
                Route::post('/offers/{id}/update', [TransactionController::class, 'exchange_offer_update']);
                
                Route::get('/invoice/show/{transaction}', [TransactionController::class, 'showInvoice']);
                Route::get('/invoice/download/{transaction}', [TransactionController::class, 'downloadInvoice']);
                
                
                // Add other transaction routes as needed
                
            });
            Route::group(['prefix' => 'beneficiaries'], function () {
                Route::get('/all', [TransactionController::class, 'benecifiary_all']);
                Route::get('/', [TransactionController::class, 'benecifiary_show']);
                Route::delete('/', [TransactionController::class, 'beneficiary_destroy']);
            });
        });
        
        //notifications
        Route::get('notifications', [UserNotificationController::class, 'index']);
        Route::get('notifications/all', [UserNotificationController::class, 'all']);
        Route::post('notifications/read', [UserNotificationController::class, 'markAsRead']);
        Route::post('notifications/all/read', [UserNotificationController::class, 'markAllAsRead']);
        
        //Support Routes
        Route::apiResource('/tickets', TicketController::class);
        Route::post('tickets/{id}/close', [TicketController::class, 'close']);
        Route::apiResource('/topics', TopicController::class);
        Route::apiResource('/messages', MessageController::class);
        Route::apiResource('/attachments', AttachmentController::class);
        
        Route::post('tickets/{id}/mark-as-read', [TicketController::class, 'markAsRead']);
        Route::post('messages/{id}/mark-as-read', [MessageController::class, 'markAsRead']);
        Route::post('tickets/{id}/mark-all-as-read', [TicketController::class, 'markAllMessagesAsRead']);
        
        
        
    });
    
    
});
Route::get('/invoice/show/{transaction}', [TransactionController::class, 'showInvoice']);
                Route::get('/invoice/download/{transaction}', [TransactionController::class, 'downloadInvoice']);
Route::get('sumsub/token', [SumsubController::class, 'getAccessToken']);
Route::get('sumsub/createApplicant', [SumsubController::class, 'createApplicant']);
