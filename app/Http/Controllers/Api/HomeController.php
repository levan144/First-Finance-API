<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function home() {
        try {
            $user = auth('sanctum')->user();
            $data = ['name' => $user->name, 'email' => $user->email];
            $data['is_verified'] =  $user->verified_at ? true : false;
            $data['all_representatives_verified'] =  $user->allRepresentativesVerified();
            return response()->json([
                'status' => true,
                'user' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
