<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

class LoanApplicationController extends Controller
{
    public function accountHolderApplication($id)
    {
        //use the id to get the actual Form and use that as the jsonData

        // Paths to the existing PDF and JSON
        $pdfPath = storage_path('app/public/account_holder_loan_application.pdf');
        $jsonPath = storage_path('app/public/account_holder_loan_application.json');

        // Load the JSON data
        $jsonData = json_decode(file_get_contents($jsonPath), true);

        // Create a new FPDI instance
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->setSourceFile($pdfPath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId);

        // Set font and text color
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);

        // **Mapping of form fields to PDF positions**
        $fields = [
            ["title" => "Title", "x" => 30, "y" => 50],
            ["title" => "Surname", "x" => 30, "y" => 60],
            ["title" => "First Name", "x" => 120, "y" => 60],
            ["title" => "Date of Birth", "x" => 30, "y" => 70],
            ["title" => "ID Number", "x" => 120, "y" => 70],
            ["title" => "Cell Number", "x" => 30, "y" => 80],
            ["title" => "Email Address", "x" => 120, "y" => 80],
            ["title" => "Employer Name", "x" => 30, "y" => 90],
            ["title" => "Job Title", "x" => 120, "y" => 90],
            ["title" => "Net Salary (USD)", "x" => 30, "y" => 100],
            ["title" => "Account Number", "x" => 30, "y" => 110],
            ["title" => "Loan Duration", "x" => 120, "y" => 110],
            ["title" => "Monthly Installment (USD)", "x" => 30, "y" => 120],
            ["title" => "Purpose/Asset Applied For", "x" => 30, "y" => 130],
            ["title" => "ZB Bank Account Number", "x" => 30, "y" => 140],
        ];

        // **Fill in the fields with JSON data**
        foreach ($fields as $field) {
            $value = "";
            foreach ($jsonData['form']['sections'] as $section) {
                foreach ($section['fields'] as $input) {
                    if ($input['label'] == $field['title']) {
                        $value = $input['default'] ?? "__________"; // Placeholder if empty
                        break;
                    }
                }
            }

            // Place the text into the PDF
            $pdf->SetXY($field['x'], $field['y']);
            $pdf->Write(10, utf8_decode($value));
        }

