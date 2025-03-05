<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Form;
use Illuminate\Http\Request;

class KYCController extends Controller
{
    public function upload(Request $request)
    {
        $validatedData = $request->validate([
            'insertId' => 'required|integer',
            'idDocument' => 'required|file|mimes:jpeg,png,pdf|max:2048',
            'passportPhoto' => 'required|file|mimes:jpeg,png|max:2048',
            'payslip' => 'required|file|mimes:jpeg,png,pdf|max:2048',
            'signature' => 'required|string',
        ]);

        // Define the paths for saving files in the 'public' folder
        $idDocumentName = time() . '_' . $request->file('idDocument')->getClientOriginalName();
        $passportPhotoName = time() . '_' . $request->file('passportPhoto')->getClientOriginalName();
        $payslipName = time() . '_' . $request->file('payslip')->getClientOriginalName();

        $idDocumentPath = 'kyc/id_documents/' . $idDocumentName;
        $passportPhotoPath = 'kyc/passport_photos/' . $passportPhotoName;
        $payslipPath = 'kyc/payslips/' . $payslipName;

        // Move the files to the 'public' folder
        $request->file('idDocument')->move(public_path('kyc/id_documents'), $idDocumentName);
        $request->file('passportPhoto')->move(public_path('kyc/passport_photos'), $passportPhotoName);
        $request->file('payslip')->move(public_path('kyc/payslips'), $payslipName);

        // Get form with the same id
        $form = Form::find($validatedData['insertId']);
        $form->id_document = $idDocumentPath;
        $form->passport_photo = $passportPhotoPath;
        $form->payslip = $payslipPath;
        $form->signature = $validatedData['signature'];
        $form->save();
        
        // Also create Document records for better tracking
        $this->createDocumentRecord($form, 'id_document', $request->file('idDocument'), $idDocumentPath);
        $this->createDocumentRecord($form, 'passport_photo', $request->file('passportPhoto'), $passportPhotoPath);
        $this->createDocumentRecord($form, 'payslip', $request->file('payslip'), $payslipPath);

        return response()->json(['success' => true, 'message' => 'KYC documents uploaded successfully.'], 201);
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
        return Document::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'file_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'document_type' => $documentType,
            'user_id' => $form->user_id,
            'agent_id' => $form->agent_id,
            'form_id' => $form->id,
            'status' => 'new',
            'notes' => "KYC document for {$form->form_name}"
        ]);
    }
}
