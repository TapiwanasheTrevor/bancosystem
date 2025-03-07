@extends('layouts.app')

@section('title', 'Edit Agent')

@section('content')
    <div class="container mx-auto p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Edit Agent</h2>
            <a href="{{ route('agents.index') }}"
               class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                Back to Agents
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('agents.update', $agent->id) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- Personal Information Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Full Name
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $agent->name) }}"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-200 focus:border-emerald-500 @error('name') border-red-500 @enderror"
                               placeholder="Enter full name">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email', $agent->email) }}"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-200 focus:border-emerald-500 @error('email') border-red-500 @enderror"
                               placeholder="agent@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number
                        </label>
                        <input type="tel"
                               id="phone_number"
                               name="phone_number"
                               value="{{ old('phone_number', $agent->phone_number) }}"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-200 focus:border-emerald-500 @error('phone_number') border-red-500 @enderror"
                               placeholder="Enter phone number">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">
                            Agent Position
                        </label>
                        <select
                            id="position"
                            name="position"
                            class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-200 focus:border-emerald-500 @error('position') border-red-500 @enderror">
                            <option value="">Select agent position</option>
                            <option value="field_agent" {{ old('position', $agent->position) == 'field_agent' ? 'selected' : '' }}>Field Agent</option>
                            <option value="office_agent" {{ old('position', $agent->position) == 'office_agent' ? 'selected' : '' }}>Office Agent</option>
                            <option value="online_agent" {{ old('position', $agent->position) == 'online_agent' ? 'selected' : '' }}>Online Agent</option>
                        </select>
                        @error('position')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Referral Information -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Referral Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Referral Code
                        </label>
                        <div class="flex items-center">
                            <input type="text"
                                   readonly
                                   value="{{ $agent->referral_code }}"
                                   class="w-full px-4 py-2 bg-gray-100 border rounded-lg text-gray-600">
                            <button type="button"
                                    onclick="copyReferralCode()"
                                    class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors focus:outline-none">
                                Copy
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Referral Link
                        </label>
                        <div class="flex items-center">
                            <input type="text"
                                   readonly
                                   value="{{ url('/?ref=' . $agent->referral_code) }}"
                                   class="w-full px-4 py-2 bg-gray-100 border rounded-lg text-gray-600">
                            <button type="button"
                                    onclick="copyReferralLink()"
                                    class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors focus:outline-none">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Referral Statistics
                    </label>
                    <div class="px-4 py-3 bg-gray-100 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Total Referrals</span>
                                <p class="text-lg font-semibold">{{ $agent->referrals->count() }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Active Referrals</span>
                                <p class="text-lg font-semibold">{{ $agent->referrals->where('status', 1)->count() }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Forms Submitted</span>
                                <p class="text-lg font-semibold">{{ $agent->referredForms()->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('agents.index') }}"
                   class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    Update Agent
                </button>
            </div>
        </form>
    </div>

    <script>
        function copyReferralCode() {
            const codeInput = document.querySelector('input[value="{{ $agent->referral_code }}"]');
            codeInput.select();
            document.execCommand('copy');
            alert('Referral code copied to clipboard!');
        }
        
        function copyReferralLink() {
            const linkInput = document.querySelector('input[value="{{ url('/?ref=' . $agent->referral_code) }}"]');
            linkInput.select();
            document.execCommand('copy');
            alert('Referral link copied to clipboard!');
        }
    </script>
@endsection