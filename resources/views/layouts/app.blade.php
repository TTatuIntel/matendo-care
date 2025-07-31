{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MedMonitor') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="flex items-center">
                                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-heartbeat text-white"></i>
                                </div>
                                <span class="text-xl font-bold text-gray-800">MedMonitor</span>
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            @auth
                                @if(auth()->user()->isAdmin())
                                    @include('layouts.navigation.admin')
                                @elseif(auth()->user()->isDoctor())
                                    @include('layouts.navigation.doctor')
                                @elseif(auth()->user()->isPatient())
                                    @include('layouts.navigation.patient')
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        @auth
                            <!-- Notifications -->
                            <div class="relative mr-4">
                                <button type="button" id="notifications-dropdown" class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-full">
                                    <i class="fas fa-bell h-5 w-5"></i>
                                    @if(auth()->user()->unreadNotifications()->count() > 0)
                                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full flex items-center justify-center">
                                            <span class="text-xs text-white font-medium">{{ auth()->user()->unreadNotifications()->count() }}</span>
                                        </span>
                                    @endif
                                </button>
                                
                                <!-- Notifications Dropdown -->
                                <div id="notifications-menu" class="hidden origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <div class="py-1 max-h-96 overflow-y-auto">
                                        <div class="px-4 py-2 border-b border-gray-100">
                                            <h3 class="text-sm font-medium text-gray-900">Notifications</h3>
                                        </div>
                                        <div id="notifications-list">
                                            <!-- Notifications will be loaded here -->
                                        </div>
                                        <div class="px-4 py-2 border-t border-gray-100">
                                            <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all notifications</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="relative">
                                <button type="button" id="user-menu-button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <div class="h-8 w-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-3 hidden md:block">
                                        <div class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</div>
                                        <div class="text-xs text-gray-500 capitalize">{{ Auth::user()->usertype }}</div>
                                    </div>
                                    <i class="fas fa-chevron-down ml-2 text-xs text-gray-400"></i>
                                </button>

                                <!-- Dropdown Menu -->
                                <div id="user-menu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <div class="py-1">
                                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-user mr-2"></i>Profile
                                        </a>
                                        <a href="{{ route('settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-cog mr-2"></i>Settings
                                        </a>
                                        <div class="border-t border-gray-100"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <i class="fas fa-sign-out-alt mr-2"></i>Log Out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700 mr-4">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Register</a>
                            @endif
                        @endauth
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <i class="fas fa-bars h-6 w-6"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    @auth
                        @if(auth()->user()->isAdmin())
                            @include('layouts.navigation.admin-mobile')
                        @elseif(auth()->user()->isDoctor())
                            @include('layouts.navigation.doctor-mobile')
                        @elseif(auth()->user()->isPatient())
                            @include('layouts.navigation.patient-mobile')
                        @endif
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        @hasSection('header')
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-900">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        @hasSection('header-actions')
                            <div class="flex space-x-3">
                                @yield('header-actions')
                            </div>
                        @endif
                    </div>
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Warning!</strong>
                        <span class="block sm:inline">{{ session('warning') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                <!-- Main Content -->
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                <h3 class="text-lg font-medium text-gray-900 mt-4">Loading...</h3>
                <p class="text-sm text-gray-500 mt-2">Please wait while we process your request.</p>
            </div>
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Custom Scripts -->
    @stack('scripts')
    
    <script>
        // Toggle mobile menu
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Toggle user menu
        document.getElementById('user-menu-button').addEventListener('click', function() {
            document.getElementById('user-menu').classList.toggle('hidden');
        });

        // Toggle notifications menu
        document.getElementById('notifications-dropdown').addEventListener('click', function() {
            const menu = document.getElementById('notifications-menu');
            menu.classList.toggle('hidden');
            if (!menu.classList.contains('hidden')) {
                loadNotifications();
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenu = document.getElementById('user-menu');
            const notificationsButton = document.getElementById('notifications-dropdown');
            const notificationsMenu = document.getElementById('notifications-menu');
            
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
            
            if (!notificationsButton.contains(event.target) && !notificationsMenu.contains(event.target)) {
                notificationsMenu.classList.add('hidden');
            }
        });

        // Load notifications
        function loadNotifications() {
            fetch('/api/notifications/unread')
                .then(response => response.json())
                .then(notifications => {
                    const list = document.getElementById('notifications-list');
                    if (notifications.length === 0) {
                        list.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">No new notifications</div>';
                        return;
                    }
                    
                    list.innerHTML = notifications.slice(0, 5).map(notification => `
                        <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-${notification.priority === 'critical' ? 'red' : 'blue'}-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-${notification.priority === 'critical' ? 'exclamation-triangle' : 'bell'} text-${notification.priority === 'critical' ? 'red' : 'blue'}-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                                    <p class="text-xs text-gray-500">${notification.message}</p>
                                    <p class="text-xs text-gray-400 mt-1">${new Date(notification.created_at).toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        // Show loading overlay
        function showLoading() {
            document.getElementById('loading-overlay').classList.remove('hidden');
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loading-overlay').classList.add('hidden');
        }

        // Auto-refresh notifications every 30 seconds
        setInterval(() => {
            if (!document.getElementById('notifications-menu').classList.contains('hidden')) {
                loadNotifications();
            }
        }, 30000);

        // Form submission loading state
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitButton = form.querySelector('button[type="submit"]');
                    if (submitButton) {
                        const originalText = submitButton.innerHTML;
                        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                        submitButton.disabled = true;
                        
                        // Re-enable after 10 seconds as fallback
                        setTimeout(() => {
                            submitButton.innerHTML = originalText;
                            submitButton.disabled = false;
                        }, 10000);
                    }
                });
            });
        });
    </script>
</body>
</html>