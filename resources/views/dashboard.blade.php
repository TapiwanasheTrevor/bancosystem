@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4 bg-white rounded-lg shadow-lg min-h-screen overflow-hidden">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Dashboard Overview</h2>
        </div>

        {{-- Top Stats Row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Users Card --}}
            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-blue-100 rounded-full shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Total Users</p>
                        <h3 class="text-xl font-bold truncate">{{ $totalUsers }}</h3>
                    </div>
                </div>
            </div>

            {{-- Active Applications Card --}}
            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-green-100 rounded-full shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Active Applications</p>
                        <h3 class="text-xl font-bold truncate">{{ $activeApplications }}</h3>
                    </div>
                </div>
            </div>

            {{-- Average Credit Term Card --}}
            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-indigo-100 rounded-full shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Avg Credit Term</p>
                        <h3 class="text-xl font-bold truncate">{{ $averageCreditTerm }} months</h3>
                    </div>
                </div>
            </div>

            {{-- Total Credit Value Card --}}
            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-yellow-100 rounded-full shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Total Credit Value</p>
                        <h3 class="text-xl font-bold truncate">${{ number_format($totalCreditValue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Monthly Applications Chart --}}
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Monthly Applications</h3>
                <div class="h-64 lg:h-72">
                    <canvas id="monthlyApplicationsChart"></canvas>
                </div>
            </div>

            {{-- Product Distribution Chart --}}
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Product Distribution</h3>
                <div class="h-64 lg:h-72">
                    <canvas id="productDistributionChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent Activity and Alerts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Applications --}}
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Recent Applications</h3>
                <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                    @foreach($recentApplications as $application)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4 min-w-0">
                                <div class="p-2 bg-blue-100 rounded-full shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium truncate">Application #{{ $application->id }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ $application->form_name }}</p>
                                    <p class="text-xs text-gray-400 truncate">
                                        ${{ number_format($application->credit_value, 2) }}
                                        - {{ $application->months }} months</p>
                                </div>
                            </div>
                            <span
                                class="px-3 py-1 bg-{{ $application->status_color }}-100 text-{{ $application->status_color }}-800 rounded-full text-sm whitespace-nowrap ml-2">
                                {{ $application->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- System Alerts --}}
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">System Alerts</h3>
                <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                    @foreach($systemAlerts as $alert)
                        <div class="flex items-center space-x-4 p-4 bg-{{ $alert->type }}-50 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-{{ $alert->type }}-500 shrink-0"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-sm break-words">{{ $alert->message }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            Chart.defaults.maintainAspectRatio = false;
            Chart.defaults.responsive = true;

            // Monthly Applications Chart
            const monthlyApplicationsCtx = document.getElementById('monthlyApplicationsChart').getContext('2d');
            new Chart(monthlyApplicationsCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlyApplicationsLabels) !!},
                    datasets: [{
                        label: 'Applications',
                        data: {!! json_encode($monthlyApplicationsData) !!},
                        borderColor: 'rgb(59, 130, 246)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: Math.max(...{!! json_encode($monthlyApplicationsData) !!}) * 1.2
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });

            // Product Distribution Chart
            const productDistributionCtx = document.getElementById('productDistributionChart').getContext('2d');
            new Chart(productDistributionCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($productDistributionLabels) !!},
                    datasets: [{
                        data: {!! json_encode($productDistributionData) !!},
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(139, 92, 246)',
                            'rgb(245, 158, 11)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
