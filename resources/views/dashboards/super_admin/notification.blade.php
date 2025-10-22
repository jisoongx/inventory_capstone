@extends('dashboards.super_admin.super_admin') 

@section('content')


    <div class="flex-1 grid grid-cols-2 gap-4 p-2">

        <div x-data="{ mode: 'custom', title: '', message: '', selected: '' }"
            class="h-[40rem] bg-white shadow-lg p-6 rounded-lg space-y-5">

            <h2 class="text-sm font-semibold border-b pb-3">Create Notification</h2>

            <form action="{{ route('dashboards.notification.send') }}" method="POST">
                @csrf

                <div class="space-y-1">
                    <label for="recipients" class="block text-xs font-medium text-gray-700 mb-2">Recipients</label>
                    <select id="recipients" name="recipients" 
                        class="w-full border border-gray-400 rounded-md p-3 text-xs focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Users</option>
                        <option value="owner">Owner</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                <div class="flex space-x-3 py-2 border-t border-b border-gray-300 mt-5">
                    <button type="button" @click="mode = 'custom'" 
                        :class="mode === 'custom' ? 'bg-red-600 text-white' : ''" 
                        class="flex-1 rounded-md text-xs py-2">
                        Custom Message
                    </button>
                    <button type="button" @click="mode = 'template'" 
                        :class="mode === 'template' ? 'bg-red-600 text-white' : ''" 
                        class="flex-1 rounded-md text-xs">
                        Template
                    </button>
                </div>

                <div x-show="mode === 'custom'" class="space-y-4 mt-5" x-cloak>
                    <div>
                        <label for="title" class="block text-xs font-medium text-gray-700 mb-2">Title</label>
                        <input id="title" name="title" type="text" placeholder="Enter notification title"
                            x-model="title"
                            class="w-full border border-gray-400 rounded p-3 text-xs focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="message" class="block text-xs font-medium text-gray-700 mb-2">Message</label>
                        <textarea id="message" name="message" placeholder="Enter your message" rows="13"
                            x-model="message"
                            class="w-full border border-gray-400 rounded p-3 text-xs focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="flex items-center gap-1 text-xs text-gray-600 hover:text-black font-semibold transition">
                            <span>Send</span>
                            <span class="material-symbols-rounded text-sm">send</span>
                        </button>
                    </div>
                </div>

                <div x-show="mode === 'template'" class="space-y-3" x-cloak>
                    <div x-show="!selected" class="space-y-2" x-cloak>
                        <h3 class="text-xs font-medium text-gray-700 mb-4 mt-5">Choose a System Notice</h3>
                            <div class="grid gap-3">

                                <button type="button"
                                    @click="selected='maintenance'; title='Scheduled Maintenance'; message='Please be informed that the system is scheduled for maintenance on [date/time]. During this period, certain features may not be accessible. We recommend saving your work in advance to avoid any inconvenience.'"
                                    class="w-full text-left p-4 rounded-xl border bg-white shadow-sm hover:shadow-md transition-all hover:border-blue-400 focus:ring-2 focus:ring-blue-300">
                                    <div class="font-medium text-gray-800 text-sm">🛠 Scheduled Maintenance</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Planned downtime for updates or improvements. Click to edit details.
                                    </div>
                                </button>

                                <button type="button"
                                    @click="selected='downtime'; title='Temporary Downtime'; message='We are currently experiencing unexpected technical issues, which may result in temporary downtime. Our technical team is actively working to restore full functionality as quickly and safely as possible. We sincerely apologize for any inconvenience this may cause.'"
                                    class="w-full text-left p-4 rounded-xl border bg-white shadow-sm hover:shadow-md transition-all hover:border-blue-400 focus:ring-2 focus:ring-blue-300">
                                    <div class="font-medium text-gray-800 text-sm">⚠️ Temporary Downtime</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Service interruption due to technical issues. Click to edit details.
                                    </div>
                                </button>

                                <button type="button" 
                                    @click="selected='update'; title='System Update'; message='A new system update has been successfully deployed to improve performance and security. To access the latest features, please log out and sign in again. We encourage you to review the update notes once available.'"
                                    class="w-full text-left p-4 rounded-xl border bg-white shadow-sm hover:shadow-md transition-all hover:border-blue-400 focus:ring-2 focus:ring-blue-300">
                                    <div class="font-medium text-gray-800 text-sm">🔄 System Update</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Recent improvements applied to the platform. Click to edit details.
                                    </div>
                                </button>

                                <button type="button" 
                                    @click="selected='security'; title='Security Alert'; message='For the continued safety of your account, we strongly advise updating your password regularly and enabling two-factor authentication (2FA) where possible. This additional layer of protection helps safeguard your information against unauthorized access.'"
                                    class="w-full text-left p-4 rounded-xl border bg-white shadow-sm hover:shadow-md transition-all hover:border-blue-400 focus:ring-2 focus:ring-blue-300">
                                    <div class="font-medium text-gray-800 text-sm">🔒 Security Alert</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Important account safety reminder. Click to edit details.
                                    </div>
                                </button>
                            </div>
                    </div>

                    <div x-show="selected" class="space-y-4" x-cloak>
                        <div class="flex items-center">
                            <button type="button" @click="selected=''; title=''; message='';" 
                                class="text-xs text-gray-600 hover:text-black">
                                <span class="material-symbols-rounded mr-1">arrow_back</span>
                            </button>
                            <h3 class="text-xs font-medium text-gray-700">Edit Notification</h3>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Title</label>
                                <input type="text" name="title" x-model="title"
                                    class="w-full border rounded p-3 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Message</label>
                                <textarea name="message" x-model="message" rows="11"
                                    class="w-full border rounded p-3 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="flex items-center gap-1 text-xs text-gray-600 hover:text-black font-semibold transition">
                                <span>Send</span>
                                <span class="material-symbols-rounded">send</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @if(session('success'))
                <div 
                    x-data="{ show: true }" 
                    x-init="setTimeout(() => show = false, 4000)" 
                    x-show="show"
                    x-transition:enter="transition transform ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition transform ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="fixed right-5 bottom-8 z-50 flex w-94 items-center space-x-3 border-l-4 border-emerald-600 bg-emerald-50 px-4 py-3 shadow-lg"
                >
                    <div class="">
                        <span class="material-symbols-rounded-full text-green-600 text-lg">check_circle</span>
                    </div>
                    
                    <div class="flex-1">
                        <p class="font-semibold text-green-800">Success!</p>
                        <p class="text-sm text-gray-800">{{ session('success') }}</p>
                    </div>
                    
                    <button @click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-rounded">close_small</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="h-[40rem] bg-white shadow-lg p-6 rounded-lg space-y-2 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                
                <div class="flex justify-between items-center mb-5">
                    <h1 class="text-sm font-semibold text-gray-900">Notification History</h1>
                    <!-- <a href="#"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow transition text-xs">
                        Clear All
                    </a> -->
                    <div>
                        <button type="button" class="text-xs hover:bg-gray-200 p-1 px-2 rounded">All</button>
                        <button type="button" class="text-xs hover:bg-gray-200 p-1 px-2 rounded">Specific</button>
                    </div>
                </div>

                @if($notification->isEmpty())
                    <div class="text-center">
                        <p class="text-gray-400 text-lg">No notifications sent yet.</p>
                    </div>
                @else
                    <div class="relative">
                        <div class="border-l-2 border-gray-300 absolute h-full left-4 top-1"></div>
                            <div class="space-y-3">
                                @foreach($notification as $notif)
                                    <div class="flex items-start relative">
                                        <div class="flex flex-col items-center mr-6">
                                            <div class="w-5 h-5 bg-gray-300 rounded-full mt-1 ml-2"></div>
                                        </div>
                                        <div class="bg-white shadow-lg rounded-xl p-6 w-full hover:shadow-2xl transition">
                                            <div class="flex justify-between items-center mb-2">
                                                <h2 class="font-semibold text-gray-900 text-xs">{{ $notif->notif_title }}</h2>
                                                <span class="text-gray-400 text-xs">{{ date('F d • g:i A', strtotime($notif->notif_created_on)) }}</span>
                                            </div>
                                            <p class="text-gray-700 leading-relaxed line-clamp-3 text-xs">{{ $notif->notif_message }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>


    </div>


@endsection