@extends('dashboards.owner.owner')

@section('content')
&nbsp;

<div class="flex justify-between items-center mb-6 mx-8">
    <h1 class="text-xl font-semibold text-slate-800">Manage Staff Accounts</h1>
    <a href="{{ route('owner.profile') }}" class="text-blue-600 hover:text-blue-800 underline text-sm">Back to Profile</a>
</div>

{{-- AJAX success message box --}}
<div id="ajaxSuccessMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded-lg relative mb-3 mx-8 max-w-[calc(100%-2rem)]" role="alert">
    <span id="ajaxSuccessText" class="block sm:inline text-sm"></span>
</div>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 mx-8">
    <div class="flex-1 relative">
        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-[20px]">
            search
        </span>
        <input type="text" id="search" placeholder="Search by name or email" autocomplete="off"
            class="w-full p-3 pl-11 text-sm text-gray-800 border border-gray-300 rounded-md bg-white focus:ring-2  focus:border-indigo-500 shadow-md transition-all duration-200 ease-in-out">
    </div>

    <div class="relative w-full sm:w-[220px]">
        <select id="statusFilter" class="appearance-none w-full p-3 pl-4 pr-10 text-sm text-gray-600 border border-gray-300 rounded-md focus:ring-gray-300 focus:border-indigo-500 shadow-md transition-all duration-200 ease-in-out">
            <option disabled selected value="">Filter by Status</option>
            <option value="">All</option>
            <option value="Active">Active</option>
            <option value="Deactivated">Deactivated</option>
        </select>
    </div>
</div>

@if ($staffMembers->isEmpty())
<p class="text-gray-600 text-sm text-center py-8">You haven't added any staff members yet.</p>
@else
<div class="overflow-y-auto bg-white shadow-md rounded mx-8 max-h-[420px]">
    <table class="min-w-full text-sm text-center text-gray-700">
        <thead class="bg-blue-100 uppercase text-gray-700 sticky top-0 z-10">
            <tr>
                <th scope="col" class="px-6 py-3 text-left font-semibold">Name</th>
                <th scope="col" class="px-6 py-3 text-center font-semibold">Email</th>
                <th scope="col" class="px-6 py-3 text-center font-semibold">Contact</th>
                <th scope="col" class="px-6 py-3 text-center font-semibold">Status</th>
                <th scope="col" class="px-6 py-3 text-center font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($staffMembers as $staff)
            <tr class="hover:bg-gray-50 transition-colors" data-staff-id="{{ $staff->staff_id }}">
                <td class="px-6 py-4 text-left">
                    <div class="text-sm font-medium text-gray-900">{{ $staff->firstname }} {{ $staff->middlename ?? '' }} {{ $staff->lastname }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ $staff->email }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ $staff->contact ?? 'N/A' }}</div>
                </td>
                <td class="px-6 py-4">
                    <form action="{{ route('owner.staff.updateStatus', $staff->staff_id) }}" method="POST" class="inline-block status-form">
                        @csrf
                        @method('PUT')
                        <select name="status" class="status-select block w-auto py-1 px-2 rounded-md shadow-sm text-sm focus:ring-blue-500 {{ $staff->status == 'Active' ? 'bg-green-50 text-green-800 border-green-300' : 'bg-red-50 text-red-800 border-red-300' }}">
                            <option value="Active" {{ $staff->status=='Active'?'selected':'' }}>Active</option>
                            <option value="Deactivated" {{ $staff->status=='Deactivated'?'selected':'' }}>Deactivated</option>
                        </select>
                    </form>
                </td>
                <td class="px-6 py-4 text-center">
                    <button type="button" class="text-blue-600 hover:text-blue-900 inline-flex items-center p-2 rounded-full hover:bg-gray-100 transition-colors duration-150 edit-staff-button"
                        data-staff-id="{{ $staff->staff_id }}"
                        data-firstname="{{ $staff->firstname }}"
                        data-middlename="{{ $staff->middlename ?? '' }}"
                        data-lastname="{{ $staff->lastname }}"
                        data-email="{{ $staff->email }}"
                        data-contact="{{ $staff->contact ?? '' }}">
                        <span class="material-symbols-rounded">edit</span>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Hidden helper to make sure Tailwind includes all color classes --}}
<span class="hidden bg-green-50 text-green-800 border-green-300 bg-red-50 text-red-800 border-red-300"></span>

