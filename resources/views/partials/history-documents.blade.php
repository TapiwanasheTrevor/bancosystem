@if($groupedDocuments->isEmpty())
    <div class="p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No processed documents</h3>
        <p class="mt-1 text-sm text-gray-500">This agent has no processed documents.</p>
    </div>
@else
    @foreach($groupedDocuments as $date => $documents)
        <div class="p-2 bg-gray-100 sticky top-0 z-10">
            <h4 class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</h4>
        </div>
        @foreach($documents as $doc)
            <div class="p-4 hover:bg-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9zM5 2a1 1 0 00-1 1v12a1 1 0 001 1h2v-2H5V4h6.586L14 6.414V12h2V6.414a2 2 0 00-.586-1.414L13 2.586A2 2 0 0011.586 2H5z"/>
                        </svg>
                        <span class="font-medium">{{ $doc->name }}</span>
                    </div>
                    <span class="text-sm text-gray-500">{{ human_filesize($doc->size ?? 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Processed {{ isset($doc->processed_at) ? $doc->processed_at->format('g:i A') : $doc->created_at->format('g:i A') }}
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
                    </div>
                </div>
                @if($doc->notes)
                    <div class="mt-2 text-xs italic text-gray-500">"{{ $doc->notes }}"</div>
                @endif
            </div>
        @endforeach
    @endforeach
@endif
