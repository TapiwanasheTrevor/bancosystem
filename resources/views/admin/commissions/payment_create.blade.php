@extends('layouts.app')

@section('title', 'Process Commission Payment')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Process Commission Payment</h1>
        <a href="{{ route('commissions.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i> Back to Commissions
        </a>
    </div>

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('commissions.payment.store') }}" method="POST" id="paymentForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                    <input type="date" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('payment_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                    <select id="payment_method" name="payment_method" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Payment Method</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="check">Check</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                    </select>
                    @error('payment_method')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                    <input type="text" id="reference_number" name="reference_number" value="{{ old('reference_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('reference_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="payment_period" class="block text-sm font-medium text-gray-700 mb-1">Payment Period *</label>
                    <select id="payment_period" name="payment_period" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Payment Period</option>
                        @foreach($paymentPeriods as $period)
                            <option value="{{ $period['value'] }}">{{ $period['label'] }}</option>
                        @endforeach
                    </select>
                    @error('payment_period')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Select Commissions to Pay</h2>
                
                <div class="bg-gray-100 rounded-lg p-4 mb-4">
                    <div class="flex flex-wrap gap-4 items-center">
                        <div>
                            <label for="agent_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Agent</label>
                            <select id="agent_filter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Agents</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="team_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Team</label>
                            <select id="team_filter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Teams</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                            <select id="status_filter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pending">Pending</option>
                                <option value="">All Statuses</option>
                                <option value="approved">Approved</option>
                                <option value="paid">Paid</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        
                        <div class="mt-6">
                            <button type="button" id="selectAllBtn" class="bg-indigo-600 text-white px-3 py-2 rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-check-square mr-1"></i> Select All
                            </button>
                            <button type="button" id="deselectAllBtn" class="bg-gray-500 text-white px-3 py-2 rounded-lg hover:bg-gray-600 ml-2">
                                <i class="fas fa-square mr-1"></i> Deselect All
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="commissionsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Value</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($commissions as $commission)
                            <tr class="commission-row" 
                                data-agent="{{ $commission->agent_id }}" 
                                data-team="{{ $commission->team_id ?? '' }}"
                                data-status="{{ $commission->status }}">
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <input type="checkbox" name="commission_ids[]" value="{{ $commission->id }}" class="commission-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $commission->agent->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $commission->team ? $commission->team->name : 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $commission->product->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${{ number_format($commission->sale_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-green-600">${{ number_format($commission->commission_amount, 2) }}</div>
                                    <div class="text-xs text-gray-500">{{ $commission->commission_rate }}%</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($commission->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($commission->status == 'approved') bg-blue-100 text-blue-800
                                        @elseif($commission->status == 'paid') bg-green-100 text-green-800
                                        @elseif($commission->status == 'rejected') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($commission->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $commission->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No commissions found for payment.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 text-right">
                    <p class="text-sm text-gray-600">Selected: <span id="selectedCount">0</span> commissions</p>
                    <p class="text-lg font-bold text-indigo-600">Total Amount: $<span id="totalAmount">0.00</span></p>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700" id="submitBtn">
                    <i class="fas fa-money-bill-wave mr-2"></i> Process Payment
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const agentFilter = document.getElementById('agent_filter');
        const teamFilter = document.getElementById('team_filter');
        const statusFilter = document.getElementById('status_filter');
        const selectAllCheckbox = document.getElementById('selectAll');
        const selectAllBtn = document.getElementById('selectAllBtn');
        const deselectAllBtn = document.getElementById('deselectAllBtn');
        const commissionRows = document.querySelectorAll('.commission-row');
        const commissionCheckboxes = document.querySelectorAll('.commission-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const totalAmountElement = document.getElementById('totalAmount');
        const paymentForm = document.getElementById('paymentForm');
        
        // Commission data for calculations
        const commissionData = @json($commissions->map(function($commission) {
            return [
                'id' => $commission->id,
                'amount' => $commission->commission_amount,
                'agent_id' => $commission->agent_id,
                'team_id' => $commission->team_id,
                'status' => $commission->status
            ];
        }));
        
        // Filter the commissions table
        function applyFilters() {
            const agentId = agentFilter.value;
            const teamId = teamFilter.value;
            const status = statusFilter.value;
            
            commissionRows.forEach(row => {
                const rowAgentId = row.getAttribute('data-agent');
                const rowTeamId = row.getAttribute('data-team');
                const rowStatus = row.getAttribute('data-status');
                
                const agentMatch = !agentId || rowAgentId === agentId;
                const teamMatch = !teamId || rowTeamId === teamId;
                const statusMatch = !status || rowStatus === status;
                
                if (agentMatch && teamMatch && statusMatch) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                    const checkbox = row.querySelector('.commission-checkbox');
                    checkbox.checked = false;
                }
            });
            
            updateTotals();
        }
        
        // Update totals based on selected checkboxes
        function updateTotals() {
            let selectedCount = 0;
            let totalAmount = 0;
            
            commissionCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedCount++;
                    const commissionId = checkbox.value;
                    const commission = commissionData.find(c => c.id == commissionId);
                    if (commission) {
                        totalAmount += parseFloat(commission.amount);
                    }
                }
            });
            
            selectedCountElement.textContent = selectedCount;
            totalAmountElement.textContent = totalAmount.toFixed(2);
            
            // Disable submit button if no commissions selected
            document.getElementById('submitBtn').disabled = selectedCount === 0;
        }
        
        // Event listeners for filters
        agentFilter.addEventListener('change', applyFilters);
        teamFilter.addEventListener('change', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        
        // Event listener for select all checkbox
        selectAllCheckbox.addEventListener('change', function() {
            const visibleCheckboxes = Array.from(commissionCheckboxes).filter(checkbox => {
                return !checkbox.closest('tr').classList.contains('hidden');
            });
            
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateTotals();
        });
        
        // Event listeners for individual checkboxes
        commissionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateTotals();
                
                // Update select all checkbox state
                const visibleCheckboxes = Array.from(commissionCheckboxes).filter(checkbox => {
                    return !checkbox.closest('tr').classList.contains('hidden');
                });
                
                const allChecked = visibleCheckboxes.every(checkbox => checkbox.checked);
                const someChecked = visibleCheckboxes.some(checkbox => checkbox.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });
        
        // Event listeners for select/deselect all buttons
        selectAllBtn.addEventListener('click', function() {
            const visibleCheckboxes = Array.from(commissionCheckboxes).filter(checkbox => {
                return !checkbox.closest('tr').classList.contains('hidden');
            });
            
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
            
            updateTotals();
        });
        
        deselectAllBtn.addEventListener('click', function() {
            commissionCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            
            updateTotals();
        });
        
        // Form submission validation
        paymentForm.addEventListener('submit', function(e) {
            const selectedCount = document.querySelectorAll('.commission-checkbox:checked').length;
            
            if (selectedCount === 0) {
                e.preventDefault();
                alert('Please select at least one commission to process payment.');
                return false;
            }
        });
        
        // Initialize the view
        applyFilters();
        updateTotals();
    });
</script>
@endpush