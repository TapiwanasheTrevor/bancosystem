@extends('layouts.app')

@section('title', 'Commission Payment Form')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Commission Payment Form</h1>
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
        <form action="{{ route('commissions.payment.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="agent_id" class="block text-sm font-medium text-gray-700 mb-1">Select Agent *</label>
                    <select id="agent_id" name="agent_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Agent</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    @error('agent_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                    <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('payment_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="period_start" class="block text-sm font-medium text-gray-700 mb-1">Period Start Date *</label>
                    <input type="date" id="period_start" name="period_start" value="{{ old('period_start', date('Y-m-01')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('period_start')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="period_end" class="block text-sm font-medium text-gray-700 mb-1">Period End Date *</label>
                    <input type="date" id="period_end" name="period_end" value="{{ old('period_end', date('Y-m-t')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('period_end')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                    <select id="payment_method" name="payment_method" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Payment Method</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    </select>
                    @error('payment_method')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-1">Transaction ID / Reference</label>
                    <input type="text" id="transaction_id" name="transaction_id" value="{{ old('transaction_id') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('transaction_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Payment Notes</label>
                <textarea id="notes" name="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div id="commissionSummary" class="bg-gray-50 p-4 rounded-lg mb-6 hidden">
                <h3 class="text-lg font-semibold mb-3">Commission Summary</h3>
                <div id="commissionData" class="space-y-2">
                    <!-- Commission data will be populated here with JavaScript -->
                </div>
                <div class="mt-4 pt-3 border-t border-gray-200">
                    <p class="text-right text-lg font-bold">Total Amount: $<span id="totalAmount">0.00</span></p>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="button" id="calculateBtn" class="mr-3 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-calculator mr-2"></i> Calculate Commission
                </button>
                <button type="submit" id="submitBtn" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 hidden">
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
        const calculateBtn = document.getElementById('calculateBtn');
        const submitBtn = document.getElementById('submitBtn');
        const commissionSummary = document.getElementById('commissionSummary');
        const commissionData = document.getElementById('commissionData');
        const totalAmountElement = document.getElementById('totalAmount');
        const agentSelect = document.getElementById('agent_id');
        const periodStartInput = document.getElementById('period_start');
        const periodEndInput = document.getElementById('period_end');
        
        // Add validation for period dates
        periodStartInput.addEventListener('change', function() {
            if (periodEndInput.value && new Date(periodStartInput.value) > new Date(periodEndInput.value)) {
                periodEndInput.value = periodStartInput.value;
            }
        });
        
        periodEndInput.addEventListener('change', function() {
            if (periodStartInput.value && new Date(periodEndInput.value) < new Date(periodStartInput.value)) {
                alert('End date cannot be earlier than start date.');
                periodEndInput.value = periodStartInput.value;
            }
        });
        
        // Calculate commission button click handler
        calculateBtn.addEventListener('click', function() {
            const agentId = agentSelect.value;
            const periodStart = periodStartInput.value;
            const periodEnd = periodEndInput.value;
            
            if (!agentId) {
                alert('Please select an agent.');
                return;
            }
            
            if (!periodStart || !periodEnd) {
                alert('Please enter period start and end dates.');
                return;
            }
            
            // Show loading state
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Calculating...';
            
            // Fetch commission data from API
            fetch(`/api/commissions/calculate?agent_id=${agentId}&period_start=${periodStart}&period_end=${periodEnd}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Reset button state
                    calculateBtn.disabled = false;
                    calculateBtn.innerHTML = '<i class="fas fa-calculator mr-2"></i> Calculate Commission';
                    
                    if (data.success) {
                        // Display commission data
                        displayCommissionData(data.commissions, data.total);
                        
                        // Show summary and submit button
                        commissionSummary.classList.remove('hidden');
                        submitBtn.classList.remove('hidden');
                    } else {
                        // Show error message
                        alert(data.message || 'No commissions found for the selected criteria.');
                        commissionSummary.classList.add('hidden');
                        submitBtn.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while calculating commissions: ' + error.message);
                    calculateBtn.disabled = false;
                    calculateBtn.innerHTML = '<i class="fas fa-calculator mr-2"></i> Calculate Commission';
                    commissionSummary.classList.add('hidden');
                    submitBtn.classList.add('hidden');
                });
        });
        
        // Display commission data in the summary section
        function displayCommissionData(commissions, total) {
            // Clear previous data
            commissionData.innerHTML = '';
            
            if (commissions.length === 0) {
                commissionData.innerHTML = '<p class="text-gray-500">No commissions found for the selected period.</p>';
                totalAmountElement.textContent = '0.00';
                return;
            }
            
            // Create commission list
            const table = document.createElement('table');
            table.className = 'min-w-full divide-y divide-gray-200';
            
            // Create table header
            const thead = document.createElement('thead');
            thead.className = 'bg-gray-50';
            thead.innerHTML = `
                <tr>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Amount</th>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                </tr>
            `;
            table.appendChild(thead);
            
            // Create table body
            const tbody = document.createElement('tbody');
            tbody.className = 'bg-white divide-y divide-gray-200';
            
            commissions.forEach((commission, index) => {
                const tr = document.createElement('tr');
                
                // Add alternating row colors
                if (index % 2 === 0) {
                    tr.className = 'bg-white';
                } else {
                    tr.className = 'bg-gray-50';
                }
                
                tr.innerHTML = `
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">${commission.product_name}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">${commission.date}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">$${commission.sale_amount.toFixed(2)}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-green-600">$${commission.commission_amount.toFixed(2)}</td>
                `;
                
                tbody.appendChild(tr);
            });
            
            table.appendChild(tbody);
            commissionData.appendChild(table);
            
            // Update total amount
            totalAmountElement.textContent = total.toFixed(2);
            
            // Add hidden fields for commission IDs
            commissions.forEach(commission => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'commission_ids[]';
                input.value = commission.id;
                commissionData.appendChild(input);
            });
        }
    });
</script>
@endpush