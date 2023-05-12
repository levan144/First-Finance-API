<?php

namespace App\Http\Controllers\Api\Auth;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use App\Mail\VerifiedEmail;
use App\Models\User;
use App\Models\LegalRepresentative;
use GuzzleHttp\Client;

class VerificationController extends Controller
{
    
  
    
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    
    public function sendEmailVerifyCode(Request $request){
        try {
            $user = auth('sanctum')->user();
            
            if($user->email_verified_at){
                return response()->json([
                    'status' => false,
                    'message' => __("The user's email has already been verified"),
                ], 200);
            }
            
            $pin = rand(100000, 999999);
            $user->email_otp = $pin;
            Mail::to($user->email)->send(new VerifyEmail($pin));
            $user->save();
            
            return response()->json([
                'status' => true,
                'message' => __('The verification code has been sent successfully'),
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function checkEmailVerifyCode(Request $request){
        try {
            $user = auth('sanctum')->user();
            $pin = $request->code;
            if($pin === $user->email_otp) {
                $user->email_verified_at = date('Y-m-d H:i:s');
                
                $user->email_otp = null;
                $user->save();
                Mail::to($user->email)->send(new VerifiedEmail());
                return response()->json([
                    'status' => true,
                    'message' => __('Email was successfully verified!'),
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => __('The verification code is invalid'),
                ], 200);
            }
            
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    

    public function sendPhoneVerifyCodeTest(Request $request)
    {
    //     try {
    //         $user = auth('sanctum')->user();
    //         $phone = $request->input('phone');
    //         $code = rand(1000,9999);
    
    //         $apiKey = 'd7f12b3c006452232e6f7ca6f4f79bde-b70f6b20-19ff-4ce5-8859-84f95fac7abf';
    //         $baseUrl = 'https://5vlm2g.api.infobip.com/sms/2/text/advanced';
    
    //         $client = new Client([
    //             'base_uri' => $baseUrl,
    //             'timeout' => 10.0,
    //         ]);
    
    //         $response = $client->request('POST', '', [
    //             'headers' => [
    //                 'Authorization' => 'App ' . base64_encode($apiKey),
    //                 'Content-Type' => 'application/json',
    //             ],
    //             'json' => [
    //                 'from' => 'One Finance',
    //                 'to' => $phone,
    //                 'pin' => $code,
    //             ],
    //         ]);
    
    //         $statusCode = $response->getStatusCode();
    //         $body = $response->getBody()->getContents();
            
    //         // Handle response
    //         if ($statusCode == 200) {
    //             // Verification successful
    //         } else {
    //             // Verification failed
    //         }
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
        
    // }
    
    try {
            $user = auth('sanctum')->user();
            
            if($user->phone_verified_at){
                return response()->json([
                    'status' => false,
                    'message' => __("The user's phone has already been verified"),
                ], 200);
            }
            
            $pin = rand(100000, 999999);
            $user->phone_otp = $pin;
            // Mail::to($user->phone)->send(new VerifyEmail($pin));
            $user->save();
            
            return response()->json([
                'status' => true,
                'message' => __('The verification code is ' . $pin),
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function sendPhoneVerifyCode(Request $request)
    {
    
        try {
            $user = auth('sanctum')->user();
            
            if($user->phone_verified_at){
                return response()->json([
                    'status' => false,
                    'message' => __("The user's phone has already been verified"),
                ], 200);
            }
            
            $pin = rand(100000, 999999);
            $user->phone_otp = $pin;
            // Mail::to($user->phone)->send(new VerifyEmail($pin));
            
            
            $curl = curl_init();
            $phone = $user->phone ?? $request->phone;
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://5vlm2g.api.infobip.com/sms/2/text/advanced',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{"messages":[{"destinations":[{"to":"'. $phone .'"}],"from":"1finance","text":"PIN Code: '. $pin .'"}]}',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: App d7f12b3c006452232e6f7ca6f4f79bde-b70f6b20-19ff-4ce5-8859-84f95fac7abf',
                    'Content-Type: application/json',
                    'Accept: application/json'
                ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);
            
            $user->save();
            
            return response()->json([
                'status' => true,
                'message' => __('A verification code has been sent to your phone'),
                'response' => $response
            ], 200);
       
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
   
    }
    
    public function checkPhoneVerifyCode(Request $request){
        try {
            $user = auth('sanctum')->user();
            $pin = $request->code;
            if($pin == $user->phone_otp) {
                $user->phone_verified_at = date('Y-m-d H:i:s');
                
                $user->phone_otp = null;
                $user->save();
                // Mail::to($user->email)->send(new VerifiedEmail());
                return response()->json([
                    'status' => true,
                    'message' => __('Phone was successfully verified!'),
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => __('The verification code is invalid'),
                ], 200);
            }
            
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    
    private function getRepresentatives($user, $locale){
        return LegalRepresentative::where('user_id', $user->id)
        ->whereNull('parent_id')
        ->select('id', 'name', 'email', 'representative_type_id', 'share','is_company')
        ->with('representativeType')
        ->get()
        ->map(function ($form) use ($locale) {
            $item = $form->toArray();
            
            if (!$item['is_company']) {
                $item['type'] = __('Person');
            } else {
                $item['type'] = __('Company');
                $item['representatives'] = $this->getChildrenRepresentatives($form, $locale);
            }
            $item['representative_type'] = $item['representative_type']['name'][$locale];
            unset($item['representative_type_id'], $item['is_company']);
            return $item;
        });
    }
    
    private function getChildrenRepresentatives($user, $locale){
    return LegalRepresentative::with('representativeType')
        ->where('parent_id', $user->id)
        ->select('id', 'name', 'email', 'parent_id', 'representative_type_id', 'is_company', 'share')
        ->get()
        ->map(function ($form) use ($locale) {
            $item = $form->toArray();
            if(!$item['is_company']){
                $item['type'] = __('Person');
            } else {
                $item['type'] = __('Company');
                $item['representatives'] = $this->getChildrenRepresentatives($form, $locale);
            }
            $item['representative_type'] = $item['representative_type']['name'][$locale];
            unset($item['representative_type_id'], $item['is_company']);
            return $item;
        });
    }
    
    public function identityVerification(Request $request){
        try {
            $locale = $request->locale ?? 'en';
            $company = auth('sanctum')->user();
            // $representatives = $company->representatives;
            $representatives = $this->getRepresentatives($company, $locale);
            $persons = array_values(collect($this->_flattened($representatives))->where('type', __('Person'))->all());
            return response()->json([
                'status' => true,
                'data' => array('representatives' => $persons),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    protected function _flattened($array){
        $flatArray = [];

        if (!is_array($array)) {
            $array = $array->toArray();
        }

        foreach($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $flatArray = array_merge($flatArray, $this->_flattened($value));
            } else {
                $flatArray[0][$key] = $value;
            }
        }

        return $flatArray;
    }
    
   


    
}
