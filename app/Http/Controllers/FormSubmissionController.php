<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function listFormSubmissions($formName, Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $query = DB::table('forms')->where('form_name', $formName);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('questionnaire_data', 'LIKE', "%{$search}%")
                    ->orWhere('form_values', 'LIKE', "%{$search}%");
            });
        }

        //loop through collection and json decode the form_values and questionnaire_data
        $query = $query->get()->map(function ($item) {
            $item->form_values = json_decode($item->form_values);
            $item->questionnaire_data = json_decode($item->questionnaire_data);

            //set the name of the applicant from the form_values
            $item->name = '';
            return $item;
        });

        return datatables()->of($query)->make(true);
    }
}
