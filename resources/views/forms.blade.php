@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 bg-white rounded-lg shadow-lg h-full">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Manage Applications</h2>
        </div>

        <div class="grid grid-cols-3 gap-6" style="height: 90%">
            <!-- Agents Column -->
            <div class="border rounded-lg bg-gray-50">
                <div class="p-4 border-b">
                    <div class="flex items-center space-x-2">
                        <div class="relative flex-1">
                            <input type="text"
                                   id="agent-search"
                                   class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-500"
                                   placeholder="Search agents...">
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="agents-list" class="divide-y max-h-[calc(100vh-16rem)] overflow-y-auto">
                    @foreach($agents as $agent)
                        <div class="agent-item p-4 hover:bg-gray-100 cursor-pointer flex items-center justify-between"
                             data-agent-id="{{ $agent->id }}">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium">{{ $agent->initials }}</span>
                                </div>
                                <span class="font-medium">{{ $agent->name }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $agent->documents_count }} docs</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- New Documents Column -->
            <div class="border rounded-lg bg-gray-50">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-700">New Documents</h3>
                    <p class="text-sm text-gray-500" id="new-docs-title">Recently uploaded forms</p>
                </div>
                <div id="new-documents" class="divide-y max-h-[calc(100vh-16rem)] overflow-y-auto">
                    @foreach($newDocuments as $doc)
                        <div class="p-4 hover:bg-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"/>
                                    </svg>
                                    <span class="font-medium">{{ $doc->name }}</span>
                                </div>
                                <span class="text-sm text-gray-500">{{ $doc->size }}</span>
                            </div>
                            <div class="text-sm text-gray-500">Uploaded {{ $doc->created_at->diffForHumans() }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- History Column -->
            <div class="border rounded-lg bg-gray-50">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-700">History</h3>
                    <p class="text-sm text-gray-500" id="history-title">Previously processed documents</p>
                </div>
                <div id="history-documents" class="max-h-[calc(100vh-16rem)] overflow-y-auto">
                    @foreach($processedDocuments->groupBy(function($doc) {
                        return $doc->processed_at->format('Y-m-d');
                    }) as $date => $documents)
                        <div class="p-4 bg-gray-100">
                            <h4 class="text-sm font-medium text-gray-600 mb-2">
                                {{ \Carbon\Carbon::parse($date)->isToday() ? 'Today' : \Carbon\Carbon::parse($date)->format('F j, Y') }}
                            </h4>
                            <div class="space-y-3">
                                @foreach($documents as $doc)
                                    <div class="bg-white p-3 rounded-lg shadow-sm">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-green-500" fill="currentColor"
                                                     viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                                </svg>
                                                <span class="font-medium">{{ $doc->name }}</span>
                                            </div>
                                            <span
                                                class="text-sm text-gray-500">{{ $doc->processed_at->format('g:i A') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-500">Processed by {{ $doc->processed_by }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const agentSearch = document.getElementById('agent-search');
                const agentsList = document.getElementById('agents-list');
                const newDocuments = document.getElementById('new-documents');
                const historyDocuments = document.getElementById('history-documents');
                const newDocsTitle = document.getElementById('new-docs-title');
                const historyTitle = document.getElementById('history-title');

                // Handle agent search
                agentSearch.addEventListener('input', function (e) {
                    const searchTerm = e.target.value.toLowerCase();

                    fetch(`/api/agents/search?term=${searchTerm}`)
                        .then(response => response.text())
                        .then(html => {
                            agentsList.innerHTML = html;
                            // Reattach click handlers to new elements
                            attachAgentClickHandlers();
                        });
                });

                // Handle agent selection
                function attachAgentClickHandlers() {
                    document.querySelectorAll('.agent-item').forEach(item => {
                        item.addEventListener('click', function () {
                            // Remove selected class from all items
                            document.querySelectorAll('.agent-item').forEach(i =>
                                i.classList.remove('bg-gray-100'));

                            // Add selected class to clicked item
                            this.classList.add('bg-gray-100');

                            const agentId = this.dataset.agentId;
                            const agentName = this.querySelector('.font-medium').textContent;

                            // Update titles
                            newDocsTitle.textContent = `${agentName}'s recent uploads`;
                            historyTitle.textContent = `${agentName}'s processed documents`;

                            // Load agent's documents
                            Promise.all([
                                fetch(`/api/documents/new/${agentId}`).then(r => r.text()),
                                fetch(`/api/documents/processed/${agentId}`).then(r => r.text())
                            ]).then(([newDocs, processedDocs]) => {
                                newDocuments.innerHTML = newDocs;
                                historyDocuments.innerHTML = processedDocs;
                            });
                        });
                    });
                }

                // Initial attachment of click handlers
                attachAgentClickHandlers();
            });
        </script>
    @endpush
@endsection
