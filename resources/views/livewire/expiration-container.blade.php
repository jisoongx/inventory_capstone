<div>
    @if ($isExpired)
    <div class="relative border border-red-300 bg-red-50 p-3 rounded-md flex items-center justify-between text-sm group overflow-hidden flex-shrink-0 mt-4">
        <span class="text-red-800">Your subscription has expired. Please renew to continue using all features.</span>
        <a href="{{ route('subscription.selection') }}" class="ml-4 px-3 py-1.5 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 z-5">
            Renew Now
        </a>
        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-red-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 ease-out z-0"></span>
    </div>
    @endif
</div>