@extends('dashboards.super_admin.super_admin') 

@section('content')


    <div class="flex-1 grid grid-cols-2 gap-4 p-2">

        <div x-data="{ mode: 'custom', selected: '', title: '', message: '' }" 
            class="h-[40rem] bg-white shadow-lg p-6 rounded-lg space-y-5">

            <h2 class="text-sm font-semibold border-b pb-3">Create Notification</h2>

            <form action="{{ route('dashboards.notification.send') }}" method="POST">
                @csrf

                <div class="space-y-1">
                    <label for="recipients" class="block text-xs font-medium text-gray-700 mb-2">Recipients</label>
                    <select id="recipients" name="recipients" 
                        class="w-full border rounded-md p-3 text-xs focus:ring-blue-500 focus:border-blue-500">
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
                            class="w-full border-b rounded p-3 text-xs focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="message" class="block text-xs font-medium text-gray-700 mb-2">Message</label>
                        <textarea id="message" name="message" placeholder="Enter your message" rows="13"
                            class="w-full border rounded p-3 text-xs focus:ring-blue-500 focus:border-blue-500"></textarea>
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
                        <h3 class="text-xs font-medium text-gray-700 mb-2 mt-5">Choose a System Notice</h3>
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
        </div>



    </div>


@endsection