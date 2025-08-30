@extends('dashboards.owner.owner')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8"> {{-- Adjusted padding for better responsiveness --}}
    <h1 class="text-2xl font-extrabold text-gray-900 mb-2">Welcome, {{ $owner->firstname }}!</h1> {{-- Smaller greeting --}}
    <p class="text-base text-gray-600 mb-6">{{ \Carbon\Carbon::now()->format('l, F d, Y') }}</p> {{-- Smaller date --}}
    <div class="bg-white shadow-xl rounded-xl p-6 max-w-3xl mx-auto space-y-6 border border-gray-100">

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
                        <h2 class="text-lg font-bold text-gray-900">{{ $owner->firstname }} {{ $owner->middlename ?? '' }} {{ $owner->lastname }}</h2>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $owner->email }}</p>
                        {{-- New: View Staff link --}}
                        <a href="{{ route('owner.show.staff') }}" class="text-green-600 hover:text-green-800 underline text-sm mt-1 inline-block">View Staff List</a>
                    </div>
                    <div class="flex flex-col space-y-2 ml-4"> {{-- New flex container for stacking buttons --}}
                        <button type="button" id="editProfileButton" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-br from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                            Edit Profile
                        </button>
                        <button type="button" id="togglePasswordSection" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-br from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                            Change Password
                        </button>
                        <button type="button" id="toggleStaffCreationSection" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-br from-green-500 to-green-700 hover:from-green-600 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105">
                            Create Staff Account
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Profile Details Section (Editable Fields) --}}
        <div class="pt-4 space-y-4">
            <form id="ownerDetailsForm" method="POST" action="{{ route('owner.profile.update') }}"> {{-- Form for general details --}}
                @csrf
                @method('PUT') {{-- Use PUT method for updating resources --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="store_name" class="block text-sm font-medium text-gray-500 mb-1">Store Name</label>
                        <input type="text" name="store_name" id="store_name" value="{{ old('store_name', $owner->store_name ?? '') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-base" disabled>
                        @error('store_name') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="contact" class="block text-sm font-medium text-gray-500 mb-1">Contact Number</label>
                        <input type="text" name="contact" id="contact" value="{{ old('contact', $owner->contact ?? '') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-base" disabled>
                        @error('contact') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="store_address" class="block text-sm font-medium text-gray-500 mb-1">Store Address</label>
                        <input type="text" name="store_address" id="store_address" value="{{ old('store_address', $owner->store_address ?? '') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-base" disabled>
                        @error('store_address') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                    {{-- Removed Status and Email Verified At fields --}}
                </div>
                <div class="text-right pt-4">
                    <button type="submit" id="saveProfileDetailsButton" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-br from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 hidden">
                        Save Profile Details
                    </button>
                </div>
            </form>
        </div>

        {{-- Password Change Section (initially hidden) --}}
        <div id="passwordSection" class="hidden mt-4 pt-4 border-t border-gray-200">
            <form method="POST" action="{{ route('owner.profile.update') }}" class="space-y-4">
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

        {{-- Staff Creation Section (initially hidden) --}}
        <div id="staffCreationSection" class="hidden mt-4 pt-4 border-t border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-300">Create New Staff Account</h3>
            <form method="POST" action="{{ route('owner.add.staff') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="staff_firstname" class="block text-sm font-medium text-gray-700 mb-0.5">First Name</label>
                        <input type="text" name="firstname" id="staff_firstname" value="{{ old('firstname') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-base">
                        @error('firstname') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="staff_middlename" class="block text-sm font-medium text-gray-700 mb-0.5">Middle Name</label>
                        <input type="text" name="middlename" id="staff_middlename" value="{{ old('middlename') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-base">
                        @error('middlename') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="staff_lastname" class="block text-sm font-medium text-gray-700 mb-0.5">Last Name</label>
                        <input type="text" name="lastname" id="staff_lastname" value="{{ old('lastname') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-base">
                        @error('lastname') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="staff_email" class="block text-sm font-medium text-gray-700 mb-0.5">Email Address</label>
                        <input type="email" name="email" id="staff_email" value="{{ old('email') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-base">
                        @error('email') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="staff_contact" class="block text-sm font-medium text-gray-700 mb-0.5">Contact Number</label>
                        <input type="text" name="contact" id="staff_contact" value="{{ old('contact') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-base">
                        @error('contact') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="staff_password" class="block text-sm font-medium text-gray-700 mb-0.5">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="staff_password"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 pr-10 password-input-field text-base">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button" data-target="staff_password">
                                <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                            </button>
                        </div>
                        @error('password') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-200">
                        <label for="staff_password_confirmation" class="block text-sm font-medium text-gray-700 mb-0.5">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="staff_password_confirmation"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 pr-10 password-input-field text-base">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button" data-target="staff_password_confirmation">
                                <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                            </button>
                        </div>
                        @error('password_confirmation') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="text-right pt-3">
                    <button type="submit" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-br from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105">
                        Create Staff
                    </button>
                </div>
            </form>
        </div> {{-- End of staffCreationSection div --}}

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePasswordSectionButton = document.getElementById('togglePasswordSection');
        const passwordSection = document.getElementById('passwordSection');
        const toggleStaffCreationSectionButton = document.getElementById('toggleStaffCreationSection');
        const staffCreationSection = document.getElementById('staffCreationSection');

        const editProfileButton = document.getElementById('editProfileButton');
        const ownerDetailsForm = document.getElementById('ownerDetailsForm');
        const detailInputs = ownerDetailsForm.querySelectorAll('input[type="text"]');
        const saveProfileDetailsButton = document.getElementById('saveProfileDetailsButton');

        // Moved these variable definitions to a higher scope within DOMContentLoaded
        const defaultBlueClasses = ['bg-gradient-to-br', 'from-blue-500', 'to-blue-700', 'hover:from-blue-600', 'hover:to-blue-800'];
        const redGradientClasses = ['bg-gradient-to-br', 'from-red-500', 'to-red-700', 'hover:from-red-700', 'hover:to-red-900'];
        const greenGradientClasses = ['bg-gradient-to-br', 'from-green-500', 'to-green-700', 'hover:from-green-600', 'hover:to-green-800'];


        // Function to close all expandable sections
        const closeAllSections = () => {
            passwordSection.classList.add('hidden');
            staffCreationSection.classList.add('hidden');
            // Reset button texts to default
            togglePasswordSectionButton.textContent = 'Change Password';
            toggleStaffCreationSectionButton.textContent = 'Create Staff Account';

            // Reset button colors to default states
            togglePasswordSectionButton.classList.remove(...redGradientClasses);
            togglePasswordSectionButton.classList.add(...defaultBlueClasses);

            toggleStaffCreationSectionButton.classList.remove(...redGradientClasses); // Remove red if it was set
            toggleStaffCreationSectionButton.classList.add(...greenGradientClasses); // Always green for staff creation button
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

        // Event listener for Create Staff Account button
        if (toggleStaffCreationSectionButton && staffCreationSection) {
            toggleStaffCreationSectionButton.addEventListener('click', function() {
                const isHidden = staffCreationSection.classList.contains('hidden');
                closeAllSections(); // Close other sections first
                if (isHidden) {
                    staffCreationSection.classList.remove('hidden');
                    this.textContent = 'Hide Staff Creation';
                    this.classList.remove(...greenGradientClasses); // Remove green
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

        // --- New JavaScript for Profile Details Edit Toggle ---
        let isDetailsEditMode = false; // Separate state for details edit mode

        // Function to set the form's edit state for general details
        const setDetailsEditMode = (enable) => {
            detailInputs.forEach(input => {
                input.disabled = !enable;
            });
            if (enable) {
                saveProfileDetailsButton.classList.remove('hidden');
                editProfileButton.textContent = 'Cancel Edit';
                // Correctly remove blue gradient and add red gradient
                editProfileButton.classList.remove(...defaultBlueClasses);
                editProfileButton.classList.add(...redGradientClasses);
                closeAllSections(); // Close other sections when editing details
            } else {
                saveProfileDetailsButton.classList.add('hidden');
                editProfileButton.textContent = 'Edit Profile';
                // Correctly remove red gradient and add blue gradient
                editProfileButton.classList.remove(...redGradientClasses);
                editProfileButton.classList.add(...defaultBlueClasses);
                // Reset form fields to original values if cancelling
                ownerDetailsForm.reset(); // This will reset to initial loaded values
            }
            isDetailsEditMode = enable;
        };

        // Initial state: disable inputs and hide save button for details
        setDetailsEditMode(false);

        editProfileButton.addEventListener('click', () => {
            setDetailsEditMode(!isDetailsEditMode);
        });

        // Optional: If there are validation errors on submission for details, keep the form in edit mode

    });
</script>
@endsection