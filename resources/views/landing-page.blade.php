<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shoplytix</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div>
        <div class="min-h-screen bg-gradient-to-b from-red-50 to-red-100">
            <header class="relative overflow-hidden">
                <div class="absolute inset-0 bg-primary-900/10 z-0"></div>
                <div class="mx-auto p-8 relative z-10">
                    <nav class="flex items-center justify-between mb-16">
                        <div class="flex items-center">
                            <img src="{{ asset('assets/logo.png') }}" class="w-10 h-10 mb-2">
                            <h1 class="ml-2 text-2xl font-bold text-primary-700">Shoplytix</h1>
                        </div>
                        <div class="hidden md:flex space-x-8 items-center">
                            <a href="#features" class="font-medium hover:text-primary-600 transition-colors">Features</a>
                            <a href="#plans" class="font-medium hover:text-primary-600 transition-colors">Pricing</a>
                            <a href="#terms" class="font-medium hover:text-primary-600 transition-colors">Terms of Service</a>
                            <a href="#policy" class="font-medium hover:text-primary-600 transition-colors">Privacy Policy</a>
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('login') }}">
                                <button
                                    class="px-5 py-2 rounded-full border border-primary-500 font-medium hover:bg-primary-50 transition-colors">
                                    Login
                                </button>
                            </a>

                        </div>
                    </nav>
                    <div class="items-center">
                        <div class="text-center">

                            <h2 class="text-4xl md:text-5xl font-bold leading-tight mb-4">
                                Simplify Your Inventory Management
                            </h2>

                            <p class="text-sm text-slate-600 mb-6 max-w-2xl mx-auto">
                                Track, manage and optimize your inventory with our powerful yet intuitive system.
                                Save time, reduce costs, and never run out of stock again.
                            </p>

                            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">

                                <button
                                    class="px-6 py-3 rounded-full border border-slate-300 font-medium hover:bg-slate-50 transition-colors flex items-center justify-center">
                                    <span class="material-symbols-outlined mr-2">play_circle</span> Watch Demo
                                </button>
                            </div>

                            <div id="custom-carousel" class="relative w-full max-w-4xl mx-auto" data-carousel="slide">
                                <!-- Carousel wrapper -->
                                <div class="relative h-64 overflow-hidden rounded-xl">
                                    <!-- Slide 1 (shows first 3 images) -->
                                    <div class="hidden duration-700 ease-in-out flex justify-center items-center space-x-6" data-carousel-item="active">
                                        <img src="https://images.unsplash.com/photo-1633332755192-727a05c4013d"
                                            class="w-40 h-28 object-cover rounded-xl shadow-md opacity-80" />
                                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330"
                                            class="w-56 h-36 object-cover rounded-xl shadow-xl -mt-4 z-10 border-4 border-white" />
                                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde"
                                            class="w-40 h-28 object-cover rounded-xl shadow-md opacity-80" />
                                    </div>

                                    <!-- Slide 2 (shows next 3 images with overlap from previous set) -->
                                    <div class="hidden duration-700 ease-in-out flex justify-center items-center space-x-6" data-carousel-item>
                                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330"
                                            class="w-40 h-28 object-cover rounded-xl shadow-md opacity-80" />
                                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde"
                                            class="w-56 h-36 object-cover rounded-xl shadow-xl -mt-4 z-10 border-4 border-white" />
                                        <img src="https://images.unsplash.com/photo-1589571894960-20bbe2828d0a"
                                            class="w-40 h-28 object-cover rounded-xl shadow-md opacity-80" />
                                    </div>

                                    <!-- Slide 3 (last 3 images, looping back to first) -->
                                    <div class="hidden duration-700 ease-in-out flex justify-center items-center space-x-6" data-carousel-item>
                                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde"
                                            class="w-40 h-28 object-cover rounded-xl shadow-md opacity-80" />
                                        <img src="https://images.unsplash.com/photo-1589571894960-20bbe2828d0a"
                                            class="w-56 h-36 object-cover rounded-xl shadow-xl -mt-4 z-10 border-4 border-white" />
                                        <img src="https://images.unsplash.com/photo-1633332755192-727a05c4013d"
                                            class="w-40 h-28 object-cover rounded-xl shadow-md opacity-80" />
                                    </div>
                                </div>

                                <!-- Indicators -->
                                <div class="absolute bottom-3 left-1/2 z-30 flex space-x-3 -translate-x-1/2">
                                    <button type="button" class="w-3 h-3 rounded-full" aria-current="true" aria-label="Slide 1"
                                        data-carousel-slide-to="0"></button>
                                    <button type="button" class="w-3 h-3 rounded-full" aria-label="Slide 2"
                                        data-carousel-slide-to="1"></button>
                                    <button type="button" class="w-3 h-3 rounded-full" aria-label="Slide 3"
                                        data-carousel-slide-to="2"></button>
                                </div>
                            </div>





                            <!-- Trusted By -->
                            <div class="mt-8 flex justify-center items-center space-x-2">
                                <div class="flex -space-x-2">
                                    <img
                                        src="https://images.unsplash.com/photo-1633332755192-727a05c4013d"
                                        alt="User"
                                        class="w-8 h-8 rounded-full border-2 border-white" />
                                    <img
                                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330"
                                        alt="User"
                                        class="w-8 h-8 rounded-full border-2 border-white" />
                                    <img
                                        src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde"
                                        alt="User"
                                        class="w-8 h-8 rounded-full border-2 border-white" />
                                </div>
                                <span class="text-sm text-slate-600">
                                    Trusted by 10,000+ businesses worldwide
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-slate-50 to-transparent"></div>
            </header>
            <section id="features" class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold mb-4">Powerful Features for Complete Inventory Control</h2>
                    <p class="text-lg text-slate-600 max-w-3xl mx-auto">
                        Everything you need to manage your inventory efficiently, from real-time tracking to
                        advanced analytics.
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary-600">inventory_2</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Real-time Tracking</h3>
                        <p class="text-slate-600">
                            Monitor your inventory levels in real-time and get instant updates on stock movements.
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary-600">landscape</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Barcode Scanning</h3>
                        <p class="text-slate-600">
                            Quickly scan barcodes for efficient inventory updates and product identification.
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary-600">insights</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Advanced Analytics</h3>
                        <p class="text-slate-600">
                            Gain valuable insights with comprehensive reports and data visualization tools.
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary-600">notifications</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Low Stock Alerts</h3>
                        <p class="text-slate-600">
                            Receive automatic notifications when inventory items reach critical levels.
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary-600">devices</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Multi-device Access</h3>
                        <p class="text-slate-600">
                            Access your inventory system from any device, anywhere, at any time.
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary-600">sync</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Integration</h3>
                        <p class="text-slate-600">
                            Seamlessly integrate with your existing e-commerce platforms and accounting software.
                        </p>
                    </div>
                </div>
                <div class="mt-16 bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <div class="p-8 md:p-10 flex flex-col justify-center">
                            <h3 class="text-2xl font-bold mb-4">See Your Inventory in Action</h3>
                            <p class="text-slate-600 mb-6">
                                Our intuitive dashboard gives you a complete overview of your inventory with just a
                                glance. Track stock levels, monitor product performance, and identify trends - all
                                from one central location.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-center">
                                    <span class="material-symbols-outlined text-green-500 mr-2">check_circle</span>
                                    <span>Customizable dashboard views</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="material-symbols-outlined text-green-500 mr-2">check_circle</span>
                                    <span>Interactive data visualizations</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="material-symbols-outlined text-green-500 mr-2">check_circle</span>
                                    <span>Product performance metrics</span>
                                </li>
                            </ul>
                        </div>
                        <div class="relative h-[400px] md:h-auto overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1612538695837-2c7c4bc286dc?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHxpbnZlbnRvcnklMjBkYXNoYm9hcmR8ZW58MHx8fHwxNzU4NDc3MDkwfDA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080"
                                alt="Inventory dashboard visualization"
                                class="absolute inset-0 w-full h-full object-cover transform hover:scale-105 transition-transform duration-700"
                                keywords="inventory dashboard, warehouse management, stock tracking, inventory control system" />
                        </div>
                    </div>
                </div>
            </section>
            <section id="plans" class="py-20 bg-slate-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold mb-4">Choose the Perfect Plan for Your Business</h2>
                        <p class="text-lg text-slate-600 max-w-3xl mx-auto">
                            Our flexible pricing options are designed to accommodate businesses of all sizes.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                        @foreach($plans as $plan)
                        <div
                            class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all
           transform hover:-translate-y-1 overflow-hidden
           flex flex-col h-full">

                            {{-- HEADER --}}
                            <div class="p-6 border-b">
                                <h3 class="text-2xl font-bold text-center">
                                    {{ $plan->plan_title }}
                                </h3>

                                <div class="mt-4 text-center">
                                    <span class="text-4xl font-bold">
                                        ₱{{ number_format($plan->plan_price, 2) }}
                                    </span>

                                    @if($plan->plan_duration_months)
                                    <span class="text-slate-500">
                                        / {{ $plan->plan_duration_months }}
                                        month{{ $plan->plan_duration_months > 1 ? 's' : '' }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- FEATURES --}}
                            <div class="p-6 flex flex-col flex-1">
                                <ul class="space-y-3">

                                    @foreach(explode("\n", $plan->plan_includes) as $feature)
                                    @if(trim($feature))
                                    <li class="flex items-center">
                                        <span class="material-symbols-outlined text-primary-600 mr-2">
                                            check
                                        </span>
                                        <span>{{ trim($feature) }}</span>
                                    </li>
                                    @endif
                                    @endforeach

                                </ul>

                                <a href="{{ route('signup') }}" class="mt-auto">
                                    <button
                                        class="w-full mt-6 px-4 py-2 rounded-lg
               border border-primary-600 text-primary-600
               font-medium hover:bg-primary-50 transition-colors">
                                        Get Started
                                    </button>
                                </a>

                            </div>
                        </div>
                        @endforeach

                    </div>

                    @if($plans->isEmpty())
                    <p class="text-center text-slate-500 mt-10">
                        No plans available at the moment.
                    </p>
                    @endif

                </div>
            </section>

            <section id="testimonials" class="py-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold mb-4">Trusted by Businesses Worldwide</h2>
                        <p class="text-lg text-slate-600 max-w-3xl mx-auto">
                            Hear what our customers have to say about how InvenTrack has transformed their inventory
                            management.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400 flex">
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                </div>
                            </div>
                            <p class="text-slate-600 mb-6">
                                &quot;InvenTrack has completely revolutionized how we manage our warehouse
                                inventory. We&#x27;ve cut down on waste and improved our ordering efficiency by
                                40%.&quot;
                            </p>
                            <div class="flex items-center">
                                <img
                                    src="https://images.unsplash.com/photo-1556741533-e228ee50f8b8?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3MzkyNDZ8MXwxfHNlYXJjaHwxfHxjdXN0b21lcnxlbnwwfHx8fDE3NTg0NzcwOTB8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080"
                                    alt="Customer"
                                    class="w-10 h-10 rounded-full mr-3" />
                                <div>
                                    <h4 class="font-semibold">Sarah Johnson</h4>
                                    <p class="text-sm text-slate-500">Operations Manager, Retail Co.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400 flex">
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                </div>
                            </div>
                            <p class="text-slate-600 mb-6">
                                &quot;The real-time tracking feature has been a game-changer for our business. We
                                now have complete visibility of our inventory across multiple locations.&quot;
                            </p>
                            <div class="flex items-center">
                                <img
                                    src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHw0fHxjdXN0b21lcnxlbnwwfHx8fDE3NTg0NzcwOTB8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080"
                                    alt="Customer"
                                    class="w-10 h-10 rounded-full mr-3" />
                                <div>
                                    <h4 class="font-semibold">Michael Chen</h4>
                                    <p class="text-sm text-slate-500">CEO, TechSupply Inc.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400 flex">
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star</span>
                                    <span class="material-symbols-outlined">star_half</span>
                                </div>
                            </div>
                            <p class="text-slate-600 mb-6">
                                &quot;The analytics provided by InvenTrack have helped us identify slow-moving
                                products and optimize our inventory levels, saving us thousands each month.&quot;
                            </p>
                            <div class="flex items-center">
                                <img
                                    src="https://images.unsplash.com/photo-1590698933947-a202b069a861?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHw1fHxjdXN0b21lcnxlbnwwfHx8fDE3NTg0NzcwOTB8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080"
                                    alt="Customer"
                                    class="w-10 h-10 rounded-full mr-3" />
                                <div>
                                    <h4 class="font-semibold">Amanda Garcia</h4>
                                    <p class="text-sm text-slate-500">Inventory Manager, GlobeDistributors</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="py-16 bg-primary-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2">
                            <div class="p-8 md:p-12">
                                <h2 class="text-3xl font-bold mb-4">
                                    Ready to Transform Your Inventory Management?
                                </h2>
                                <p class="text-lg text-slate-600 mb-6">
                                    Join thousands of businesses that use InvenTrack to streamline their operations
                                    and boost productivity.
                                </p>
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <span class="material-symbols-outlined text-primary-600 mr-3">schedule</span>
                                        <span>Set up in minutes, not days</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="material-symbols-outlined text-primary-600 mr-3">cloud_done</span>
                                        <span>No complicated installation required</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="material-symbols-outlined text-primary-600 mr-3">support_agent</span>
                                        <span>Dedicated support team to help you get started</span>
                                    </div>
                                </div>
                                <div class="mt-8 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                                    <button
                                        class="px-6 py-3 rounded-full bg-primary-600 text-white font-medium shadow-md hover:bg-primary-700 transition-colors">
                                        Start Free Trial
                                    </button>
                                    <button
                                        class="px-6 py-3 rounded-full border border-slate-300 font-medium hover:bg-slate-50 transition-colors">
                                        Schedule Demo
                                    </button>
                                </div>
                            </div>
                            <div class="relative h-[300px] lg:h-auto overflow-hidden">
                                <img
                                    src="https://images.unsplash.com/photo-1664382953403-fc1ac77073a0?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHx3YXJlaG91c2UlMjB3b3JrZXJ8ZW58MHx8fHwxNzU4NDc3MDkyfDA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080"
                                    alt="Warehouse worker using inventory system"
                                    class="absolute inset-0 w-full h-full object-cover"
                                    keywords="warehouse worker, inventory scanning, barcode scanner, inventory management system" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- TERMS OF SERVICE SECTION -->
            <section id="terms" class="py-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                    <!-- Header -->
                    <div class="text-center mb-16">
                       
                        <h2 class="text-3xl font-bold text-slate-900 mb-4">Terms of Service</h2>
                        <p class="text-slate-600">Last updated: January 2026</p>
                    </div>

                    <!-- Content -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- Left Column -->
                        <div class="space-y-6">

                            <!-- Section 1 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm flex-shrink-0">1</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Acceptance of Terms</h3>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed">
                                    By accessing and using Shoplytix ("Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
                                </p>
                            </div>

                            <!-- Section 2 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0">2</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Use License</h3>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed mb-3">
                                    Permission is granted to temporarily use Shoplytix for personal, non-commercial transitory viewing only. Under this license you may not:
                                </p>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-red-500 text-lg mt-0.5">close</span>
                                        <span>Modify or copy the materials</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-red-500 text-lg mt-0.5">close</span>
                                        <span>Use for commercial purposes</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-red-500 text-lg mt-0.5">close</span>
                                        <span>Reverse engineer any software</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Section 3 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 font-bold text-sm flex-shrink-0">3</span>
                                    <h3 class="text-xl font-semibold text-slate-900">User Accounts</h3>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed mb-3">
                                    When you create an account, you must provide accurate information. You are responsible for:
                                </p>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                                        <span>Maintaining account confidentiality</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                                        <span>Restricting account access</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                                        <span>All activities under your account</span>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">

                            <!-- Section 4 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-pink-100 rounded-lg flex items-center justify-center text-pink-600 font-bold text-sm flex-shrink-0">4</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Subscription & Payment</h3>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed">
                                    Some parts of the Service are billed on a subscription basis. You will be billed in advance on a recurring periodic basis. Billing cycles are set monthly or annually.
                                </p>
                            </div>

                            <!-- Section 5 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600 font-bold text-sm flex-shrink-0">5</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Termination</h3>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed">
                                    We may terminate or suspend your account immediately, without prior notice, for any reason including if you breach the Terms. Upon termination, your right to use the Service will cease.
                                </p>
                            </div>

                            <!-- Section 6 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600 font-bold text-sm flex-shrink-0">6</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Limitation of Liability</h3>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed">
                                    Shoplytix, its directors, employees, partners, or affiliates shall not be liable for any indirect, incidental, special, or consequential damages including loss of profits or data.
                                </p>
                            </div>

                            <!-- Contact Card -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-md p-6 border border-blue-200">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-blue-600 text-xl">info</span>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-1 text-sm">Questions about our Terms?</h4>
                                        <p class="text-slate-600 text-xs">
                                            Contact us at <a href="mailto:legal@shoplytix.com" class="text-blue-600 hover:underline font-medium">legal@shoplytix.com</a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </section>

            <!-- PRIVACY POLICY SECTION -->
            <section id="policy" class="py-20 bg-slate-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                    <!-- Header -->
                    <div class="text-center mb-16">
                        
                        <h2 class="text-3xl font-bold text-slate-900 mb-4">Privacy Policy</h2>
                        <p class="text-slate-600">Last updated: January 2026</p>
                    </div>

                    <!-- Content -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- Left Column -->
                        <div class="space-y-6">

                            <!-- Introduction -->
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200 shadow-md">
                                <p class="text-slate-700 text-sm leading-relaxed">
                                    At Shoplytix, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information.
                                </p>
                            </div>

                            <!-- Section 1 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center text-green-600 font-bold text-sm flex-shrink-0">1</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Information We Collect</h3>
                                </div>
                                <p class="text-slate-600 text-sm mb-3">We collect information that you provide directly to us:</p>
                                <div class="space-y-2">
                                    <div class="flex items-start gap-2 p-2 bg-slate-50 rounded-lg">
                                        <span class="material-symbols-outlined text-green-600 text-lg">person</span>
                                        <div>
                                            <h4 class="font-semibold text-slate-900 text-sm">Personal Info</h4>
                                            <p class="text-xs text-slate-600">Name, email, phone number</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2 p-2 bg-slate-50 rounded-lg">
                                        <span class="material-symbols-outlined text-green-600 text-lg">inventory_2</span>
                                        <div>
                                            <h4 class="font-semibold text-slate-900 text-sm">Business Data</h4>
                                            <p class="text-xs text-slate-600">Inventory and transactions</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm flex-shrink-0">2</span>
                                    <h3 class="text-xl font-semibold text-slate-900">How We Use Your Info</h3>
                                </div>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-blue-600 text-lg mt-0.5">check_circle</span>
                                        <span>Provide and maintain our Service</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-blue-600 text-lg mt-0.5">check_circle</span>
                                        <span>Improve user experience</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-blue-600 text-lg mt-0.5">check_circle</span>
                                        <span>Send updates and notifications</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-slate-600 text-sm">
                                        <span class="material-symbols-outlined text-blue-600 text-lg mt-0.5">check_circle</span>
                                        <span>Detect and prevent fraud</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Section 3 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 font-bold text-sm flex-shrink-0">3</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Information Sharing</h3>
                                </div>
                                <p class="text-slate-600 text-sm mb-3">We do not sell your data. We may share only when:</p>
                                <div class="space-y-2">
                                    <div class="flex items-start gap-2 p-2 bg-slate-50 rounded-lg">
                                        <span class="material-symbols-outlined text-purple-600 text-lg">verified_user</span>
                                        <span class="text-sm text-slate-600">With trusted service providers</span>
                                    </div>
                                    <div class="flex items-start gap-2 p-2 bg-slate-50 rounded-lg">
                                        <span class="material-symbols-outlined text-purple-600 text-lg">gavel</span>
                                        <span class="text-sm text-slate-600">When required by law</span>
                                    </div>
                                    <div class="flex items-start gap-2 p-2 bg-slate-50 rounded-lg">
                                        <span class="material-symbols-outlined text-purple-600 text-lg">business</span>
                                        <span class="text-sm text-slate-600">During business transfers</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">

                            <!-- Section 4 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600 font-bold text-sm flex-shrink-0">4</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Data Security</h3>
                                </div>
                                <p class="text-slate-600 text-sm mb-4">We protect your information with:</p>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="text-center p-3 bg-orange-50 rounded-lg border border-orange-200">
                                        <span class="material-symbols-outlined text-2xl text-orange-600 mb-1">lock</span>
                                        <p class="text-xs font-semibold text-slate-900">Encryption</p>
                                    </div>
                                    <div class="text-center p-3 bg-orange-50 rounded-lg border border-orange-200">
                                        <span class="material-symbols-outlined text-2xl text-orange-600 mb-1">cloud_done</span>
                                        <p class="text-xs font-semibold text-slate-900">Secure Storage</p>
                                    </div>
                                    <div class="text-center p-3 bg-orange-50 rounded-lg border border-orange-200">
                                        <span class="material-symbols-outlined text-2xl text-orange-600 mb-1">admin_panel_settings</span>
                                        <p class="text-xs font-semibold text-slate-900">Access Control</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 5 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600 font-bold text-sm flex-shrink-0">5</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Your Rights</h3>
                                </div>
                                <p class="text-slate-600 text-sm mb-3">You have the right to:</p>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg">
                                        <span class="material-symbols-outlined text-teal-600 text-lg">visibility</span>
                                        <span class="text-sm text-slate-700"><strong>Access</strong> your data</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg">
                                        <span class="material-symbols-outlined text-teal-600 text-lg">edit</span>
                                        <span class="text-sm text-slate-700"><strong>Correct</strong> your information</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg">
                                        <span class="material-symbols-outlined text-teal-600 text-lg">delete</span>
                                        <span class="text-sm text-slate-700"><strong>Delete</strong> your account</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg">
                                        <span class="material-symbols-outlined text-teal-600 text-lg">block</span>
                                        <span class="text-sm text-slate-700"><strong>Opt-out</strong> of marketing</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 6 -->
                            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start gap-3 mb-3">
                                    <span class="w-7 h-7 bg-pink-100 rounded-lg flex items-center justify-center text-pink-600 font-bold text-sm flex-shrink-0">6</span>
                                    <h3 class="text-xl font-semibold text-slate-900">Policy Changes</h3>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed">
                                    We may update our Privacy Policy from time to time. We will notify you of changes by posting the new policy on this page and updating the "Last updated" date.
                                </p>
                            </div>

                            <!-- Contact Card -->
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl shadow-md p-6 border border-green-200">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-green-600 text-xl">contact_support</span>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-1 text-sm">Privacy Questions?</h4>
                                        <p class="text-slate-600 text-xs">
                                            Contact us at <a href="mailto:privacy@shoplytix.com" class="text-green-600 hover:underline font-medium">privacy@shoplytix.com</a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </section>
            <footer class="bg-slate-800 text-white py-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div>
                            <div class="flex items-center mb-4">
                                <svg
                                    class="h-8 w-8 text-primary-400"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M4 7V17C4 19.2091 5.79086 21 8 21H16C18.2091 21 20 19.2091 20 17V7M4 7H20M4 7C4 4.79086 5.79086 3 8 3H16C18.2091 3 20 4.79086 20 7M12 12H16M12 16H16M8 12H8.01M8 16H8.01"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </svg>
                                <h1 class="ml-2 text-xl font-bold text-white">InvenTrack</h1>
                            </div>
                            <p class="text-slate-300 mb-4">
                                Simplify your inventory management with our powerful and intuitive system.
                            </p>
                            <div class="flex space-x-4">
                                <a href="#" class="text-slate-300 hover:text-white transition-colors">
                                    <i class="fa-brands fa-facebook"></i>
                                </a>
                                <a href="#" class="text-slate-300 hover:text-white transition-colors">
                                    <i class="fa-brands fa-twitter"></i>
                                </a>
                                <a href="#" class="text-slate-300 hover:text-white transition-colors">
                                    <i class="fa-brands fa-linkedin"></i>
                                </a>
                                <a href="#" class="text-slate-300 hover:text-white transition-colors">
                                    <i class="fa-brands fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Company</h3>
                            <ul class="space-y-2">
                                <li>
                                    <a href="#" class="text-slate-300 hover:text-white transition-colors">About Us</a>
                                </li>
                                <li>
                                    <a href="#" class="text-slate-300 hover:text-white transition-colors">Careers</a>
                                </li>
                                <li>
                                    <a href="#" class="text-slate-300 hover:text-white transition-colors">Blog</a>
                                </li>
                                <li>
                                    <a href="#" class="text-slate-300 hover:text-white transition-colors">Press</a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Resources</h3>
                            <ul class="space-y-2">
                                <li>
                                    <a href="#" class="text-slate-300 hover:text-white transition-colors">Documentation</a>
                                </li>
                                <li>
                                    <a href="#" class="text-slate-300 hover:text-white transition-colors">Help Center</a>
                                </li>
                                <li>
                                    <a href="#" class="text-slate-300 hover:text-white transition-colors">API Reference</a>
                                </li>
                                <li>
                                    <a href="#" class="text-slate-300 hover:text-white transition-colors">Community</a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Contact</h3>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <span class="material-symbols-outlined mr-2 text-slate-400">email</span>
                                    <span class="text-slate-300">shoplytix4@gmail.com</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="material-symbols-outlined mr-2 text-slate-400">call</span>
                                    <span class="text-slate-300">+1 (555) 123-4567</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="material-symbols-outlined mr-2 text-slate-400">location_on</span>
                                    <span class="text-slate-300">123 Inventory St, San Francisco, CA</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div
                        class="mt-12 pt-8 border-t border-slate-700 flex flex-col md:flex-row justify-between items-center">
                        <p class="text-slate-400 mb-4 md:mb-0">© 2026</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>