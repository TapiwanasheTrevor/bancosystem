<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Services\FormSchemaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class FormController extends Controller
{
    protected FormSchemaService $formSchemaService;
    
    public function __construct(FormSchemaService $formSchemaService)
    {
        $this->formSchemaService = $formSchemaService;
    }
    
    /**
     * Get form schema by type
     * 
     * @param string $formType
     * @return JsonResponse
     */
    public function show(string $formType): JsonResponse
    {
        try {
            $formSchema = $this->formSchemaService->getFormSchema($formType);
            return response()->json($formSchema);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
    
    /**
     * List all available form types
     * 
     * @return JsonResponse
     */
    public function listForms(): JsonResponse
    {
        $formTypes = $this->formSchemaService->getAllFormTypes();
        $formMetadata = $this->formSchemaService->getFormMetadata();
        
        return response()->json([
            'form_types' => $formTypes,
            'form_metadata' => $formMetadata
        ]);
    }

    /**
     * Load form submissions for admin view
     * 
     * @return View
     */
    public function loadApplications(): View
    {
        // Fetch all form submissions with pagination
        $formSubmissions = Form::with(['user', 'agent', 'referrer'])
            ->latest()
            ->paginate(15);
            
        // Get unique form types for filtering
        $formTypes = Form::select('form_name')
            ->distinct()
            ->pluck('form_name');
            
        return view('admin.form_submissions', compact('formSubmissions', 'formTypes'));
    }
    
    /**
     * Filter and search form submissions
     * 
     * @param Request $request
     * @return View
     */
    public function filterApplications(Request $request): View
    {
        $query = Form::with(['user', 'agent', 'referrer']);
        
        // Filter by form type
        if ($request->has('form_type') && $request->form_type) {
            $query->where('form_name', $request->form_type);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Search by applicant name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_id_number', 'like', "%{$search}%")
                  ->orWhere('uuid', 'like', "%{$search}%");
            });
        }
        
        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Sort results
        $sortField = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDir);
        
        $formSubmissions = $query->paginate(15)->withQueryString();
        
        // Get unique form types for filtering
        $formTypes = Form::select('form_name')
            ->distinct()
            ->pluck('form_name');
            
        return view('admin.form_submissions', compact('formSubmissions', 'formTypes'));
    }
}