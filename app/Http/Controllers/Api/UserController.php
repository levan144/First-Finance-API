<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\LegalRepresentative;
class UserController extends Controller
{
    public function index(){
        
    }
    
    public function single(){
        
    }
    
    public function store(){
        
    }
    
    public function update(){
        
    }
    
    public function refreshToken(Request $request){
    $validatedData = $request->validate([
        'refresh_token' => 'required|string',
    ]);
    $user = auth('sanctum')->user();
    $newAccessToken = $user->createToken('authToken')->accessToken;

    return response()->json([
        'status' => true,
        'user' => $user,
        'access_token' => $newAccessToken,
    ], 200);
}
    
    public function logout(Request $request) {
        auth('sanctum')->user()->tokens()->delete();
        return response()->json([
                    'status' => true,
                    'message' => __('Logged out successfully'),
                    'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 200);
    }
    
    public function changePassword(Request $request) {
    $validatedData = $request->validate([
        'current_password' => 'required|string|min:8',
        'new_password' => 'required|string|min:8',
    ]);

    $user = auth()->user();

    if (!Hash::check($validatedData['current_password'], $user->password)) {
        return response()->json(['status' => false, 'message' => __('Current password is incorrect')], 400);
    }

    $user->password = Hash::make($validatedData['new_password']);
    $user->save();

    return response()->json(['message' => 'Password changed successfully'], 200);
}
    
    public function destroy(Request $request){
        try{
            $user = auth('sanctum')->user();
            $legalRepresentative = LegalRepresentative::where('id', $request->representative_id)->where('user_id', $user->id);
            $user->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
