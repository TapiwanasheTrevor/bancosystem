@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Agent Information Header -->
        <div class="p-6 sm:px-8 bg-gradient-to-r from-emerald-500 to-emerald-700 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Agent Dashboard</h1>
                    <p class="text-emerald-100 mt-1">Welcome, {{ $agent->name }}</p>
                </div>
                <div class="mt-4 md:mt-0 flex flex-col md:items-end">
                    <div class="text-sm text-emerald-100">Agent ID: {{ $agent->id }}</div>
                    <div class="text-sm text-emerald-100">{{ ucfirst($agent->position) }}</div>
                    <div class="text-sm font-semibold text-white">
                        Total Referrals: {{ $agent->referral_count }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Referral Link Section -->
        <div class="p-6 bg-white border-b">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Your Referral Link</h2>
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Share this link with potential customers. When they visit this link and submit an application, they'll be automatically linked to your account.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-grow">
                    <div class="relative">
                        <input type="text" id="referral-link" value="{{ url('/?ref=' . $agent->referral_code) }}" readonly
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none">
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="copyToClipboard()" 
                        class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition-colors flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                        <span>Copy Link</span>
                    </button>
                    <button id="generate-new-link" 
                        class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Generate New</span>
                    </button>
                </div>
            </div>
            <div id="copy-feedback" class="mt-2 text-emerald-600 text-sm hidden">
                Link copied to clipboard!
            </div>
        </div>

        <!-- Statistics Card Grid -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Total Referrals</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $agent->referral_count }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Total Applications</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $referredForms->count() }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Approved Applications</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $referredForms->where('status', 'approved')->count() }}</p>
            </div>
        </div>

        <!-- Recent Referrals Table -->
        <div class="p-6 bg-white">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Referrals</h2>
            
            @if($referrals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($referrals->take(5) as $referral)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $referral->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-500">{{ $referral->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $referral->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($referrals->count() > 5)
                    <div class="mt-4 text-center">
                        <a href="#" class="text-emerald-600 hover:text-emerald-800">View all referrals</a>
                    </div>
                @endif
            @else
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <p class="text-gray-500">No referrals yet. Share your referral link to get started!</p>
                </div>
            @endif
        </div>

        <!-- Recent Applications Table -->
        <div class="p-6 bg-white">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Applications</h2>
            
            @if($referredForms->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Form Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($referredForms->take(5) as $form)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $form->form_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-900">
                                            @if($form->user)
                                                {{ $form->user->name }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($form->status === 'approved') bg-green-100 text-green-800
                                            @elseif($form->status === 'rejected') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst($form->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $form->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="#" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($referredForms->count() > 5)
                    <div class="mt-4 text-center">
                        <a href="#" class="text-emerald-600 hover:text-emerald-800">View all applications</a>
                    </div>
                @endif
            @else
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <p class="text-gray-500">No applications have been submitted yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function copyToClipboard() {
        const linkInput = document.getElementById('referral-link');
        linkInput.select();
        document.execCommand('copy');
        
        // Show feedback
        const feedback = document.getElementById('copy-feedback');
        feedback.classList.remove('hidden');
        
        // Hide feedback after 2 seconds
        setTimeout(() => {
            feedback.classList.add('hidden');
        }, 2000);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const generateBtn = document.getElementById('generate-new-link');
        
        generateBtn.addEventListener('click', function() {
            // Disable button during request
            generateBtn.disabled = true;
            generateBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Generating...</span>
            `;
            
            // Send AJAX request to generate new link
            fetch('{{ route('agents.generate-link', $agent->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update the link in the input
                document.getElementById('referral-link').value = data.referral_link;
                
                // Re-enable button
                generateBtn.disabled = false;
                generateBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Generate New</span>
                `;
                
                // Show success message
                alert('New referral link generated successfully!');
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Re-enable button
                generateBtn.disabled = false;
                generateBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Generate New</span>
                `;
                
                // Show error message
                alert('Error generating new referral link. Please try again.');
            });
        });
    });
</script>
@endsection