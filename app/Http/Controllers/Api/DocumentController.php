<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Upload a new document for a client
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'client_id' => 'required|exists:users,id',
            'document_type' => 'required|string',
            'notes' => 'nullable|string'
        ]);
        
        $file = $request->file('file');
        $clientId = $request->input('client_id');
        $documentType = $request->input('document_type');
        
        // Get authenticated user (agent)
        $agent = Auth::user();
        
        // Get client
        $client = User::findOrFail($clientId);
        
        // Check if agent is allowed to upload for this client
        if ($client->referred_by != $agent->id && !$agent->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to upload documents for this client'
            ], 403);
        }
        
        // Generate a unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Store the file directly in the public folder
        $uploadPath = 'documents/' . $clientId;
        $publicPath = public_path($uploadPath);
        
        // Create directory if it doesn't exist
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0755, true);
        }
        
        // Move the file to public directory
        $file->move($publicPath, $filename);
        $path = $uploadPath . '/' . $filename;
        
        // Create document record
        $document = Document::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'file_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'agent_id' => $agent->id,
            'user_id' => $clientId,
            'status' => 'new',
            'notes' => $request->input('notes')
        ]);
        
        return response()->json([
            'success' => true,
            'document' => $document
        ]);
    }
    
    /**
     * Get new documents for an agent
     */
    public function getNewDocuments($agentId)
    {
        $agent = User::findOrFail($agentId);
        
        // Check if agent_id column exists in documents table
        $hasAgentId = Schema::hasColumn('documents', 'agent_id');
        
        if ($hasAgentId) {
            // Use agent_id relationship if available
            $documents = Document::where('agent_id', $agent->id)
                ->where('status', 'new')
                ->orderBy('created_at', 'desc')
                ->with('user')
                ->get();
        } else {
            // Otherwise fall back to fetching documents by user.id
            $documents = Document::where('user_id', $agent->id)
                ->where(function($query) {
                    $query->where('status', 'new')
                          ->orWhereNull('status');
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }
            
        return response()->view('partials.documents-list', [
            'documents' => $documents,
            'type' => 'new'
        ])->header('Content-Type', 'text/html');
    }
    
    /**
     * Get processed documents for an agent
     */
    public function getProcessedDocuments($agentId)
    {
        $agent = User::findOrFail($agentId);
        
        // Check if agent_id column exists in documents table
        $hasAgentId = Schema::hasColumn('documents', 'agent_id');
        
        if ($hasAgentId) {
            // Use agent_id relationship if available
            // Check if processed_at column exists
            if (Schema::hasColumn('documents', 'processed_at')) {
                $documents = Document::where('agent_id', $agent->id)
                    ->where('status', 'processed')
                    ->orderBy('processed_at', 'desc')
                    ->with('user')
                    ->get();
            } else {
                // Fall back to created_at if processed_at doesn't exist
                $documents = Document::where('agent_id', $agent->id)
                    ->where('status', 'processed')
                    ->orderBy('created_at', 'desc')
                    ->with('user')
                    ->get();
            }
        } else {
            // Otherwise fall back to fetching documents by user.id
            $documents = Document::where('user_id', $agent->id)
                ->where('status', 'processed')
                ->orderBy('created_at', 'desc')
                ->get();
        }
            
        // Group by date for the history view
        $groupedDocuments = $documents->groupBy(function ($doc) {
            // Always use created_at since processed_at might not exist
            return $doc->created_at->format('Y-m-d');
        });
        
        return response()->view('partials.history-documents', [
            'groupedDocuments' => $groupedDocuments
        ])->header('Content-Type', 'text/html');
    }
    
    /**
     * Mark a document as processed
     */
    public function markProcessed(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string'
        ]);
        
        $document = Document::findOrFail($id);
        $user = Auth::user();
        
        // Only admins and the uploading agent can process documents
        if ($document->agent_id != $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to process this document'
            ], 403);
        }
        
        // Update document status
        $document->status = 'processed';
        
        // Only set processed_at if the column exists
        if (Schema::hasColumn('documents', 'processed_at')) {
            $document->processed_at = Carbon::now();
        }
        
        // Only set processed_by if the column exists
        if (Schema::hasColumn('documents', 'processed_by')) {
            $document->processed_by = $user->id;
        }
        
        if ($request->has('notes')) {
            $document->notes = $request->input('notes');
        }
        
        $document->save();
        
        return response()->json([
            'success' => true,
            'document' => $document
        ]);
    }
    
    /**
     * Search for agents by name or email
     */
    public function searchAgents(Request $request)
    {
        $term = $request->input('term', '');
        
        $agents = User::where('role', 'agent')
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%");
            })
            ->get();
            
        // Calculate initials and document count manually
        foreach ($agents as $agent) {
            // Calculate initials
            $nameParts = explode(' ', $agent->name);
            $agent->initials = '';
            foreach ($nameParts as $part) {
                if (strlen($part) > 0) {
                    $agent->initials .= strtoupper(substr($part, 0, 1));
                }
            }
            
            // Check if agent_id column exists in documents table
            $hasAgentId = Schema::hasColumn('documents', 'agent_id');
            
            // Set document count
            if ($hasAgentId) {
                $agent->documents_count = Document::where('agent_id', $agent->id)
                    ->where('status', 'new')
                    ->count();
            } else {
                $agent->documents_count = 0; // Default to 0
            }
        }
            
        return response()->view('partials.agents-list', [
            'agents' => $agents
        ])->header('Content-Type', 'text/html');
    }
    
    /**
     * Get clients for an agent
     */
    public function getAgentClients($agentId)
    {
        $agent = User::findOrFail($agentId);
        
        // Get users who were referred by this agent
        $clients = User::where('referred_by', $agent->id)
            ->where('role', 'client')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
            
        return response()->json([
            'success' => true,
            'clients' => $clients
        ]);
    }
}