{{-- Edit Staff Modal --}}
<div id="editStaffModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border w-[700px] shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-800">Edit Staff Details</h3>
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
                        <label for="modal_firstname" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="firstname" id="modal_firstname" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_firstname" class="text-red-500 text-sm mt-0.5 block hidden"></span>
                    </div>
                    <div>
                        <label for="modal_middlename" class="block text-sm font-medium text-gray-700">Middle Name</label>
                        <input type="text" name="middlename" id="modal_middlename" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_middlename" class="text-red-500 text-sm mt-0.5 block hidden"></span>
                    </div>
                    <div>
                        <label for="modal_lastname" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="lastname" id="modal_lastname" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_lastname" class="text-red-500 text-sm mt-0.5 block hidden"></span>
                    </div>
                    <div>
                        <label for="modal_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" id="modal_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_email" class="text-red-500 text-sm mt-0.5 block hidden"></span>
                    </div>
                    <div>
                        <label for="modal_contact" class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <input type="text" name="contact" id="modal_contact" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        <span id="error_contact" class="text-red-500 text-sm mt-0.5 block hidden"></span>
                    </div>
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

        // ---------- Modal logic (same as before) ----------
        const editStaffModal = document.getElementById('editStaffModal');
        const closeEditStaffModalButton = document.getElementById('closeEditStaffModal');
        const cancelEditButton = document.getElementById('cancelEdit');
        const editStaffForm = document.getElementById('editStaffForm');
        const modalFirstname = document.getElementById('modal_firstname');
        const modalMiddlename = document.getElementById('modal_middlename');
        const modalLastname = document.getElementById('modal_lastname');
        const modalEmail = document.getElementById('modal_email');
        const modalContact = document.getElementById('modal_contact');
        const errorFirstname = document.getElementById('error_firstname');
        const errorMiddlename = document.getElementById('error_middlename');
        const errorLastname = document.getElementById('error_lastname');
        const errorEmail = document.getElementById('error_email');
        const errorContact = document.getElementById('error_contact');
        const ajaxSuccessMessage = document.getElementById('ajaxSuccessMessage');
        const ajaxSuccessText = document.getElementById('ajaxSuccessText');

        const clearErrors = () => {
            [errorFirstname, errorMiddlename, errorLastname, errorEmail, errorContact].forEach(el => el.classList.add('hidden'));
        };

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.edit-staff-button');
            if (!button) return;
            clearErrors();
            modalFirstname.value = button.dataset.firstname;
            modalMiddlename.value = button.dataset.middlename;
            modalLastname.value = button.dataset.lastname;
            modalEmail.value = button.dataset.email;
            modalContact.value = button.dataset.contact;
            editStaffForm.action = `/owner/staff/${button.dataset.staffId}`;
            editStaffModal.classList.remove('hidden');
        });

        const closeModal = () => editStaffModal.classList.add('hidden');
        closeEditStaffModalButton.addEventListener('click', closeModal);
        cancelEditButton.addEventListener('click', closeModal);
        editStaffModal.addEventListener('click', e => {
            if (e.target === editStaffModal) closeModal();
        });

        // ---------- Submit edit staff via AJAX ----------
        editStaffForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors();
            const formData = new FormData(editStaffForm);

            fetch(editStaffForm.action, {
                    method: editStaffForm.method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.errors) {
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
                    } else if (data.message && data.staff) {
                        ajaxSuccessText.textContent = data.message;
                        ajaxSuccessMessage.classList.remove('hidden');
                        ajaxSuccessMessage.style.opacity = 1;
                        setTimeout(() => {
                            ajaxSuccessMessage.style.opacity = 0;
                            setTimeout(() => ajaxSuccessMessage.classList.add('hidden'), 500);
                        }, 3000);
                        closeModal();

                        const row = document.querySelector(`tr[data-staff-id="${data.staff.staff_id}"]`);
                        if (row) {
                            row.querySelector('td:nth-child(1) div').textContent = `${data.staff.firstname} ${data.staff.middlename ?? ''} ${data.staff.lastname}`;
                            row.querySelector('td:nth-child(2) div').textContent = data.staff.email;
                            row.querySelector('td:nth-child(3) div').textContent = data.staff.contact ?? 'N/A';
                            const select = row.querySelector('.status-select');
                            select.value = data.staff.status;
                            updateStatusColor(select);
                        }
                    }
                }).catch(err => console.error(err));
        });

        // ---------- Status change via AJAX ----------
        const updateStatusColor = (select) => {
            if (select.value === 'Active') {
                select.className = 'status-select block w-auto py-1 px-2 rounded-md shadow-sm text-sm focus:ring-blue-500 bg-green-50 text-green-800 border-green-300';
            } else {
                select.className = 'status-select block w-auto py-1 px-2 rounded-md shadow-sm text-sm focus:ring-blue-500 bg-red-50 text-red-800 border-red-300';
            }
        };

        document.addEventListener('change', function(e) {
            if (e.target.matches('.status-select')) {
                const select = e.target;
                const form = select.closest('form');
                const action = form.action;
                const formData = new FormData(form);

                fetch(action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.message) {
                            ajaxSuccessText.textContent = data.message;
                            ajaxSuccessMessage.classList.remove('hidden');
                            ajaxSuccessMessage.style.opacity = 1;
                            setTimeout(() => {
                                ajaxSuccessMessage.style.opacity = 0;
                                setTimeout(() => ajaxSuccessMessage.classList.add('hidden'), 500);
                            }, 2500);
                        }
                        updateStatusColor(select);
                    }).catch(err => console.error(err));
            }
        });

        // ---------- Search/filter ----------
        const searchInput = document.getElementById('search');
        const statusFilter = document.getElementById('statusFilter');
        const tableBody = document.querySelector('tbody');

        const fetchFilteredData = () => {
            const query = searchInput.value.trim();
            const status = statusFilter.value;
            fetch(`{{ route('owner.staff.filter') }}?search=${encodeURIComponent(query)}&status=${encodeURIComponent(status)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.staffMembers) renderTableRows(data.staffMembers);
                }).catch(err => console.error(err));
        };

        const renderTableRows = (staffMembers) => {
            tableBody.innerHTML = '';
            if (staffMembers.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="5" class="py-6 text-gray-500 text-sm text-center">No matching staff found.</td></tr>`;
                return;
            }
            staffMembers.forEach(staff => {
                tableBody.insertAdjacentHTML('beforeend', `
            <tr class="hover:bg-gray-50 transition-colors" data-staff-id="${staff.staff_id}">
                <td class="px-6 py-4 text-left"><div class="text-sm font-medium text-gray-900">${staff.firstname} ${staff.middlename ?? ''} ${staff.lastname}</div></td>
                <td class="px-6 py-4"><div class="text-sm text-gray-900">${staff.email}</div></td>
                <td class="px-6 py-4"><div class="text-sm text-gray-900">${staff.contact ?? 'N/A'}</div></td>
                <td class="px-6 py-4">
                    <form action="/owner/staff/${staff.staff_id}/status" method="POST" class="inline-block status-form">
                        @csrf
                        @method('PUT')
                        <select name="status" class="status-select block w-auto py-1 px-2 rounded-md shadow-sm text-sm focus:ring-blue-500 ${staff.status==='Active'?'bg-green-50 text-green-800 border-green-300':'bg-red-50 text-red-800 border-red-300'}">
                            <option value="Active" ${staff.status==='Active'?'selected':''}>Active</option>
                            <option value="Deactivated" ${staff.status==='Deactivated'?'selected':''}>Deactivated</option>
                        </select>
                    </form>
                </td>
                <td class="px-6 py-4 text-center">
                    <button type="button" class="text-blue-600 hover:text-blue-900 inline-flex items-center p-2 rounded-full hover:bg-gray-100 transition-colors duration-150 edit-staff-button"
                        data-staff-id="${staff.staff_id}"
                        data-firstname="${staff.firstname}"
                        data-middlename="${staff.middlename ?? ''}"
                        data-lastname="${staff.lastname}"
                        data-email="${staff.email}"
                        data-contact="${staff.contact ?? ''}">
                        <span class="material-symbols-rounded">edit</span>
                    </button>
                </td>
            </tr>
            `);
            });

            // Make sure colors are correct after rendering
            document.querySelectorAll('.status-select').forEach(select => updateStatusColor(select));
        };

        let debounceTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(fetchFilteredData, 300);
        });
        statusFilter.addEventListener('change', fetchFilteredData);

        // ---------- Make initial table colors correct ----------
        document.querySelectorAll('.status-select').forEach(select => updateStatusColor(select));
    });
</script>
@endsection 