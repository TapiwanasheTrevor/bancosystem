<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DirectorLink;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class DirectorLinkController extends Controller
{
    /**
     * Generate links for directors to fill out their details
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_id' => 'required|string',
            'business_name' => 'required|string',
            'business_details' => 'required|array',
            'total_directors' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $formId = $request->input('form_id');
        $businessName = $request->input('business_name');
        $businessDetails = $request->input('business_details');
        $totalDirectors = $request->input('total_directors');

        $directorLinks = [];

        // Create a link for each director
        for ($i = 1; $i <= $totalDirectors; $i++) {
            $token = DirectorLink::generateUniqueToken();
            $isFinalDirector = ($i === $totalDirectors);

            $directorLink = DirectorLink::create([
                'token' => $token,
                'form_id' => $formId,
                'business_name' => $businessName,
                'business_details' => $businessDetails,
                'director_position' => $i,
                'total_directors' => $totalDirectors,
                'is_final_director' => $isFinalDirector,
                'expires_at' => Carbon::now()->addDays(7), // Links expire in 7 days
            ]);

            $url = URL::to('/director-form/' . $token);

            $directorLinks[] = [
                'position' => $i,
                'token' => $token,
                'url' => $url,
                'is_final_director' => $isFinalDirector
            ];
        }

        return response()->json([
            'success' => true,
            'links' => $directorLinks
        ]);
    }

    /**
     * Get director form data from token
     * 
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDirectorFormData($token)
    {
        $directorLink = DirectorLink::where('token', $token)->first();

        if (!$directorLink) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired link'
            ], 404);
        }

        if ($directorLink->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'This link has expired'
            ], 410);
        }

        if ($directorLink->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'This form has already been completed'
            ], 410);
        }

        // Return form data with director position information
        return response()->json([
            'success' => true,
            'form_id' => $directorLink->form_id,
            'business_name' => $directorLink->business_name,
            'business_details' => $directorLink->business_details,
            'director_position' => $directorLink->director_position,
            'total_directors' => $directorLink->total_directors,
            'is_final_director' => $directorLink->is_final_director
        ]);
    }

    /**
     * Submit director data
     * 
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitDirectorData(Request $request, $token)
    {
        $directorLink = DirectorLink::where('token', $token)->first();

        if (!$directorLink) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired link'
            ], 404);
        }

        if ($directorLink->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'This link has expired'
            ], 410);
        }

        if ($directorLink->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'This form has already been completed'
            ], 410);
        }

        $validator = Validator::make($request->all(), [
            'director_data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update the director link with the submitted data
        $directorLink->form_data = $request->input('director_data');
        $directorLink->is_completed = true;
        $directorLink->save();

        // If this is the final director and they're submitting the full form
        if ($directorLink->is_final_director && $request->has('submit_full_form') && $request->input('submit_full_form')) {
            // Gather all directors' data
            $allDirectorsData = [];
            
            for ($i = 1; $i <= $directorLink->total_directors; $i++) {
                $directorData = DirectorLink::where('form_id', $directorLink->form_id)
                    ->where('director_position', $i)
                    ->where('is_completed', true)
                    ->first();
                
                if (!$directorData) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not all directors have completed their forms yet'
                    ], 400);
                }
                
                $allDirectorsData["director{$i}"] = $directorData->form_data;
            }
            
            // Submit the complete form with all directors' data
            // Here you would call the FormSubmissionController or similar
            
            return response()->json([
                'success' => true,
                'message' => 'Full application submitted successfully',
                'all_directors_data' => $allDirectorsData
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $directorLink->is_final_director ? 
                'Your details have been saved. You can now submit the full application.' : 
                'Your details have been saved. Please share the link with the next director.'
        ]);
    }
}
