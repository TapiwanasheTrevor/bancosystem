<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AdminAgentController extends Controller
{
    public function index()
    {
        return view('agents');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone_number' => ['required', 'string', 'max:20'],
            'referral' => ['nullable', 'string', 'exists:users,referral_code'],
            'documents.license' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        try {
            $agent = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make('password'),
                'phone_number' => $validated['phone_number'],
                'role' => 'agent',
            ]);

            // Generate referral code
            $agent->generateReferralCode();

            // Handle license document upload if provided
            if ($request->hasFile('documents.license')) {
                $path = $request->file('documents.license')->store('agent-documents/' . $agent->id);
                $agent->addDocument('license', $path);
            }

            // Handle referral
            if ($request->filled('referral_code')) {
                $referrer = User::where('referral_code', $request->referral_code)->first();
                if ($referrer) {
                    $agent->referred_by = $referrer->id;
                    $agent->save();
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
}
