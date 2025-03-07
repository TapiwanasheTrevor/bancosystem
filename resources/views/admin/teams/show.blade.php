@extends('layouts.app')

@section('title', 'Team Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Team: {{ $team->name }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('teams.edit', $team->id) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-edit mr-2"></i> Edit Team
            </a>
            <a href="{{ route('teams.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to Teams
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Team Information Card -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold">Team Information</h2>
                </div>
                
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="font-medium">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $team->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $team->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Team Leader</p>
                        <p class="font-medium">{{ $team->leader->name }}</p>
                        <p class="text-xs text-gray-500">{{ $team->leader->email }}</p>
                    </div>
                    
                    @if($team->region)
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Region</p>
                        <p class="font-medium">{{ $team->region }}</p>
                    </div>
                    @endif
                    
                    @if($team->description)
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Description</p>
                        <p class="font-medium">{{ $team->description }}</p>
                    </div>
                    @endif
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Created</p>
                        <p class="font-medium">{{ $team->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Team Performance Stats Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold">Team Performance</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-blue-600">Members</p>
                            <p class="text-xl font-bold text-blue-800">{{ $team->members->count() }}</p>
                        </div>
                        
                        <div class="p-3 bg-green-50 rounded-lg">
                            <p class="text-xs text-green-600">Applications</p>
                            <p class="text-xl font-bold text-green-800">{{ $applicationCount }}</p>
                        </div>
                        
                        <div class="p-3 bg-indigo-50 rounded-lg">
                            <p class="text-xs text-indigo-600">Month Sales</p>
                            <p class="text-xl font-bold text-indigo-800">${{ number_format($monthSales, 2) }}</p>
                        </div>
                        
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <p class="text-xs text-purple-600">Target Achieved</p>
                            <p class="text-xl font-bold text-purple-800">
                                @if($team->team_target > 0)
                                    {{ round(($monthSales / $team->team_target) * 100) }}%
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Commission Configuration</h3>
                        <div class="text-sm">
                            <p><span class="font-medium">Type:</span> {{ ucfirst($team->commission_type) }}</p>
                            <p><span class="font-medium">Rate:</span> {{ $team->commission_rate }}%</p>
                            <p><span class="font-medium">Monthly Target:</span> ${{ number_format($team->team_target, 2) }}</p>
                            <p><span class="font-medium">Bonus Threshold:</span> {{ $team->bonus_threshold }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Team Members Section -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold">Team Members</h2>
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.remove('hidden')" class="bg-indigo-600 text-white px-3 py-1 rounded-lg hover:bg-indigo-700 text-sm">
                        <i class="fas fa-plus mr-1"></i> Add Member
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applications</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commissions</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($team->members as $member)
                            @php 
                                $memberAgentStats = $agentStats[$member->agent_id] ?? [
                                    'applications' => 0,
                                    'sales' => 0,
                                    'commissions' => 0
                                ];
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-indigo-700 font-medium">{{ substr($member->agent->name, 0, 2) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $member->agent->name }}</div>
                                            <div class="text-xs text-gray-500">Joined: {{ $member->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $member->agent->email }}</div>
                                    <div class="text-xs text-gray-500">{{ $member->agent->phone ?? 'No phone' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $memberAgentStats['applications'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${{ number_format($memberAgentStats['sales'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${{ number_format($memberAgentStats['commissions'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('teams.remove-member', $member->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this member from the team?');">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Remove from team">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No team members yet. Add team members to get started.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Team Performance Charts -->
            <div class="bg-white rounded-lg shadow mt-6 p-6">
                <h2 class="text-lg font-semibold mb-4">Performance Metrics</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Monthly Sales Progress</h3>
                        
                        <div class="mt-1 relative pt-1">
                            <div class="flex mb-2 items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold inline-block text-indigo-600">
                                        {{ $team->team_target > 0 ? round(($monthSales / $team->team_target) * 100) : 0 }}% of Target
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold inline-block text-indigo-600">
                                        ${{ number_format($monthSales, 2) }} / ${{ number_format($team->team_target, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
                                <div style="width:{{ $team->team_target > 0 ? min(100, ($monthSales / $team->team_target) * 100) : 0 }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"></div>
                            </div>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-2">
                            @if($team->team_target > 0 && $monthSales >= $team->team_target)
                                Team has reached the monthly target!
                            @else
                                ${{ number_format(max(0, $team->team_target - $monthSales), 2) }} more to reach the target.
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Applications by Status</h3>
                        
                        <div class="space-y-2 mt-3">
                            @foreach($applicationsByStatus as $status => $count)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-medium text-gray-700">{{ ucfirst($status) }}</span>
                                    <span class="text-xs font-medium text-gray-700">{{ $count }} ({{ $totalApplications > 0 ? round(($count / $totalApplications) * 100) : 0 }}%)</span>
                                </div>
                                <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                    <div style="width:{{ $totalApplications > 0 ? ($count / $totalApplications) * 100 : 0 }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center
                                        @if($status == 'approved') bg-green-500
                                        @elseif($status == 'pending') bg-yellow-500
                                        @elseif($status == 'rejected') bg-red-500
                                        @else bg-blue-500
                                        @endif
                                    "></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Add Team Member</h2>
            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="document.getElementById('addMemberModal').classList.add('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('teams.add-member', $team->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="agent_id" class="block text-sm font-medium text-gray-700 mb-1">Select Agent *</label>
                <select id="agent_id" name="agent_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">Select an Agent</option>
                    @foreach($availableAgents as $agent)
                        <option value="{{ $agent->id }}">
                            {{ $agent->name }} - {{ $agent->email }}
                        </option>
                    @endforeach
                </select>
                @error('agent_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300" onclick="document.getElementById('addMemberModal').classList.add('hidden')">
                    Cancel
                </button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    Add to Team
                </button>
            </div>
        </form>
    </div>
</div>
@endsection