@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 bg-white rounded-lg shadow-lg h-full">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Agent Document Management</h2>
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
                    <div class="p-4 text-center text-gray-500">
                        <p>Select an agent to view their documents</p>
                    </div>
                    @foreach($agents as $agent)
                        <div class="agent-item p-4 hover:bg-gray-100 cursor-pointer flex items-center justify-between"
                             data-agent-id="{{ $agent->id }}" 
                             data-agent-name="{{ $agent->name }}">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium">
                                        {{ substr($agent->name, 0, 1) . (strpos($agent->name, ' ') !== false ? substr($agent->name, strpos($agent->name, ' ') + 1, 1) : '') }}
                                    </span>
                                </div>
                                <span class="font-medium">{{ $agent->name }}</span>
                            </div>
                            <span class="text-sm text-gray-500">
                                <span class="document-count">0</span> docs
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- New Documents Column -->
            <div class="border rounded-lg bg-gray-50">
                <div class="p-4 border-b">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-700">New Documents</h3>
                            <p class="text-sm text-gray-500" id="new-docs-title">Select an agent to see their documents</p>
                        </div>
                        <button id="upload-document-btn" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" disabled>
                            <i class="fas fa-upload mr-2"></i> Upload PDF
                        </button>
                    </div>
                </div>
                <div id="new-documents" class="divide-y max-h-[calc(100vh-16rem)] overflow-y-auto">
                    @if($newDocuments->isEmpty())
                        <div class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
                            <p class="mt-1 text-sm text-gray-500">Select an agent to view their documents.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- History Column -->
            <div class="border rounded-lg bg-gray-50">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-700">History</h3>
                    <p class="text-sm text-gray-500" id="history-title">Select an agent to see processed documents</p>
                </div>
                <div id="history-documents" class="max-h-[calc(100vh-16rem)] overflow-y-auto">
                    @if($processedDocuments->isEmpty())
                        <div class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No processed documents</h3>
                            <p class="mt-1 text-sm text-gray-500">Select an agent to view their processed documents.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Document Viewer Modal -->
    <div id="document-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium" id="modal-title">View Document</h3>
                <button id="close-modal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-auto p-4" id="document-content">
                <!-- Document will be displayed here -->
                <iframe id="document-iframe" class="w-full h-full border-0" src=""></iframe>
            </div>
        </div>
    </div>
    
    <!-- Process Document Modal -->
    <div id="process-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium">Process Document</h3>
                <button id="close-process-modal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <form id="process-form">
                    <input type="hidden" id="document-id" name="document_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" id="cancel-process" class="mr-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700">
                            Mark as Processed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Upload Document Modal -->
    <div id="upload-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium">Upload Application Document</h3>
                <button id="close-upload-modal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <form id="upload-form" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="client_id">Client</label>
                        <select id="client_id" name="client_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <option value="">Select a client</option>
                            <!-- Will be populated via AJAX when an agent is selected -->
                        </select>
                        <p class="text-xs text-gray-500 mt-1" id="no-clients-message" style="display: none;">
                            No clients found for this agent. Please select a different agent.
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="document_type">Document Type</label>
                        <select id="document_type" name="document_type" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <option value="">Select document type</option>
                            <option value="account_application">Account Application</option>
                            <option value="loan_application">Loan Application</option>
                            <option value="id_document">ID Document</option>
                            <option value="proof_of_residence">Proof of Residence</option>
                            <option value="payslip">Payslip</option>
                            <option value="bank_statement">Bank Statement</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="file">PDF Document</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload a file</span>
                                        <input id="file" name="file" type="file" accept="application/pdf" class="sr-only" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF up to 10MB</p>
                            </div>
                        </div>
                        <div id="file-preview" class="mt-2 hidden">
                            <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded">
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"/>
                                </svg>
                                <span id="file-name" class="text-sm"></span>
                                <button id="remove-file" type="button" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="upload_notes">Notes</label>
                        <textarea id="upload_notes" name="notes" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200"></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" id="cancel-upload" class="mr-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" id="submit-upload" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700">
                            Upload
                        </button>
                    </div>
                </form>
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
                
                // Modal elements
                const documentModal = document.getElementById('document-modal');
                const documentIframe = document.getElementById('document-iframe');
                const modalTitle = document.getElementById('modal-title');
                const closeModal = document.getElementById('close-modal');
                
                // Process Modal elements
                const processModal = document.getElementById('process-modal');
                const processForm = document.getElementById('process-form');
                const documentIdInput = document.getElementById('document-id');
                const closeProcessModal = document.getElementById('close-process-modal');
                const cancelProcess = document.getElementById('cancel-process');
                
                let selectedAgentId = null;

                // Handle agent search
                agentSearch.addEventListener('input', function (e) {
                    const searchTerm = e.target.value.toLowerCase();

                    fetch(`/web-api/agents/search?term=${searchTerm}`)
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

                            selectedAgentId = this.dataset.agentId;
                            const agentName = this.querySelector('.font-medium').textContent;

                            // Update titles
                            newDocsTitle.textContent = `${agentName}'s recent uploads`;
                            historyTitle.textContent = `${agentName}'s processed documents`;

                            // Load agent's documents
                            loadAgentDocuments(selectedAgentId);
                        });
                    });
                }
                
                // Load agent documents
                function loadAgentDocuments(agentId) {
                    Promise.all([
                        fetch(`/web-api/documents/new/${agentId}`).then(r => r.text()),
                        fetch(`/web-api/documents/processed/${agentId}`).then(r => r.text())
                    ]).then(([newDocs, processedDocs]) => {
                        newDocuments.innerHTML = newDocs;
                        historyDocuments.innerHTML = processedDocs;
                        
                        // Attach document action handlers
                        attachDocumentActionHandlers();
                    });
                }
                
                // Attach handlers for document actions
                function attachDocumentActionHandlers() {
                    // View document buttons
                    document.querySelectorAll('.view-document').forEach(button => {
                        button.addEventListener('click', function() {
                            const docId = this.dataset.documentId;
                            const docPath = this.dataset.documentPath;
                            
                            // Set the iframe source
                            documentIframe.src = docPath;
                            
                            // Show the modal
                            documentModal.classList.remove('hidden');
                        });
                    });
                    
                    // Mark as processed buttons
                    document.querySelectorAll('.mark-processed').forEach(button => {
                        button.addEventListener('click', function() {
                            const docId = this.dataset.documentId;
                            documentIdInput.value = docId;
                            
                            // Show the process modal
                            processModal.classList.remove('hidden');
                        });
                    });
                }
                
                // Close document viewer modal
                closeModal.addEventListener('click', function() {
                    documentModal.classList.add('hidden');
                    documentIframe.src = '';
                });
                
                // Close process modal
                function closeProcessModalHandler() {
                    processModal.classList.add('hidden');
                    processForm.reset();
                }
                
                closeProcessModal.addEventListener('click', closeProcessModalHandler);
                cancelProcess.addEventListener('click', closeProcessModalHandler);
                
                // Handle document processing form submission
                processForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const docId = documentIdInput.value;
                    const notes = document.getElementById('notes').value;
                    
                    fetch(`/api/documents/${docId}/process`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ notes })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Close the modal
                            closeProcessModalHandler();
                            
                            // Reload the agent's documents
                            if (selectedAgentId) {
                                loadAgentDocuments(selectedAgentId);
                            }
                        } else {
                            alert('Error processing document: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while processing the document.');
                    });
                });

                // Upload document elements
                const uploadBtn = document.getElementById('upload-document-btn');
                const uploadModal = document.getElementById('upload-modal');
                const closeUploadModal = document.getElementById('close-upload-modal');
                const cancelUpload = document.getElementById('cancel-upload');
                const uploadForm = document.getElementById('upload-form');
                const fileInput = document.getElementById('file');
                const filePreview = document.getElementById('file-preview');
                const fileName = document.getElementById('file-name');
                const removeFile = document.getElementById('remove-file');
                const clientSelect = document.getElementById('client_id');
                const noClientsMessage = document.getElementById('no-clients-message');
                
                // Show upload modal
                uploadBtn.addEventListener('click', function() {
                    if (!selectedAgentId) {
                        alert('Please select an agent first');
                        return;
                    }
                    
                    // Load clients for the selected agent
                    fetchClientsForAgent(selectedAgentId);
                    
                    // Show the modal
                    uploadModal.classList.remove('hidden');
                });
                
                // Close upload modal
                function closeUploadModalHandler() {
                    uploadModal.classList.add('hidden');
                    uploadForm.reset();
                    filePreview.classList.add('hidden');
                    noClientsMessage.style.display = 'none';
                }
                
                closeUploadModal.addEventListener('click', closeUploadModalHandler);
                cancelUpload.addEventListener('click', closeUploadModalHandler);
                
                // Fetch clients for an agent
                function fetchClientsForAgent(agentId) {
                    // Clear existing options
                    while (clientSelect.options.length > 1) {
                        clientSelect.remove(1);
                    }
                    
                    fetch(`/api/agents/${agentId}/clients`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.clients && data.clients.length > 0) {
                                data.clients.forEach(client => {
                                    const option = document.createElement('option');
                                    option.value = client.id;
                                    option.textContent = client.name;
                                    clientSelect.appendChild(option);
                                });
                                noClientsMessage.style.display = 'none';
                            } else {
                                noClientsMessage.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            noClientsMessage.style.display = 'block';
                        });
                }
                
                // Handle file input change
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        
                        // Check if it's a PDF
                        if (file.type !== 'application/pdf') {
                            alert('Please upload a PDF file');
                            this.value = '';
                            return;
                        }
                        
                        // Show file preview
                        fileName.textContent = file.name;
                        filePreview.classList.remove('hidden');
                    }
                });
                
                // Remove file button
                removeFile.addEventListener('click', function() {
                    fileInput.value = '';
                    filePreview.classList.add('hidden');
                });
                
                // Handle upload form submission
                uploadForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Check if an agent is selected
                    if (!selectedAgentId) {
                        alert('Please select an agent first');
                        return;
                    }
                    
                    // Check if client is selected
                    if (!clientSelect.value) {
                        alert('Please select a client');
                        return;
                    }
                    
                    // Create form data
                    const formData = new FormData(this);
                    
                    // Show loading state
                    const submitBtn = document.getElementById('submit-upload');
                    const originalText = submitBtn.textContent;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Uploading...';
                    
                    // Send the request
                    fetch('/api/documents/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Close the modal
                            closeUploadModalHandler();
                            
                            // Reload the agent's documents
                            if (selectedAgentId) {
                                loadAgentDocuments(selectedAgentId);
                            }
                            
                            // Show success message
                            alert('Document uploaded successfully');
                        } else {
                            alert('Error uploading document: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while uploading the document.');
                    })
                    .finally(() => {
                        // Reset loading state
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
                });
                
                // Initial attachment of click handlers
                attachAgentClickHandlers();
            });
        </script>
    @endpush
@endsection
