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

@if(count($agents) === 0)
    <div class="p-4 text-center text-gray-500">
        No agents found matching your search
    </div>
@endif