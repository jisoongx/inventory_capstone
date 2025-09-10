<div class="p-2">  
    <p class="font-semibold text-lg mt-2">Technical Support Panel</p>
    
    <div class="flex items-center mt-5 gap-3">
        
        <div class="flex gap-2 bg-white px-3 py-2 rounded-lg text-xs border-2">
            <button wire:click="filterByStatus('')" type="button"
                class="py-2 px-3 hover:bg-slate-200 rounded-lg">All</button>
            <button wire:click="filterByStatus('Pending')" type="button"
                class="py-2 px-3 hover:bg-slate-200 rounded-lg">Pending</button>
            <button wire:click="filterByStatus('In Progress')" type="button"
                class="py-2 px-3 hover:bg-slate-200 rounded-lg">In Progress</button>
            <button wire:click="filterByStatus('Resolved')" type="button"
                class="py-2 px-3 hover:bg-slate-200 rounded-lg">Resolve</button>
        </div>

        <div>
            <input 
                wire:model.live="searchWord"
                class="py-4 px-5 bg-white w-96 rounded-lg text-xs border-2" 
                placeholder="Search conversation here..."
            />
        </div>
    </div>

    <div class="relative overflow-x-auto mt-5 h-[31rem]">
        <table class="w-full text-xs text-left rtl:text-right p-2">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 sticky top-0 z-10">
                <tr>
                    <th scope="col" class="px-6 py-5 rounded-s-lg">
                        Ticket Number
                    </th>
                    <th scope="col" class="px-6 py-5">
                        Date Submitted
                    </th>
                    <th scope="col" class="px-6 py-5 rounded">
                        Concern
                    </th>
                    <th scope="col" class="px-6 py-5 rounded-e-lg">
                        Status
                    </th>
                </tr>
            </thead>
            <tbody wire:poll.keep-alive="refreshRequests">
                @forelse($request as $req)
                <tr class="bg-white">
                    <td class="px-6 py-3"> 
                        <button class="flex items-center gap-3" type="button"
                        wire:click='openModal( {{ $req->req_id }} )'>
                            <div class="relative">
                                <span class="material-symbols-rounded text-blue-800 bg-blue-100 p-3 rounded-full">confirmation_number</span>
                            </div>
                            <div class="flex flex-col items-start gap-1">
                                <p class="text-xs font-bold">{{ $req->req_ticket }}</p>
                            </div>
                        </button>                
                    </td>
                    <td class="px-6 py-6"> {{ date('F j, Y', strtotime($req->req_date)) }} </td>
                    <td class="px-6 py-6"> {{ $req->req_title }} </td>
                    <td class="px-6 py-6">
                        @php $s = strtolower($req->req_status); @endphp
                        <span class="px-4 py-1.5 rounded-xl text-xs font-semibold
                            @if ($s === 'pending')
                                bg-orange-100 text-orange-700
                            @elseif ($s === 'in progress')
                                bg-blue-100 text-blue-700
                            @elseif (in_array($s, ['resolve','resolved']))
                                bg-green-100 text-green-700
                            @else
                                bg-gray-100 text-gray-700
                            @endif
                        ">
                            {{ $req->req_status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr class="bg-white justify-center">
                    <td colspan="4" class="px-6 py-6 text-center text-gray-500 font-medium"> Nothing to show... </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>



@if($showModal)
<div class="fixed bottom-4 right-4 z-50 flex flex-col bg-white border border-gray-800 rounded-xl shadow-xl w-[26rem] h-[32rem]"
    wire:poll.keep-alive="refreshConversation($ticket->req_id)">
    <div class="flex justify-between items-center p-5 border-b">
        <div class="flex flex-col text-left space-y-1">
            <div class="flex gap-2 text-xs">
                <span class="font-semibold">Ticket ID:</span>
                <p>{{ $ticket->req_ticket }}</p>
            </div>
            <div class="flex gap-2 text-xs">
                <span class="font-semibold">Sent by:</span>
                <p>{{ ucwords($ticket->firstname) }} {{ ucwords($ticket->lastname) }} of {{ strtoupper($ticket->store_name) }}</p>
            </div>
            <div class="flex gap-2 text-xs">
                <span class="font-semibold">Concern:</span>
                <p>{{ $ticket->req_title }}</p>
            </div>
            <!-- <div class="flex gap-2 text-xs">
                <span class="font-semibold">Status:</span>
                <p>{{ $ticket->req_status }}</p>
            </div> -->
        </div>

        <div>
            <div class="relative inline-block text-left">
                <button wire:click="openOption()" class="text-gray-500 hover:text-gray-700">
                    <span class="material-symbols-rounded-small">more_vert</span>
                </button>

                @if($showOption)
                    <div class="absolute right-0 mt-2 w-48 bg-white border border-black rounded shadow-lg z-50 hover:bg-gray-100" wire:click.away="closeOption">
                        <button 
                            wire:click="" 
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 space-x-2"
                        >
                            <span class="material-symbols-rounded-small">bookmark_check</span>
                            <span>Mark as Resolved</span>
                        </button>
                    </div>
                @endif
            </div>


            <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-rounded-small">close</span>
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-3">
        @forelse($convos as $msg)
            @if($msg->sender_type === 'super')
                <div class="self-end flex flex-col items-end max-w-[80%] ml-auto">
                    <div class="bg-red-500 text-white px-4 py-2 rounded-2xl rounded-br-none shadow text-xs">
                        {{ $msg->message }}
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1">
                        {{ date('M j, g:i A', strtotime($msg->msg_date)) }}
                    </span>
                </div>
            @else
                <div class="self-start flex flex-col items-start max-w-[80%]">
                    <div class="bg-gray-100 text-gray-800 px-4 py-2 rounded-2xl rounded-bl-none shadow text-xs">
                        {{ $msg->message }}
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1">
                        {{ date('M j, g:i A', strtotime($msg->msg_date)) }}
                    </span>
                </div>
            @endif
        @empty
            <p class="text-xs text-gray-400 text-center">No messages yet...</p>
        @endforelse
    </div>

    <div class="border-t p-3 flex gap-2">
        <textarea 
            wire:model.defer="newMessage"
            class="flex-1 rounded-lg border px-3 py-2 text-xs focus:outline-none focus:ring focus:ring-red-200" 
            placeholder="Type a message..."></textarea>

        <button wire:click="sendMessage({{ $ticket->req_id }})"
            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-xs">
            Send
        </button>
    </div>
</div>
@endif


</div>
