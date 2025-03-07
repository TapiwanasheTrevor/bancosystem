@extends('layouts.app')

@section('title', 'Manage Applications')

@push('styles')
<style>
    .tab-highlight {
        animation: tab-pulse 0.3s ease-in-out;
    }
    
    @keyframes tab-pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.03); box-shadow: 0 0 8px rgba(16, 185, 129, 0.5); }
        100% { transform: scale(1); }
    }
    
    /* Style for active and inactive tabs */
    button[id].active-tab {
        @apply font-bold bg-white text-emerald-600 border-emerald-500;
    }
    
    button[id]:not(.active-tab) {
        @apply text-gray-500 border-transparent;
    }
    
    button[id]:not(.active-tab):hover {
        @apply text-gray-700 border-gray-300;
    }
    
    /* Ensure tab underline is more visible */
    button[id].active-tab:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #10B981; /* emerald-500 */
        z-index: 10;
    }
</style>
@endpush

@section('content')
    <div class="container mx-auto p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Manage Applications</h2>
            <div class="flex space-x-2">
                <a href="javascript:void(0)" id="export-data" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 focus:ring focus:ring-emerald-400 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Export Data
                </a>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div class="relative">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" id="search"
                           class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-400 outline-none"
                           placeholder="Name, ID Number, Reference...">
                </div>

                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status-filter"
                            class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-400 outline-none">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div>
                    <label for="date-from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" id="date-from" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-400 outline-none">
                </div>
                
                <div>
                    <label for="date-to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" id="date-to" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-emerald-400 outline-none">
                </div>
            </div>
            
            <div class="flex justify-end mt-4">
                <button id="apply-filters" 
                        class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 focus:ring focus:ring-emerald-400">
                    Apply Filters
                </button>
            </div>
            </div>
        </div>

        {{-- Batch Actions --}}
        <div class="my-8 flex justify-between items-center bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div id="batch-actions" class="flex space-x-2 opacity-50 pointer-events-none transition-all">
                <button id="mark-approved"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring focus:ring-green-400 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Approve Selected
                </button>
                <button id="mark-rejected"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring focus:ring-red-400 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Reject Selected
                </button>
                <button id="mark-processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring focus:ring-blue-400 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    Mark as Processing
                </button>
            </div>
            <div>
                <span id="selected-count" class="text-sm text-gray-600 font-medium">0 items selected</span>
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ activeTab: 'account_holder_loan_application' }">
            <div class="border-b border-gray-300 bg-gray-50 rounded-t-lg">
                <nav class="flex -mb-px">
                    @php
                        $tabs = [
                            'account_holder_loan_application' => [
                                'label' => 'Account Holder Loans',
                                'count' => \App\Models\Form::where('form_name', 'account_holder_loan_application')
                                    ->where('status', 'pending')
                                    ->count()
                                ],
                            'individual_account_opening' => [
                                'label' => 'Individual Accounts',
                                'count' => \App\Models\Form::where('form_name', 'individual_account_opening')
                                    ->where('status', 'pending')
                                    ->count()
                                ],
                            'pensioners_loan_account' => [
                                'label' => 'Pensioners Loans',
                                 'count' => \App\Models\Form::where('form_name', 'pensioners_loan_account')
                                    ->where('status', 'pending')
                                    ->count()
                                ],
                            'smes_business_account_opening' => [
                                'label' => 'SMEs Accounts',
                                 'count' => \App\Models\Form::where('form_name', 'smes_business_account_opening')
                                    ->where('status', 'pending')
                                    ->count()
                                ],
                            'ssb_account_opening_form' => [
                                'label' => 'SSB Accounts',
                                'count' => \App\Models\Form::where('form_name', 'ssb_account_opening_form')
                                    ->where('status', 'pending')
                                    ->count()
                                ],
                        ];
                    @endphp

                    @foreach($tabs as $id => $tab)
                        <button
                            @click="activeTab = '{{ $id }}'; loadDataTable('{{ $id }}')"
                            :class="{ 'border-emerald-500 text-emerald-600 font-bold bg-white': activeTab === '{{ $id }}',
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== '{{ $id }}' }"
                            class="w-1/5 py-4 px-2 text-center border-b-2 font-medium text-sm focus:outline-none relative transition-all duration-200"
                            id="{{ $id }}">
                            {{ $tab['label'] }}
                            @if($tab['count'] > 0)
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                {{ $tab['count'] }}
                            </span>
                            @endif
                            <span x-show="activeTab === '{{ $id }}'"
                                  class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 shadow-sm"></span>
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- Tab Content with DataTable --}}
            <div class="mt-4">
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200" id="applications-table">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 w-6">
                                <input type="checkbox"
                                       class="select-all rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ref #
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Applicant
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product / Loan
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Term
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>

                <!-- Pagination controls -->
                <div class="flex items-center justify-between mt-4">
                    <div>
                        <p class="text-sm text-gray-700" id="pagination-info">
                            Showing <span class="font-medium" id="current-page-start">1</span> to <span class="font-medium" id="current-page-end">10</span> of <span class="font-medium" id="total-items">100</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination" id="pagination-controls">
                            <a href="#" id="prev-page" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <div id="page-numbers" class="flex">
                                <!-- Page numbers will be inserted here by JavaScript -->
                            </div>
                            <a href="#" id="next-page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="status-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Update Application Status</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" id="close-status-modal">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-2">
                <form id="status-update-form">
                    <input type="hidden" id="form-id" name="form_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="status">Status</label>
                        <select id="status-select" name="status" class="w-full p-2 border rounded-lg focus:ring focus:ring-emerald-400 outline-none">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="notes">Notes</label>
                        <textarea id="status-notes" name="notes" rows="3" class="w-full p-2 border rounded-lg focus:ring focus:ring-emerald-400 outline-none"></textarea>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300" id="cancel-status-update">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Initialize DataTable
            const initializeDataTable = (tableId, formName) => {
                console.log(`Initializing DataTable for form: ${formName}`); // Debug log

                if ($.fn.dataTable.isDataTable(`#${tableId}`)) {
                    $(`#${tableId}`).DataTable().destroy();
                }

                const dataTable = $(`#${tableId}`).DataTable({
                    processing: true,
                    serverSide: true,
                    paging: true,
                    lengthMenu: [10, 25, 50, 100],
                    pageLength: 10,
                    searching: false, // We'll handle search manually
                    ordering: true,
                    info: true,
                    autoWidth: false,
                    responsive: true,
                    language: {
                        // Custom language settings for better error handling
                        emptyTable: "No data available",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)"
                    },
                    ajax: {
                        url: `/api/applications/${formName}`,
                        data: function (d) {
                            // Include DataTables standard parameters
                            // Using standard properties for server-side processing
                            d.status = $('#status-filter').val() || '';
                            d.search = $('#search').val() || '';
                            d.date_from = $('#date-from').val() || '';
                            d.date_to = $('#date-to').val() || '';
                            
                            // Keep the custom sort parameters
                            if (d.order && d.order.length > 0 && d.columns) {
                                const columnIndex = d.order[0].column;
                                if (d.columns[columnIndex] && d.columns[columnIndex].data) {
                                    d.sort_by = d.columns[columnIndex].data;
                                    d.sort_dir = d.order[0].dir;
                                } else {
                                    // Default sort if the column data is not available
                                    d.sort_by = 'created_at';
                                    d.sort_dir = 'desc';
                                }
                            }
                            
                            console.log("Request data:", d); // Debug info
                        },
                        error: function (xhr, error, thrown) {
                            console.error('DataTables Ajax Error:', error, thrown);
                            console.error('Response Text:', xhr.responseText);
                            console.error('Status:', xhr.status);
                            console.error('Status Text:', xhr.statusText);
                            
                            // Show error message on the page
                            $('#applications-table tbody').html(
                                `<tr><td colspan="9" class="text-center p-5 text-red-600">
                                    Error loading data: ${error || 'Unknown error'}<br>
                                    ${thrown || ''}<br>
                                    ${xhr.responseText ? 'Response: ' + xhr.responseText.substring(0, 100) + '...' : ''}
                                </td></tr>`
                            );
                        },
                        dataSrc: function (json) {
                            // Update pagination info
                            if (json.meta) {
                                updatePaginationInfo(json.meta.current_page, json.meta.per_page, json.meta.total);
                            } else {
                                // Use DataTables standard info
                                const start = json.start || 0;
                                const length = json.length || 10;
                                const total = json.recordsTotal || 0;
                                const currentPage = Math.floor(start / length) + 1;
                                updatePaginationInfo(currentPage, length, total);
                            }
                            
                            // Return the data array
                            return json.data || [];
                        }
                    },
                    drawCallback: function(settings) {
                        const api = this.api();
                        // Update pagination controls with proper values
                        const start = api.page.info().start;
                        const length = api.page.info().length;
                        const recordsTotal = api.page.info().recordsTotal;
                        const recordsFiltered = api.page.info().recordsFiltered;
                        const currentPage = api.page.info().page + 1; // DataTables is 0-indexed
                        const totalPages = api.page.info().pages;
                        
                        console.log("DataTable info:", {
                            start, length, recordsTotal, recordsFiltered, currentPage, totalPages
                        });
                        
                        updatePaginationControls(
                            start, 
                            length, 
                            recordsTotal !== null ? recordsTotal : 0,
                            currentPage,
                            totalPages
                        );
                    },
                    columns: [
                        {
                            data: null,
                            orderable: false,
                            render: function (data, type, row) {
                                return `<input type="checkbox" class="row-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" value="${row.id}">`;
                            }
                        },
                        {
                            data: 'uuid',
                            render: function(data) {
                                return `<span class="text-xs font-mono">${data ? data.substr(0, 8) : 'N/A'}</span>`;
                            }
                        },
                        {
                            data: 'applicant_name',
                            render: function (data, type, row) {
                                if (data && data !== 'N/A') return data;
                                
                                try {
                                    // Fallback to JSON data if needed
                                    const questData = row.questionnaire_data;
                                    const formValues = row.form_values;
                                    
                                    return questData?.applicationDetails?.name || 
                                        (formValues?.title ? (formValues.title + ' ' + formValues.surname) : 'N/A');
                                } catch (e) {
                                    return 'N/A';
                                }
                            }
                        },
                        {
                            data: 'applicant_id_number',
                            render: function(data, type, row) {
                                if (data && data !== 'N/A') return data;
                                
                                try {
                                    return row.form_values?.['id-number'] || 'N/A';
                                } catch (e) {
                                    return 'N/A';
                                }
                            }
                        },
                        {
                            data: 'product',
                            render: function (data, type, row) {
                                return data || 'N/A';
                            }
                        },
                        {
                            data: 'loan_amount',
                            render: function (data, type, row) {
                                try {
                                    if (data && data !== 'null' && data !== null) {
                                        return `$${parseFloat(data).toFixed(2)}`;
                                    }
                                    
                                    // Fallback to JSON data
                                    const creditOption = row.questionnaire_data?.selectedProduct?.selectedCreditOption;
                                    return creditOption?.final_price ? `$${creditOption.final_price}` : 'N/A';
                                } catch (e) {
                                    return 'N/A';
                                }
                            }
                        },
                        {
                            data: 'loan_term_months',
                            render: function (data, type, row) {
                                try {
                                    if (data && data !== 'null' && data !== null) {
                                        return `${data} months`;
                                    }
                                    
                                    // Fallback to JSON data
                                    const creditOption = row.questionnaire_data?.selectedProduct?.selectedCreditOption;
                                    return creditOption?.months ? `${creditOption.months} months` : 'N/A';
                                } catch (e) {
                                    return 'N/A';
                                }
                            }
                        },
                        {
                            data: 'status',
                            render: function (data, type, row) {
                                const colors = {
                                    pending: 'bg-yellow-100 text-yellow-800',
                                    approved: 'bg-green-100 text-green-800',
                                    rejected: 'bg-red-100 text-red-800',
                                    processing: 'bg-blue-100 text-blue-800',
                                    completed: 'bg-purple-100 text-purple-800',
                                    new: 'bg-gray-100 text-gray-800'
                                };
                                
                                const status = data || 'new';
                                const color = colors[status.toLowerCase()] || colors.new;
                                
                                return `<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${color}">
                                    ${status.toUpperCase()}
                                </span>`;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function (data, type, row) {
                                return `
                                <div class="flex justify-center space-x-2">
                                    <button
                                        class="p-1 text-emerald-600 hover:text-emerald-800 focus:outline-none" 
                                        title="View Details"
                                        onclick="handleAction('view', ${row.id})"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button
                                        class="p-1 text-blue-600 hover:text-blue-800 focus:outline-none" 
                                        title="Update Status"
                                        onclick="openStatusModal('${row.id}', '${row.status || 'pending'}')"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                    <a
                                        href="/download/${row.form_name || formName}/${row.id}"
                                        class="p-1 text-purple-600 hover:text-purple-800 focus:outline-none" 
                                        title="Download"
                                        target="_blank"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                    <button
                                        class="p-1 text-red-600 hover:text-red-800 focus:outline-none" 
                                        title="Delete"
                                        onclick="confirmDelete(${row.id})"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>`;
                            }
                        }
                    ]
                });

                return dataTable;
            };

            // Load DataTable with the specified form name
            const loadDataTable = (formName) => {
                console.log(`Loading DataTable for form: ${formName}`); // Debug log
                return initializeDataTable('applications-table', formName);
            };
            
            // Update pagination info text
            function updatePaginationInfo(currentPage, perPage, total) {
                console.log("Pagination info:", { currentPage, perPage, total });
                
                if (total === 0) {
                    // Handle case with no records
                    $('#pagination-info').html('Showing 0 to 0 of 0 entries');
                    return;
                }
                
                const start = (currentPage - 1) * perPage + 1;
                const end = Math.min(currentPage * perPage, total);
                
                $('#current-page-start').text(start);
                $('#current-page-end').text(end);
                $('#total-items').text(total);
                
                // If using filtered data, show the filtered info
                if (window.filteredTotal !== undefined && window.filteredTotal !== total) {
                    $('#pagination-info').html(
                        `Showing ${start} to ${end} of ${total} entries (filtered from ${window.filteredTotal} total entries)`
                    );
                }
            }
            
            // Update pagination controls
            function updatePaginationControls(start, length, totalRecords, currentPage, totalPages) {
                const paginationElement = $('#page-numbers');
                paginationElement.empty();
                
                // Store the total unfiltered count for display
                window.filteredTotal = totalRecords;
                
                // If there are no records, hide pagination controls
                if (totalRecords === 0 || totalPages === 0) {
                    $('#pagination-controls').hide();
                    return;
                } else {
                    $('#pagination-controls').show();
                }
                
                // Determine range of page numbers to show
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }
                
                // First page
                if (startPage > 1) {
                    paginationElement.append(`
                        <a href="#" data-page="1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            1
                        </a>
                    `);
                    
                    if (startPage > 2) {
                        paginationElement.append(`
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>
                        `);
                    }
                }
                
                // Page numbers
                for (let i = startPage; i <= endPage; i++) {
                    const isActive = i === currentPage;
                    paginationElement.append(`
                        <a href="#" data-page="${i}" class="relative inline-flex items-center px-4 py-2 border ${isActive ? 'border-emerald-500 bg-emerald-50 text-emerald-600' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'}  text-sm font-medium">
                            ${i}
                        </a>
                    `);
                }
                
                // Last page
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        paginationElement.append(`
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>
                        `);
                    }
                    
                    paginationElement.append(`
                        <a href="#" data-page="${totalPages}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            ${totalPages}
                        </a>
                    `);
                }
                
                // Enable/disable prev/next buttons
                $('#prev-page').toggleClass('opacity-50 cursor-not-allowed', currentPage === 1);
                $('#next-page').toggleClass('opacity-50 cursor-not-allowed', currentPage === totalPages);
                
                // Set up page click handlers
                $('#page-numbers a').on('click', function(e) {
                    e.preventDefault();
                    const page = parseInt($(this).data('page'));
                    goToPage(page);
                });
                
                // Set up prev/next click handlers
                $('#prev-page').off('click').on('click', function(e) {
                    e.preventDefault();
                    if (currentPage > 1) {
                        goToPage(currentPage - 1);
                    }
                });
                
                $('#next-page').off('click').on('click', function(e) {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        goToPage(currentPage + 1);
                    }
                });
            }
            
            // Go to specified page
            function goToPage(page) {
                const table = $('#applications-table').DataTable();
                table.page(page - 1).draw('page');
            }
            
            // Status Modal Functions
            function openStatusModal(formId, currentStatus) {
                $('#form-id').val(formId);
                $('#status-select').val(currentStatus);
                $('#status-notes').val('');
                $('#status-modal').removeClass('hidden');
            }
            
            function closeStatusModal() {
                $('#status-modal').addClass('hidden');
            }
            
            // Handle bulk selection changes
            function updateBatchActions() {
                const selectedCount = $('.row-checkbox:checked').length;
                $('#selected-count').text(`${selectedCount} items selected`);
                
                if (selectedCount > 0) {
                    $('#batch-actions').removeClass('opacity-50 pointer-events-none');
                } else {
                    $('#batch-actions').addClass('opacity-50 pointer-events-none');
                }
            }
            
            // Confirm delete action
            function confirmDelete(formId) {
                if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
                    // Call delete API
                    $.ajax({
                        url: `/api/forms/${formId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            // Reload table
                            $('#applications-table').DataTable().ajax.reload();
                        },
                        error: function(error) {
                            console.error('Error deleting form:', error);
                            alert('An error occurred while deleting the form.');
                        }
                    });
                }
            }
            
            // Handle view action - redirect to detail page
            function handleAction(action, formId) {
                if (action === 'view') {
                    window.location.href = `/applications/${formId}`;
                }
            }

            $(document).ready(function () {
                console.log("Document ready, initializing DataTable");
                
                // Initialize with the default form
                let currentFormName = 'account_holder_loan_application';
                const dataTable = loadDataTable(currentFormName);
                
                // Set the initial active tab
                $(`button[id="${currentFormName}"]`).addClass('active-tab');
                
                // Store reference to DataTable instance for debugging
                window.activeDataTable = dataTable;
                
                // Track the currently active tab/form type
                let activeFormType = currentFormName;
                
                // Handle export button click
                $('#export-data').on('click', function() {
                    // Use the tracked active form type
                    const activeTab = activeFormType;
                    
                    // Get filter values
                    const status = $('#status-filter').val() || '';
                    const search = $('#search').val() || '';
                    const dateFrom = $('#date-from').val() || '';
                    const dateTo = $('#date-to').val() || '';
                    
                    // Build export URL with query parameters
                    let exportUrl = `/applications/export?type=${activeTab}`;
                    
                    if (status) exportUrl += `&status=${status}`;
                    if (search) exportUrl += `&search=${search}`;
                    if (dateFrom) exportUrl += `&date_from=${dateFrom}`;
                    if (dateTo) exportUrl += `&date_to=${dateTo}`;
                    
                    console.log('Exporting data for form type:', activeTab);
                    
                    // Open export URL in new tab
                    window.open(exportUrl, '_blank');
                });

                // Handle tab clicks
                $('[x-data]').on('click', 'button[id]', function () {
                    const formName = $(this).attr('id');
                    console.log(`Tab clicked. Current form: ${currentFormName}, New form: ${formName}`); // Debug log
                    
                    // Update the active class on tabs
                    $('button[id]').removeClass('active-tab');
                    $(this).addClass('active-tab');
                    
                    // Visually highlight the clicked tab with a brief animation
                    $(this).addClass('tab-highlight');
                    setTimeout(() => {
                        $(this).removeClass('tab-highlight');
                    }, 300);
                    
                    if (currentFormName !== formName) {
                        currentFormName = formName;
                        // Update the tracked form type for exports
                        activeFormType = formName;
                        console.log('Updated active form type to:', activeFormType);
                        loadDataTable(currentFormName);
                    }
                });

                // Apply filters button
                $('#apply-filters').click(function () {
                    $('#applications-table').DataTable().ajax.reload();
                });
                
                // Handle status modal actions
                $('#close-status-modal, #cancel-status-update').on('click', closeStatusModal);
                
                // Handle status update form submission
                $('#status-update-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    const formId = $('#form-id').val();
                    const status = $('#status-select').val();
                    const notes = $('#status-notes').val();
                    
                    $.ajax({
                        url: `/api/forms/${formId}/status`,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            status: status,
                            notes: notes
                        },
                        success: function() {
                            closeStatusModal();
                            $('#applications-table').DataTable().ajax.reload();
                        },
                        error: function(error) {
                            console.error('Error updating status:', error);
                            alert('An error occurred while updating the status.');
                        }
                    });
                });
                
                // Handle select all checkbox
                $('.select-all').on('change', function() {
                    const isChecked = $(this).prop('checked');
                    $('.row-checkbox').prop('checked', isChecked);
                    updateBatchActions();
                });
                
                // Handle row checkboxes
                $(document).on('change', '.row-checkbox', function() {
                    updateBatchActions();
                });
                
                // Batch action handlers
                $('#mark-approved').on('click', function() {
                    updateBatchStatus('approved');
                });
                
                $('#mark-rejected').on('click', function() {
                    updateBatchStatus('rejected');
                });
                
                $('#mark-processing').on('click', function() {
                    updateBatchStatus('processing');
                });
                
                // Batch status update function
                function updateBatchStatus(status) {
                    const selectedIds = $('.row-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();
                    
                    if (selectedIds.length > 0) {
                        if (confirm(`Are you sure you want to mark ${selectedIds.length} applications as ${status}?`)) {
                            $.ajax({
                                url: '/api/forms/batch-status',
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: {
                                    ids: selectedIds,
                                    status: status
                                },
                                success: function() {
                                    $('#applications-table').DataTable().ajax.reload();
                                    $('.select-all').prop('checked', false);
                                    updateBatchActions();
                                },
                                error: function(error) {
                                    console.error('Error updating batch status:', error);
                                    alert('An error occurred while updating the status.');
                                }
                            });
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection