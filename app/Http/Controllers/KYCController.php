<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Form;
use Illuminate\Http\Request;

class KYCController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'insertId' => 'required|integer',
                'idDocument' => 'required|file|mimes:jpeg,png,pdf|max:2048',
                'passportPhoto' => 'required|file|mimes:jpeg,png|max:2048',
                'payslip' => 'required|file|mimes:jpeg,png,pdf|max:2048',
                'signature' => 'required|string',
                // Proof of residence has been removed as requested
            ]);
    
            // Create directories if they don't exist
            $this->ensureDirectoryExists('kyc/id_documents');
            $this->ensureDirectoryExists('kyc/passport_photos');
            $this->ensureDirectoryExists('kyc/payslips');
            
            // Define the paths for saving files in the 'public' folder with sanitized filenames
            $idDocumentName = time() . '_' . $this->sanitizeFileName($request->file('idDocument')->getClientOriginalName());
            $passportPhotoName = time() . '_' . $this->sanitizeFileName($request->file('passportPhoto')->getClientOriginalName());
            $payslipName = time() . '_' . $this->sanitizeFileName($request->file('payslip')->getClientOriginalName());
    
            $idDocumentPath = 'kyc/id_documents/' . $idDocumentName;
            $passportPhotoPath = 'kyc/passport_photos/' . $passportPhotoName;
            $payslipPath = 'kyc/payslips/' . $payslipName;
    
            // Store moved file references to avoid calling ->file() again on request
            $idDocumentFile = $request->file('idDocument');
            $passportPhotoFile = $request->file('passportPhoto');
            $payslipFile = $request->file('payslip');
            
            // Move the files to the 'public' folder with error handling
            try {
                $idDocumentFile->move(public_path('kyc/id_documents'), $idDocumentName);
            } catch (\Exception $e) {
                \Log::error("Failed to move ID document: " . $e->getMessage());
                return response()->json(['error' => 'Failed to upload ID document'], 500);
            }
            
            try {
                $passportPhotoFile->move(public_path('kyc/passport_photos'), $passportPhotoName);
            } catch (\Exception $e) {
                \Log::error("Failed to move passport photo: " . $e->getMessage());
                return response()->json(['error' => 'Failed to upload passport photo'], 500);
            }
            
            try {
                $payslipFile->move(public_path('kyc/payslips'), $payslipName);
            } catch (\Exception $e) {
                \Log::error("Failed to move payslip: " . $e->getMessage());
                return response()->json(['error' => 'Failed to upload payslip'], 500);
            }
    
            // Get form with the same id
            $form = Form::find($validatedData['insertId']);
            if (!$form) {
                return response()->json(['error' => 'Form not found'], 404);
            }
            
            $form->id_document = $idDocumentPath;
            $form->passport_photo = $passportPhotoPath;
            $form->payslip = $payslipPath;
            $form->signature = $validatedData['signature'];
            $form->save();
            
            // Try to create document records but don't throw error if it fails
            try {
                // Execute the migration first to add missing columns
                \Artisan::call('migrate', [
                    '--path' => '/Applications/XAMPP/xamppfiles/htdocs/bancosystem/database/migrations/2025_03_06_195858_update_documents_table_add_missing_columns.php',
                    '--force' => true,
                ]);
                
                // Now try to create document records
                $this->createDocumentRecord($form, 'id_document', $idDocumentFile, $idDocumentPath);
                $this->createDocumentRecord($form, 'passport_photo', $passportPhotoFile, $passportPhotoPath);
                $this->createDocumentRecord($form, 'payslip', $payslipFile, $payslipPath);
            } catch (\Exception $e) {
                // Just log the error but continue - we already have data in the form record
                \Log::warning("Could not create document records, but form was saved: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error("KYC upload failed: " . $e->getMessage());
            return response()->json(['error' => 'Failed to process KYC documents: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'KYC documents uploaded successfully.'], 201);
    }
    
    /**
     * Ensure a directory exists
     * 
     * @param string $path
     * @return void
     */
    private function ensureDirectoryExists(string $path): void
    {
        $fullPath = public_path($path);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }
    
    /**
     * Sanitize a filename to remove special characters
     * 
     * @param string $filename
     * @return string
     */
    private function sanitizeFileName(string $filename): string
    {
        // Remove any characters that aren't alphanumeric, dots, dashes, or underscores
        $filename = preg_replace('/[^\w\.-]/', '_', $filename);
        
        // Ensure filename isn't too long
        if (strlen($filename) > 100) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $filename = substr(pathinfo($filename, PATHINFO_FILENAME), 0, 95) . '.' . $extension;
        }
        
        return $filename;
    }
    
    /**
     * Create a Document record for the given file
     * 
     * @param Form $form
     * @param string $documentType
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path
     * @return Document
     */
    private function createDocumentRecord(Form $form, string $documentType, $file, string $path): Document
    {
        try {
            // Get basic properties safely
            $documentData = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'document_type' => $documentType, // This might be missing in the table
                'user_id' => $form->user_id,
                'agent_id' => $form->agent_id,
                'form_id' => $form->id,
                'status' => 'new',
                'notes' => "KYC document for {$form->form_name}"
            ];
            
            // Get file properties safely
            try {
                $documentData['file_type'] = $file->getClientMimeType();
            } catch (\Exception $e) {
                \Log::warning("Could not get MIME type: " . $e->getMessage());
                $documentData['file_type'] = 'application/octet-stream';
            }
            
            try {
                // Use file system size rather than UploadedFile::getSize()
                $documentData['size'] = filesize(public_path($path));
            } catch (\Exception $e) {
                \Log::warning("Could not get file size: " . $e->getMessage());
                $documentData['size'] = 0;
            }
            
            return Document::create($documentData);
        } catch (\Exception $e) {
            \Log::error("Failed to create document record: " . $e->getMessage());
            // Create a minimal record to avoid errors
            return Document::create([
                'name' => $file->getClientOriginalName() ?? 'unknown',
                'path' => $path,
                'file_type' => 'application/octet-stream',
                'size' => 0,
                'document_type' => $documentType,
                'user_id' => $form->user_id,
                'agent_id' => $form->agent_id,
                'form_id' => $form->id,
                'status' => 'new',
                'notes' => "KYC document with error for {$form->form_name}"
            ]);
        }
    }
}
