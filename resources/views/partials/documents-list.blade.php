@if($documents->isEmpty())
    <div class="p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
        <p class="mt-1 text-sm text-gray-500">This agent has no uploaded documents.</p>
    </div>
@else
    @foreach($documents as $doc)
        <div class="p-4 hover:bg-gray-100">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"/>
                    </svg>
                    <span class="font-medium">{{ $doc->name }}</span>
                </div>
                <span class="text-sm text-gray-500">{{ human_filesize($doc->size ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Uploaded {{ $doc->created_at->diffForHumans() }}
                    @if($doc->user)
                        for {{ $doc->user->name }}
                    @endif
                </div>
                <div class="flex space-x-2">
                    <button 
                        class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 view-document" 
                        data-document-id="{{ $doc->id }}"
                        data-document-path="{{ asset($doc->path) }}"
                    >
                        View
                    </button>
                    <button 
                        class="text-xs px-2 py-1 bg-green-50 text-green-600 rounded hover:bg-green-100 mark-processed" 
                        data-document-id="{{ $doc->id }}"
                    >
                        Mark Processed
                    </button>
                </div>
            </div>
            @if($doc->notes)
                <div class="mt-2 text-xs italic text-gray-500">"{{ $doc->notes }}"</div>
            @endif
        </div>
    @endforeach
@endif
