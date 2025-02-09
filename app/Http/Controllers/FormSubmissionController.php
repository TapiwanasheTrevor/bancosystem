<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function submit(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'formId' => 'required|string',
            'formValues' => 'required|array',
            'questionnaireData' => 'required|array',
        ]);

        // Save the form submission to the database
        $formSubmission = new Form();
        $formSubmission->form_name = $validatedData['formId'];
        $formSubmission->form_values = json_encode($validatedData['formValues']);
        $formSubmission->questionnaire_data = json_encode($validatedData['questionnaireData']);
        $formSubmission->agent_id = $validatedData['agent_id'] ?? null;
        $formSubmission->status = 'pending';
        $formSubmission->save();

        // Return a response
        return response()->json([
            'message' => 'Form submitted successfully',
            'data' => $formSubmission,
        ], 200);
    }


    //list all form with the same form name
    public function listFormSubmissions($formName)
    {
        //return all forms where form_name = $formName
        $formSubmissions = Form::where('form_name', $formName)->get();
        //return json response
        return response()->json($formSubmissions);
    }
}
