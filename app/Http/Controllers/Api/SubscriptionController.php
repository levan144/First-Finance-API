<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubscriptionEmailStoreRequest;
use App\Models\SubscriptionEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscribedUser;
class SubscriptionController extends Controller
{
    public function store(SubscriptionEmailStoreRequest $request){
        try {
            $user = SubscriptionEmail::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone
            ]);
            
            Mail::to('makarychev@1finance.net')->send(new SubscribedUser($user));
            
            return response()->json([
                'status' => true,
                'message' => __('Your data has been successfully added to the list'),
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
