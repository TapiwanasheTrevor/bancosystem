@if(count($groupedDocuments) > 0)
    @foreach($groupedDocuments as $date => $documents)
        <div class="p-4 bg-gray-100">
            <h4 class="text-sm font-medium text-gray-600 mb-2">
                {{ \Carbon\Carbon::parse($date)->isToday() ? 'Today' : \Carbon\Carbon::parse($date)->format('F j, Y') }}
            </h4>
            <div class="space-y-3">
                @foreach($documents as $doc)
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="font-medium">{{ $doc->name }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $doc->processed_at->format('g:i A') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500">
                                @if($doc->user)
                                    Client: {{ $doc->user->name }}
                                @endif
                            </p>
                            <button 
                                class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 view-document" 
                                data-document-id="{{ $doc->id }}"
                                data-document-path="{{ asset($doc->path) }}"
                            >
                                View
                            </button>
                        </div>
                        @if($doc->notes)
                            <div class="mt-2 text-xs italic text-gray-500">"{{ $doc->notes }}"</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div class="p-4 text-center text-gray-500">
        No processed documents found
    </div>
@endif