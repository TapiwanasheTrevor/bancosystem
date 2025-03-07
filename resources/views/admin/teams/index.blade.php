@extends('layouts.app')

@section('title', 'Team Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Team Management</h1>
        <a href="{{ route('teams.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Create Team
        </a>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- Teams Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($teams as $team)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5 border-b border-gray-200">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-xl font-bold text-gray-800">{{ $team->name }}</h2>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $team->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $team->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                    <i class="fas fa-calendar-alt mr-1"></i> Formed: {{ $team->formed_date->format('M d, Y') }}
                </p>
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-indigo-800 font-semibold">{{ strtoupper(substr($team->teamLead->name ?? 'NA', 0, 2)) }}</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ $team->teamLead->name ?? 'No Team Lead' }}</p>
                        <p class="text-xs text-gray-500">Team Lead</p>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-users mr-1"></i> {{ $team->members->count() }} members
                        </p>
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-money-bill-wave mr-1"></i> ${{ number_format($team->getTotalCommissions(), 2) }} total commissions
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('teams.show', $team->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                            View Team
                        </a>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="flex flex-wrap gap-1">
                    @foreach($team->members->take(5) as $member)
                        <div class="bg-indigo-100 rounded-full h-8 w-8 flex items-center justify-center" title="{{ $member->user->name ?? 'Unknown' }}">
                            <span class="text-xs text-indigo-800 font-semibold">{{ strtoupper(substr($member->user->name ?? 'U', 0, 2)) }}</span>
                        </div>
                    @endforeach
                    
                    @if($team->members->count() > 5)
                        <div class="bg-gray-200 rounded-full h-8 w-8 flex items-center justify-center">
                            <span class="text-xs text-gray-700 font-semibold">+{{ $team->members->count() - 5 }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-lg shadow p-6 text-center text-gray-500">
            <i class="fas fa-users text-4xl mb-3 text-gray-400"></i>
            <p class="text-lg">No teams found.</p>
            <p class="text-sm mt-2">Create your first team to manage your sales agents effectively.</p>
            <a href="{{ route('teams.create') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i> Create Team
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection