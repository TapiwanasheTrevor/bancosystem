<?php

namespace App\Http\Controllers;

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

        //get forms with the same id
        $form = Form::find($validatedData['insertId']);
        $form->id_document = $idDocumentPath;
        $form->passport_photo = $passportPhotoPath;
        $form->payslip = $payslipPath;
        $form->signature = $validatedData['signature'];
        $form->save();

        return response()->json(['success' => true, 'message' => 'KYC documents uploaded successfully.'], 201);
    }
}
