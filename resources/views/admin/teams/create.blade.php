@extends('layouts.app')

@section('title', 'Create Team')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create New Team</h1>
        <a href="{{ route('teams.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i> Back to Teams
        </a>
    </div>

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('teams.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Team Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="leader_id" class="block text-sm font-medium text-gray-700 mb-1">Team Leader *</label>
                    <select id="leader_id" name="leader_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Team Leader</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('leader_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }} - {{ $agent->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('leader_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                    <input type="text" id="region" name="region" value="{{ old('region') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('region')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="is_active" class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Team is active</span>
                    </label>
                </div>
            </div>
            
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Team Members</h3>
                <p class="text-sm text-gray-600 mb-4">You can add team members after creating the team.</p>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Team Configuration</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="commission_type" class="block text-sm font-medium text-gray-700 mb-1">Commission Type</label>
                        <select id="commission_type" name="commission_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="percentage" {{ old('commission_type') == 'percentage' ? 'selected' : '' }}>Percentage of Sales</option>
                            <option value="fixed" {{ old('commission_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            <option value="tiered" {{ old('commission_type') == 'tiered' ? 'selected' : '' }}>Tiered Structure</option>
                        </select>
                        @error('commission_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="commission_rate" class="block text-sm font-medium text-gray-700 mb-1">Team Commission Rate (%)</label>
                        <input type="number" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', 0) }}" step="0.01" min="0" max="100" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('commission_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="team_target" class="block text-sm font-medium text-gray-700 mb-1">Monthly Team Target ($)</label>
                        <input type="number" id="team_target" name="team_target" value="{{ old('team_target', 0) }}" step="0.01" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('team_target')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="bonus_threshold" class="block text-sm font-medium text-gray-700 mb-1">Bonus Threshold (%)</label>
                        <input type="number" id="bonus_threshold" name="bonus_threshold" value="{{ old('bonus_threshold', 100) }}" step="1" min="0" max="200" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Percentage of target to achieve for bonus</p>
                        @error('bonus_threshold')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i> Create Team
                </button>
            </div>
        </form>
    </div>
</div>
@endsection