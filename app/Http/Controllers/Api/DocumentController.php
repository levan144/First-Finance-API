<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LegalRepresentative;
use App\Models\Document;
use App\Http\Requests\Api\DocumentShowRequest;
use App\Http\Requests\Api\DocumentUploadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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
}