<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormSubmissionController extends Controller
{
    public function submit(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'formId' => 'required|string',
            'formValues' => 'required|array',
            'questionnaireData' => 'required|array',
            'referral_code' => 'nullable|string|exists:users,referral_code',
        ]);

        // Save the form submission to the database
        $formSubmission = new Form();
        $formSubmission->form_name = $validatedData['formId'];
        $formSubmission->form_values = json_encode($validatedData['formValues']);
        $formSubmission->questionnaire_data = json_encode($validatedData['questionnaireData']);
        
        // Link to current authenticated user if available
        if (auth()->check()) {
            $formSubmission->user_id = auth()->id();
        }
        
        // Handle referral if provided
        if (!empty($validatedData['referral_code'])) {
            $agent = User::where('referral_code', $validatedData['referral_code'])
                         ->where('role', 'agent')
                         ->where('status', 1)
                         ->first();
                         
            if ($agent) {
                $formSubmission->referred_by = $agent->id;
                
                // Track this referral
                $agent->trackReferral();
                
                // Also assign this agent to the form
                $formSubmission->agent_id = $agent->id;
            }
        } else {
            $formSubmission->agent_id = $validatedData['agent_id'] ?? null;
        }
        
        // Generate a unique UUID as reference number
        $formSubmission->uuid = (string) Str::uuid();
        
        // Set default status
        $formSubmission->status = 'pending';
        
        // Extract key fields for easier querying
        $formValues = $validatedData['formValues'];
        $questionnaireData = $validatedData['questionnaireData'];
        
        // Extract applicant details - check all possible field names
        $firstName = $formValues['first-name'] ?? $formValues['forename'] ?? $formValues['forenames'] ?? $formValues['customerFirstName'] ?? '';
        $surname = $formValues['surname'] ?? $formValues['last-name'] ?? $formValues['customerSurname'] ?? '';
        $formSubmission->applicant_name = trim($firstName . ' ' . $surname);
        
        // Extract ID number from various possible fields
        $formSubmission->applicant_id_number = $formValues['id-number'] ?? $formValues['national-id'] ?? $formValues['identity-number'] ?? 
                                              $formValues['customerIdNumber'] ?? $formValues['idNumber'] ?? '';
        
        // Extract phone number
        $formSubmission->applicant_phone = $formValues['cell-number'] ?? $formValues['phone-number'] ?? $formValues['phone'] ?? 
                                          $formValues['mobile'] ?? $formValues['customerCellNumber'] ?? '';
        
        // Extract email
        $formSubmission->applicant_email = $formValues['email-address'] ?? $formValues['email'] ?? $formValues['customerEmail'] ?? '';
        
        // Extract employer
        $formSubmission->employer = $formValues['employer-name'] ?? $formValues['employer'] ?? $formValues['customerEmployer'] ?? 
                                    $questionnaireData['employer'] ?? '';
        
        // Extract loan details if available
        if (isset($questionnaireData['selectedProduct'])) {
            $selectedProduct = $questionnaireData['selectedProduct'];
            
            if (isset($selectedProduct['selectedCreditOption'])) {
                $creditOption = $selectedProduct['selectedCreditOption'];
                
                // Extract loan amount
                if (isset($creditOption['final_price'])) {
                    $formSubmission->loan_amount = (float) $creditOption['final_price'];
                } elseif (isset($formValues['loan-amount'])) {
                    $formSubmission->loan_amount = (float) $formValues['loan-amount'];
                } elseif (isset($formValues['applied-amount'])) {
                    $formSubmission->loan_amount = (float) $formValues['applied-amount'];
                }
                
                // Extract loan term
                if (isset($creditOption['months'])) {
                    $formSubmission->loan_term_months = (int) $creditOption['months'];
                }
                
                // Extract loan dates
                if (isset($selectedProduct['loanStartDate'])) {
                    $formSubmission->loan_start_date = $selectedProduct['loanStartDate'];
                }
                
                if (isset($selectedProduct['loanEndDate'])) {
                    $formSubmission->loan_end_date = $selectedProduct['loanEndDate'];
                }
            }
        }
        
        $formSubmission->save();

        // Return a response with reference number
        return response()->json([
            'message' => 'Form submitted successfully',
            'data' => $formSubmission,
            'insertId' => $formSubmission->id,
            'reference_number' => $formSubmission->uuid
        ], 200);
    }


    /**
     * List all form submissions with the same form name
     * 
     * @param string $formName
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listFormSubmissions($formName, Request $request)
    {
        try {
            // Handle both custom parameters and DataTables standard parameters
            $status = $request->input('status');
            $search = $request->input('search');
            
            // Get page parameters from DataTables or fallback to custom parameters
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', $request->input('per_page', 15)));
            $draw = intval($request->input('draw', 1));
            $page = max(1, floor($start / $length) + 1);
            
            // Set perPage from length for pagination
            $perPage = max(1, $length); // Ensure minimum of 1
            
            // Get sort parameters
            $validColumns = ['created_at', 'applicant_name', 'applicant_id_number', 'applicant_phone', 
                          'applicant_email', 'status', 'loan_amount', 'loan_term_months', 'uuid'];
            $sortBy = in_array($request->input('sort_by'), $validColumns) ? $request->input('sort_by') : 'created_at';
            $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';
    
            // Start with base query
            $query = Form::where('form_name', $formName);
    
            // Apply status filter
            if ($status && !empty($status)) {
                $query->where('status', $status);
            }
    
            // Apply search filter - first try indexed fields
            if ($search && !empty($search)) {
                $searchTerm = '%' . $search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('applicant_name', 'LIKE', $searchTerm)
                      ->orWhere('applicant_id_number', 'LIKE', $searchTerm)
                      ->orWhere('applicant_phone', 'LIKE', $searchTerm)
                      ->orWhere('applicant_email', 'LIKE', $searchTerm)
                      ->orWhere('uuid', 'LIKE', $searchTerm)
                      // Fallback to JSON search if needed
                      ->orWhere('questionnaire_data', 'LIKE', $searchTerm)
                      ->orWhere('form_values', 'LIKE', $searchTerm);
                });
            }
            
            // Apply date range filters if provided
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Apply loan amount filter if provided
            if ($request->has('min_amount') && is_numeric($request->min_amount)) {
                $minAmount = floatval($request->min_amount);
                $query->where('loan_amount', '>=', $minAmount);
            }
            
            if ($request->has('max_amount') && is_numeric($request->max_amount)) {
                $maxAmount = floatval($request->max_amount);
                $query->where('loan_amount', '<=', $maxAmount);
            }
        
            // Apply sorting - make sure we only use valid columns
            try {
                $query->orderBy($sortBy, $sortDir);
            } catch (\Exception $e) {
                // Fallback to default sort if there's an error with the requested column
                $query->orderBy('created_at', 'desc');
                \Log::error('Failed to apply requested sort', [
                    'requested_sort' => $sortBy, 
                    'error' => $e->getMessage()
                ]);
            }
            
            // Calculate total records before any filtering
            $recordsTotal = Form::where('form_name', $formName)->count();
            
            // Option to paginate or get all
            if ($perPage === 'all') {
                $forms = $query->get();
                $recordsFiltered = $forms->count();
            } else {
                // If start and length are provided, manually set the page for pagination
                if ($request->has('start') && $request->has('length')) {
                    $forms = $query->paginate($perPage, ['*'], 'page', $page);
                } else {
                    $forms = $query->paginate($perPage);
                }
                $recordsFiltered = $forms->total();
            }
    
            // Transform the results
            $results = $forms->map(function ($form) {
                // Initialize an object with basic form data
                $result = [
                    'id' => $form->id,
                    'uuid' => $form->uuid,
                    'status' => $form->status,
                    'created_at' => $form->created_at->format('Y-m-d H:i:s'),
                    'applicant_name' => $form->applicant_name ?? 'N/A',
                    'applicant_id_number' => $form->applicant_id_number ?? 'N/A',
                    'applicant_phone' => $form->applicant_phone ?? 'N/A',
                    'applicant_email' => $form->applicant_email ?? 'N/A',
                    'employer' => $form->employer ?? 'N/A',
                    'loan_amount' => $form->loan_amount ?? null,
                    'loan_term_months' => $form->loan_term_months ?? null,
                    'loan_start_date' => $form->loan_start_date ? $form->loan_start_date->format('Y-m-d') : null,
                    'loan_end_date' => $form->loan_end_date ? $form->loan_end_date->format('Y-m-d') : null,
                    'form_name' => $form->form_name
                ];
                
                // Add status color
                $result['status_color'] = $form->getStatusColor();
                
                try {
                    // If we need to include the full JSON data for detailed views
                    if ($form->form_values) {
                        $result['form_values'] = json_decode($form->form_values);
                    }
                    
                    if ($form->questionnaire_data) {
                        $questionnaireData = json_decode($form->questionnaire_data);
                        
                        // Extract product details if available
                        if (isset($questionnaireData->selectedProduct)) {
                            $result['product'] = $questionnaireData->selectedProduct->product->name ?? null;
                            
                            if (isset($questionnaireData->selectedProduct->selectedCreditOption)) {
                                $result['installment'] = $questionnaireData->selectedProduct->selectedCreditOption->installment_amount ?? null;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // JSON parsing error, just continue without these fields
                }
                
                return $result;
            });
            
            // Return appropriate response
            if ($perPage === 'all') {
                return response()->json($results);
            } else {
                // Format response specifically for DataTables server-side processing
                return response()->json([
                    'data' => $results,
                    'draw' => $draw,
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'meta' => [
                        'current_page' => $forms->currentPage(),
                        'last_page' => $forms->lastPage(),
                        'per_page' => $forms->perPage(),
                        'total' => $forms->total()
                    ]
                ]);
            }
        } catch (\Exception $e) {
            // Log the error with detailed information
            \Log::error('DataTables error in listFormSubmissions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'form_name' => $formName
            ]);
            
            // Return a clean error response
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while processing the request: ' . $e->getMessage(),
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
    }
}
