@extends('dashboards.owner.owner') {{-- Extend your owner dashboard layout --}}

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-extrabold text-gray-900">Manage Staff Accounts</h1>
        <a href="{{ route('owner.profile') }}" class="text-blue-600 hover:text-blue-800 underline text-sm">Back to Profile</a>
    </div>

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
    {{-- AJAX success message box (shown via JS) --}}
    <div id="ajaxSuccessMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded-lg relative mb-3" role="alert">
        <span id="ajaxSuccessText" class="block sm:inline text-sm"></span>
    </div>




    @if ($staffMembers->isEmpty())
    <p class="text-gray-600 text-sm text-center py-8">You haven't added any staff members yet.</p>
    @else
    <div class="overflow-x-auto rounded-lg shadow-md border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-200 text-xs font-semibold text-gray-700 tracking-wider text-center">
                <tr>
                    <th scope="col" class="px-6 py-3 text-center text-xs ">
                        Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs ">
                        Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs ">
                        Contact
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs ">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs ">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($staffMembers as $staff)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-sm font-medium text-gray-900">{{ $staff->firstname }} {{ $staff->middlename ?? '' }} {{ $staff->lastname }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-sm text-gray-900">{{ $staff->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-sm text-gray-900">{{ $staff->contact ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <form action="{{ route('owner.staff.updateStatus', $staff->staff_id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()"
                                class="block w-auto py-1 px-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm
                                                @if($staff->status == 'Active') bg-green-50 text-green-800 border-green-300
                                                @elseif($staff->status == 'Inactive') bg-red-50 text-red-800 border-red-300
                                                @else bg-gray-50 text-gray-800 border-gray-300 @endif">
                                <option value="Active" {{ $staff->status == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $staff->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <button type="button" class="text-blue-600 hover:text-blue-900 inline-flex items-center p-2 rounded-full hover:bg-gray-100 transition-colors duration-150 edit-staff-button"
                            data-staff-id="{{ $staff->staff_id }}"
                            data-firstname="{{ $staff->firstname }}"
                            data-middlename="{{ $staff->middlename ?? '' }}"
                            data-lastname="{{ $staff->lastname }}"
                            data-email="{{ $staff->email }}"
                            data-contact="{{ $staff->contact ?? '' }}">
                            <span class="material-symbols-rounded text-lg">edit</span>
                        </button>
                        <form action="{{ route('owner.staff.destroy', $staff->staff_id) }}" method="POST" class="inline-block ml-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 inline-flex items-center p-2 rounded-full hover:bg-gray-100 transition-colors duration-150" onclick="return confirm('Are you sure you want to delete this staff member?');">
                                <span class="material-symbols-rounded text-lg">delete</span>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $staffMembers->links() }} {{-- Pagination links --}}
    </div>
    @endif
</div>


{{-- Edit Staff Modal --}}
<div id="editStaffModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border w-[700px] shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-md font-semibold text-gray-800">Edit Staff Details</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" id="closeEditStaffModal">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>

        <div class="mt-4">
            <form id="editStaffForm" method="POST" action="">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="modal_firstname" class="block text-xs font-medium text-gray-700">First Name</label>
                        <input type="text" name="firstname" id="modal_firstname" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_firstname" class="text-red-500 text-xs mt-0.5 block hidden"></span>
                    </div>

                    <div>
                        <label for="modal_middlename" class="block text-xs font-medium text-gray-700">Middle Name</label>
                        <input type="text" name="middlename" id="modal_middlename" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_middlename" class="text-red-500 text-xs mt-0.5 block hidden"></span>
                    </div>

                    <div>
                        <label for="modal_lastname" class="block text-xs font-medium text-gray-700">Last Name</label>
                        <input type="text" name="lastname" id="modal_lastname" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_lastname" class="text-red-500 text-xs mt-0.5 block hidden"></span>
                    </div>

                    <div>
                        <label for="modal_email" class="block text-xs font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" id="modal_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_email" class="text-red-500 text-xs mt-0.5 block hidden"></span>
                    </div>

                    <div>
                        <label for="modal_contact" class="block text-xs font-medium text-gray-700">Contact Number</label>
                        <input type="text" name="contact" id="modal_contact" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_contact" class="text-red-500 text-xs mt-0.5 block hidden"></span>
                    </div>

                    <!-- Optional: Add another empty div to maintain grid balance -->
                    <div></div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="cancelEdit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-br from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editStaffButtons = document.querySelectorAll('.edit-staff-button');
        const editStaffModal = document.getElementById('editStaffModal');
        const closeEditStaffModalButton = document.getElementById('closeEditStaffModal');
        const cancelEditButton = document.getElementById('cancelEdit');
        const editStaffForm = document.getElementById('editStaffForm');

        // Input fields in the modal
        const modalFirstname = document.getElementById('modal_firstname');
        const modalMiddlename = document.getElementById('modal_middlename');
        const modalLastname = document.getElementById('modal_lastname');
        const modalEmail = document.getElementById('modal_email');
        const modalContact = document.getElementById('modal_contact');

        // Error spans in the modal
        const errorFirstname = document.getElementById('error_firstname');
        const errorMiddlename = document.getElementById('error_middlename');
        const errorLastname = document.getElementById('error_lastname');
        const errorEmail = document.getElementById('error_email');
        const errorContact = document.getElementById('error_contact');

        // Success message elements
        const ajaxSuccessMessage = document.getElementById('ajaxSuccessMessage');
        const ajaxSuccessText = document.getElementById('ajaxSuccessText');

        // Function to clear previous errors
        const clearErrors = () => {
            errorFirstname.classList.add('hidden');
            errorMiddlename.classList.add('hidden');
            errorLastname.classList.add('hidden');
            errorEmail.classList.add('hidden');
            errorContact.classList.add('hidden');
        };

        editStaffButtons.forEach(button => {
            button.addEventListener('click', function() {
                clearErrors(); // Clear errors from previous attempts

                const staffId = this.dataset.staffId;
                const firstname = this.dataset.firstname;
                const middlename = this.dataset.middlename;
                const lastname = this.dataset.lastname;
                const email = this.dataset.email;
                const contact = this.dataset.contact;

                // Populate modal fields
                modalFirstname.value = firstname;
                modalMiddlename.value = middlename;
                modalLastname.value = lastname;
                modalEmail.value = email;
                modalContact.value = contact;

                // Set form action dynamically
                editStaffForm.action = `/owner/staff/${staffId}`;

                editStaffModal.classList.remove('hidden');
            });
        });

        closeEditStaffModalButton.addEventListener('click', function() {
            editStaffModal.classList.add('hidden');
        });

        cancelEditButton.addEventListener('click', function() {
            editStaffModal.classList.add('hidden');
        });

        editStaffModal.addEventListener('click', function(event) {
            if (event.target === editStaffModal) {
                editStaffModal.classList.add('hidden');
            }
        });

        // Submit form via AJAX
        editStaffForm.addEventListener('submit', function(event) {
            event.preventDefault();
            clearErrors();

            const formData = new FormData(editStaffForm);
            const url = editStaffForm.action;
            const method = editStaffForm.method;

            fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.errors) {
                        // Show validation errors
                        if (data.errors.firstname) {
                            errorFirstname.textContent = data.errors.firstname[0];
                            errorFirstname.classList.remove('hidden');
                        }
                        if (data.errors.middlename) {
                            errorMiddlename.textContent = data.errors.middlename[0];
                            errorMiddlename.classList.remove('hidden');
                        }
                        if (data.errors.lastname) {
                            errorLastname.textContent = data.errors.lastname[0];
                            errorLastname.classList.remove('hidden');
                        }
                        if (data.errors.email) {
                            errorEmail.textContent = data.errors.email[0];
                            errorEmail.classList.remove('hidden');
                        }
                        if (data.errors.contact) {
                            errorContact.textContent = data.errors.contact[0];
                            errorContact.classList.remove('hidden');
                        }
                    } else if (data.message) {
                        // Show custom success message
                        ajaxSuccessText.textContent = data.message;
                        ajaxSuccessMessage.classList.remove('hidden');

                        // Close modal
                        editStaffModal.classList.add('hidden');

                        // Auto-hide message
                        setTimeout(() => {
                            ajaxSuccessMessage.classList.add('hidden');
                        }, 3000);

                        // Reload the page after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        });
    });
</script>

@endsection