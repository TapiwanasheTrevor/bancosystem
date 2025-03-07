<?php

namespace App\Exports;

use App\Models\Form;
use Illuminate\Support\Facades\Response;

class ApplicationsExport
{
    /**
     * Export applications data as CSV
     */
    public static function generateCsv($request)
    {
        // Get form name (type) from query parameter
        $formName = $request->input('type', 'account_holder_loan_application');
        
        // Apply filters
        $query = Form::where('form_name', $formName);
        
        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Apply date range filters
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('applicant_name', 'LIKE', $searchTerm)
                  ->orWhere('applicant_id_number', 'LIKE', $searchTerm)
                  ->orWhere('applicant_phone', 'LIKE', $searchTerm)
                  ->orWhere('applicant_email', 'LIKE', $searchTerm)
                  ->orWhere('uuid', 'LIKE', $searchTerm);
            });
        }
        
        // Get results ordered by creation date
        $applications = $query->orderBy('created_at', 'desc')->get();
        
        // Create CSV file
        $filename = $formName . '_applications_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        // Define CSV columns
        $columns = [
            'Reference Number',
            'Applicant Name',
            'ID Number',
            'Phone',
            'Email',
            'Employer',
            'Product/Loan',
            'Loan Amount',
            'Term (Months)',
            'Status',
            'Submission Date'
        ];
        
        // Create a streamed response for large datasets
        $callback = function() use ($applications, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM to fix Excel UTF-8 display issues
            fputs($file, "\xEF\xBB\xBF");
            
            // Add header row
            fputcsv($file, $columns);
            
            // Add data rows
            foreach ($applications as $application) {
                $rowData = [
                    $application->uuid,
                    $application->applicant_name ?? 'N/A',
                    $application->applicant_id_number ?? 'N/A',
                    $application->applicant_phone ?? 'N/A',
                    $application->applicant_email ?? 'N/A',
                    $application->employer ?? 'N/A',
                    self::extractProductName($application),
                    $application->loan_amount ? '$' . number_format($application->loan_amount, 2) : 'N/A',
                    $application->loan_term_months ?? 'N/A',
                    strtoupper($application->status),
                    $application->created_at->format('Y-m-d H:i:s')
                ];
                
                fputcsv($file, $rowData);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    /**
     * Helper method to extract product name from application data
     */
    private static function extractProductName($application)
    {
        try {
            if ($application->questionnaire_data) {
                $questionnaireData = json_decode($application->questionnaire_data);
                if (isset($questionnaireData->selectedProduct->product->name)) {
                    return $questionnaireData->selectedProduct->product->name;
                }
            }
        } catch (\Exception $e) {
            // Ignore exceptions in JSON parsing
        }
        
        return 'N/A';
    }
}