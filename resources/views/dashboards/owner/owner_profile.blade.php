@extends('dashboards.owner.owner')

@section('content')
<div class="bg-slate-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-slate-800">Welcome, {{ $owner->firstname }}!</h1>
            <p class="text-sm text-slate-500">{{ \Carbon\Carbon::now()->format('l, F d, Y') }}</p>
        </div>

        {{-- Main Content Card --}}
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 max-w-5xl mx-auto border border-slate-100">

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
                    <h2 class="text-xl mb-2 font-bold text-slate-900">{{ $owner->firstname }} {{ $owner->middlename ?? '' }} {{ $owner->lastname }}</h2>
                    @if ($subscription)
                    @php
                    $plan = strtolower($subscription->planDetails->plan_title);
                    $icon = 'magic_button';
                    $gradient = 'from-yellow-400 to-yellow-500';
                    $textColor = 'text-yellow-50';

                    $icon = 'magic_button';
                    $gradient = 'from-blue-400 to-blue-500';
                    $textColor = 'text-blue-50';

                    if ($plan === 'standard') {
                    $icon = 'star';
                    $gradient = 'from-orange-400 to-orange-500';
                    $textColor = 'text-orange-50';
                    } elseif ($plan === 'premium') {
                    $icon = 'diamond';
                    $gradient = 'from-rose-400 to-rose-500';
                    $textColor = 'text-rose-50';
                    }
                    @endphp

                    {{-- Plan Display --}}
                    <div class="mt-2 sm:mt-0 flex items-center gap-3">
                        <p class="text-sm text-slate-500">{{ $owner->email }}</p>

                        {{-- Clickable plan badge --}}
                        <button
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold {{ $textColor }} bg-gradient-to-r {{ $gradient }} shadow-sm shadow-slate-200/40 hover:opacity-90 transition"
                            onclick="document.getElementById('planModal').classList.remove('hidden')">
                            <span class="material-symbols-rounded text-sm">{{ $icon }}</span>
                            <span class="uppercase tracking-wide">{{ $subscription->planDetails->plan_title }}</span>
                            <span class="material-symbols-rounded text-base ml-1">info</span>
                        </button>
                    </div>

                    {{-- Plan Modal --}}
                    <div id="planModal"
                        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
                            {{-- Close button --}}
                            <button class="absolute top-3 right-3 text-slate-400 hover:text-slate-600 transition"
                                onclick="document.getElementById('planModal').classList.add('hidden')">
                                <span class="material-symbols-rounded text-lg">close</span>
                            </button>

                            {{-- Modal Content --}}
                            <div class="text-center mt-2">
                                <h2 class="text-lg font-semibold text-slate-800">
                                    {{ ucfirst($subscription->planDetails->plan_title) }} Plan
                                </h2>

                                @php
                                $status = ucfirst($subscription->status); // use DB status
                                $statusColor = 'text-green-600';
                                if ($subscription->status === 'cancelled') {
                                $statusColor = 'text-red-600';
                                } elseif ($subscription->status === 'expired') {
                                $statusColor = 'text-gray-500';
                                }
                                @endphp

                                {{-- Status below plan title --}}
                                <p class="text-sm font-medium {{ $statusColor }} mt-1">
                                    Status: {{ $status }}
                                </p>
                                @if($subscription->subscription_end)
                                <p class="text-sm text-slate-500 mt-1">
                                    Active until {{ \Carbon\Carbon::parse($subscription->subscription_end)->format('F j, Y') }}
                                </p>
                                @else
                                <p class="text-sm text-slate-500 mt-1">
                                    Free Access
                                </p>
                                @endif

                                <p class="mt-4 text-xs text-slate-500 leading-snug">
                                    Note: Downgrading to a lower plan will not include any refund for the remaining time on your current
                                    subscription.
                                </p>
                            </div>

                            @php
                            $isDisabled = $subscription->status === 'cancelled'
                            || $subscription->status === 'expired'
                            || strtolower($subscription->planDetails->plan_title) === 'basic';
                            @endphp

                            <div class="mt-4 flex justify-center gap-3">
                                <a href="{{ route('owner.upgrade') }}"
                                    class="inline-flex items-center px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                                    Change Plan
                                </a>

                                <button id="cancelSubscriptionBtn"
                                    class="inline-flex items-center px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition
        {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ $isDisabled ? 'disabled' : '' }}>
                                    Cancel Subscription
                                </button>
                            </div>


                        </div>
                    </div>


                    @endif

                    <div class="mt-2 mb-3 flex items-center gap-6">
                        <a href="{{ route('owner.show.staff') }}"
                            class="flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 underline text-sm font-medium">
                            <span class="material-symbols-rounded text-base">group</span>
                            View Staff List
                        </a>

                        <a href="{{ route('billing.history2') }}"
                            class="flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 underline text-sm font-medium">
                            <span class="material-symbols-rounded text-base">receipt_long</span>
                            Billing History
                        </a>
                    </div>


                </div>


                <div class="flex items-center gap-2 pt-2">
                    <button type="button" id="editProfileButton" class="px-4 py-2 border border-slate-300 text-sm font-semibold rounded-lg shadow-sm text-slate-700 bg-white hover:bg-slate-50 focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-colors duration-200">Edit Profile</button>
                    <button type="button" id="togglePasswordSection" class="px-4 py-2 border border-slate-300 text-sm font-semibold rounded-lg shadow-sm text-slate-700 bg-white hover:bg-slate-50 focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-colors duration-200">Change Password</button>
                    <div x-data="{ showPopup: false }" class="flex items-center gap-3 relative">
                        <div x-show="showPopup"
                            x-transition
                            class="absolute bottom-full mb-2 left-1/2 -translate-x-3/4 
                                    bg-yellow-100 border border-yellow-400 text-yellow-800 
                                    text-xs rounded-lg px-3 py-2 shadow-lg z-50 whitespace-nowrap"
                            style="display: none;">
                            {{ $staffLimitReached }}
                        </div>
                        <button x-on:click="
                                @if($staffReached)
                                    showPopup = true;
                                    setTimeout(() => showPopup = false, 3500);
                                @endif
                            " type="button" id="toggleStaffCreationSection" class="px-4 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-offset-1 focus:ring-green-500 transition-colors duration-200">Create Staff</button>
                    </div>
                </div>

            </div>

            {{-- Profile Details Form --}}
            <div class="pt-6 space-y-4">
                <form id="ownerDetailsForm" method="POST" action="{{ route('owner.profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">

                        {{-- Store Name --}}
                        <div>
                            <label for="store_name" class="block text-sm font-medium text-slate-700 mb-1">
                                Store Name
                            </label>
                            <input type="text"
                                name="store_name"
                                id="store_name"
                                value="{{ old('store_name', $owner->store_name ?? '') }}"
                                class="block w-full rounded-lg border-slate-300 shadow-sm
                   focus:border-indigo-500 focus:ring-indigo-500
                   disabled:bg-slate-50 disabled:text-slate-500 text-base"
                                disabled>
                        </div>

                        {{-- Contact Number --}}
                        <div>
                            <label for="contact" class="block text-sm font-medium text-slate-700 mb-1">
                                Contact Number
                            </label>
                            <input type="text"
                                name="contact"
                                id="contact"
                                value="{{ old('contact', $owner->contact ?? '') }}"
                                class="block w-full rounded-lg border-slate-300 shadow-sm
                   focus:border-indigo-500 focus:ring-indigo-500
                   disabled:bg-slate-50 disabled:text-slate-500 text-base"
                                disabled>
                        </div>

                        {{-- Store Address --}}
                        <div>
                            <label for="store_address" class="block text-sm font-medium text-slate-700 mb-1">
                                Store Address
                            </label>
                            <input type="text"
                                name="store_address"
                                id="store_address"
                                value="{{ old('store_address', $owner->store_address ?? '') }}"
                                class="block w-full rounded-lg border-slate-300 shadow-sm
                   focus:border-indigo-500 focus:ring-indigo-500
                   disabled:bg-slate-50 disabled:text-slate-500 text-base"
                                disabled>
                        </div>

                        {{-- TIN Number (locked) --}}
                        <div>
                            <label for="tin_number" class="block text-sm font-medium text-slate-700 mb-1">
                                TIN Number
                            </label>
                            <input type="text"
                                id="tin_number"
                                value="{{ $owner->tin_number ?? '—' }}"
                                class="block w-full rounded-lg border-slate-300 bg-slate-100
                   text-slate-600 text-base cursor-not-allowed"
                                disabled>

                           
                        </div>

                    </div>

                    <div class="text-right mt-6">
                        <button type="submit" id="saveProfileDetailsButton" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-colors duration-200 hidden">Save Profile Details</button>
                    </div>
                </form>
            </div>

            {{-- Password Change Section --}}
            <div id="passwordSection" class="hidden mt-6 pt-6 border-t border-slate-200">
                <form id="passwordForm" method="POST" action="{{ route('owner.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Current Password *</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="current_password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10 password-input-field text-base">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button">
                                    <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password *</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10 password-input-field text-base">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button">
                                    <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password *</label>
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

            {{-- Staff Creation Section --}}
            @if(!$staffReached)
            <div id="staffCreationSection" class="hidden mt-6 pt-6 border-t border-slate-200">
                <form id="staffCreationForm" method="POST" action="{{ route('owner.add.staff') }}" class="space-y-4">
                    @csrf
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Create New Staff Account</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label for="staff_firstname" class="block text-sm font-medium text-slate-700 mb-1">First Name *</label>
                            <input type="text" name="firstname" id="staff_firstname" value="{{ old('firstname') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                        </div>
                        <div>
                            <label for="staff_middlename" class="block text-sm font-medium text-slate-700 mb-1">Middle Name</label>
                            <input type="text" name="middlename" id="staff_middlename" value="{{ old('middlename') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                        </div>
                        <div>
                            <label for="staff_lastname" class="block text-sm font-medium text-slate-700 mb-1">Last Name *</label>
                            <input type="text" name="lastname" id="staff_lastname" value="{{ old('lastname') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                        </div>
                        <div>
                            <label for="staff_email" class="block text-sm font-medium text-slate-700 mb-1">Email Address *</label>
                            <input type="email" name="email" id="staff_email" value="{{ old('email') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                        </div>
                        <div>
                            <label for="staff_contact" class="block text-sm font-medium text-slate-700 mb-1">Contact Number *</label>
                            <input type="text" name="contact" id="staff_contact" value="{{ old('contact') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                        </div>
                        <div>
                            <label for="staff_password" class="block text-sm font-medium text-slate-700 mb-1">Password *</label>
                            <div class="relative">
                                <input type="password" name="password" id="staff_password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10 password-input-field text-base">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button">
                                    <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                                </button>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="staff_password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm Password *</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="staff_password_confirmation" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10 password-input-field text-base">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 password-toggle-button">
                                    <span class="material-symbols-rounded password-eye-icon hidden text-lg">visibility_off</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="text-right pt-2">
                        <button type="submit" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-offset-1 focus:ring-green-500 transition-colors duration-200">
                            Create Staff
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Section Elements
        const passwordSection = document.getElementById('passwordSection');
        const staffCreationSection = document.getElementById('staffCreationSection');
        const ownerDetailsForm = document.getElementById('ownerDetailsForm');
        const passwordForm = document.getElementById('passwordForm');
        const staffCreationForm = document.getElementById('staffCreationForm');

        // Profile header elements
        const profileName = document.querySelector('h2');
        const profileEmail = document.querySelector('p.text-sm.text-slate-500');

        // Buttons
        const togglePasswordSectionButton = document.getElementById('togglePasswordSection');
        const toggleStaffCreationSectionButton = document.getElementById('toggleStaffCreationSection');
        const editProfileButton = document.getElementById('editProfileButton');
        const saveProfileDetailsButton = document.getElementById('saveProfileDetailsButton');

        // Profile inputs
        const detailInputs = ownerDetailsForm.querySelectorAll('input[type="text"]');
        let isDetailsEditMode = false;

        // Button classes
        const secondaryBtnClasses = ['bg-white', 'text-slate-700', 'border-slate-300', 'hover:bg-slate-50'];
        const destructiveBtnClasses = ['bg-red-600', 'text-white', 'border-transparent', 'hover:bg-red-700'];
        const primaryGreenBtnClasses = ['bg-green-600', 'text-white', 'border-transparent', 'hover:bg-green-700'];

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
            staffCreationSection.classList.add('hidden');

            togglePasswordSectionButton.textContent = 'Change Password';
            togglePasswordSectionButton.classList.remove(...destructiveBtnClasses);
            togglePasswordSectionButton.classList.add(...secondaryBtnClasses);

            toggleStaffCreationSectionButton.textContent = 'Create Staff';
            toggleStaffCreationSectionButton.classList.remove(...destructiveBtnClasses);
            toggleStaffCreationSectionButton.classList.add(...primaryGreenBtnClasses);
        };

        // Toggle profile edit mode
        const setDetailsEditMode = (enable) => {
            detailInputs.forEach(input => input.disabled = !enable);
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
                detailInputs.forEach(input => input.value = input.defaultValue);
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

        toggleStaffCreationSectionButton.addEventListener('click', function() {
            const isHidden = staffCreationSection.classList.contains('hidden');
            if (isDetailsEditMode) setDetailsEditMode(false);
            closeAllSections();
            if (isHidden) {
                staffCreationSection.classList.remove('hidden');
                this.textContent = 'Hide Section';
                this.classList.remove(...primaryGreenBtnClasses);
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
        [ownerDetailsForm, passwordForm, staffCreationForm].forEach(form => {
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
                            // Reset form only if Staff creation is successful
                            if (form === staffCreationForm) form.reset();
                            if (form === ownerDetailsForm && data.owner) {
                                ['store_name', 'contact', 'store_address'].forEach(id => {
                                    const input = document.getElementById(id);
                                    input.value = data.owner[id];
                                    input.defaultValue = data.owner[id];
                                });
                                profileName.textContent = `${data.owner.firstname} ${data.owner.middlename ?? ''} ${data.owner.lastname}`;
                                profileEmail.textContent = data.owner.email;
                                setDetailsEditMode(false);
                            }
                            if (form === passwordForm) form.reset();
                        }

                        // Show errors as toast, but do NOT reset staff form
                        if (data.errors) {
                            Object.values(data.errors).forEach(errArr => errArr.forEach(err => showToast(err, "error")));
                        }

                        if (data.info) showToast(data.info, "info");
                    })
                    .catch(() => showToast("Something went wrong.", "error"));
            });
        });

        setDetailsEditMode(false);
    });

    document.getElementById('cancelSubscriptionBtn').addEventListener('click', function() {
        if (this.disabled) return; // exit if disabled
        if (!confirm("Are you sure you want to cancel your subscription?")) return;

        fetch("{{ route('owner.subscription.cancel') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    window.location.reload();
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                alert("Something went wrong. Please try again.");
            });
    });
</script>



@endsection