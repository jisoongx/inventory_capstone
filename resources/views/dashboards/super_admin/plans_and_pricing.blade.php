@extends('dashboards.super_admin.super_admin')

@section('content')
<div class="mt-6 space-y-6 px-4 mb-4">

    {{-- INFO BANNER --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="text-sm font-semibold text-blue-900">Live Updates</p>
            <p class="text-sm text-blue-700 mt-0.5">
                Changes here automatically affect the landing and subscription pages.
            </p>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Subscription Plans</h2>
                <p class="text-xs text-gray-500 mt-0.5">Manage and configure your pricing tiers</p>
            </div>

            {{-- ADD PLAN BUTTON --}}
            <button onclick="openCreateModal()"
                class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
                Add Plan
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Features</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($plans as $plan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        {{-- PLAN --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($plan->plan_title, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $plan->plan_title }}</p>
                                   
                                </div>
                            </div>
                        </td>

                        {{-- PRICE --}}
                        <td class="px-6 py-4">
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg font-bold text-slate-900">₱{{ number_format($plan->plan_price, 0) }}</span>
                                @if($plan->plan_duration_months)
                                <span class="text-xs text-gray-500">/mo</span>
                                @endif
                            </div>
                        </td>

                        {{-- DURATION --}}
                        <td class="px-6 py-4">
                            @if($plan->plan_duration_months)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $plan->plan_duration_months }} {{ $plan->plan_duration_months > 1 ? 'months' : 'month' }}
                            </span>
                            @else
                            <span class="text-gray-400 text-sm">—</span>
                            @endif
                        </td>

                        {{-- FEATURES --}}
                        <td class="px-6 py-4">
                            <div class="max-w-md">
                                @php
                                $features = array_filter(array_map('trim', explode("\n", $plan->plan_includes)));
                                $displayFeatures = array_slice($features, 0, 2);
                                $remainingCount = count($features) - 2;
                                @endphp

                                @if(count($features) > 0)
                                <ul class="space-y-1">
                                    @foreach($displayFeatures as $feature)
                                    <li class="flex items-start text-xs text-gray-700">
                                        <svg class="w-3.5 h-3.5 text-green-500 mr-1.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="line-clamp-1">{{ $feature }}</span>
                                    </li>
                                    @endforeach
                                </ul>

                                @if($remainingCount > 0)
                                <button type="button" class="mt-1.5 text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors"
                                    title="{{ $plan->plan_includes }}">
                                    + {{ $remainingCount }} more
                                </button>
                                @endif
                                @else
                                <span class="text-xs text-gray-400">No features listed</span>
                                @endif
                            </div>
                        </td>

                        {{-- STATUS --}}
                        <td class="px-6 py-4 text-center">
                            @if($plan->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                Active
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                                Hidden
                            </span>
                            @endif
                        </td>

                        {{-- ACTIONS --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    onclick='openEditModal(@json($plan))'
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </button>

                                <form action="{{ url('/super-admin/plans-pricing/'.$plan->plan_id.'/toggle') }}"
                                    method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium border rounded-md transition-colors
                                        {{ $plan->is_active ? 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' : 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' }}">
                                        @if($plan->is_active)
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                        </svg>
                                        Disable
                                        @else
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Enable
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16">
                            <div class="text-center">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <h3 class="mt-4 text-base font-semibold text-gray-900">No plans available</h3>
                                <p class="mt-2 text-sm text-gray-500">Get started by creating your first subscription plan.</p>
                                <div class="mt-6">
                                    <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Your First Plan
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div id="editPlanModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4"
    onclick="if(event.target === this) closeModal()">

    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">

        {{-- MODAL HEADER --}}
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-900" id="modalTitle">Add Plan</h3>
                    <p class="text-sm text-gray-500 mt-0.5" id="modalSubtitle">Create a new subscription plan</p>
                </div>
                <button type="button" onclick="closeModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- MODAL BODY --}}
        <form id="editPlanForm" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="_method" value="POST">

            <div class="space-y-5">
                {{-- PLAN TITLE --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Plan Title
                        <span class="text-red-500">*</span>
                    </label>
                    <input id="edit_plan_title" name="plan_title" type="text"
                        class="w-full placeholder-gray-400 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow"
                        placeholder="e.g., Premium Plan"
                        required>
                </div>

                {{-- PRICE & DURATION ROW --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- PRICE --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Price
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">₱</span>
                            <input id="edit_plan_price" name="plan_price"
                                type="number" min="0" step="1"
                                class="w-full placeholder-gray-400 border border-gray-300 rounded-lg pl-8 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow"
                                placeholder="0"
                                required>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Whole numbers only</p>
                    </div>

                    {{-- DURATION --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Duration
                        </label>
                        <div class="relative">
                            <input id="edit_plan_duration" name="plan_duration_months"
                                type="number" min="1"
                                class="w-full placeholder-gray-400 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow"
                                placeholder="e.g., 1, 6, 12">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">months</span>
                        </div>
                    </div>
                </div>

                {{-- FEATURES --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Features
                        <span class="text-red-500">*</span>
                        <span class="text-xs font-normal text-gray-500 ml-1">(one per line)</span>
                    </label>

                    <textarea id="edit_plan_includes" name="plan_includes"
                        rows="6"
                        required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm
           placeholder-gray-400
           focus:ring-2 focus:ring-blue-500 focus:border-blue-500
           transition-shadow font-mono"
                        placeholder="Access to premium content&#10;Priority customer support">
            </textarea>


                    <p class="mt-1.5 text-xs text-gray-500 flex items-start gap-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Enter each feature on a new line. These will be displayed with checkmarks in the table.</span>
                    </p>
                </div>
            </div>

            {{-- FORM ACTIONS --}}
            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                <button type="button" onclick="closeModal()"
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    <span id="submitButtonText">Create Plan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- JS --}}
<script>
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Add Plan';
        document.getElementById('modalSubtitle').innerText = 'Create a new subscription plan';
        document.getElementById('submitButtonText').innerText = 'Create Plan';
        document.getElementById('editPlanForm').action = `/super-admin/plans-pricing`;
        document.querySelector('#editPlanForm input[name="_method"]').value = 'POST';

        ['edit_plan_title', 'edit_plan_price', 'edit_plan_duration', 'edit_plan_includes']
        .forEach(id => document.getElementById(id).value = '');

        showModal();
    }

    function openEditModal(plan) {
        document.getElementById('modalTitle').innerText = 'Edit Plan';
        document.getElementById('modalSubtitle').innerText = 'Update plan details and pricing information';
        document.getElementById('submitButtonText').innerText = 'Save Changes';
        document.getElementById('edit_plan_title').value = plan.plan_title;
        document.getElementById('edit_plan_price').value = plan.plan_price;
        document.getElementById('edit_plan_duration').value = plan.plan_duration_months ?? '';
        document.getElementById('edit_plan_includes').value = plan.plan_includes;

        document.getElementById('editPlanForm').action =
            `/super-admin/plans-pricing/${plan.plan_id}`;
        document.querySelector('#editPlanForm input[name="_method"]').value = 'PUT';

        showModal();
    }

    function showModal() {
        const modal = document.getElementById('editPlanModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Prevent body scroll when modal is open
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('editPlanModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // Restore body scroll
        document.body.style.overflow = '';
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endsection