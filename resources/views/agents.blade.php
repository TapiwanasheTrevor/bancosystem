@extends('layouts.app')

@section('content')

    <div class="container mx-auto p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Create New Agent</h2>
            <a href="/agents"
               class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                Back to Agents
            </a>
        </div>

        <form class="space-y-8">
            <!-- Personal Information Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            First Name
                        </label>
                        <input type="text"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-500"
                               placeholder="Enter first name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Last Name
                        </label>
                        <input type="text"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-500"
                               placeholder="Enter last name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input type="email"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-500"
                               placeholder="agent@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number
                        </label>
                        <input type="tel"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-500"
                               placeholder="Enter phone number">
                    </div>
                </div>
            </div>

            <!-- Account Details Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Details</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Username
                        </label>
                        <input type="text"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-500"
                               placeholder="Choose a username">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <input type="password"
                               class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-500"
                               placeholder="Enter password">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Agent Type
                        </label>
                        <select
                            class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-500">
                            <option value="">Select agent type</option>
                            <option value="field">Field Agent</option>
                            <option value="office">Office Agent</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>
                        <select
                            class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:border-indigo-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending Approval</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Referral Program Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Referral Program</h3>
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-gray-700">Referral ID:</span>
                        <span class="px-4 py-2 bg-gray-100 rounded-lg text-gray-600" id="referral-uuid">
                        Will be generated upon creation
                    </span>
                        <button type="button"
                                class="px-4 py-2 text-sm bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors"
                                onclick="generateUUID()">
                            Generate New
                        </button>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Referral Link
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="text"
                                   id="referral-link"
                                   readonly
                                   class="flex-1 px-4 py-2 bg-gray-100 border rounded-lg text-gray-600"
                                   value="Link will be generated with UUID">
                            <button type="button"
                                    onclick="copyToClipboard()"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                </svg>
                                <span>Copy</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button"
                        class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Create Agent
                </button>
            </div>
        </form>
    </div>

    <script>
        function generateUUID() {
            const uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = Math.random() * 16 | 0;
                const v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });

            document.getElementById('referral-uuid').textContent = uuid;
            document.getElementById('referral-link').value = `${window.location.origin}?referral=${uuid}`;
        }

        function copyToClipboard() {
            const linkInput = document.getElementById('referral-link');
            linkInput.select();
            document.execCommand('copy');

            // Show feedback (you might want to replace this with a better UI feedback)
            alert('Referral link copied to clipboard!');
        }

        // Initialize UUID on page load
        document.addEventListener('DOMContentLoaded', generateUUID);
    </script>

@endsection
