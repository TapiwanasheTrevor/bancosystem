@if(count($documents) > 0)
    @foreach($documents as $doc)
        <div class="p-4 hover:bg-gray-100">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"/>
                    </svg>
                    <span class="font-medium">{{ $doc->name }}</span>
                </div>
                <span class="text-sm text-gray-500">{{ human_filesize($doc->size) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">Uploaded {{ $doc->created_at->diffForHumans() }}</div>
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
@else
    <div class="p-4 text-center text-gray-500">
        No documents found
    </div>
@endif