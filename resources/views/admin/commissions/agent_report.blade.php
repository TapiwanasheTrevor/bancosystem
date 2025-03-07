@extends('layouts.app')

@section('title', 'Agent Commission Report')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Agent Commission Report</h1>
        <div class="flex space-x-2">
            @if(isset($agent))
                <button onclick="window.print()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    <i class="fas fa-print mr-2"></i> Print Report
                </button>
            @endif
            <a href="{{ route('commissions.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to Commissions
            </a>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('commissions.agent-report') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-2">
                    <label for="agent_id" class="block text-sm font-medium text-gray-700 mb-1">Select Agent</label>
                    <select id="agent_id" name="agent_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Agent</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ (isset($agent) && $agent->id == $a->id) ? 'selected' : '' }}>
                                {{ $a->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i> Generate Report
                </button>
            </div>
        </form>
    </div>

    @if(isset($agent))
        <!-- Agent Information -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 bg-indigo-700 text-white">
                <h2 class="text-lg font-semibold">Agent Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Agent Name</h3>
                        <p class="text-lg font-semibold">{{ $agent->name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Email</h3>
                        <p class="text-lg font-semibold">{{ $agent->email }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Report Period</h3>
                        <p class="text-lg font-semibold">
                            {{ isset($startDate) ? \Carbon\Carbon::parse($startDate)->format('M d, Y') : 'All Time' }} 
                            - 
                            {{ isset($endDate) ? \Carbon\Carbon::parse($endDate)->format('M d, Y') : 'Present' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Total Commission</h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-2xl font-bold text-indigo-600">${{ number_format($totalCommission, 2) }}</p>
                    <p class="text-sm text-gray-500">{{ $commissions->count() }} Transactions</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Paid Commission</h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-2xl font-bold text-green-600">${{ number_format($paidCommission, 2) }}</p>
                    <p class="text-sm text-gray-500">{{ $commissions->where('status', 'paid')->count() }} Transactions</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Approved (Unpaid)</h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-2xl font-bold text-blue-600">${{ number_format($approvedCommission, 2) }}</p>
                    <p class="text-sm text-gray-500">{{ $commissions->where('status', 'approved')->count() }} Transactions</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Pending</h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-2xl font-bold text-yellow-600">${{ number_format($pendingCommission, 2) }}</p>
                    <p class="text-sm text-gray-500">{{ $commissions->where('status', 'pending')->count() }} Transactions</p>
                </div>
            </div>
        </div>

        <!-- Commission By Product -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 bg-indigo-700 text-white">
                <h2 class="text-lg font-semibold">Commission By Product</h2>
            </div>
            <div class="p-6">
                @php
                    $productCommissions = [];
                    foreach ($commissions as $commission) {
                        $productId = $commission->product_id;
                        if (!isset($productCommissions[$productId])) {
                            $productCommissions[$productId] = [
                                'name' => $commission->product->name,
                                'amount' => 0,
                                'count' => 0
                            ];
                        }
                        $productCommissions[$productId]['amount'] += $commission->commission_amount;
                        $productCommissions[$productId]['count']++;
                    }
                    
                    // Sort by amount (highest first)
                    uasort($productCommissions, function($a, $b) {
                        return $b['amount'] <=> $a['amount'];
                    });
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($productCommissions as $product)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $product['name'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $product['count'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-green-600">${{ number_format($product['amount'], 2) }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                No commission data available
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-4">Distribution by Product</h3>
                        <div class="space-y-4">
                            @foreach($productCommissions as $productId => $product)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-gray-700">{{ $product['name'] }}</span>
                                        <span class="text-gray-500">${{ number_format($product['amount'], 2) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $totalCommission > 0 ? ($product['amount'] / $totalCommission * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Transactions -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 bg-indigo-700 text-white">
                <h2 class="text-lg font-semibold">Commission Transactions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Ref</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($commissions as $commission)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $commission->product->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($commission->sale_date)->format('M d, Y') }}
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
                                    {{ $commission->payment_reference ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No commission transactions found for this agent in the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-gray-500 mb-4">
                <i class="fas fa-user-tie text-5xl"></i>
            </div>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Select an Agent to Generate Report</h2>
            <p class="text-gray-500">Choose an agent from the dropdown above and click 'Generate Report' to view commission data.</p>
        </div>
    @endif
</div>

<style type="text/css" media="print">
    @page {
        size: A4;
        margin: 1cm;
    }
    body {
        background: white;
        font-size: 12pt;
    }
    .container {
        width: 100%;
        max-width: 100%;
        padding: 0 !important;
    }
    .no-print, form, button, a[href]:not([href^='#']) {
        display: none !important;
    }
    .bg-indigo-700 {
        background-color: #4338ca !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .bg-gray-100 {
        background-color: #f3f4f6 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid #e5e7eb;
    }
    thead {
        background-color: #f9fafb !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
</style>
@endsection