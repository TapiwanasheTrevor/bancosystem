<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicationStatusController extends Controller
{
    /**
     * Check application status by reference number
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string',
        ]);

        $referenceNumber = $request->reference_number;
        
        // Find application by reference number (UUID)
        $application = Form::where('uuid', $referenceNumber)->first();
        
        if (!$application) {
            return response()->json([
                'status' => 'error',
                'message' => 'Application not found with the provided reference number',
            ], 404);
        }
        
        // Extract relevant data from the application
        $applicationData = [
            'id' => $application->id,
            'uuid' => $application->uuid,
            'status' => $application->status ?? 'pending',
            'created_at' => $application->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $application->updated_at->format('Y-m-d H:i:s'),
        ];
        
        // Include product details if available
        if ($application->questionnaire_data) {
            $questionnaireData = json_decode($application->questionnaire_data, true);
            if (isset($questionnaireData['selectedProduct'])) {
                $applicationData['product'] = [
                    'name' => $questionnaireData['selectedProduct']['product']['name'] ?? 'Unknown',
                    'category' => $questionnaireData['selectedProduct']['category'] ?? 'Unknown',
                    'months' => $questionnaireData['selectedProduct']['selectedCreditOption']['months'] ?? 0,
                    'installment_amount' => $questionnaireData['selectedProduct']['selectedCreditOption']['installment_amount'] ?? '0',
                ];
            }
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $applicationData,
        ]);
    }
}
