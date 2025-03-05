<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FormStatusController extends Controller
{
    /**
     * Update the status of a form
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,processing,completed',
            'notes' => 'nullable|string'
        ]);

        try {
            $form = Form::findOrFail($id);
            $form->status = $request->status;
            
            // If you have a notes field in your form table, you can save that too
            // $form->status_notes = $request->notes;
            
            $form->save();

            return response()->json([
                'success' => true,
                'message' => 'Form status updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating form status', [
                'form_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update form status'
            ], 500);
        }
    }

    /**
     * Update the status of multiple forms in batch
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function batchUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:forms,id',
            'status' => 'required|string|in:pending,approved,rejected,processing,completed',
            'notes' => 'nullable|string'
        ]);

        try {
            Form::whereIn('id', $request->ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' forms updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error batch updating form status', [
                'form_ids' => $request->ids,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update form status'
            ], 500);
        }
    }

    /**
     * Delete a form
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteForm(int $id): JsonResponse
    {
        try {
            $form = Form::findOrFail($id);
            $form->delete();

            return response()->json([
                'success' => true,
                'message' => 'Form deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting form', [
                'form_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete form'
            ], 500);
        }
    }
}