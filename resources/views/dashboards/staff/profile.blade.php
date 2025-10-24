@extends('dashboards.staff.staff')

@section('content')
<div class="bg-slate-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-slate-800">Welcome, {{ $staff->firstname }}!</h1>
            <p class="text-sm text-slate-500">{{ \Carbon\Carbon::now()->format('l, F d, Y') }}</p>
        </div>

        {{-- Main Content Card --}}
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 max-w-4xl mx-auto border border-slate-100">

            {{-- Success/Error/Info messages --}}
            @if (session('success'))
            <div class="flex items-center bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <span class="material-symbols-rounded mr-2">check_circle</span>
                <span class="block sm:inline text-sm font-medium">{{ session('success') }}</span>
            </div>
            @endif
            @if (session('info'))
            <div class="flex items-center bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <span class="material-symbols-rounded mr-2">info</span>
                <span class="block sm:inline text-sm font-medium">{{ session('info') }}</span>
            </div>
            @endif
            @if ($errors->any())
            <div class="flex items-start bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <span class="material-symbols-rounded mr-2 mt-0.5">error</span>
                <div>
                    <strong class="font-bold text-sm">Oops!</strong>
                    <ul class="list-disc list-inside text-sm mt-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- Profile Header --}}
            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 pb-6 border-b border-slate-200">
                <img src="{{ asset('assets/user.png') }}" alt="Profile Picture" class="w-24 h-24 rounded-full object-cover ring-2 ring-offset-2 ring-indigo-500">
                <div class="text-center sm:text-left flex-grow">
                    <h2 class="text-xl font-bold text-slate-900">{{ $staff->firstname }} {{ $staff->middlename ?? '' }} {{ $staff->lastname }}</h2>
                    <p class="text-sm text-slate-500 mt-1">Store staff</p>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="button" id="editProfileButton" class="px-4 py-2 border border-slate-300 text-sm font-semibold rounded-lg shadow-sm text-slate-700 bg-white hover:bg-slate-50 focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-colors duration-200">Edit Profile</button>
                    <button type="button" id="togglePasswordSection" class="px-4 py-2 border border-slate-300 text-sm font-semibold rounded-lg shadow-sm text-slate-700 bg-white hover:bg-slate-50 focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-colors duration-200">Change Password</button>
                </div>
            </div>

            {{-- Profile Details Form --}}
            <div class="pt-6 space-y-4">
                <form id="staffDetailsForm" method="POST" action="{{ route('staff.profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label for="store_name" class="block text-sm font-medium text-slate-700 mb-1">Store Name</label>
                            <input type="text" id="store_name" value="{{ $staff->owner->store_name ?? 'N/A' }}" class="block w-full rounded-lg border-slate-300 shadow-sm disabled:bg-slate-50 disabled:text-slate-500 text-base" disabled>
                        </div>
                        <div>
                            <label for="position" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="text" id="position" value="{{ $staff->email}}" class="block w-full rounded-lg border-slate-300 shadow-sm disabled:bg-slate-50 disabled:text-slate-500 text-base" disabled>
                        </div>
                        <div class="md:col-span-2">
                            <label for="contact" class="block text-sm font-medium text-slate-700 mb-1">Contact Number</label>
                            <input type="text" name="contact" id="contact" value="{{ old('contact', $staff->contact ?? '') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-50 disabled:text-slate-500 text-base" disabled>
                        </div>
                    </div>
                    <div class="text-right mt-6">
                        <button type="submit" id="saveProfileDetailsButton" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-colors duration-200 hidden">Save Changes</button>
                    </div>
                </form>
            </div>

            {{-- Password Change Section --}}
            <div id="passwordSection" class="hidden mt-6 pt-6 border-t border-slate-200">
                <form id="passwordForm" method="POST" action="{{ route('staff.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Current Password</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="current_password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10 password-input-field text-base">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button">
                                    <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10 password-input-field text-base">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button">
                                    <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10 password-input-field text-base">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button">
                                    <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="text-right pt-2">
                        <button type="submit" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-offset-1 focus:ring-red-500 transition-colors duration-200">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Section Elements
        const passwordSection = document.getElementById('passwordSection');
        const staffDetailsForm = document.getElementById('staffDetailsForm');
        const passwordForm = document.getElementById('passwordForm');

        // Buttons
        const togglePasswordSectionButton = document.getElementById('togglePasswordSection');
        const editProfileButton = document.getElementById('editProfileButton');
        const saveProfileDetailsButton = document.getElementById('saveProfileDetailsButton');

        // Profile inputs (only 'contact' is editable)
        const contactInput = document.getElementById('contact');
        let isDetailsEditMode = false;

        // Button classes
        const secondaryBtnClasses = ['bg-white', 'text-slate-700', 'border-slate-300', 'hover:bg-slate-50'];
        const destructiveBtnClasses = ['bg-red-600', 'text-white', 'border-transparent', 'hover:bg-red-700'];

        // Toast Helper
        function showToast(message, type = "success") {
            const colors = {
                success: "bg-green-600",
                info: "bg-blue-600",
                error: "bg-red-600"
            };
            const toast = document.createElement("div");
            toast.className = `${colors[type] || colors.success} text-white px-4 py-2 rounded shadow fixed top-16 right-4 z-50 transition-opacity duration-500`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = "0";
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }

        // Close all toggle sections
        const closeAllSections = () => {
            passwordSection.classList.add('hidden');
            togglePasswordSectionButton.textContent = 'Change Password';
            togglePasswordSectionButton.classList.remove(...destructiveBtnClasses);
            togglePasswordSectionButton.classList.add(...secondaryBtnClasses);
        };

        // Toggle profile edit mode
        const setDetailsEditMode = (enable) => {
            contactInput.disabled = !enable;
            if (enable) {
                saveProfileDetailsButton.classList.remove('hidden');
                editProfileButton.textContent = 'Cancel Edit';
                editProfileButton.classList.remove(...secondaryBtnClasses);
                editProfileButton.classList.add(...destructiveBtnClasses);
                closeAllSections();
            } else {
                saveProfileDetailsButton.classList.add('hidden');
                editProfileButton.textContent = 'Edit Profile';
                editProfileButton.classList.remove(...destructiveBtnClasses);
                editProfileButton.classList.add(...secondaryBtnClasses);
                contactInput.value = contactInput.defaultValue; // Revert changes on cancel
            }
            isDetailsEditMode = enable;
        };

        // Toggle Sections
        togglePasswordSectionButton.addEventListener('click', function() {
            const isHidden = passwordSection.classList.contains('hidden');
            if (isDetailsEditMode) setDetailsEditMode(false);
            closeAllSections();
            if (isHidden) {
                passwordSection.classList.remove('hidden');
                this.textContent = 'Hide Section';
                this.classList.remove(...secondaryBtnClasses);
                this.classList.add(...destructiveBtnClasses);
            }
        });

        editProfileButton.addEventListener('click', () => setDetailsEditMode(!isDetailsEditMode));

        // Password toggle buttons
        document.querySelectorAll('.password-toggle-button').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const eye = this.querySelector('.password-eye-icon');
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                if (eye) eye.textContent = type === 'password' ? 'visibility_off' : 'visibility';
            });
        });

        document.querySelectorAll('.password-input-field').forEach(input => {
            const eye = input.nextElementSibling.querySelector('.password-eye-icon');
            const toggleEye = () => eye && eye.classList.toggle('hidden', input.value.length === 0);
            input.addEventListener('input', toggleEye);
            toggleEye();
        });

        // AJAX submission for all forms
        [staffDetailsForm, passwordForm].forEach(form => {
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(form.action, {
                        method: form.method,
                        body: new FormData(form),
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.success, "success");
                            if (form === passwordForm) {
                                form.reset();
                            }
                            if (form === staffDetailsForm && data.staff) {
                                // Update the contact input's value and default value to reflect the change
                                contactInput.value = data.staff.contact;
                                contactInput.defaultValue = data.staff.contact;
                                setDetailsEditMode(false); // Exit edit mode
                            }
                        }

                        if (data.errors) {
                            Object.values(data.errors).forEach(errArr => errArr.forEach(err => showToast(err, "error")));
                        }

                        if (data.info) {
                            showToast(data.info, "info");
                        }
                    })
                    .catch(() => showToast("Something went wrong.", "error"));
            });
        });

        // Initialize view
        setDetailsEditMode(false);
    });
</script>
@endsection