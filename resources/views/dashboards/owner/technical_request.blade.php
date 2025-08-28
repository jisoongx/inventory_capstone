@extends('dashboards.owner.owner') 

@section('content')


    <div class="flex-1 grid grid-cols-3 gap-4 p-2">
        <div class="h-[40rem] bg-white shadow-lg p-5 rounded-lg col-span-1 flex flex-col">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-lg">Technical Request</span>
                    <button data-modal-target="add-modal" data-modal-toggle="add-modal" type="button">
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
                
            <div class="overflow-y-auto mt-3">
                @forelse ($requests as $req)
                    <a href="{{ route('dashboards.owner.technical_request', ['req_id' => $req->req_id]) }}"
                    class="flex items-center py-5 gap-3 hover:bg-slate-100 p-2 rounded-lg">
                        <div class="relative">
                            <span class="material-symbols-rounded text-red-800 bg-red-100 p-3 rounded-full">
                                confirmation_number
                            </span>
                            <span class="
                                absolute bottom-1 right-0.5 w-3 h-3 rounded-full border-2 border-white
                                @if($req->req_status === 'Pending') bg-orange-500 
                                @elseif($req->req_status === 'In Progress') bg-blue-500 
                                @elseif($req->req_status === 'Resolved') bg-green-500 
                                @else bg-gray-400 @endif
                            "></span>
                        </div>
                        <div class="flex flex-col items-start gap-1">
                            <span class="font-medium text-red-700 text-sm">{{ $req->req_ticket }}</span>
                            <p class="text-xs">{{ $req->req_title }}</p>
                        </div>
                    </a>
                @empty
                    <div class="flex items-center align-center justify-center mt-20">
                        <span class="text-sm text-gray-500">No requests to show.</span>
                    </div>
                @endforelse

            </div>
        </div>
        
        <div class="h-[40rem] bg-white shadow-lg p-4 rounded-lg col-span-2 flex flex-col relative">
            <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-200">
                <div>
                    <span class="material-symbols-rounded text-red-800 bg-red-100 p-3 rounded-full">confirmation_number</span>
                </div>
                <div>
                    @if(empty($recentreq))
                        <p class="text-sm">No request ticket to show</p>
                    @else
                        <p class="text-sm font-semibold text-gray-800">{{ $recentreq->req_title }}</p>
                        <p class="text-xs text-gray-400">{{ $recentreq->req_ticket }}</p>
                    @endif
                </div>
            </div>

            <div id="chat-container" class="flex-1 overflow-y-auto flex flex-col space-y-2 mt-2 p-3">
                @forelse($convos as $msg)
                    @if($msg->sender_type === 'owner')
                        <div class="self-end flex flex-col items-end max-w-[60%]">
                            <div class="bg-red-500 text-white px-4 py-2 rounded-2xl rounded-br-none shadow text-sm">
                                {{ $msg->message }}
                            </div>
                            <span class="text-xs text-slate-700 mt-1">{{ date('F j, Y \a\t g:i A', strtotime($msg->msg_date)) }}</span>
                        </div>
                    @else
                        <div class="self-start flex flex-col items-start max-w-[60%]">
                            <div class="bg-gray-100 text-gray-800 px-4 py-2 rounded-2xl rounded-bl-none shadow text-sm">
                                {{ $msg->message }}
                            </div>
                            <span class="text-xs text-slate-700 mt-1">{{ date('F j, Y \a\t g:i A', strtotime($msg->msg_date)) }}</span>
                        </div>
                    @endif
                @empty
                    <div class="px-3 py-1 rounded-full text-xs font-semibold">
                    </div>
                @endforelse

                <div class="flex mt-3 justify-center align-center pb-24">
                   @php
                        $statusColors = [
                            'pending'     => 'bg-orange-100 text-orange-600',
                            'in progress' => 'bg-blue-100 text-blue-600',
                            'resolved'    => 'bg-green-100 text-green-600',
                        ];
                    @endphp

                    <div class="flex mt-3 justify-center items-center pb-16">
                        @if($recentreq)
                            @php
                                $statusKey  = strtolower(trim($recentreq->req_status ?? ''));
                                $badgeClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-500';
                                $badgeLabel = $recentreq->req_status ? strtolower($recentreq->req_status) : 'no status';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                {{ ucwords($badgeLabel) }}
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                No messages to show
                            </span>
                        @endif
                    </div>

                </div>
            </div>
            
            @if ($recentreq && $recentreq->req_status != "Resolved")
                <div class="absolute bottom-2 left-0 w-full px-4">
                    <div class="relative">
                        <form action="{{ route('dashboards.owner.technical_insert', ['req_id' => $recentreq->req_id]) }}" method="POST">
                            @csrf
                            <textarea name="message" class="w-full border border-slate-200 rounded-lg p-3 pr-12 resize-none bg-white text-sm" placeholder="Enter message here..." rows="3"></textarea>
                            <button type="submit" class="absolute right-2 bottom-2">
                                <span class="material-symbols-rounded">send</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="absolute bottom-2 left-0 w-full px-4">
                    <textarea class="w-full border border-slate-200 rounded-lg p-3 pr-12 resize-none text-sm" placeholder="Enter message here..." rows="3" disabled></textarea>
                </div>
            @endif
        </div>
    </div>

    <div id="add-modal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded shadow-sm pt-12">
                
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2">
                    <img src="{{ asset('assets/technical.png') }}" class="w-20 h-20 rounded-full border-4 border-white">
                </div>

                <div class="flex items-center justify-center p-3">
                    <h3 class="text-sm font-semibold">Technical Request Ticket</h3>
                </div>

                <div class="px-5">
                    <form action="{{ route('dashboards.owner.technical_add') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="title" class="block text-xs font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" id="title" name="title" 
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                                placeholder="Enter message title" required>
                        </div>
                        <div>
                            <label for="body" class="block text-xs font-medium text-gray-700 mb-2">Message</label>
                            <textarea id="body" name="body" rows="7" class="w-full border border-gray-300 rounded px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter your message..." required></textarea>
                        </div>
                        <div class="flex justify-center pb-4">
                            <button type="submit" 
                                class="bg-red-700 text-white text-sm px-10 py-2 rounded shadow hover:bg-red-800 focus:ring-2 focus:ring-red-400 w-full">
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let chatContainer = document.getElementById("chat-container");
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>

@endsection