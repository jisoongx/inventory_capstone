<div>

    <div class="flex-1 grid grid-cols-3 gap-4 p-2">
        <div class="h-[40rem] bg-white shadow-lg p-5 rounded-lg col-span-1 flex flex-col">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-lg">Technical Request</span>
                    <button wire:click='addModal()' type="button">
                        <span class="material-symbols-rounded bg-slate-200 rounded-full p-2">
                            edit_square
                        </span>
                    </button>
                </div>
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        search
                    </span>
                    <input type="text" name="search" id="search" placeholder="Search here..."
                        class="border border-gray-300 text-gray-900 text-sm rounded-full focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-3"
                    />
                </div>
            </div>
                 
            <div class="overflow-y-auto mt-3 -z-1" wire:poll.keep-alive.5s>
                @forelse ($requests as $req)
                    <button wire:click="show({{ $req->req_id }})" type="button"
                    wire:key="req-{{ $req->req_id }}"
                    wire:loading.attr="disabled"
                    class="flex items-center py-5 gap-3 hover:bg-slate-50 p-2 rounded-lg w-full {{ $recentreq && $recentreq->req_id === $req->req_id ? 'bg-slate-100' : '' }}">
                        <div class="relative">
                            <span class="material-symbols-rounded text-red-800 bg-red-100 p-3 rounded-full">confirmation_number</span>
                            <span class="
                                absolute bottom-1 right-0.5 w-3 h-3 rounded-full border-2 border-white
                                @if($req->req_status === 'Pending') bg-orange-500 
                                @elseif($req->req_status === 'In Progress') bg-blue-500 
                                @elseif($req->req_status === 'Resolved') bg-green-500 
                                @else bg-gray-400 @endif
                            "></span>
                        </div>
                        <div class="flex flex-col flex-1 min-w-0">
                            <div class="flex  items-center w-full">
                                <span class="font-medium text-red-700 text-sm truncate">{{ ucwords($req->req_title) }}</span>
                                <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                                    {{ \Carbon\Carbon::parse($req->last_message_date)->diffForHumans(null, true, true) }} ago
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 truncate text-left mt-1">{{ $req->req_ticket }}</p>
                        </div>
                        <div>
                            @if (($countUnread[$req->req_id] ?? 0) > 0)
                                <span class="bg-rose-600 h-4 w-4 rounded-full text-white text-[10px] flex items-center justify-center font-semibold mr-2">
                                    {{ $countUnread[$req->req_id] }}
                                </span>
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="flex items-center align-center justify-center mt-20">
                        <span class="text-sm text-gray-500">No requests to show.</span>
                    </div>
                @endforelse
            </div>
        </div>
        
        <div class="h-[40rem] bg-white shadow-lg p-4 rounded-lg col-span-2 flex flex-col relative">
            <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-200">
                    
                    @if(empty($recentreq))
                    <div>
                        <span class="material-symbols-rounded text-transparent bg-transparent p-3 rounded-full">confirmation_number</span>
                    </div>
                    <div><p class="text-sm"></p></div>
                    @else
                    <div>
                        <span class="material-symbols-rounded text-red-800 bg-red-100 p-3 rounded-full">confirmation_number</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ ucwords($recentreq->req_title) }}</p>
                        <p class="text-xs text-gray-400">{{ $recentreq->req_ticket }}</p>
                    </div>
                    @endif
            </div>

            <div id="chat-container"
                class="flex-1 overflow-y-auto flex flex-col-reverse space-y-2 mt-2 p-3 h-full"
                @if($recentreq) wire:poll.keep-alive.5s="refreshConvo({{ $recentreq->req_id }})" @endif>
                @forelse($convos as $msg)
                    @if(!($msg->sender_type === 'super'))
                        <div class="self-end flex flex-col items-end max-w-[60%] min-w-0">
                            <div class="bg-red-500 text-white px-4 py-2 rounded-2xl rounded-br-none shadow text-sm break-all" wire:key="msg-{{ $msg->msg_id }}">
                                {{ $msg->message }}
                            </div>
                            <span class="text-xs text-slate-700 mt-1 mb-2">{{ date('F j, Y \a\t g:i A', strtotime($msg->msg_date)) }}</span>
                        </div>
                    @else
                        <div class="self-start flex flex-col items-start max-w-[60%] min-w-0">
                            <div class="bg-gray-100 text-gray-800 px-4 py-2 rounded-2xl rounded-bl-none shadow text-sm break-all">
                                {{ $msg->message }}
                            </div>
                            <span class="text-xs text-slate-700 mt-1 mb-4">{{ date('F j, Y \a\t g:i A', strtotime($msg->msg_date)) }}</span>
                        </div>
                    @endif
                @empty
                    <div class="flex flex-col justify-center items-center h-full space-y-3">
                        <span class="material-symbols-rounded-big text-gray-400">taunt</span>
                        <span class="text-xs text-gray-500">Choose a conversation to show...</span>
                    </div>
                @endforelse
            </div>
            
            @if ($recentreq && strtolower(trim($recentreq->req_status)) !== "resolved")
                <div class="flex justify-center align-center pb-20">
                    <div class="flex justify-center items-center pb-5">
                        @if($recentreq)
                            @php
                                $statusKey = strtolower(trim($recentreq->req_status ?? ''));
                            @endphp

                            <span 
                                class="py-1 px-3 rounded-full text-xs font-semibold 
                                    @if($statusKey === 'pending') bg-orange-100 text-orange-600 
                                    @elseif($statusKey === 'in progress') bg-blue-100 text-blue-600 
                                    @elseif($statusKey === 'resolved') bg-green-100 text-green-600 
                                    @else bg-gray-100 text-gray-500 
                                    @endif">
                                {{ ucwords($recentreq->req_status ?? 'No Status') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="absolute bottom-2 left-0 w-full px-4">
                    <div class="relative">
                        <textarea wire:model.live="newMessage" :key="$newMessage" class="w-full border border-slate-200 rounded-lg p-3 pr-12 resize-none bg-white text-sm" placeholder="Enter message here..." rows="3"></textarea>
                        <button wire:click="sendMessage({{ $recentreq->req_id }})" type="button" class="absolute right-2 bottom-2">
                            <span class="material-symbols-rounded">send</span>
                        </button>
                    </div>
                </div>
            @elseif ($recentreq && strtolower(trim($recentreq->req_status)) == "resolved")
                <div class="flex justify-center align-center pb-20">
                    <div class="flex justify-center items-center pb-5">
                        @if($recentreq)
                            @php
                                $statusKey = strtolower(trim($recentreq->req_status ?? ''));
                            @endphp

                            <span 
                                class="py-1 px-3 rounded-full text-xs font-semibold 
                                    @if($statusKey === 'pending') bg-orange-100 text-orange-600 
                                    @elseif($statusKey === 'in progress') bg-blue-100 text-blue-600 
                                    @elseif($statusKey === 'resolved') bg-green-100 text-green-600 
                                    @else bg-gray-100 text-gray-500 
                                    @endif">
                                {{ ucwords($recentreq->req_status ?? 'No Status') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="absolute bottom-2 left-0 w-full px-4">
                    <textarea wire:model.defer="newMessage" class="w-full border border-slate-200 rounded-lg p-3 pr-12 resize-none text-sm" placeholder="Enter message here..." rows="3" disabled></textarea>
                </div>
            @endif
        </div>
    </div>

    @if($addModalOpen)
    <div class="fixed inset-0 z-50 flex justify-center items-center bg-black/40">
        <div class="relative p-4 w-full max-w-md max-h-full" wire:click.away="closeModal">
            <div class="relative bg-white rounded shadow-sm pt-12">
                
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2">
                    <img src="{{ asset('assets/technical.png') }}" class="w-20 h-20 rounded-full border-4 border-white">
                </div>

                <div class="flex items-center justify-center p-3">
                    <h3 class="text-sm font-semibold">Technical Request Ticket</h3>
                </div>

                <div class="px-4">
                    <div>
                        <label for="title" class="block text-xs font-medium text-gray-700 mb-2">Title</label>
                        <input wire:model.defer="addRequestTitle" type="text" id="title" name="title" 
                            class="w-full border border-gray-300 rounded px-3 py-2 mb-3 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                            placeholder="Enter message title" required>
                    </div>
                    <div>
                        <label for="body" class="block text-xs font-medium text-gray-700 mb-2">Message</label>
                        <textarea wire:model.defer="addRequestMessage" id="body" name="body" rows="7" class="w-full border border-gray-300 rounded px-3 py-2 mb-3 text-xs resize-none focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter your message..." required></textarea>
                    </div>
                    <div class="flex justify-center pb-4">
                        <button wire:click="addRequest()" type="button" 
                            class="bg-red-700 text-white text-sm px-10 py-2 mb-2 rounded shadow hover:bg-red-800 focus:ring-2 focus:ring-red-400 w-full">
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
</div>
