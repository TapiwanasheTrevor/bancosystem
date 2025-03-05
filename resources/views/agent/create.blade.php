@extends('layouts.app')

@section('title', 'Create New Agent')

@section('content')
    <div class="container mx-auto p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Create New Agent</h2>
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

        <form action="{{ route('agents.store') }}" method="POST" class="space-y-8">
            @csrf
            
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
                               value="{{ old('name') }}"
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
                               value="{{ old('email') }}"
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
                               value="{{ old('phone_number') }}"
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
                            <option value="field_agent" {{ old('position') == 'field_agent' ? 'selected' : '' }}>Field Agent</option>
                            <option value="office_agent" {{ old('position') == 'office_agent' ? 'selected' : '' }}>Office Agent</option>
                            <option value="supervisor" {{ old('position') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        </select>
                        @error('position')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Account Details Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-200 focus:border-emerald-500 @error('password') border-red-500 @enderror"
                               placeholder="Enter password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm Password
                        </label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-200 focus:border-emerald-500"
                               placeholder="Confirm password">
                    </div>
                </div>
            </div>

            <!-- Referral Program Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Referral Setup</h3>
                <div class="space-y-4">
                    <p class="text-sm text-gray-600">A unique referral code will be automatically generated when the agent is created.</p>
                    <div>
                        <label for="referral_code" class="block text-sm font-medium text-gray-700 mb-1">
                            Referred By (Optional)
                        </label>
                        <input type="text"
                               id="referral_code"
                               name="referral_code"
                               value="{{ old('referral_code') }}"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-200 focus:border-emerald-500 @error('referral_code') border-red-500 @enderror"
                               placeholder="Enter referral code if this agent was referred">
                        @error('referral_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">If this agent was referred by another agent, enter their referral code here.</p>
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
                    Create Agent
                </button>
            </div>
        </form>
    </div>
@endsection