@section('title', 'MVR Background Check')

<x-app-layout>

    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="px-6 py-8 sm:px-10">
                    <div class="text-center">
                        <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                            Motor Vehicle Record Check
                        </h1>
                        <p class="mt-4 text-lg text-gray-600">
                            Verify driving history and license status with our comprehensive MVR screening
                        </p>
                    </div>

                    <div class="mt-8 bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-blue-900 mb-2">Test Integration</h3>
                        <p class="text-blue-700 mb-4">Test the MVR integration with sample data (Staging Environment)</p>
                        <button id="test-mvr-btn"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                            Test MVR Check
                        </button>
                    </div>

                    <div class="mt-10">
                        <form id="mvr-form" class="space-y-6">
                            @csrf

                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700">First
                                            Name *</label>
                                        <input type="text" id="first_name" name="first_name" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    </div>
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name
                                            *</label>
                                        <input type="text" id="last_name" name="last_name" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email
                                            *</label>
                                        <input type="email" id="email" name="email" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    </div>
                                    <div>
                                        <label for="dob" class="block text-sm font-medium text-gray-700">Date of
                                            Birth *</label>
                                        <input type="date" id="dob" name="dob" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone
                                            Number</label>
                                        <input type="tel" id="phone" name="phone"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    </div>
                                    <div>
                                        <label for="zipcode" class="block text-sm font-medium text-gray-700">ZIP
                                            Code</label>
                                        <input type="text" id="zipcode" name="zipcode"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-blue-50 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Driver License Information</h3>
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="driver_license_number"
                                            class="block text-sm font-medium text-gray-700">Driver License Number
                                            *</label>
                                        <input type="text" id="driver_license_number" name="driver_license_number"
                                            required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    </div>
                                    <div>
                                        <label for="driver_license_state"
                                            class="block text-sm font-medium text-gray-700">State *</label>
                                        <select id="driver_license_state" name="driver_license_state" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                            <option value="">Select State</option>
                                            <option value="CA">California</option>
                                            <option value="NY">New York</option>
                                            <option value="TX">Texas</option>
                                            <option value="FL">Florida</option>
                                            <option value="IL">Illinois</option>
                                            <option value="PA">Pennsylvania</option>
                                            <option value="OH">Ohio</option>
                                            <option value="GA">Georgia</option>
                                            <option value="NC">North Carolina</option>
                                            <option value="MI">Michigan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" id="submit-mvr-btn"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200 disabled:opacity-50">
                                    Run MVR Check
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="results-section" class="mt-10 hidden">
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">MVR Check Results</h3>
                            <div id="results-content" class="space-y-4">
                                <!-- Results will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testBtn = document.getElementById('test-mvr-btn');
            const form = document.getElementById('mvr-form');
            const submitBtn = document.getElementById('submit-mvr-btn');
            const resultsSection = document.getElementById('results-section');
            const resultsContent = document.getElementById('results-content');

            testBtn.addEventListener('click', function() {
                testBtn.disabled = true;
                testBtn.textContent = 'Testing...';

                fetch('/mvr/test', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        showResults(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Test failed: ' + error.message);
                    })
                    .finally(() => {
                        testBtn.disabled = false;
                        testBtn.textContent = 'Test MVR Check';
                    });
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                const formData = new FormData(form);
                const data = Object.fromEntries(formData);

                fetch('/mvr/check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        showResults(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('MVR check failed: ' + error.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Run MVR Check';
                    });
            });

            function showResults(data) {
                resultsContent.innerHTML = '';

                if (data.success) {
                    resultsContent.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">MVR Check Initiated Successfully</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p><strong>Message:</strong> ${data.message}</p>
                                ${data.data ? `
                                        <p><strong>Candidate ID:</strong> ${data.data.candidate_id || 'N/A'}</p>
                                        <p><strong>MVR ID:</strong> ${data.data.mvr_id || 'N/A'}</p>
                                        <p><strong>Status:</strong> ${data.data.status || 'N/A'}</p>
                                        <p><strong>Estimated Completion:</strong> ${data.data.estimated_completion || 'N/A'}</p>
                                    ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
                } else {
                    resultsContent.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">MVR Check Failed</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>${data.message}</p>
                                ${data.errors ? Object.values(data.errors).map(err => `<p>• ${err}</p>`).join('') : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
                }

                resultsSection.classList.remove('hidden');
                resultsSection.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            function showError(message) {
                showResults({
                    success: false,
                    message: message
                });
            }
        });
    </script>
</x-app-layout>
