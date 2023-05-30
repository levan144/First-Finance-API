<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Document;
class HomeController extends Controller
{
    public function home() {
        try {
            $user = auth('sanctum')->user();
            $data = ['name' => $user->name, 'email' => $user->email, 'balance_due' => $user->balance_due];
            // Determine company verification status
            if ($user->verified_at) {
                
                $data['company_verification'] = 'Approved';
            } else {
                $documents = $user->documents;
                $verificationStatuses = $documents->pluck('status')->unique();
        
                if ($verificationStatuses->contains('Rejected')) {
                    $data['company_verification'] = 'Rejected';
                } elseif ($verificationStatuses->contains('Pending') || $verificationStatuses->contains('Approved')) {
                    $data['company_verification'] = 'Pending';
                } else {
                    $data['company_verification'] = 'Unknown';
                }
            }
        
            // Check if all representatives are verified
            $data['all_representatives_verified'] = $user->allRepresentativesVerified();
            $data['is_verified'] = $user->verified_at ? true : false;
            return response()->json([
                'status' => true,
                'user' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }

    }
}