        // Output the completed PDF for download
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="loan_application_filled.pdf"');
    }

    public function downloadForm($formType, $id)
    {
        // Get the form data
        $form = Form::findOrFail($id);
        
        // If formType doesn't match stored form_name, use form_name from database
        if ($form->form_name && $formType != $form->form_name) {
            $formType = $form->form_name;
        }
        
        // Normalize form type name to match method naming
        $methodName = lcfirst(str_replace('_', '', ucwords($formType, '_'))) . 'Form';
        
        // Map common form names to their method handlers
        $formTypeMap = [
            'ssb' => 'ssbLoanApplicationForm',
            'ssb_form' => 'ssbLoanApplicationForm',
            'ssb_account' => 'ssbLoanApplicationForm',
            'ssb_account_application' => 'ssbLoanApplicationForm'
        ];
        
        // Check if the form is in our mapping
        if (isset($formTypeMap[$formType])) {
            $methodName = $formTypeMap[$formType];
        }
        
        // Log for debugging
        \Log::info("PDF download requested for form: {$id}, type: {$formType}, trying method: {$methodName}");
        
        // If there's a specific method for this form type, use it
        if (method_exists($this, $methodName)) {
            return $this->$methodName($id);
        }
        
        // For all SSB forms, use the SSB method
        if (stripos($formType, 'ssb') !== false) {
            return $this->ssbLoanApplicationForm($id);
        }

        try {
            // Verify template files exist
            $templateJsonPath = public_path('templates/json/' . $formType . '.json');
            $pdfTemplatePath = public_path('templates/pdf/' . $formType . '.pdf');
            
            if (!file_exists($templateJsonPath)) {
                throw new \Exception("Template JSON file not found for form type: {$formType}");
            }
            
            if (!file_exists($pdfTemplatePath)) {
                throw new \Exception("PDF template file not found for form type: {$formType}");
            }
            
            // Load JSON files
            $dbJson = $form->form_values;
            $templateJson = file_get_contents($templateJsonPath);
            
            // Map form values to template
            $jsonData = json_decode($this->mapAndMergeJson($dbJson, $templateJson), true);
            
            // Create a new FPDI instance
            $pdf = new Fpdi();
            $pdf->AddPage();
            $pdf->setSourceFile($pdfTemplatePath);
            $templateId = $pdf->importPage(1);
            $pdf->useTemplate($templateId);
            
            // Set font and text color
            $pdf->SetFont('Arial', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            
            // Get appropriate field mappings based on form type
            $fields = $this->getFieldMappings($formType);
            
            // Fill in the fields with JSON data
            foreach ($fields as $field) {
                $value = "__________"; // Default placeholder
                
                // Try direct field mapping first
                $directValue = null;
                
                // Format field names for lookup
                $lookupKey = str_replace([' ', '(', ')', '-'], ['-', '', '', '-'], strtolower($field['title']));
                $lookupKey = str_replace(['---', '--'], '-', $lookupKey);
                
                // Check several possible field keys in the raw form values
                $possibleKeys = [
                    $lookupKey,
                    $field['title'],
                    str_replace(' ', '-', strtolower($field['title'])),
                    str_replace([' ', '/'], ['-', '-'], strtolower($field['title']))
                ];
                
                // Add custom mappings for common fields
                if (stripos($field['title'], 'amount') !== false) {
                    $possibleKeys[] = 'loan-amount';
                    $possibleKeys[] = 'applied-amount';
                }
                
                if (stripos($field['title'], 'purpose') !== false) {
                    $possibleKeys[] = 'purpose';
                    $possibleKeys[] = 'asset-applied-for';
                    $possibleKeys[] = 'purpose/asset-applied-for';
                }
                
                // Try to find direct value in form_values
                foreach ($possibleKeys as $key) {
                    if (isset($form->form_values)) {
                        $rawFormValues = json_decode($form->form_values, true) ?? [];
                        if (isset($rawFormValues[$key]) && !empty($rawFormValues[$key])) {
                            $directValue = $rawFormValues[$key];
                            break;
                        }
                    }
                }
                
                // If we found a direct value, use it
                if ($directValue !== null) {
                    $value = $directValue;
                } else {
                    // Otherwise try the mapped JSON data
                    foreach ($jsonData as $sectionTitle => $sectionData) {
                        // Ensure section exists
                        if (isset($jsonData[$sectionTitle]) && is_array($sectionData)) {
                            // Check if the field exists in the section
                            if (isset($sectionData[$field['title']])) {
                                $value = $sectionData[$field['title']] ?? "__________";
                                break; // Stop searching once found
                            }
                        }
                    }
                }
                
                // Write the value into the PDF at the specified coordinates
                $pdf->SetXY($field['x'], $field['y']);
                $pdf->Write(10, utf8_decode($value));
            }
            
            // Output the completed PDF for download
            $filename = str_replace('_', '-', $formType) . '-' . $id . '.pdf';
            return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            // Log the error
            \Log::error("PDF generation error: " . $e->getMessage());
            
            // Return error response
            return response()->json([
                'error' => 'Failed to generate PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getFieldMappings($formType) {
        switch ($formType) {
            case 'ssb_account_opening_form':
                return [
                    ["title" => "Surname", "x" => 40, "y" => 50],
                    ["title" => "First Name", "x" => 120, "y" => 50],
                    ["title" => "Gender", "x" => 40, "y" => 60],
                    ["title" => "Date of Birth", "x" => 120, "y" => 60],
                    ["title" => "ID Number", "x" => 40, "y" => 70],
                    ["title" => "Cell Number", "x" => 120, "y" => 70],
                    ["title" => "Email Address", "x" => 40, "y" => 80],
                    ["title" => "Name of Responsible Ministry", "x" => 120, "y" => 80],
                    ["title" => "Employer Name", "x" => 40, "y" => 90],
                    ["title" => "Employer Address", "x" => 120, "y" => 90],
                    ["title" => "Job Title", "x" => 40, "y" => 100],
                    ["title" => "Date of Employment", "x" => 120, "y" => 100],
                    ["title" => "Employment Number", "x" => 40, "y" => 110],
                    ["title" => "Current Net Salary (USD)", "x" => 120, "y" => 110],
                    ["title" => "Applied Amount (USD)", "x" => 40, "y" => 120],
                    ["title" => "Loan Duration", "x" => 120, "y" => 120],
                    ["title" => "Purpose/Asset Applied For", "x" => 40, "y" => 130],
                    ["title" => "Bank", "x" => 120, "y" => 130],
                    ["title" => "Branch", "x" => 40, "y" => 140],
                    ["title" => "Account Number", "x" => 120, "y" => 140],
                    ["title" => "Employee Code Number", "x" => 40, "y" => 150],
                    ["title" => "Check Letter", "x" => 120, "y" => 150],
                    ["title" => "Monthly Rate (Installment Amount)", "x" => 40, "y" => 160],
                    ["title" => "From Date", "x" => 120, "y" => 160],
                    ["title" => "Declaration - Full Name", "x" => 40, "y" => 170],
                ];
            
            default:
                return [
                    ["title" => "First Name", "x" => 40, "y" => 50],
                    ["title" => "Surname", "x" => 120, "y" => 50],
                    ["title" => "Gender", "x" => 40, "y" => 60],
                    ["title" => "Date of Birth", "x" => 120, "y" => 60],
                    ["title" => "Place of Birth", "x" => 40, "y" => 70],
                    ["title" => "Nationality", "x" => 120, "y" => 70],
                    ["title" => "Marital Status", "x" => 40, "y" => 80],
                    ["title" => "National ID Number", "x" => 120, "y" => 80],
                    ["title" => "Residential Address", "x" => 40, "y" => 90],
                    ["title" => "Mobile", "x" => 120, "y" => 90],
                    ["title" => "Email Address", "x" => 40, "y" => 100],
                    ["title" => "Employer Name", "x" => 40, "y" => 110],
                    ["title" => "Occupation", "x" => 120, "y" => 110],
                    ["title" => "Gross Monthly Salary (USD)", "x" => 40, "y" => 120],
                    ["title" => "Other Sources of Income", "x" => 120, "y" => 120],
                    ["title" => "Spouse/Next of Kin - Full Name", "x" => 40, "y" => 130],
                    ["title" => "Spouse/Next of Kin - Contact Number", "x" => 120, "y" => 130],
                    ["title" => "Type of Account", "x" => 40, "y" => 140]
                ];
        }
    }

    public function pensionersLoanApplication($id)
    {
        $form = Form::findOrFail($id);

        // Load JSON files (assuming they are stored in Laravel's public folder)
        $dbJson = $form->form_values;
        $templateJson = file_get_contents(public_path('templates/json/pensioners_loan_application.json'));

        // Execute function
        $jsonData = json_decode($this->mapAndMergeJson($dbJson, $templateJson), true);

        //load pdf document
        $pdfPath = public_path('templates/pdf/pensioners_loan_application.pdf');

        // Create a new FPDI instance
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->setSourceFile($pdfPath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId);

        // Set font and text color
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);

        // **Mapping of form fields to PDF positions**
        $fields = [
            ["title" => "Surname", "x" => 40, "y" => 50],
            ["title" => "First Name", "x" => 120, "y" => 50],
            ["title" => "Gender", "x" => 40, "y" => 60],
            ["title" => "Date of Birth", "x" => 120, "y" => 60],
            ["title" => "ID Number", "x" => 40, "y" => 70],
            ["title" => "Cell Number", "x" => 120, "y" => 70],
            ["title" => "Email Address", "x" => 40, "y" => 80],
            ["title" => "Permanent Address", "x" => 120, "y" => 80],
            ["title" => "Applied Loan Amount (USD)", "x" => 40, "y" => 90],
            ["title" => "Loan Duration", "x" => 120, "y" => 90],
            ["title" => "Purpose/Asset Applied For", "x" => 40, "y" => 100],
            ["title" => "Bank", "x" => 120, "y" => 100],
            ["title" => "Branch", "x" => 40, "y" => 110],
            ["title" => "Account Number", "x" => 120, "y" => 110],
            ["title" => "Monthly Deduction Amount (USD)", "x" => 40, "y" => 120],
            ["title" => "Start Date", "x" => 120, "y" => 120],
            ["title" => "Reference Number", "x" => 40, "y" => 130],
            ["title" => "Full Name (Declaration)", "x" => 120, "y" => 130],
        ];

        // **Fill in the fields with JSON data**
        foreach ($fields as $field) {
            $value = "__________"; // Default placeholder

            foreach ($jsonData as $sectionTitle => $sectionData) {
                // Ensure section exists
                if (isset($jsonData[$sectionTitle]) && is_array($sectionData)) {
                    // Check if the field exists in the section
                    if (isset($sectionData[$field['title']])) {
                        $value = $sectionData[$field['title']] ?? "__________";
                        break; // Stop searching once found
                    }
                }
            }

            // Write the value into the PDF at the specified coordinates
            $pdf->SetXY($field['x'], $field['y']);
            $pdf->Write(10, utf8_decode($value));
        }

        // Output the completed PDF for download
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="pensioners_loan_application_filled.pdf"');
    }

    public function smesBusinessApplicationForm($id)
    {
        // Paths to the existing PDF and JSON
        $pdfPath = storage_path('app/public/smes_business_account_application.pdf');
        $jsonPath = storage_path('app/public/smes_business_account_application.json');

        // Load the JSON data
        $jsonData = json_decode(file_get_contents($jsonPath), true);

        // Create a new FPDI instance
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->setSourceFile($pdfPath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId);

        // Set font and text color
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);

        // **Mapping of form fields to PDF positions**
        $fields = [
            ["title" => "Registered Name", "x" => 40, "y" => 50],
            ["title" => "Trading Name", "x" => 120, "y" => 50],
            ["title" => "Certificate of Incorporation Number", "x" => 40, "y" => 60],
            ["title" => "Business Type", "x" => 120, "y" => 60],
            ["title" => "Business Address", "x" => 40, "y" => 70],
            ["title" => "Contact Phone Number", "x" => 120, "y" => 70],
            ["title" => "Email Address", "x" => 40, "y" => 80],
            ["title" => "Period at Current Business Location (Years)", "x" => 120, "y" => 80],
            ["title" => "Number of Employees (Full-time & Owner)", "x" => 40, "y" => 90],
            ["title" => "Number of Employees (Part-time)", "x" => 120, "y" => 90],
            ["title" => "Number of Employees (Non-paid Family)", "x" => 40, "y" => 100],
            ["title" => "Main Products/Services", "x" => 120, "y" => 100],
            ["title" => "Loan Amount (USD)", "x" => 40, "y" => 110],
            ["title" => "Repayment Period (Months)", "x" => 120, "y" => 110],
            ["title" => "Purpose of Loan", "x" => 40, "y" => 120],
            ["title" => "Reference 1 Name", "x" => 40, "y" => 130],
            ["title" => "Reference 1 Phone Number", "x" => 120, "y" => 130],
            ["title" => "Asset 1 Description", "x" => 40, "y" => 140],
            ["title" => "Asset 1 Serial/Reg Number", "x" => 120, "y" => 140],
            ["title" => "Asset 1 Estimated Value (USD)", "x" => 40, "y" => 150],
            ["title" => "First Name (Director)", "x" => 40, "y" => 160],
            ["title" => "Surname (Director)", "x" => 120, "y" => 160],
            ["title" => "Bank", "x" => 40, "y" => 170],
            ["title" => "Branch", "x" => 120, "y" => 170],
            ["title" => "Account Number", "x" => 40, "y" => 180],
            ["title" => "Full Name (Declaration)", "x" => 120, "y" => 180],
        ];

        // **Fill in the fields with JSON data**
        foreach ($fields as $field) {
            $value = "";
            foreach ($jsonData['form']['sections'] as $section) {
                foreach ($section['fields'] as $input) {
                    if ($input['label'] == $field['title']) {
                        $value = $input['default'] ?? "__________"; // Placeholder if empty
                        break;
                    }
                }
            }

            // Place the text into the PDF
            $pdf->SetXY($field['x'], $field['y']);
            $pdf->Write(10, utf8_decode($value));
        }

        // Output the completed PDF for download
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="smes_business_account_filled.pdf"');
    }

    public function ssbLoanApplicationForm($id)
    {
        try {
            $form = Form::findOrFail($id);
            
            // Log the form data for debugging
            \Log::info("Generating PDF for form ID: {$id}");
            
            // Get form values from database with safe default values
            $formValues = [];
            $questionnaireData = [];
            
            try {
                $formValues = json_decode($form->form_values, true);
                if (!is_array($formValues)) {
                    $formValues = [];
                    \Log::warning("Form values for ID {$id} could not be parsed as array");
                }
            } catch (\Exception $e) {
                \Log::error("Error parsing form_values for ID {$id}: " . $e->getMessage());
                $formValues = [];
            }
            
            try {
                $questionnaireData = json_decode($form->questionnaire_data, true);
                if (!is_array($questionnaireData)) {
                    $questionnaireData = [];
                    \Log::warning("Questionnaire data for ID {$id} could not be parsed as array");
                }
            } catch (\Exception $e) {
                \Log::error("Error parsing questionnaire_data for ID {$id}: " . $e->getMessage());
                $questionnaireData = [];
            }
            
            // Create a new clean PDF without template
            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->AddPage();
            
            // Set document information
            $pdf->SetTitle('SSB Account Opening Form');
            $pdf->SetAuthor('BancoSystem');
            $pdf->SetCreator('BancoSystem PDF Generator');
            
            // Add institution logo or header
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'SSB LOAN APPLICATION FORM', 0, 1, 'C');
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Application #' . $id, 0, 1, 'C');
            
            // Add horizontal line
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(5);
            
            // Applicant information section
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'APPLICANT INFORMATION', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            // Helper function to safely get value from an array
            $safeGet = function($array, $key, $default = 'N/A') {
                if (!is_array($array)) return $default;
                if (!isset($array[$key])) return $default;
                
                $value = $array[$key];
                
                // Handle different value types
                if (is_array($value)) {
                    return json_encode($value); // Convert arrays to JSON string
                } elseif (is_bool($value)) {
                    return $value ? 'Yes' : 'No';
                } elseif (is_null($value)) {
                    return $default;
                } else {
                    return (string)$value; // Convert to string
                }
            };
            
            // Extract applicant details with safe retrieval
            $applicantName = $form->applicant_name ?? 
                             $safeGet($formValues, 'customerFullName') ?? 
                             $safeGet($formValues, 'full-name') ?? '';
                             
            $applicantId = $form->applicant_id_number ?? 
                           $safeGet($formValues, 'id-number') ?? 
                           $safeGet($formValues, 'customerIdNumber') ?? '';
                           
            $applicantPhone = $form->applicant_phone ?? 
                              $safeGet($formValues, 'cell-number') ?? 
                              $safeGet($formValues, 'customerCellNumber') ?? '';
                              
            $applicantEmail = $form->applicant_email ?? 
                              $safeGet($formValues, 'email-address') ?? 
                              $safeGet($formValues, 'customerEmail') ?? '';
            
            // Format name parts
            $surname = $safeGet($formValues, 'surname') ?? 
                       $safeGet($formValues, 'customerSurname') ?? '';
                       
            $firstName = $safeGet($formValues, 'first-name') ?? 
                         $safeGet($formValues, 'customerFirstName') ?? '';
            
            // Display applicant details in a table-like format
            $pdf->Cell(60, 7, 'Full Name:', 0, 0);
            $pdf->Cell(130, 7, $applicantName, 0, 1);
            
            $pdf->Cell(60, 7, 'ID Number:', 0, 0);
            $pdf->Cell(130, 7, $applicantId, 0, 1);
            
            $pdf->Cell(60, 7, 'Phone Number:', 0, 0);
            $pdf->Cell(130, 7, $applicantPhone, 0, 1);
            
            $pdf->Cell(60, 7, 'Email Address:', 0, 0);
            $pdf->Cell(130, 7, $applicantEmail, 0, 1);
            
            // Check if this is a business/SME form
            $isSmeForm = false;
            if (stripos($form->form_name, 'sme') !== false || 
                stripos($form->form_name, 'business') !== false ||
                $safeGet($formValues, 'business-name') != 'N/A' ||
                $safeGet($formValues, 'registered-name') != 'N/A') {
                $isSmeForm = true;
            }
            
            if ($isSmeForm) {
                // Business details section for SME forms
                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 10, 'BUSINESS DETAILS', 0, 1, 'L');
                $pdf->SetFont('Arial', '', 10);
                
                $businessName = $safeGet($formValues, 'business-name') ?? 
                                $safeGet($formValues, 'registered-name') ?? 
                                $safeGet($formValues, 'trading-name') ?? 'N/A';
                                
                $businessType = $safeGet($formValues, 'business-type') ?? 
                                $safeGet($formValues, 'type-of-business') ?? 
                                $safeGet($formValues, 'sector') ?? 'N/A';
                                
                $regNumber = $safeGet($formValues, 'registration-number') ?? 
                             $safeGet($formValues, 'certificate-of-incorporation-number') ?? 'N/A';
                             
                $businessAddress = $safeGet($formValues, 'business-address') ?? 
                                   $safeGet($formValues, 'physical-address') ?? 'N/A';
                                   
                $yearsInBusiness = $safeGet($formValues, 'years-in-business') ?? 
                                   $safeGet($formValues, 'period-at-current-business-location-(years)') ?? 'N/A';
                                   
                $employeesFT = $safeGet($formValues, 'number-of-employees-full-time') ?? 
                               $safeGet($formValues, 'number-of-employees-(full-time-&-owner)') ?? 'N/A';
                               
                $employeesPT = $safeGet($formValues, 'number-of-employees-part-time') ?? 
                               $safeGet($formValues, 'number-of-employees-(part-time)') ?? 'N/A';
                               
                $products = $safeGet($formValues, 'main-products/services') ?? 
                            $safeGet($formValues, 'products-services') ?? 'N/A';
                
                $pdf->Cell(60, 7, 'Business Name:', 0, 0);
                $pdf->Cell(130, 7, $businessName, 0, 1);
                
                $pdf->Cell(60, 7, 'Business Type:', 0, 0);
                $pdf->Cell(130, 7, $businessType, 0, 1);
                
                $pdf->Cell(60, 7, 'Registration Number:', 0, 0);
                $pdf->Cell(130, 7, $regNumber, 0, 1);
                
                $pdf->Cell(60, 7, 'Business Address:', 0, 0);
                $pdf->Cell(130, 7, $businessAddress, 0, 1);
                
                $pdf->Cell(60, 7, 'Years in Business:', 0, 0);
                $pdf->Cell(130, 7, $yearsInBusiness, 0, 1);
                
                $pdf->Cell(60, 7, 'Employees (Full-time):', 0, 0);
                $pdf->Cell(130, 7, $employeesFT, 0, 1);
                
                $pdf->Cell(60, 7, 'Employees (Part-time):', 0, 0);
                $pdf->Cell(130, 7, $employeesPT, 0, 1);
                
                $pdf->Cell(60, 7, 'Products/Services:', 0, 0);
                $pdf->Cell(130, 7, $products, 0, 1);
            } else {
                // Extract employer details using safe getter for individual forms
                $employer = $form->employer ?? 
                            $safeGet($formValues, 'employer-name') ?? 
                            $safeGet($formValues, 'customerEmployer') ?? 'N/A';
                            
                $ministry = $safeGet($formValues, 'name-of-responsible-ministry') ?? 
                            $safeGet($formValues, 'customerMinistry') ?? 
                            $safeGet($formValues, 'ministry') ?? 'N/A';
                
                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 10, 'EMPLOYMENT DETAILS', 0, 1, 'L');
                $pdf->SetFont('Arial', '', 10);
                
                $pdf->Cell(60, 7, 'Employer:', 0, 0);
                $pdf->Cell(130, 7, $employer, 0, 1);
                
                $pdf->Cell(60, 7, 'Ministry:', 0, 0);
                $pdf->Cell(130, 7, $ministry, 0, 1);
            }
            
            // Loan details section
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'LOAN DETAILS', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            // Try to extract loan details from multiple possible sources
            $loanAmount = $form->loan_amount ?? 
                          $safeGet($formValues, 'loan-amount') ?? 
                          $safeGet($formValues, 'applied-amount') ?? 'N/A';
                          
            $loanTermMonths = $form->loan_term_months ?? 
                              $safeGet($formValues, 'productLoanPeriodMonths') ?? 'N/A';
                              
            // Handle date formatting safely
            $loanStartDate = 'N/A';
            if ($form->loan_start_date) {
                try {
                    $loanStartDate = date('d M Y', strtotime($form->loan_start_date));
                } catch (\Exception $e) {
                    $loanStartDate = $form->loan_start_date;
                }
            } else {
                $loanStartDate = $safeGet($formValues, 'autoLoanStartDateText') ?? 
                                 $safeGet($formValues, 'From Date') ?? 'N/A';
            }
            
            $loanEndDate = 'N/A';
            if ($form->loan_end_date) {
                try {
                    $loanEndDate = date('d M Y', strtotime($form->loan_end_date));
                } catch (\Exception $e) {
                    $loanEndDate = $form->loan_end_date;
                }
            } else {
                $loanEndDate = $safeGet($formValues, 'autoLoanEndDateText') ?? 
                               $safeGet($formValues, 'To Date') ?? 'N/A';
            }
            
            // Get product details from questionnaire data if available
            $productName = 'N/A';
            $installmentAmount = 'N/A';
            
            // Safely get nested values from questionnaire data
            if (is_array($questionnaireData) && 
                isset($questionnaireData['selectedProduct']) && 
                is_array($questionnaireData['selectedProduct']) && 
                isset($questionnaireData['selectedProduct']['product']) && 
                is_array($questionnaireData['selectedProduct']['product']) && 
                isset($questionnaireData['selectedProduct']['product']['name'])) {
                $productName = $questionnaireData['selectedProduct']['product']['name'];
            } elseif ($safeGet($formValues, 'purpose/asset-applied-for') != 'N/A') {
                $productName = $safeGet($formValues, 'purpose/asset-applied-for');
            } elseif ($safeGet($formValues, 'productDescription') != 'N/A') {
                $productName = $safeGet($formValues, 'productDescription');
            }
            
            // Safely get installment amount
            if ($safeGet($formValues, 'productInstallment') != 'N/A') {
                $installmentAmount = $safeGet($formValues, 'productInstallment');
            } elseif ($safeGet($formValues, 'Monthly Rate (Installment Amount)') != 'N/A') {
                $installmentAmount = $safeGet($formValues, 'Monthly Rate (Installment Amount)');
            } elseif (is_array($questionnaireData) && 
                      isset($questionnaireData['selectedProduct']) && 
                      is_array($questionnaireData['selectedProduct']) && 
                      isset($questionnaireData['selectedProduct']['selectedCreditOption']) && 
                      is_array($questionnaireData['selectedProduct']['selectedCreditOption']) && 
                      isset($questionnaireData['selectedProduct']['selectedCreditOption']['installment_amount'])) {
                $installmentAmount = $questionnaireData['selectedProduct']['selectedCreditOption']['installment_amount'];
            }
            
            $pdf->Cell(60, 7, 'Product/Asset:', 0, 0);
            $pdf->Cell(130, 7, $productName, 0, 1);
            
            $pdf->Cell(60, 7, 'Loan Amount (USD):', 0, 0);
            $pdf->Cell(130, 7, $loanAmount, 0, 1);
            
            $pdf->Cell(60, 7, 'Monthly Installment (USD):', 0, 0);
            $pdf->Cell(130, 7, $installmentAmount, 0, 1);
            
            $pdf->Cell(60, 7, 'Loan Term (Months):', 0, 0);
            $pdf->Cell(130, 7, $loanTermMonths, 0, 1);
            
            $pdf->Cell(60, 7, 'Start Date:', 0, 0);
            $pdf->Cell(130, 7, $loanStartDate, 0, 1);
            
            $pdf->Cell(60, 7, 'End Date:', 0, 0);
            $pdf->Cell(130, 7, $loanEndDate, 0, 1);
            
            // Next of Kin / Emergency Contact Details
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'NEXT OF KIN / EMERGENCY CONTACT DETAILS', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            // Use safe getters for all fields
            $spouseName = $safeGet($formValues, "spouse's-full-name") ?? 
                          $safeGet($formValues, 'emergency-contact-name') ?? 'N/A';
                          
            $spousePhone = $safeGet($formValues, 'phone-numbers') ?? 
                           $safeGet($formValues, 'emergency-contact-phone') ?? 'N/A';
                           
            $relationship = $safeGet($formValues, 'relationship') ?? 
                            $safeGet($formValues, 'emergency-contact-relationship') ?? 'N/A';
                            
            $address = $safeGet($formValues, 'residential-address') ?? 
                       $safeGet($formValues, 'emergency-contact-address') ?? 'N/A';
            
            $pdf->Cell(60, 7, 'Full Name:', 0, 0);
            $pdf->Cell(130, 7, $spouseName, 0, 1);
            
            $pdf->Cell(60, 7, 'Phone Number:', 0, 0);
            $pdf->Cell(130, 7, $spousePhone, 0, 1);
            
            $pdf->Cell(60, 7, 'Relationship:', 0, 0);
            $pdf->Cell(130, 7, $relationship, 0, 1);
            
            $pdf->Cell(60, 7, 'Address:', 0, 0);
            $pdf->Cell(130, 7, $address, 0, 1);
            
            // Residential Details
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'RESIDENTIAL DETAILS', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            $permanentAddress = $safeGet($formValues, 'permanent-address') ?? 
                                $safeGet($formValues, 'customerAddress') ?? 'N/A';
                                
            $propertyOwnership = $safeGet($formValues, 'property-ownership') ?? 'N/A';
            $periodAtAddress = $safeGet($formValues, 'period-at-current-address') ?? 'N/A';
            $province = $safeGet($formValues, 'province') ?? 'N/A';
            
            $pdf->Cell(60, 7, 'Permanent Address:', 0, 0);
            $pdf->Cell(130, 7, $permanentAddress, 0, 1);
            
            $pdf->Cell(60, 7, 'Property Ownership:', 0, 0);
            $pdf->Cell(130, 7, $propertyOwnership, 0, 1);
            
            $pdf->Cell(60, 7, 'Period at Current Address:', 0, 0);
            $pdf->Cell(130, 7, $periodAtAddress, 0, 1);
            
            $pdf->Cell(60, 7, 'Province:', 0, 0);
            $pdf->Cell(130, 7, $province, 0, 1);
            
            // Employment Additional Details
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'EMPLOYMENT ADDITIONAL DETAILS', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            $employmentStatus = $safeGet($formValues, 'employment-status');
            $dateOfEmployment = $safeGet($formValues, 'date-of-employment');
            $employmentNumber = $safeGet($formValues, 'employment-number');
            $headOfInstitution = $safeGet($formValues, 'name-of-head-of-institution');
            $employeeCode = $safeGet($formValues, 'employee-code-number');
            $departmentCode = $safeGet($formValues, 'department-code-(as-it-appears-on-your-pay-slip)');
            $stationCode = $safeGet($formValues, 'station-code-(as-it-appears-on-your-pay-slip)');
            
            $pdf->Cell(60, 7, 'Employment Status:', 0, 0);
            $pdf->Cell(130, 7, $employmentStatus, 0, 1);
            
            $pdf->Cell(60, 7, 'Date of Employment:', 0, 0);
            $pdf->Cell(130, 7, $dateOfEmployment, 0, 1);
            
            $pdf->Cell(60, 7, 'Employment Number:', 0, 0);
            $pdf->Cell(130, 7, $employmentNumber, 0, 1);
            
            $pdf->Cell(60, 7, 'Head of Institution:', 0, 0);
            $pdf->Cell(130, 7, $headOfInstitution, 0, 1);
            
            $pdf->Cell(60, 7, 'Employee Code:', 0, 0);
            $pdf->Cell(130, 7, $employeeCode, 0, 1);
            
            $pdf->Cell(60, 7, 'Department Code:', 0, 0);
            $pdf->Cell(130, 7, $departmentCode, 0, 1);
            
            $pdf->Cell(60, 7, 'Station Code:', 0, 0);
            $pdf->Cell(130, 7, $stationCode, 0, 1);
            
            // Bank details section if available
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'BANK DETAILS', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            $bankBranch = $safeGet($formValues, 'bank-branch');
            $accountType = $safeGet($formValues, 'account-type');
            $currency = $safeGet($formValues, 'currency-of-account', 'USD');
            
            $pdf->Cell(60, 7, 'Account Type:', 0, 0);
            $pdf->Cell(130, 7, $accountType, 0, 1);
            
            $pdf->Cell(60, 7, 'Currency:', 0, 0);
            $pdf->Cell(130, 7, $currency, 0, 1);
            
            $pdf->Cell(60, 7, 'Branch:', 0, 0);
            $pdf->Cell(130, 7, $bankBranch, 0, 1);
            
            // Uploaded Documents
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'UPLOADED DOCUMENTS', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            // Safely get uploaded files
            $uploadedFiles = [];
            try {
                if (!empty($form->uploaded_files)) {
                    $uploadedFiles = json_decode($form->uploaded_files, true);
                    if (!is_array($uploadedFiles)) {
                        $uploadedFiles = [];
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Error parsing uploaded_files for form {$id}: " . $e->getMessage());
            }
            
            // Check KYC documents in direct fields
            $kycDocuments = [
                'ID Document' => $form->id_document ?? null, 
                'Passport Photo' => $form->passport_photo ?? null,
                'Payslip' => $form->payslip ?? null
            ];
            
            $hasDocuments = false;
            
            // Check for directors information in questionnaire data or form values
            $hasDirectors = false;
            $directors = [];
            
            // Try to find directors in different possible locations in the data
            try {
                // Check for directors in questionnaire_data.directors array
                if (isset($questionnaireData['directors']) && is_array($questionnaireData['directors'])) {
                    $directors = $questionnaireData['directors'];
                    $hasDirectors = true;
                }
                // Check form_values for director data
                elseif (isset($formValues['directors']) && is_array($formValues['directors'])) {
                    $directors = $formValues['directors'];
                    $hasDirectors = true;
                }
                // Check for individually named directors
                else {
                    $directorKeys = [];
                    foreach ($formValues as $key => $value) {
                        if (strpos($key, 'director') !== false || 
                            strpos($key, 'director-name') !== false || 
                            strpos($key, 'director-1') !== false) {
                            $directorKeys[] = $key;
                        }
                    }
                    
                    if (!empty($directorKeys)) {
                        $hasDirectors = true;
                        foreach ($directorKeys as $key) {
                            $directors[] = [
                                'name' => $safeGet($formValues, $key),
                                'key' => $key
                            ];
                        }
                    }
                }
                
                // If we have directors, add a Directors section to the PDF
                if ($hasDirectors && !empty($directors)) {
                    $pdf->Ln(5);
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(0, 10, 'DIRECTORS INFORMATION', 0, 1, 'L');
                    $pdf->SetFont('Arial', '', 10);
                    
                    $directorNumber = 1;
                    foreach ($directors as $director) {
                        $pdf->SetFont('Arial', 'B', 10);
                        $pdf->Cell(0, 7, "Director {$directorNumber}", 0, 1);
                        $pdf->SetFont('Arial', '', 10);
                        
                        if (is_array($director)) {
                            // For each potential director field try to display it
                            $directorFields = [
                                'name' => 'Name',
                                'firstName' => 'First Name',
                                'first_name' => 'First Name',
                                'lastName' => 'Last Name',
                                'last_name' => 'Last Name',
                                'surname' => 'Surname',
                                'email' => 'Email',
                                'phone' => 'Phone Number',
                                'id_number' => 'ID Number',
                                'idNumber' => 'ID Number',
                                'position' => 'Position',
                                'title' => 'Title',
                                'shareholding' => 'Shareholding',
                                'shares' => 'Shares'
                            ];
                            
                            foreach ($directorFields as $field => $label) {
                                if (isset($director[$field]) && !empty($director[$field])) {
                                    $pdf->Cell(60, 7, $label . ':', 0, 0);
                                    $pdf->Cell(130, 7, $director[$field], 0, 1);
                                }
                            }
                        } elseif (is_string($director)) {
                            $pdf->Cell(60, 7, 'Name:', 0, 0);
                            $pdf->Cell(130, 7, $director, 0, 1);
                        }
                        
                        $directorNumber++;
                        $pdf->Ln(3);
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Error processing director information for form {$id}: " . $e->getMessage());
                // Continue processing the PDF
            }
            
            // Display uploaded files
            if (!empty($uploadedFiles)) {
                foreach ($uploadedFiles as $key => $file) {
                    if (is_array($file) && isset($file['original_name'])) {
                        $pdf->Cell(60, 7, ucwords(str_replace(['-', '_'], ' ', $key)) . ':', 0, 0);
                        $pdf->Cell(130, 7, $file['original_name'], 0, 1);
                        $hasDocuments = true;
                    }
                }
            }
            
            // Display KYC documents
            foreach ($kycDocuments as $docType => $docPath) {
                if (!empty($docPath)) {
                    $pdf->Cell(60, 7, $docType . ':', 0, 0);
                    $pdf->Cell(130, 7, basename($docPath), 0, 1);
                    $hasDocuments = true;
                }
            }
            
            if (!$hasDocuments) {
                $pdf->Cell(0, 7, 'No documents uploaded', 0, 1);
            }
            
            // Add signature if available
            try {
                if (!empty($form->signature) && is_string($form->signature) && strpos($form->signature, 'data:image') === 0) {
                    $pdf->Ln(10);
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(0, 10, 'APPLICANT SIGNATURE', 0, 1, 'L');
                    
                    // Convert base64 signature to image
                    $signatureData = $form->signature;
                    $signatureFile = tempnam(sys_get_temp_dir(), 'signature') . '.png';
                    
                    try {
                        // Extract the actual base64 content
                        $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
                        $decodedData = base64_decode($signatureData);
                        
                        if ($decodedData !== false) {
                            file_put_contents($signatureFile, $decodedData);
                            
                            // Add signature image to PDF
                            if (file_exists($signatureFile)) {
                                $pdf->Image($signatureFile, 20, $pdf->GetY(), 60);
                                $pdf->Ln(30); // Space for signature
                                unlink($signatureFile); // Delete temp file
                            }
                        } else {
                            \Log::warning("Failed to decode signature base64 data for form {$id}");
                            $pdf->Cell(0, 10, '[Signature data could not be decoded]', 0, 1, 'L');
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error processing signature for form {$id}: " . $e->getMessage());
                        $pdf->Cell(0, 10, '[Signature could not be processed]', 0, 1, 'L');
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Error in signature section for form {$id}: " . $e->getMessage());
                // Continue with the PDF - don't let signature error break the whole PDF
            }
            
            // Add declaration text
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'DECLARATION', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(0, 5, 'I hereby declare that the information provided in this application is true and correct. I understand that any false statement may result in the rejection of my application or termination of any agreement entered into on the basis of such information.', 0, 'L');
            
            $pdf->Ln(5);
            $pdf->Cell(60, 7, 'Date:', 0, 0);
            $pdf->Cell(130, 7, date('d M Y'), 0, 1);
            
            // Add application status
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'APPLICATION STATUS: ' . strtoupper($form->status ?? 'PENDING'), 0, 1, 'L');
            
            // Output the PDF
            return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="ssb_loan_application_' . $id . '.pdf"');
        
        } catch (\Exception $e) {
            // Log the error
            \Log::error("SSB PDF generation error: " . $e->getMessage());
            
            // Return error response
            return response()->json([
                'error' => 'Failed to generate SSB PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    function mapAndMergeJson($dbJson, $templateJson)
    {
        // Decode JSON data
        $dbData = json_decode($dbJson, true);
        $templateData = json_decode($templateJson, true);

        // Initialize the mapped response
        $mappedData = [];

        // Iterate through template sections
        foreach ($templateData['form']['sections'] as $section) {
            $sectionTitle = $section['title'];
            $mappedData[$sectionTitle] = [];

            // Iterate through fields in each section
            foreach ($section['fields'] as $field) {
                $fieldLabel = $field['label'];

                // Convert label to a compatible key format (lowercase, dashes)
                $formattedKey = strtolower(str_replace([' ', '(', ')', '’', '–'], ['-', '', '', '', '-'], $fieldLabel));

                // Check if the key exists in DB response and assign it
                $mappedData[$sectionTitle][$fieldLabel] = $dbData[$formattedKey] ?? null;
            }
        }

        return json_encode($mappedData, JSON_PRETTY_PRINT);
    }
}
