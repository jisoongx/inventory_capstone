<div class="relative z-30" x-data="{ open: @entangle('active').live }">

    <button type="button" @click="open = !open" class="relative grouprounded-full transition-all duration-200"
        wire:click='markAsSeen()'>
        <span class="material-symbols-rounded-notif text-gray-700 group-hover:text-gray-900">notifications_active</span>

        @if ($count == 0)
            <span wire:poll.keep-alive="refreshNotifs"
                class="absolute -top-1 -right-2 bg-transparent text-transparent font-bold rounded-full h-4 w-4 flex items-center justify-center">
                {{ $count }}
            </span>
        @else
            <span 
                wire:poll.keep-alive="refreshNotifs"
                class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-lg">
                {{ $count }}
            </span>
        @endif
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.away="open = false"
        class="absolute right-0 top-full mt-3 w-[28rem] bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden z-50">
        
        <div class="flex items-center justify-between p-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <div class="flex items-center gap-2">
                <span class="material-symbols-rounded text-gray-700">notifications</span>
                <span class="text-base font-semibold text-gray-900">Notifications</span>
                @if ($count > 0)
                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $count }}</span>
                @endif
            </div>
            <div class="flex gap-1">
                <button type="button" wire:click="showAll" class="text-xs font-medium px-3 py-1.5 rounded-lg transition-colors duration-200 {{ $activeTab === 'all' ? 'bg-gray-200 text-gray-900' : 'hover:bg-gray-100 text-gray-600' }}">All</button>
                <button type="button" wire:click="showUnread" class="text-xs font-medium px-3 py-1.5 rounded-lg transition-colors duration-200 {{ $activeTab === 'unread' ? 'bg-gray-200 text-gray-900' : 'hover:bg-gray-100 text-gray-600' }}">Unread</button>
            </div>
        </div>

        <div class="max-h-[28rem] overflow-y-auto">
            <ul class="divide-y divide-gray-100">
                @forelse($notifs as $notif)
                    <li class="p-4 hover:bg-gray-50 transition-all duration-200 cursor-pointer group"
                        wire:click='openModal({{ $notif->notif_id }}, @json($notif->notif_title), @json($notif->notif_message), @json($notif->notif_created_on))'>

                        <div class="flex gap-3">
                            @if ($notif->notif_type === 'system') 
                                <div class="flex-shrink-0">
                                    <div class="p-2.5 rounded-xl bg-gradient-to-br from-green-100 to-emerald-100 shadow-sm group-hover:shadow-md transition-shadow duration-200">
                                        <span class="material-symbols-rounded text-green-600 text-xl">campaign</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <span class="font-semibold text-sm text-gray-900">{{ $notif->notif_title }}</span>
                                        @if($notif->usernotif_is_read == 0)
                                            <div class="flex-shrink-0 rounded-full h-2 w-2 bg-blue-500 mt-1.5"></div>
                                        @endif
                                    </div>
                                    <p class="text-gray-600 text-xs leading-relaxed line-clamp-2 mb-2">{{ $notif->notif_message }}</p>
                                    <span class="text-[11px] text-gray-500 font-medium">{{ \Carbon\Carbon::parse($notif->notif_created_on)->diffForHumans() }}</span>
                                </div>
                            @elseif ($notif->notif_type === 'specific')
                                <div class="flex-shrink-0">
                                    <div class="p-2.5 rounded-xl bg-gradient-to-br from-red-100 to-rose-100 shadow-sm group-hover:shadow-md transition-shadow duration-200">
                                        <span class="material-symbols-rounded text-red-600 text-xl">priority_high</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <span class="font-semibold text-sm text-gray-900">{{ $notif->notif_title }}</span>
                                        @if($notif->usernotif_is_read == 0)
                                            <div class="flex-shrink-0 rounded-full h-2 w-2 bg-blue-500 mt-1.5"></div>
                                        @endif
                                    </div>
                                    <p class="text-gray-600 text-xs leading-relaxed line-clamp-2 mb-3">{{ $notif->notif_message }}</p>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="bg-blue-600 hover:bg-blue-700 px-3 py-1.5 text-white font-medium rounded-lg text-xs transition-colors duration-200 shadow-sm">
                                            Renew
                                        </button>
                                        <button type="button" class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors duration-200">
                                            Dismiss
                                        </button>
                                    </div>
                                    <span class="text-[11px] text-gray-500 font-medium block mt-2">{{ \Carbon\Carbon::parse($notif->notif_created_on)->diffForHumans() }}</span>
                                </div>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="py-16 text-center">
                        <span class="material-symbols-rounded text-gray-300 text-5xl mb-3 block">notifications_off</span>
                        <p class="text-gray-400 text-sm font-medium">No notifications yet</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 flex justify-center items-center bg-black/60 backdrop-blur-sm p-4"
             x-data="{ show: true }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             @click.self="$wire.closeModal()">
            <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 @click.stop>
                
                <div class="flex items-start justify-between p-6 border-b border-gray-200">
                    <div class="flex-1 pr-8">
                        <h3 class="text-xl font-bold text-gray-900">{{ $notifTitle }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ date('F d, Y • g:i A', strtotime($notifDate)) }}</p>
                    </div>
                    <button wire:click="closeModal" class="flex-shrink-0 hover:bg-gray-100 p-2 rounded-full transition-colors duration-200">
                        <span class="material-symbols-rounded text-gray-500">close</span>
                    </button>
                </div>

                <div class="p-6">
                    <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">{{ $notifMessage }}</p>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-gray-500 text-sm">visibility</span>
                        <span class="text-sm text-gray-600">
                            Read by <span class="font-semibold text-gray-900">{{ $notifCountRead->countRead }}</span> {{ $notifCountRead->countRead == 1 ? 'user' : 'users' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>