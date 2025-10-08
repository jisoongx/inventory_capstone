@extends('dashboards.super_admin.super_admin') {{-- Adjust if your base layout name is different --}}

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-xl font-semibold text-gray-800 ">Welcome Admin!</h1> {{-- Smaller greeting --}}
    <p class="text-base text-gray-600 mb-6">{{ \Carbon\Carbon::now()->format('l, F d, Y') }}</p> {{-- Smaller date --}}

    <div class="bg-white shadow-lg rounded-xl p-6 max-w-3xl mx-auto space-y-6 border border-slate-100">
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
                        <h2 class="text-lg font-bold text-gray-700">{{ $superAdmin->firstname }} {{ $superAdmin->middlename ?? '' }} {{ $superAdmin->lastname }}</h2>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $superAdmin->email }}</p>
                    </div>
                    <div class="flex flex-col space-y-2 ml-4"> {{-- New flex container for stacking buttons --}}

                        <button type="button" id="togglePasswordSection" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-br from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 focus:ring-gray-300 transition-all duration-200 transform hover:scale-105">
                            Change Password
                        </button>

                    </div>
                </div>
            </div>
        </div>


        {{-- Password Change Section (initially hidden) --}}
        <div id="passwordSection" class="hidden mt-4 pt-4 border-t border-gray-200">
            <form method="POST" action="{{ route('super_admin.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT') {{-- Use PUT method for updating resources --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3"> {{-- Added grid for consistent layout --}}
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-0.5">Current Password</label>
                        <div class="relative">
                            <input type="password" name="current_password" id="current_password"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-300 p-2 pr-10 password-input-field text-base">
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
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-300 p-2 pr-10 password-input-field text-base">
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
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-300 p-2 pr-10 password-input-field text-base">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button" data-target="password_confirmation">
                                <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                            </button>
                        </div>
                        @error('password_confirmation') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="text-right pt-3">
                    <button type="submit" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-br from-red-600 to-red-800 hover:from-red-700 hover:to-red-900 focus:ring-gray-300 transition-all duration-200 transform hover:scale-105">
                        Save Changes
                    </button>
                </div>
            </form>
        </div> {{-- End of passwordSection div --}}
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePasswordSectionButton = document.getElementById('togglePasswordSection');
        const passwordSection = document.getElementById('passwordSection');

        // Moved these variable definitions to a higher scope within DOMContentLoaded
        const defaultBlueClasses = ['bg-gradient-to-br', 'from-blue-500', 'to-blue-700', 'hover:from-blue-600', 'hover:to-blue-800'];
        const redGradientClasses = ['bg-gradient-to-br', 'from-red-500', 'to-red-700', 'hover:from-red-700', 'hover:to-red-900'];



        // Function to close all expandable sections
        const closeAllSections = () => {
            passwordSection.classList.add('hidden');

            // Reset button texts to default
            togglePasswordSectionButton.textContent = 'Change Password';


            // Reset button colors to default states
            togglePasswordSectionButton.classList.remove(...redGradientClasses);
            togglePasswordSectionButton.classList.add(...defaultBlueClasses);
        };

        // Event listener for Change Password button
        if (togglePasswordSectionButton && passwordSection) {
            togglePasswordSectionButton.addEventListener('click', function() {
                const isHidden = passwordSection.classList.contains('hidden');
                closeAllSections(); // Close other sections first
                if (isHidden) {
                    passwordSection.classList.remove('hidden');
                    this.textContent = 'Hide Password Fields';
                    this.classList.remove(...defaultBlueClasses); // Remove blue
                    this.classList.add(...redGradientClasses); // Add red
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