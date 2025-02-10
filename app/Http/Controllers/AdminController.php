<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Form;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    static function getStatusColor($status)
    {
        return [
            'pending' => 'yellow',
            'active' => 'green',
            'rejected' => 'red',
            'completed' => 'blue',
        ][$status] ?? 'gray';
    }

    static function generateSystemAlerts()
    {
        $alerts = collect();

        // Add alerts based on business logic
        $pendingOver48h = Form::where('status', 'pending')
            ->where('created_at', '<=', now()->subHours(48))
            ->count();

        if ($pendingOver48h > 0) {
            $alerts->push((object)[
                'type' => 'red',
                'message' => "{$pendingOver48h} applications pending review for over 48 hours"
            ]);
        }

        $pendingDocuments = Document::where('status', 'pending')
            ->count();

        if ($pendingDocuments > 0) {
            $alerts->push((object)[
                'type' => 'yellow',
                'message' => "{$pendingDocuments} documents awaiting verification"
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push((object)[
                'type' => 'green',
                'message' => "All systems operating normally"
            ]);
        }

        return $alerts;

    }

}
