@extends('layouts.app')

@section('content')
<script>
    // Redirect to the new agents list page
    window.location.href = "{{ route('agents.index') }}";
</script>

<div class="container mx-auto p-6">
    <div class="flex justify-center">
        <div class="text-center">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Redirecting...</h2>
            <p>If you are not redirected automatically, please <a href="{{ route('agents.index') }}" class="text-blue-600 hover:text-blue-800">click here</a>.</p>
        </div>
    </div>
</div>
@endsection
