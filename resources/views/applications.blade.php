@extends('layouts.app')

@section('title', 'Manage Applications')

@section('content')
    <div class="container mx-auto p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Manage Applications</h2>
        </div>

        {{-- Filters Section --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text" id="search"
                           class="w-64 px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-400 outline-none"
                           placeholder="Search applications...">
                </div>

                <select id="status-filter"
                        class="w-40 px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-400 outline-none">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>

                <button class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
            </div>

            <button id="mark-read"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring focus:ring-indigo-400">
                Mark Selected as Read
            </button>
        </div>

        {{-- Tabs --}}
        <div x-data="{ activeTab: 'account_holder_loan_application' }">
            <div class="border-b border-gray-300">
                <nav class="flex -mb-px">
                    @php
                        $tabs = [
                            'account_holder_loan_application' => [
                                'label' => 'Account Holder Form',
                                'count' => \App\Models\Form::where('form_name', 'account_holder_loan_application')
                                    ->whereNull('status')
                                    ->count()
                                ],
                            'individual_account_opening_form' => [
                                'label' => 'Individual Account Form',
                                'count' => \App\Models\Form::where('form_name', 'individual_account_opening_form')
                                    ->whereNull('status')
                                    ->count()
                                ],
                            'pensioners_loan_application' => [
                                'label' => 'Pensioners Form',
                                 'count' => \App\Models\Form::where('form_name', 'pensioners_loan_application')
                                    ->whereNull('status')
                                    ->count()
                                ],
                            'smes_business_account_application' => [
                                'label' => 'SMEs Form',
                                 'count' => \App\Models\Form::where('form_name', 'smes_business_account_application')
                                    ->whereNull('status')
                                    ->count()
                                ],
                            'ssb_account_application_form' => [
                                'label' => 'SSB Account Form',
                                'count' => \App\Models\Form::where('form_name', 'ssb_account_application_form')
                                    ->whereNull('status')
                                    ->count()
                                ],
                        ];
                    @endphp

                    @foreach($tabs as $id => $tab)
                        <button
                            @click="activeTab = '{{ $id }}'; loadDataTable('{{ $id }}')"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === '{{ $id }}',
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== '{{ $id }}' }"
                            class="w-1/5 py-4 px-2 text-left border-b-2 font-medium text-sm focus:outline-none relative"
                            id="{{ $id }}">
                            {{ $tab['label'] }}
                            @if($tab['count'] > 0)
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                {{ $tab['count'] }}
                            </span>
                            @endif
                            <span x-show="activeTab === '{{ $id }}'"
                                  class="absolute bottom-0 left-0 w-full h-0.5 bg-indigo-500"></span>
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
                                       class="select-all rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Agent
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Duration
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Installment
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

                $(`#${tableId}`).DataTable({
                    processing: true,
                    serverSide: true,
                    paging: false, // Disable pagination
                    ajax: {
                        url: `/api/applications/${formName}`,
                        data: function (d) {
                            d.status = $('#status-filter').val();
                            d.search = $('#search').val();
                        },
                        error: function (xhr, error, thrown) {
                            console.error('DataTables Ajax Error:', error, thrown);
                        },
                        dataSrc: function (json) {
                            console.log('Server Response:', json);
                            if (Array.isArray(json)) {
                                return json;
                            }
                            return json.data || [];
                        }
                    },
                    debug: true,
                    columns: [
                        {
                            data: null,
                            orderable: false,
                            render: function (data, type, row) {
                                return `<input type="checkbox" class="row-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="${row.id}">`;
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                const name = row.questionnaire_data?.applicationDetails?.name ||
                                    row.form_values?.['title'] + '&nbsp;' + row.form_values?.['surname'] ||
                                    'N/A';
                                return name;
                            }
                        },
                        {
                            data: 'agent_id',
                            render: function (data, type, row) {
                                return data || 'Nil';
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                return row.questionnaire_data?.selectedProduct?.product?.name ||
                                    row.form_values?.['product-details']?.['selected-product'] ||
                                    'N/A';
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                const months = row.questionnaire_data?.selectedProduct?.selectedCreditOption?.months ||
                                    row.form_values?.['product-details']?.['credit-option']?.months;
                                return months ? `${months} months` : 'N/A';
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                const finalPrice = row.questionnaire_data?.selectedProduct?.selectedCreditOption?.final_price ||
                                    row.form_values?.['product-details']?.['credit-option']?.final_price;
                                return finalPrice ? `$${finalPrice}` : 'N/A';
                            }
                        },
                        {
                            data: 'status',
                            render: function (data, type, row) {
                                const colors = {
                                    pending: 'bg-yellow-100 text-yellow-800',
                                    approved: 'bg-green-100 text-green-800',
                                    rejected: 'bg-red-100 text-red-800',
                                    new: 'bg-red-100 text-red-800'
                                };
                                if (data === null) {
                                    return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colors['new']}">
                                    NEW
                                </span>`;
                                } else {
                                    return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colors[data.toLowerCase()]}">
                                    ${data}
                                </span>`;
                                }
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function (data, type, row) {
                                return `
        <div class="flex justify-center space-x-2">
        <button
            class="text-gray-400 hover:text-gray-500 focus:outline-none"
            onclick="toggleDropdown(event, 'dropdown-${row.id}')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div id="dropdown-${row.id}"
             class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
             style="top: 2rem;"
        >
            <div class="py-1" role="menu" aria-orientation="vertical">
                <button
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center space-x-2"
                    role="menuitem"
                    onclick="handleAction('update', ${row.id})"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Update Status</span>
                </button>

                <button
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center space-x-2"
                    role="menuitem"
                    onclick="handleAction('download', '/download/${row.form_name}/${row.id}')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download</span>
                </button>

                <button
                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:text-red-700 flex items-center space-x-2"
                    role="menuitem"
                    onclick="handleAction('delete', ${row.id})"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </div>
                    `;
                            }
                        }
                    ]
                });
            };

            // Load DataTable with the specified form name
            const loadDataTable = (formName) => {
                console.log(`Loading DataTable for form: ${formName}`); // Debug log
                initializeDataTable('applications-table', formName);
            };

            $(document).ready(function () {
                // Initialize with the default form
                let currentFormName = 'account_holder_loan_application';
                loadDataTable(currentFormName);

                // Handle tab clicks
                $('[x-data]').on('click', 'button', function () {
                    const formName = $(this).attr('id');
                    console.log(`Tab clicked. Current form: ${currentFormName}, New form: ${formName}`); // Debug log
                    if (currentFormName !== formName) {
                        currentFormName = formName;
                        loadDataTable(currentFormName);
                    }
                });

                // Handle status filter change
                $('#status-filter').change(function () {
                    $('#applications-table').DataTable().ajax.reload();
                });

                // Handle search input
                let searchTimeout;
                $('#search').on('keyup', function () {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        $('#applications-table').DataTable().ajax.reload();
                    }, 500);
                });

                // Handle mark as read button click
                $('#mark-read').click(function () {
                    const selectedIds = $('.row-checkbox:checked').map(function () {
                        return $(this).val();
                    }).get();

                    if (selectedIds.length > 0) {
                        $.ajax({
                            url: '/api/forms/mark-read',
                            method: 'POST',
                            data: {ids: selectedIds},
                            success: function () {
                                $('#applications-table').DataTable().ajax.reload();
                            }
                        });
                    }
                });
            });

            // Handle dropdown toggle
            let activeDropdown = null;

            function toggleDropdown(event, dropdownId) {
                event.stopPropagation();

                if (activeDropdown && activeDropdown !== dropdownId) {
                    document.getElementById(activeDropdown).classList.add('hidden');
                }

                const dropdown = document.getElementById(dropdownId);
                const isHidden = dropdown.classList.contains('hidden');

                dropdown.classList.toggle('hidden');
                activeDropdown = isHidden ? dropdownId : null;
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function (event) {
                if (activeDropdown) {
                    const dropdown = document.getElementById(activeDropdown);
                    if (!dropdown.contains(event.target)) {
                        dropdown.classList.add('hidden');
                        activeDropdown = null;
                    }
                }
            });

            // Handle action buttons in the dropdown
            function handleAction(action, rowId) {
                switch (action) {
                    case 'update':
                        console.log('Update status for row:', rowId);
                        break;
                    case 'download':
                        console.log('Download for row:', rowId);
                        break;
                    case 'delete':
                        console.log('Delete row:', rowId);
                        break;
                }

                if (activeDropdown) {
                    document.getElementById(activeDropdown).classList.add('hidden');
                    activeDropdown = null;
                }
            }
        </script>
    @endpush
@endsection
