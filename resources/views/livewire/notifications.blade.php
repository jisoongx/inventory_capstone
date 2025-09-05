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
        class="absolute right-0 mt-2 w-96 bg-white border rounded shadow-xl h-96 overflow-y-auto transition-all duration-300">

        <div class="flex items-center justify-between p-4 border-b">
            <span class="text-sm font-semibold">Notifications</span>
            <div class="flex space-x-2">
                <button type="button" wire:click="showAll" class="text-xs hover:bg-gray-200 p-1 px-2 rounded">All</button>
                <button type="button" wire:click="showUnread" class="text-xs hover:bg-gray-200 p-1 px-2 rounded">Unread</button>
            </div>
        </div>

        <ul class="text-xs">
            @forelse($notifs as $notif)
                <li class="py-5 px-5 hover:bg-gray-100 transition border-gray-300 border-b
                @if($notif->usernotif_is_read == 0) bg-blue-50
                @else bg-white
                @endif">
                    <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <span class="font-semibold text-xs text-gray-800">{{ $notif->notif_title }}</span>
                        <span class="text-[10px] text-gray-500 ml-2">{{ \Carbon\Carbon::parse($notif->notif_created_on)->diffForHumans() }}</span>
                        <p class="text-gray-700 text-xs mt-1 leading-relaxed line-clamp-2">{{ $notif->notif_message }}</p>
                    </div>

                    <div class="flex-shrink-0 ml-3 mt-1">
                        <div class="rounded-full h-3 w-3
                            @if($notif->usernotif_is_read == 0) bg-blue-500
                            @else bg-transparent
                            @endif">
                        </div>
                    </div>
                </div>
                </li>
            @empty
                <li class="py-3 text-center text-gray-500 text-xs">No notifications available at this time.</li>
            @endforelse
        </ul>
    </div>
</div>
