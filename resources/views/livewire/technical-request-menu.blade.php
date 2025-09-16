<div wire:poll.keep-alive.5s>
    <div 
    class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white">
        <span class="nav-label material-symbols-rounded">support_agent</span>
        <span class="nav-label">Technical Support</span>
        @if($countUnread > 0)
            <span class="text-[8px] font-bold bg-red-600 p-1 rounded-xl">NEW</span>
        @endif
    </div>
</div>
