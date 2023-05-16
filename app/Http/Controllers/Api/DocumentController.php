<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\LegalRepresentative;
use App\Models\Document;
use App\Http\Requests\Api\DocumentShowRequest;
use App\Http\Requests\Api\DocumentUploadRequest;
use App\Models\RequiredDocumentType;
use Illuminate\Support\Facades\File;
class DocumentController extends Controller
{
    public function store(DocumentUploadRequest $request){
        try{
            // The incoming request is valid...
            $documentable = auth('sanctum')->user();
            $documents = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store('documents', 'public');
                $document = new Document;
                $document->name = $file->getClientOriginalName();
                // $document->file_path = Storage::url($path);
                $document->file_path = $path;
                $documents[] = $document;
            }
            
            // Save the documents
            $documentable->documents()->saveMany($documents);
        
            return response()->json(['status' => true, 'message' => __('File uploaded successfully'), 'documents' => $documents]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function show(DocumentShowRequest $request) {
        try{
            $user = auth('sanctum')->user();
            $documents = $user->documents;
            if ($documents->count() == 0) {
                return response()->json(['status' => false, 'message' => __('No such entity found')], 404);
            }
        
            return response()->json(['status' => true, 'documents' => $documents]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function required(Request $request) {
        try{
            $user = auth('sanctum')->user();
            $locale = $request->locale ?? 'en';
            if($user->verified_at) {
                $requiredDocumentTypes = RequiredDocumentType::selectRaw("id, name->>'$." . $locale . "' as title")->get();
                return response()->json(['status' => true, 'required_documents' => $requiredDocumentTypes]);
            }
            return response()->json(['status' => true, 'required_documents' => null, 'message' => __('The company has already been verified')]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function destroy(Request $request){
        try{
            $user = auth('sanctum')->user();
            // Find the document by ID and check if it belongs to the user
            $document = Document::where('id', $request->id)
                        ->where('documentable_type', User::class) // Assuming the document is associated with the User model
                        ->where('documentable_id', $user->id)
                        ->first();
            // If the document does not exist or does not belong to the user, return an error response
            if (!$document) {
                return response()->json(['status' => false ,'message' => __('Document not found or unauthorized')], 403);
            }
             // Delete the document file
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            // Delete the document record from the database
            $document->delete();
            return response()->json(['status' => true ,'message' => __('Document deleted successfully')]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
