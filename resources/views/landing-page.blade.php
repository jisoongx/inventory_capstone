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
                                <img src="{{ asset('assets/logo.png') }}" class="w-8 h-8 mb-2">
                                <h1 class="ml-2 text-2xl font-bold text-primary-700">Shoplytix</h1>
                            </div>
                            <div class="hidden md:flex space-x-8 items-center">
                                <a href="#features" class="font-medium hover:text-primary-600 transition-colors"
                                    >Features</a
                                >
                                <a href="#plans" class="font-medium hover:text-primary-600 transition-colors"
                                    >Pricing</a
                                >
                                <a href="#contact" class="font-medium hover:text-primary-600 transition-colors"
                                    >Contact</a
                                >
                            </div>
                            <div class="flex items-center space-x-4">
                                <button
                                    class="px-5 py-2 rounded-full border border-primary-500 font-medium hover:bg-primary-50 transition-colors"
                                >
                                    Login
                                </button>
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
                                        class="px-6 py-3 rounded-full bg-primary-600 text-white font-medium shadow-lg hover:shadow-xl hover:bg-primary-700 transform hover:-translate-y-1 transition-all"
                                    >
                                        Start Free Trial
                                    </button>
                                    <button
                                        class="px-6 py-3 rounded-full border border-slate-300 font-medium hover:bg-slate-50 transition-colors flex items-center justify-center"
                                    >
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
                                            class="w-8 h-8 rounded-full border-2 border-white"
                                        />
                                        <img
                                            src="https://images.unsplash.com/photo-1494790108377-be9c29b29330"
                                            alt="User"
                                            class="w-8 h-8 rounded-full border-2 border-white"
                                        />
                                        <img
                                            src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde"
                                            alt="User"
                                            class="w-8 h-8 rounded-full border-2 border-white"
                                        />
                                    </div>
                                    <span class="text-sm text-slate-600">
                                        Trusted by 10,000+ businesses worldwide
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-slate-50 to-transparent"
                    ></div>
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
                                    keywords="inventory dashboard, warehouse management, stock tracking, inventory control system"
                                />
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
                            <div
                                class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all transform hover:-translate-y-1 overflow-hidden"
                            >
                                <div class="p-6 border-b">
                                    <h3 class="text-2xl font-bold text-center">Basic</h3>
                                    <div class="mt-4 text-center">
                                        <span class="text-4xl font-bold">$29</span>
                                        <span class="text-slate-500">/month</span>
                                    </div>
                                    <p class="mt-2 text-center text-slate-600">
                                        Perfect for small businesses just getting started
                                    </p>
                                </div>
                                <div class="p-6">
                                    <ul class="space-y-3">
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Up to 500 inventory items</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>2 user accounts</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Basic reporting</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Email support</span>
                                        </li>
                                        <li class="flex items-center text-slate-400">
                                            <span class="material-symbols-outlined mr-2">close</span>
                                            <span>Advanced analytics</span>
                                        </li>
                                        <li class="flex items-center text-slate-400">
                                            <span class="material-symbols-outlined mr-2">close</span>
                                            <span>API access</span>
                                        </li>
                                    </ul>
                                    <button
                                        class="w-full mt-6 px-4 py-2 rounded-lg border border-primary-600 text-primary-600 font-medium hover:bg-primary-50 transition-colors"
                                    >
                                        Get Started
                                    </button>
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-xl shadow-lg relative transform hover:-translate-y-1 transition-all overflow-hidden"
                            >
                                <div
                                    class="absolute top-0 right-0 bg-primary-600 text-white px-4 py-1 rounded-bl-lg font-medium"
                                >
                                    Popular
                                </div>
                                <div class="p-6 border-b bg-primary-50">
                                    <h3 class="text-2xl font-bold text-center">Professional</h3>
                                    <div class="mt-4 text-center">
                                        <span class="text-4xl font-bold">$79</span>
                                        <span class="text-slate-500">/month</span>
                                    </div>
                                    <p class="mt-2 text-center text-slate-600">
                                        Ideal for growing businesses with expanding inventory
                                    </p>
                                </div>
                                <div class="p-6">
                                    <ul class="space-y-3">
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Up to 5,000 inventory items</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>10 user accounts</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Advanced reporting</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Priority email support</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Basic analytics</span>
                                        </li>
                                        <li class="flex items-center text-slate-400">
                                            <span class="material-symbols-outlined mr-2">close</span>
                                            <span>API access</span>
                                        </li>
                                    </ul>
                                    <button
                                        class="w-full mt-6 px-4 py-2 rounded-lg bg-primary-600 text-white font-medium hover:bg-primary-700 shadow-md transition-colors"
                                    >
                                        Get Started
                                    </button>
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all transform hover:-translate-y-1 overflow-hidden"
                            >
                                <div class="p-6 border-b">
                                    <h3 class="text-2xl font-bold text-center">Enterprise</h3>
                                    <div class="mt-4 text-center">
                                        <span class="text-4xl font-bold">$199</span>
                                        <span class="text-slate-500">/month</span>
                                    </div>
                                    <p class="mt-2 text-center text-slate-600">
                                        Complete solution for large businesses with complex needs
                                    </p>
                                </div>
                                <div class="p-6">
                                    <ul class="space-y-3">
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Unlimited inventory items</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Unlimited user accounts</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Custom reporting</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>24/7 phone &amp; email support</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>Advanced analytics</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-2">check</span>
                                            <span>API access</span>
                                        </li>
                                    </ul>
                                    <button
                                        class="w-full mt-6 px-4 py-2 rounded-lg border border-primary-600 text-primary-600 font-medium hover:bg-primary-50 transition-colors"
                                    >
                                        Contact Sales
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-12 bg-white p-8 rounded-xl shadow-md">
                            <h3 class="text-xl font-bold mb-4 text-center">Need a custom solution?</h3>
                            <p class="text-center text-slate-600 mb-6">
                                Contact our team for a tailored plan that fits your specific business requirements.
                            </p>
                            <div class="flex justify-center">
                                <button
                                    class="px-6 py-2 rounded-full bg-primary-600 text-white font-medium hover:bg-primary-700 shadow-md transition-colors"
                                >
                                    Contact Us
                                </button>
                            </div>
                        </div>
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
                                        class="w-10 h-10 rounded-full mr-3"
                                    />
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
                                        class="w-10 h-10 rounded-full mr-3"
                                    />
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
                                        class="w-10 h-10 rounded-full mr-3"
                                    />
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
                                            <span class="material-symbols-outlined text-primary-600 mr-3"
                                                >schedule</span
                                            >
                                            <span>Set up in minutes, not days</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-3"
                                                >cloud_done</span
                                            >
                                            <span>No complicated installation required</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="material-symbols-outlined text-primary-600 mr-3"
                                                >support_agent</span
                                            >
                                            <span>Dedicated support team to help you get started</span>
                                        </div>
                                    </div>
                                    <div class="mt-8 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                                        <button
                                            class="px-6 py-3 rounded-full bg-primary-600 text-white font-medium shadow-md hover:bg-primary-700 transition-colors"
                                        >
                                            Start Free Trial
                                        </button>
                                        <button
                                            class="px-6 py-3 rounded-full border border-slate-300 font-medium hover:bg-slate-50 transition-colors"
                                        >
                                            Schedule Demo
                                        </button>
                                    </div>
                                </div>
                                <div class="relative h-[300px] lg:h-auto overflow-hidden">
                                    <img
                                        src="https://images.unsplash.com/photo-1664382953403-fc1ac77073a0?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHx3YXJlaG91c2UlMjB3b3JrZXJ8ZW58MHx8fHwxNzU4NDc3MDkyfDA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080"
                                        alt="Warehouse worker using inventory system"
                                        class="absolute inset-0 w-full h-full object-cover"
                                        keywords="warehouse worker, inventory scanning, barcode scanner, inventory management system"
                                    />
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
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M4 7V17C4 19.2091 5.79086 21 8 21H16C18.2091 21 20 19.2091 20 17V7M4 7H20M4 7C4 4.79086 5.79086 3 8 3H16C18.2091 3 20 4.79086 20 7M12 12H16M12 16H16M8 12H8.01M8 16H8.01"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></path>
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
                                        <a href="#" class="text-slate-300 hover:text-white transition-colors"
                                            >About Us</a
                                        >
                                    </li>
                                    <li>
                                        <a href="#" class="text-slate-300 hover:text-white transition-colors"
                                            >Careers</a
                                        >
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
                                        <a href="#" class="text-slate-300 hover:text-white transition-colors"
                                            >Documentation</a
                                        >
                                    </li>
                                    <li>
                                        <a href="#" class="text-slate-300 hover:text-white transition-colors"
                                            >Help Center</a
                                        >
                                    </li>
                                    <li>
                                        <a href="#" class="text-slate-300 hover:text-white transition-colors"
                                            >API Reference</a
                                        >
                                    </li>
                                    <li>
                                        <a href="#" class="text-slate-300 hover:text-white transition-colors"
                                            >Community</a
                                        >
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Contact</h3>
                                <ul class="space-y-2">
                                    <li class="flex items-center">
                                        <span class="material-symbols-outlined mr-2 text-slate-400">email</span>
                                        <span class="text-slate-300">support@inventrack.com</span>
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
                            class="mt-12 pt-8 border-t border-slate-700 flex flex-col md:flex-row justify-between items-center"
                        >
                            <p class="text-slate-400 mb-4 md:mb-0">© 2023</p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </body>
</html>
