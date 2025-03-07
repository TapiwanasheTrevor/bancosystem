<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminAgentController extends Controller
{
    public function index()
    {
        // Get all users with role 'agent'
        $agents = User::where('role', 'agent')->with('referrals')->get();
        
        // If no agents exist, create a sample agent
        if ($agents->isEmpty()) {
            // Create a sample agent if no agents exist
            $agent = User::create([
                'name' => 'Sample Agent',
                'email' => 'sample.agent@example.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'phone_number' => '1234567890',
                'position' => 'field_agent',
                'status' => 1
            ]);
            
            // Generate referral code
            $agent->generateReferralCode();
            
            // Refresh the query
            $agents = User::where('role', 'agent')->with('referrals')->get();
        }
        
        // Debug info - remove in production
        \Log::info('Agent count: ' . $agents->count());
        
        return view('agent.list', compact('agents'));
    }
    
    /**
     * Show form to create a new agent
     */
    public function create()
    {
        return view('agent.create');
    }
    
    /**
     * Edit an existing agent
     */
    public function edit($id)
    {
        $agent = User::findOrFail($id);
        
        if ($agent->role !== 'agent') {
            abort(403, 'Only agents can be edited');
        }
        
        return view('agent.edit', compact('agent'));
    }
    
    /**
     * Update an existing agent
     */
    public function update(Request $request, $id)
    {
        $agent = User::findOrFail($id);
        
        if ($agent->role !== 'agent') {
            abort(403, 'Only agents can be updated');
        }
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'phone_number' => ['required', 'string', 'max:20'],
            'position' => ['required', 'string', 'in:field_agent,office_agent,online_agent'],
        ]);
        
        $agent->update($validated);
        
        return redirect()
            ->route('agents.index')
            ->with('success', 'Agent updated successfully');
    }
    
    /**
     * Toggle agent status (active/inactive)
     */
    public function toggleStatus($id)
    {
        $agent = User::findOrFail($id);
        
        if ($agent->role !== 'agent') {
            abort(403, 'Only agents can have status toggled');
        }
        
        // Toggle status
        $agent->status = !$agent->status;
        $agent->save();
        
        $statusText = $agent->status ? 'activated' : 'deactivated';
        
        return redirect()
            ->route('agents.index')
            ->with('success', "Agent {$statusText} successfully");
    }

    /**
     * Create new agent
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone_number' => ['required', 'string', 'max:20'],
            'position' => ['required', 'string', 'in:field_agent,office_agent,online_agent'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ]);

        try {
            $agent = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone_number' => $validated['phone_number'],
                'position' => $validated['position'],
                'role' => 'agent',
                'status' => 1, // Active by default
            ]);

            // Generate referral code
            $agent->generateReferralCode();

            // Handle referral
            if ($request->filled('referral_code')) {
                $referrer = User::where('referral_code', $request->referral_code)->first();
                if ($referrer) {
                    $agent->referred_by = $referrer->id;
                    $agent->save();
                    
                    // Track this referral
                    $referrer->trackReferral();
                }
            }

            return redirect()
                ->route('agents.index')
                ->with('success', 'Agent created successfully');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating agent: ' . $e->getMessage());
        }
    }
    
    /**
     * Show agent dashboard with their referrals
     */
    public function dashboard($id)
    {
        $agent = User::findOrFail($id);
        
        if ($agent->role !== 'agent') {
            abort(403, 'Only agents can access this dashboard');
        }
        
        $referrals = $agent->referrals;
        $referredForms = Form::where('referred_by', $agent->id)->with('user')->get();
        
        return view('agent.dashboard', compact('agent', 'referrals', 'referredForms'));
    }
    
    /**
     * Generate a new referral link for agent
     */
    public function generateReferralLink($id)
    {
        $agent = User::findOrFail($id);
        
        // Regenerate referral code
        $referralCode = $agent->generateReferralCode();
        
        // Return the new referral code and link
        return response()->json([
            'referral_code' => $referralCode,
            'referral_link' => url('/?ref=' . $referralCode)
        ]);
    }
}
