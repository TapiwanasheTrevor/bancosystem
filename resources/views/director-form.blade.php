<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Director Details Form</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Include Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="text-center mb-8">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16 mx-auto mb-4">
                    <h1 class="text-2xl font-semibold text-gray-800">Director Information Form</h1>
                    <p class="text-gray-600" id="business-name">Loading business details...</p>
                    <div class="mt-2 text-sm text-gray-500" id="director-position-info">Loading director position
                        information...
                    </div>
                </div>

                <div id="loading-container" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
                </div>

                <div id="error-container" class="hidden bg-red-50 p-4 rounded-md text-red-700 mb-6">
                    <p id="error-message"></p>
                </div>

                <div id="form-container" class="hidden">
                    <!-- Form will be dynamically loaded here -->
                </div>

                <div id="success-container" class="hidden text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-emerald-600" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Information Submitted Successfully</h2>
                    <p class="text-gray-600 mb-6" id="success-message"></p>
                    <div id="final-director-actions" class="hidden">
                        <button id="submit-all-btn"
                                class="px-6 py-3 bg-emerald-600 text-white rounded-lg shadow hover:bg-emerald-700">
                            Submit Complete Application
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const token = '{{ $token }}';
        const formContainer = document.getElementById('form-container');
        const loadingContainer = document.getElementById('loading-container');
        const errorContainer = document.getElementById('error-container');
        const errorMessage = document.getElementById('error-message');
        const businessName = document.getElementById('business-name');
        const directorPositionInfo = document.getElementById('director-position-info');
        const successContainer = document.getElementById('success-container');
        const successMessage = document.getElementById('success-message');
        const finalDirectorActions = document.getElementById('final-director-actions');
        const submitAllBtn = document.getElementById('submit-all-btn');

        let directorFormData = null;

        // Fetch the director form data
        fetch(`/api/director-links/${token}`)
            .then(response => response.json())
            .then(data => {
                loadingContainer.classList.add('hidden');

                if (!data.success) {
                    showError(data.message);
                    return;
                }

                directorFormData = data;
                businessName.textContent = `Business Name: ${data.business_name}`;
                directorPositionInfo.textContent = `Director ${data.director_position} of ${data.total_directors}`;

                // Show the form container
                formContainer.classList.remove('hidden');

                // Generate the form fields for this director
                generateDirectorForm(data);
            })
            .catch(error => {
                loadingContainer.classList.add('hidden');
                showError('Failed to load form data. Please try again later.');
                console.error('Error:', error);
            });

        function showError(message) {
            errorMessage.textContent = message;
            errorContainer.classList.remove('hidden');
        }

        function generateDirectorForm(data) {
            // Create form elements based on the director template
            const formHtml = `
                    <form id="director-form" class="space-y-6">
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-first-name">
                                First Name <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="director-first-name"
                                name="director-first-name"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-surname">
                                Surname <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="director-surname"
                                name="director-surname"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-title">
                                Title <span class="text-emerald-500">*</span>
                            </label>
                            <select
                                id="director-title"
                                name="director-title"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                                <option value="">Select Title</option>
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                <option value="Dr">Dr</option>
                                <option value="Prof">Prof</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700">
                                Gender <span class="text-emerald-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex items-center p-3 border rounded-xl transition-all duration-300 bg-white hover:border-emerald-300">
                                    <input
                                        type="radio"
                                        id="director-gender-male"
                                        name="director-gender"
                                        value="Male"
                                        required
                                        class="mr-3 text-emerald-500 focus:ring-emerald-400 h-4 w-4"
                                    >
                                    <label for="director-gender-male" class="text-gray-700 text-sm flex-1 cursor-pointer">Male</label>
                                </div>
                                <div class="flex items-center p-3 border rounded-xl transition-all duration-300 bg-white hover:border-emerald-300">
                                    <input
                                        type="radio"
                                        id="director-gender-female"
                                        name="director-gender"
                                        value="Female"
                                        required
                                        class="mr-3 text-emerald-500 focus:ring-emerald-400 h-4 w-4"
                                    >
                                    <label for="director-gender-female" class="text-gray-700 text-sm flex-1 cursor-pointer">Female</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-dob">
                                Date of Birth <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="director-dob"
                                name="director-dob"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-nationality">
                                Nationality <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="director-nationality"
                                name="director-nationality"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-id-number">
                                ID Number <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="director-id-number"
                                name="director-id-number"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-marital-status">
                                Marital Status <span class="text-emerald-500">*</span>
                            </label>
                            <select
                                id="director-marital-status"
                                name="director-marital-status"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                                <option value="">Select Marital Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-cell-number">
                                Cell Number <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="director-cell-number"
                                name="director-cell-number"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-email">
                                Email Address <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="director-email"
                                name="director-email"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <h3 class="text-lg font-medium text-gray-800 border-b pb-2 mt-8 mb-4">Next of Kin Details</h3>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-next-of-kin-name">
                                Full Name <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="director-next-of-kin-name"
                                name="director-next-of-kin-name"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-next-of-kin-relationship">
                                Relationship <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="director-next-of-kin-relationship"
                                name="director-next-of-kin-relationship"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700" for="director-next-of-kin-phone">
                                Phone Numbers <span class="text-emerald-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="director-next-of-kin-phone"
                                name="director-next-of-kin-phone"
                                required
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300"
                            >
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-3 text-white bg-gradient-to-r from-emerald-500 to-orange-400 rounded-xl hover:from-emerald-600 hover:to-orange-500 transition-all duration-300">
                                Submit Director Information
                            </button>
                        </div>
                    </form>
                `;

            formContainer.innerHTML = formHtml;

            // Add event listener to the form
            document.getElementById('director-form').addEventListener('submit', function (e) {
                e.preventDefault();
                submitDirectorForm();
            });

            // Update relationship based on gender and marital status
            const genderRadios = document.querySelectorAll('input[name="director-gender"]');
            const maritalStatus = document.getElementById('director-marital-status');
            const relationship = document.getElementById('director-next-of-kin-relationship');

            function updateRelationship() {
                const gender = document.querySelector('input[name="director-gender"]:checked')?.value;
                const status = maritalStatus.value;

                if (status === 'Married' && gender) {
                    relationship.value = gender === 'Male' ? 'WIFE' : 'HUSBAND';
                }
            }

            genderRadios.forEach(radio => {
                radio.addEventListener('change', updateRelationship);
            });

            maritalStatus.addEventListener('change', updateRelationship);
        }

        function submitDirectorForm() {
            const form = document.getElementById('director-form');

            // Collect all form data
            const directorData = {
                firstName: document.getElementById('director-first-name').value,
                surname: document.getElementById('director-surname').value,
                title: document.getElementById('director-title').value,
                gender: document.querySelector('input[name="director-gender"]:checked').value,
                dob: document.getElementById('director-dob').value,
                nationality: document.getElementById('director-nationality').value,
                idNumber: document.getElementById('director-id-number').value,
                maritalStatus: document.getElementById('director-marital-status').value,
                cellNumber: document.getElementById('director-cell-number').value,
                email: document.getElementById('director-email').value,
                nextOfKin: {
                    name: document.getElementById('director-next-of-kin-name').value,
                    relationship: document.getElementById('director-next-of-kin-relationship').value,
                    phone: document.getElementById('director-next-of-kin-phone').value
                }
            };

            // Show loading
            formContainer.classList.add('hidden');
            loadingContainer.classList.remove('hidden');

            // Submit the form data
            fetch(`/api/director-links/${token}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    director_data: directorData
                })
            })
                .then(response => response.json())
                .then(data => {
                    loadingContainer.classList.add('hidden');

                    if (!data.success) {
                        formContainer.classList.remove('hidden');
                        showError(data.message || 'Failed to submit your information. Please try again.');
                        return;
                    }

                    // Show success message
                    successMessage.textContent = data.message;
                    successContainer.classList.remove('hidden');

                    // If this is the final director, show the submit all button
                    if (directorFormData.is_final_director) {
                        finalDirectorActions.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    loadingContainer.classList.add('hidden');
                    formContainer.classList.remove('hidden');
                    showError('Failed to submit your information. Please try again later.');
                    console.error('Error:', error);
                });
        }

        // Submit all button for the final director
        if (submitAllBtn) {
            submitAllBtn.addEventListener('click', function () {
                loadingContainer.classList.remove('hidden');
                successContainer.classList.add('hidden');

                fetch(`/api/director-links/${token}/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        submit_full_form: true
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        loadingContainer.classList.add('hidden');

                        if (!data.success) {
                            successContainer.classList.remove('hidden');
                            showError(data.message || 'Failed to submit the complete application. Please try again.');
                            return;
                        }

                        // Show success message for full submission
                        successMessage.textContent = 'The complete application has been submitted successfully!';
                        finalDirectorActions.classList.add('hidden');
                        successContainer.classList.remove('hidden');
                    })
                    .catch(error => {
                        loadingContainer.classList.add('hidden');
                        successContainer.classList.remove('hidden');
                        showError('Failed to submit the complete application. Please try again later.');
                        console.error('Error:', error);
                    });
            });
        }
    });
</script>
</body>
</html>
