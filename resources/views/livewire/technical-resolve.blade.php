<div class="p-2">  
    <p class="font-semibold text-lg mt-2">Technical Support Panel</p>
    
    <div class="flex items-center mt-5 gap-3">
        
        <div class="flex gap-2 bg-white px-3 py-2 rounded-lg text-xs border-2">
            <button wire:click="filterByStatus('Pending')" type="button"
                class="py-2 px-3 hover:bg-slate-200 rounded-lg">Pending</button>
            <button wire:click="filterByStatus('In Progress')" type="button"
                class="py-2 px-3 hover:bg-slate-200 rounded-lg">In Progress</button>
            <button wire:click="filterByStatus('Resolved')" type="button"
                class="py-2 px-3 hover:bg-slate-200 rounded-lg">Resolve</button>
        </div>


        <div class="">
            <input 
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
                        Issue
                    </th>
                    <th scope="col" class="px-6 py-5 rounded-e-lg">
                        Status
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($request as $req)
                <tr class="bg-white">
                    <td class="px-6 py-3"> 
                        <button class="flex items-center gap-3" type="button">
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
                                bg-green-100 text-green-700
                            @elseif (in_array($s, ['resolve','resolved']))
                                bg-blue-100 text-blue-700
                            @else
                                bg-gray-100 text-gray-700
                            @endif
                        ">
                            {{ $req->req_status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr class="bg-white">
                    <td class="px-6 py-6"> Nothing to show... </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
