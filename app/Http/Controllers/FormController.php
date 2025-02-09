<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function show($formType)
    {
        $path = resource_path("forms/{$formType}.json");

        if (!file_exists($path)) {
            return response()->json(['error' => 'Form not found'], 404);
        }

        $formSchema = json_decode(file_get_contents($path), true);
        return response()->json($formSchema);
    }

    public function loadApplications()
    {
        // Fetch all form submissions
        $formSubmissions = Form::all();

        // Return the view with form submissions
        return view('admin.form_submissions', compact('formSubmissions'));
    }
}
