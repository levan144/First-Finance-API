<?php

namespace App\Http\Controllers\Api\Auth;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\LegalForm;
use App\Models\RepresentativeType;
use App\Models\LegalRepresentative;
use App\Http\Requests\Api\CompanyDetailsGetRequest;
use App\Http\Requests\Api\CompanyDetailsUpdateRequest;
use App\Http\Requests\Api\RegisterUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Requests\Api\LocaleRequest;
use App\Http\Requests\Api\LegalRepresentativeStoreRequest;
use App\Http\Requests\Api\LegalRepresentativeUpdateRequest;
class RegisterController extends Controller
{
    
    /**
     * Create User, Step 1
     * @param Request $request
     * @return User Token
     */
    public function createUser(RegisterUserRequest $request)
    {
        try {
            $user = User::create([
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)
            ]);
            $user->assignRole('company');
            return response()->json([
                'status' => true,
                'message' => __('User successfully created'),
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create User, Step 1
     * @param Request $request
     * @return User Token
     */
    public function updateUser(UpdateUserRequest $request)
    {
        try {
            $user = auth('sanctum')->user();
            
            if(($request->email) && $request->email !== $user->email){
                $user->email_verified_at = null;
                $user->save();
            }
            
            if(($request->phone) && $request->phone !== $user->phone){
                $user->phone_verified_at = null;
                $user->save();
            }
            
            $user = $user->update([
                'email' => $request->email ?? $user->email,
                'phone' => $request->phone ?? $user->phone,
                'password' => Hash::make($request->password)
            ]);
            return response()->json([
                'status' => true,
                'message' => __('User successfully updated'),
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get Data for Company details page, Step 2
     * @param Request $request
     * @return Legal Form list
     */
    public function companyDetailsGet(CompanyDetailsGetRequest $request, LegalForm $legalForm){
        try {
            $user = auth('sanctum')->user();
            $locale = $request->locale ?? 'en';
            $legalForms = $legalForm->all()->map(function ($form) use ($locale) {
                return [
                    'id' => $form->id,
                    'name' => __($form->name, [], $locale),
                ];
            })->reject(function ($value) {
                return empty($value['name']);
            });
            
            return response()->json([
                'status' => true,
                'data' => ['legal_forms' => $legalForms],
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    
    /**
     * Update User, Step 2
     * @param Request $request
     * @return Message
     */
    public function companyDetailsUpdate(CompanyDetailsUpdateRequest $request){
        $user = auth('sanctum')->user();
        try {
            $user->update([
                'name' => $request->input('name'),
                'legal_form' => $request->input('legal_form'),
                'registration_date' => $request->input('registration_date'),
                'registration_number' => $request->input('registration_number'),
                // 'address' => $request->input('address'),
            ]);
    
            return response()->json([
                'status' => true,
                'message' => __('User Data updated successfully'),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    
    /**
     * Update User's Address
     * @param Request $request
     * @return Message
     */
    public function companyAddressUpdate(Request $request){
        $user = auth('sanctum')->user();
        try {
            $address = [
                'line_1' => $request->input('line_1'),
                'line_2' => $request->input('line_2'),
                'zip' => $request->input('zip'),
                'city' => $request->input('city'),
                'country' => $request->input('country'),
            ];
            
            $user->address = $address;
            $user->save();
            
            return response()->json([
                'status' => true,
                'message' => __('User address updated successfully'),
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update User's Address
     * @param Request $request
     * @return Message
     */
    public function companyRegistrationAddressUpdate(Request $request){
        $user = auth('sanctum')->user();
        try {
            $address = [
                'line_1' => $request->input('line_1'),
                'line_2' => $request->input('line_2'),
                'zip' => $request->input('zip'),
                'city' => $request->input('city'),
                'country' => $request->input('country'),
            ];
            
            $user->registration_address = $address;
            $user->save();
            
            return response()->json([
                'status' => true,
                'message' => __('User address updated successfully'),
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get Data for legal representatives page, Step 2
     * @param Request $request
     * @return Representatives(users) & Representative Types
     */
    public function legalRepresentativesGet(LocaleRequest $request){
        // try {
            $user = auth('sanctum')->user();
            $locale = $request->locale ?? 'en';
        
            // Get all legal representative types and map string to the language.
            $legalRepresentativeTypes = RepresentativeType::all()->map(function ($type) use ($locale) {
                return [
                    'id' => $type->id,
                    'name' => __($type->name, [], $locale),
                ];
            })->reject(function ($type) {
                return empty($type['name']);
            })->values();
                        
            //Get Representatives and Map , is it Company Or Person
            $representatives = $this->getRepresentatives($user, $locale);
            return response()->json([
                'status' => true,
                'data' => [
                    'representatives' => $representatives,
                    'representative_types' => $legalRepresentativeTypes,
                ],
            ]);
            
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => $th->getMessage()
        //     ], 500);
        // }
        
    }
    
    private function getRepresentatives($user, $locale){
        return LegalRepresentative::where('user_id', $user->id)
        ->whereNull('parent_id')
        ->select('id', 'name', 'email', 'representative_type', 'share','is_company', 'main_account')
        // ->with('representativeType')
        ->get()
        ->map(function ($form) use ($locale) {
            $item = $form->toArray();
            
            if (!$item['is_company']) {
                $item['type'] = __('Person');
            } else {
                $item['type'] = __('Company');
                $item['representatives'] = $this->getChildrenRepresentatives($form, $locale);
            }
            // $item['representative_type'] = $item['representative_type']['name'][$locale] ?? '';
            unset($item['representative_type_id'], $item['is_company']);
            return $item;
        });
    }
    
    private function getChildrenRepresentatives($user, $locale){
    return LegalRepresentative::where('parent_id', $user->id)
        // ->with('representativeType')
        ->select('id', 'name', 'email', 'parent_id', 'representative_type', 'is_company', 'share', 'main_account')
        ->get()
        ->map(function ($form) use ($locale) {
            $item = $form->toArray();
            if(!$item['is_company']){
                $item['type'] = __('Person');
            } else {
                $item['type'] = __('Company');
                $item['representatives'] = $this->getChildrenRepresentatives($form, $locale);
            }
            // $item['representative_type'] = $item['representative_type']['name'][$locale];
            unset($item['representative_type_id'], $item['is_company']);
            
            return $item;
        });
    }
    
    /**
     * Store Data for legal representatives, Step 2
     * @param Request $request
     * @return User
     */
    public function LegalRepresentativesStore(LegalRepresentativeStoreRequest $request){
        try {
            $company = auth('sanctum')->user();
            $legalRepresentative = new LegalRepresentative([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'phone' => $request->phone ?? null,
                'representative_type' => $request->representative_type ?? null,
                'parent_id' => $request->parent_id ?? null,
                'is_company' => $request->is_company,
                'share' => $request->share ?? 0,
                'user_id' => $company->id,
                'main_account' => $request->is_main_account,
            ]);
            $legalRepresentative->save();
            
            return response()->json([
                'status' => true,
                'message' => __('Representative created successfully'),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update Data for legal representatives, Step 2
     * @param Request $request
     * @return User
     */
    public function legalRepresentativesUpdate(LegalRepresentativeUpdateRequest $request){
        try {
            $legalRepresentative = LegalRepresentative::findOrFail($request->representative_id);
            $legalRepresentative->update([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'phone' => $request->phone,
                'representative_type' => $request->representative_type,
                'is_company' => $request->is_company,
                'share' => $request->share,
                'main_account' => $request->is_main_account,
            ]);
            
            return response()->json([
                'status' => true,
                'message' => __('Representative updated successfully'),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
     public function legalRepresentativesDestroy(Request $request){
        try{
            $user = auth('sanctum')->user();
            $legalRepresentative = LegalRepresentative::find($request->representative_id);
            
            if ($legalRepresentative && $legalRepresentative->user_id === $user->id) {
                $legalRepresentative->delete();
                
                return response()->json([
                    'status' => true,
                    'message' => __('Representative deleted successfully'),
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => __('Representative not found or unauthorized to delete'),
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    

    
}
