<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductDelivery;
use App\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryTrackingController extends Controller
{
    /**
     * Track delivery by tracking number
     */
    public function trackByNumber(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string'
        ]);

        $delivery = ProductDelivery::with(['product', 'statusUpdates'])
            ->where('tracking_number', $request->tracking_number)
            ->first();

        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Tracking number not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'delivery' => [
                'tracking_number' => $delivery->tracking_number,
                'status' => $delivery->status,
                'status_label' => $delivery->status_label,
                'current_location' => $delivery->current_location,
                'estimated_delivery_date' => $delivery->estimated_delivery_date?->format('Y-m-d'),
                'actual_delivery_date' => $delivery->actual_delivery_date?->format('Y-m-d'),
                'product' => [
                    'id' => $delivery->product->id,
                    'name' => $delivery->product->name,
                    'image' => $delivery->product->image
                ],
                'status_updates' => $delivery->statusUpdates->map(function ($update) {
                    return [
                        'status' => $update->status,
                        'status_label' => $update->status_label,
                        'location' => $update->location,
                        'notes' => $update->notes,
                        'datetime' => $update->created_at->format('Y-m-d H:i:s')
                    ];
                })
            ]
        ]);
    }

    /**
     * Get all deliveries for a user's application form
     */
    public function getUserDeliveries(Request $request): JsonResponse
    {
        $request->validate([
            'form_uuid' => 'required|string|exists:forms,uuid'
        ]);

        $form = Form::where('uuid', $request->form_uuid)->first();
        
        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        $deliveries = ProductDelivery::with(['product', 'statusUpdates'])
            ->where('form_id', $form->id)
            ->get();

        return response()->json([
            'success' => true,
            'deliveries' => $deliveries->map(function ($delivery) {
                return [
                    'id' => $delivery->id,
                    'tracking_number' => $delivery->tracking_number,
                    'status' => $delivery->status,
                    'status_label' => $delivery->status_label,
                    'current_location' => $delivery->current_location,
                    'estimated_delivery_date' => $delivery->estimated_delivery_date?->format('Y-m-d'),
                    'product' => [
                        'id' => $delivery->product->id,
                        'name' => $delivery->product->name,
                        'image' => $delivery->product->image
                    ],
                    'latest_update' => $delivery->statusUpdates->first() ? [
                        'status' => $delivery->statusUpdates->first()->status,
                        'location' => $delivery->statusUpdates->first()->location,
                        'notes' => $delivery->statusUpdates->first()->notes,
                        'datetime' => $delivery->statusUpdates->first()->created_at->format('Y-m-d H:i:s')
                    ] : null
                ];
            })
        ]);
    }
    
    /**
     * Get detailed delivery information
     */
    public function getDeliveryDetails(Request $request): JsonResponse
    {
        $request->validate([
            'delivery_id' => 'required|integer|exists:product_deliveries,id'
        ]);

        $delivery = ProductDelivery::with(['product', 'statusUpdates'])
            ->findOrFail($request->delivery_id);

        return response()->json([
            'success' => true,
            'delivery' => [
                'id' => $delivery->id,
                'tracking_number' => $delivery->tracking_number,
                'status' => $delivery->status,
                'status_label' => $delivery->status_label,
                'status_color' => $delivery->status_color,
                'current_location' => $delivery->current_location,
                'status_notes' => $delivery->status_notes,
                'estimated_delivery_date' => $delivery->estimated_delivery_date?->format('Y-m-d'),
                'actual_delivery_date' => $delivery->actual_delivery_date?->format('Y-m-d'),
                'product' => [
                    'id' => $delivery->product->id,
                    'name' => $delivery->product->name,
                    'description' => $delivery->product->description,
                    'image' => $delivery->product->image
                ],
                'status_updates' => $delivery->statusUpdates->map(function ($update) {
                    return [
                        'status' => $update->status,
                        'status_label' => $update->status_label,
                        'location' => $update->location,
                        'notes' => $update->notes,
                        'datetime' => $update->created_at->format('Y-m-d H:i:s')
                    ];
                })
            ]
        ]);
    }
}
