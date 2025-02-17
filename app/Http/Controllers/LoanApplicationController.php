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

    public function downloadForm($form, $id)
    {
        $form = Form::findOrFail($id);

        // Load JSON files (assuming they are stored in Laravel's public folder)
        $dbJson = $form->form_values;
        $templateJson = file_get_contents(public_path('templates/json/' . $form . '.json'));

        // Execute function
        $jsonData = json_decode($this->mapAndMergeJson($dbJson, $templateJson), true);

        //load pdf document
        $pdfPath = public_path('templates/pdf/' . $form . '.pdf');

        return $jsonData;

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
            ->header('Content-Disposition', 'attachment; filename="document.pdf"');
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
        // Paths to the existing PDF and JSON
        $pdfPath = storage_path('app/public/ssb_account_application_form.pdf');
        $jsonPath = storage_path('app/public/ssb_account_application_form.json');

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
            ["title" => "Loan Amount (USD)", "x" => 40, "y" => 120],
            ["title" => "Loan Duration", "x" => 120, "y" => 120],
            ["title" => "Purpose/Asset Applied For", "x" => 40, "y" => 130],
            ["title" => "Bank", "x" => 120, "y" => 130],
            ["title" => "Branch", "x" => 40, "y" => 140],
            ["title" => "Account Number", "x" => 120, "y" => 140],
            ["title" => "Deduction Order Form - First Name", "x" => 40, "y" => 150],
            ["title" => "Deduction Order Form - Surname", "x" => 120, "y" => 150],
            ["title" => "Monthly Rate (Installment Amount)", "x" => 40, "y" => 160],
            ["title" => "From Date", "x" => 120, "y" => 160],
            ["title" => "Declaration - Full Name", "x" => 40, "y" => 170],
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
            ->header('Content-Disposition', 'attachment; filename="ssb_loan_application_filled.pdf"');
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
