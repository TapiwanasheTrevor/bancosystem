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
                            'account_holder_loan_application' => ['label' => 'Account Holder Form', 'count' => 12],
                            'individual_account_opening' => ['label' => 'Individual Account Form', 'count' => 5],
                            'pensioners_loan_account' => ['label' => 'Pensioners Form', 'count' => 3],
                            'smes_business_account_opening' => ['label' => 'SMEs Form', 'count' => 8],
                            'ssb_account_opening_form' => ['label' => 'SSB Account Form', 'count' => 2],
                        ];
                    @endphp

                    @foreach($tabs as $id => $tab)
                        <button
                            @click="activeTab = '{{ $id }}'; loadDataTable('{{ $id }}')"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === '{{ $id }}',
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== '{{ $id }}' }"
                            class="w-1/5 py-4 px-1 text-center border-b-2 font-medium text-sm focus:outline-none relative">
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
                            <th class="p-2 w-12">
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
            const initializeDataTable = (tableId, formName) => {
                if ($.fn.dataTable.isDataTable(`#${tableId}`)) {
                    $(`#${tableId}`).DataTable().destroy();
                }

                $(`#${tableId}`).DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: `/api/applications/${formName}`,
                        data: function (d) {
                            d.status = $('#status-filter').val();
                            d.search = $('#search').val();
                        }
                    },
                    columns: [
                        {
                            data: null,
                            orderable: false,
                            render: function (data, type, row) {
                                return `<input type="checkbox" class="row-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="${row.id}">`;
                            }
                        },
                        {data: 'name'},
                        {data: 'agent'},
                        {data: 'product'},
                        {data: 'duration'},
                        {data: 'installment'},
                        {
                            data: 'status',
                            render: function (data, type, row) {
                                const colors = {
                                    pending: 'bg-yellow-100 text-yellow-800',
                                    approved: 'bg-green-100 text-green-800',
                                    rejected: 'bg-red-100 text-red-800'
                                };
                                return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colors[data.toLowerCase()]}">
                            ${data}
                        </span>`;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function (data, type, row) {
                                return `
                            <div class="flex justify-center space-x-2">
                                <button class="text-gray-400 hover:text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </button>
                            </div>
                        `;
                            }
                        }
                    ]
                });
            };

            const loadDataTable = (formName) => {
                initializeDataTable('applications-table', formName);
            };

            $(document).ready(function () {
                let currentFormName = 'account_holder_loan_application';
                loadDataTable(currentFormName);

                $('[x-data]').on('click', 'button', function () {
                    const formName = $(this).attr('id');
                    if (currentFormName !== formName) {
                        currentFormName = formName;
                        loadDataTable(currentFormName);
                    }
                });

                $('#status-filter').change(function () {
                    $('#applications-table').DataTable().ajax.reload();
                });

                let searchTimeout;
                $('#search').on('keyup', function () {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        $('#applications-table').DataTable().ajax.reload();
                    }, 500);
                });

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
        </script>
    @endpush
@endsection
