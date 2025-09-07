<div class="relative z-30">
    
    <button type="button" wire:click="togglePanel" class="relative">
        <span class="material-symbols-rounded">notifications_active</span>

        @if ($count > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center">
                {{ $count }}
            </span>
        @endif
    </button>

    <div @if(!$active) style="display: none;" @endif
        class="absolute right-0 top-full mt-2 w-96 bg-white border rounded-xl shadow-lg h-96 overflow-y-auto z-50 transition-all duration-300">

        <div class="flex items-center justify-between p-4 border-b">
            <span class="text-sm font-semibold">Notifications</span>
            <div class="flex space-x-2">
                <button type="button" wire:click="showAll" class="text-xs hover:bg-gray-200 p-1 px-2 rounded">All</button>
                <button type="button" wire:click="showUnread" class="text-xs hover:bg-gray-200 p-1 px-2 rounded">Unread</button>
            </div>
        </div>

        <ul class="text-xs">
            @forelse($notifs as $notif)
                <li class="py-5 px-5 hover:bg-slate-100 transition border-gray-300 border-b select-none"
                    wire:click='openModal( {{ $notif->notif_id }}, @json($notif->notif_title), @json($notif->notif_message), @json($notif->notif_created_on))'>

                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-rounded p-3 rounded-full bg-green-100 text-green-500 mt-1 shadow">campaign</span>

                            <div class="flex-1">
                                <span class="font-semibold text-xs text-gray-800">{{ $notif->notif_title }}</span>
                                <span class="text-[10px] text-gray-500 ml-2">{{ \Carbon\Carbon::parse($notif->notif_created_on)->diffForHumans() }}</span>
                                <p class="text-gray-700 text-xs mt-1 leading-relaxed line-clamp-2">{{ $notif->notif_message }}</p>
                            </div>
                        </div>


                        <div class="flex-shrink-0 ml-3 mt-1">
                            <div class="rounded-full h-2 w-2
                                @if($notif->usernotif_is_read == 0) bg-green-600
                                @else bg-transparent
                                @endif">
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-3 text-center text-gray-500 text-xs mt-3">Nothing to show...</li>
            @endforelse
        </ul>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex justify-center items-center bg-black/50">
        <div class="bg-white rounded-xl p-5 w-[50rem] relative">
            <button wire:click="closeModal" class="absolute top-4 right-5">
                <span class="material-symbols-rounded-small p-1 rounded-full bg-gray-200">close_small</span>
            </button>
            <div class="border-b border-gray-300 pb-3">
                <h3 class="text-lg font-semibold">{{ $notifTitle }}</h3>
            </div>
            <p class="mt-5 text-sm text-gray-800 whitespace-pre-line">{{ $notifMessage }}</p>
            <p class="mt-4 text-xs text-gray-500">{{ date('F d • g:i A', strtotime($notifDate)) }}</p>

            <div class="border-t border-b border-gray-300 py-2 mt-4">
                <span class="text-xs font-medium">Read by {{ $notifCountRead->countRead }} user</span>
            </div>
        </div>
    </div>
    @endif
</div>
