<?php

namespace App\Http\Controllers\Api\Auth;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Api\LoginUserRequest;
use App\Models\User;
use App\Notifications\LoginNotification;
class LoginController extends Controller
{
    
    /**
     * Login The User
     * @param Request $request
     * @return User Token
     */
    public function loginUser(LoginUserRequest $request)
    {
        try {
           
            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            
            $user->notify(new LoginNotification());
            
            return response()->json([
                'status' => true,
                'message' => 'The user has successfully logged in',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    
}
