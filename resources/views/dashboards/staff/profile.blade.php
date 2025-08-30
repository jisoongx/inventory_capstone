@extends('dashboards.staff.staff') {{-- This extends your main staff dashboard layout --}}

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8"> {{-- Adjusted padding for better responsiveness --}}
    <h1 class="text-2xl font-extrabold text-gray-900 mb-2">Welcome, {{ $staff->firstname }}!</h1> {{-- Smaller greeting --}}
    <p class="text-base text-gray-600 mb-6">{{ \Carbon\Carbon::now()->format('l, F d, Y') }}</p> {{-- Smaller date --}}

    <div class="bg-white shadow-xl rounded-xl p-6 max-w-3xl mx-auto space-y-6 border border-gray-100"> {{-- Reduced padding, max-width, and spacing --}}


        {{-- Success and error message display --}}
        @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded-lg relative mb-3" role="alert">
            <span class="block sm:inline text-sm">{{ session('success') }}</span>
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded-lg relative mb-3" role="alert">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Profile Header Section --}}
        <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 pb-4 border-b border-gray-200">
            <img src="{{ asset('assets/user.png') }}" alt="Profile Picture" class="w-24 h-24 rounded-full border-3 border-red-500 object-cover shadow-sm">
            <div class="text-center sm:text-left flex-grow">
                <div class="flex items-center justify-between"> {{-- Flex container for name/email and button group --}}
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $staff->firstname }} {{ $staff->middlename ?? '' }} {{ $staff->lastname }}</h2>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $staff->email }}</p>
                    </div>
                    <div class="flex flex-col space-y-2 ml-4"> {{-- New flex container for stacking buttons --}}

                        <button type="button" id="togglePasswordSection" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-br from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                            Change Password
                        </button>

                    </div>
                </div>
            </div>
        </div>

        {{-- Profile Details Section --}}
        <div class="pt-4 space-y-4"> {{-- Reduced spacing --}}

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3"> {{-- Reduced gap --}}
                <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200"> {{-- Reduced padding --}}
                    <p class="text-sm font-medium text-gray-500">Store Name</p>
                    <p class="text-base text-gray-900 font-medium mt-0.5">{{ $staff->owner->store_name ?? 'N/A' }}</p> {{-- Smaller text --}}
                </div>
                <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200"> {{-- Reduced padding --}}
                    <p class="text-sm font-medium text-gray-500">Position</p>
                    <p class="text-base text-gray-900 font-medium mt-0.5">{{ $staff->position ?? 'Staff' }}</p> {{-- Smaller text --}}
                </div>
                <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200"> {{-- Reduced padding --}}
                    <p class="text-sm font-medium text-gray-500">Contact Number</p>
                    <p class="text-base text-gray-900 font-medium mt-0.5">{{ $staff->contact ?? 'N/A' }}</p> {{-- Smaller text --}}
                </div>
            </div>
        </div>

        {{-- Password Change Section (initially hidden) --}}
        <div id="passwordSection" class="hidden mt-4 pt-4 border-t border-gray-200">
            <form method="POST" action="{{ route('staff.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT') {{-- Use PUT method for updating resources --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3"> {{-- Added grid for consistent layout --}}
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-0.5">Current Password</label>
                        <div class="relative">
                            <input type="password" name="current_password" id="current_password"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 pr-10 password-input-field text-base">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button" data-target="current_password">
                                <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                            </button>
                        </div>
                        @error('current_password') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-0.5">New Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 pr-10 password-input-field text-base">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button" data-target="password">
                                <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                            </button>
                        </div>
                        @error('password') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-0.5">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 pr-10 password-input-field text-base">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button" data-target="password_confirmation">
                                <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                            </button>
                        </div>
                        @error('password_confirmation') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="text-right pt-3">
                    <button type="submit" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-br from-red-600 to-red-800 hover:from-red-700 hover:to-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 transform hover:scale-105">
                        Save Changes
                    </button>
                </div>
            </form>
        </div> {{-- End of passwordSection div --}}

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Logic for toggling the password change section
                const togglePasswordSectionButton = document.getElementById('togglePasswordSection');
                const passwordSection = document.getElementById('passwordSection');

                if (togglePasswordSectionButton && passwordSection) {
                    togglePasswordSectionButton.addEventListener('click', function() {
                        passwordSection.classList.toggle('hidden');
                        // Change button text and color
                        if (passwordSection.classList.contains('hidden')) {
                            this.textContent = 'Change Password';
                            this.classList.remove('bg-red-600', 'hover:bg-red-700');
                            this.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        } else {
                            this.textContent = 'Hide Password Fields';
                            this.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                            this.classList.add('bg-red-600', 'hover:bg-red-700');
                        }
                    });
                }

                // Select all password input fields and their corresponding toggle buttons/icons
                const passwordInputFields = document.querySelectorAll('.password-input-field');

                passwordInputFields.forEach(inputField => {
                    const toggleButton = inputField.nextElementSibling; // Assuming button is the next sibling
                    const eyeIcon = toggleButton ? toggleButton.querySelector('.password-eye-icon') : null;

                    // Function to show/hide the eye icon based on input value
                    const toggleEyeIconVisibility = () => {
                        if (eyeIcon) {
                            if (inputField.value.length > 0) {
                                eyeIcon.classList.remove('hidden');
                            } else {
                                eyeIcon.classList.add('hidden');
                            }
                        }
                    };

                    // Initial check on page load (if there's pre-filled data, though unlikely for password fields)
                    toggleEyeIconVisibility();

                    // Listen for input events (typing)
                    inputField.addEventListener('input', toggleEyeIconVisibility);

                    // Existing logic for toggling password visibility (eye icon click)
                    if (toggleButton) {
                        toggleButton.addEventListener('click', function() {
                            const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
                            inputField.setAttribute('type', type);
                            if (eyeIcon) {
                                eyeIcon.textContent = type === 'password' ? 'visibility_off' : 'visibility';
                            }
                        });
                    }
                });
            });
        </script>
        @endsection