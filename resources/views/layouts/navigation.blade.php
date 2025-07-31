{{-- resources/views/layouts/navigation.blade.php --}}
<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-heartbeat text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">MedMonitor</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    @auth
                        <!-- Universal Dashboard Link -->
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <i class="fas fa-tachometer-alt mr-2"></i>{{ __('Dashboard') }}
                        </x-nav-link>

                        @if(auth()->user()->isAdmin())
                            <!-- Admin Navigation -->
                            <x-nav-link :href="route('admin.patients.index')" :active="request()->routeIs('admin.patients.*')">
                                <i class="fas fa-users mr-2"></i>{{ __('Patients') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.doctors.index')" :active="request()->routeIs('admin.doctors.*')">
                                <i class="fas fa-user-md mr-2"></i>{{ __('Doctors') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.appointments.index')" :active="request()->routeIs('admin.appointments.*')">
                                <i class="fas fa-calendar-alt mr-2"></i>{{ __('Appointments') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.notifications.index')" :active="request()->routeIs('admin.notifications.*')">
                                <i class="fas fa-bell mr-2"></i>{{ __('Notifications') }}
                            </x-nav-link>

                        @elseif(auth()->user()->isDoctor())
                            <!-- Doctor Navigation -->
                            <x-nav-link :href="route('doctor.patients.index')" :active="request()->routeIs('doctor.patients.*')">
                                <i class="fas fa-users mr-2"></i>{{ __('My Patients') }}
                            </x-nav-link>
                            <x-nav-link :href="route('doctor.appointments.index')" :active="request()->routeIs('doctor.appointments.*')">
                                <i class="fas fa-calendar-alt mr-2"></i>{{ __('Appointments') }}
                            </x-nav-link>
                            <x-nav-link :href="route('doctor.real-time.monitor')" :active="request()->routeIs('doctor.real-time.*')">
                                <i class="fas fa-heartbeat mr-2"></i>{{ __('Monitor') }}
                            </x-nav-link>
                            <x-nav-link :href="route('doctor.critical-alerts')" :active="request()->routeIs('doctor.critical-alerts')">
                                <i class="fas fa-exclamation-triangle mr-2"></i>{{ __('Alerts') }}
                                @php($criticalCount = auth()->user()->notifications()->critical()->unread()->count())
                                @if($criticalCount > 0)
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $criticalCount }}
                                    </span>
                                @endif
                            </x-nav-link>

                        @elseif(auth()->user()->isPatient())
                            <!-- Patient Navigation -->
                            <x-nav-link :href="route('patient.health.daily')" :active="request()->routeIs('patient.health.*')">
                                <i class="fas fa-heartbeat mr-2"></i>{{ __('Health Data') }}
                            </x-nav-link>
                            <x-nav-link :href="route('patient.appointments.index')" :active="request()->routeIs('patient.appointments.*')">
                                <i class="fas fa-calendar-alt mr-2"></i>{{ __('Appointments') }}
                            </x-nav-link>
                            <x-nav-link :href="route('patient.medications.index')" :active="request()->routeIs('patient.medications.*')">
                                <i class="fas fa-pills mr-2"></i>{{ __('Medications') }}
                            </x-nav-link>
                            <x-nav-link :href="route('patient.documents.index')" :active="request()->routeIs('patient.documents.*')">
                                <i class="fas fa-folder mr-2"></i>{{ __('Documents') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            @auth
                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <!-- Notifications -->
                    <div class="relative mr-3">
                        <button type="button" 
                                onclick="toggleNotifications()"
                                class="relative inline-flex items-center p-2 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="sr-only">View notifications</span>
                            <i class="fas fa-bell h-5 w-5"></i>
                            @php($unreadCount = auth()->user()->notifications()->unread()->count())
                            @if($unreadCount > 0)
                                <span id="notification-count" class="absolute -top-1 -right-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500 text-white">
                                    {{ $unreadCount }}
                                </span>
                            @else
                                <span id="notification-count" class="hidden absolute -top-1 -right-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500 text-white"></span>
                            @endif
                        </button>
                        
                        <!-- Notifications Dropdown -->
                        <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <h3 class="text-sm font-medium text-gray-900">Recent Notifications</h3>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                @forelse(auth()->user()->notifications()->latest()->limit(5)->get() as $notification)
                                    <div class="px-4 py-3 hover:bg-gray-50 {{ !$notification->is_read ? 'bg-blue-50' : '' }}">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-{{ $notification->priority_color }}-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-{{ $notification->priority_icon }} text-{{ $notification->priority_color }}-600 text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                                                <p class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                        No new notifications
                                    </div>
                                @endforelse
                            </div>
                            <div class="border-t border-gray-100 px-4 py-2">
                                <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-500">
                                    View all notifications
                                </a>
                            </div>
                        </div>
                    </div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium mr-2">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="text-left">
                                        <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                                        <div class="font-medium text-xs text-gray-500">{{ ucfirst(Auth::user()->usertype) }}</div>
                                    </div>
                                </div>
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                <i class="fas fa-user mr-2"></i>{{ __('Profile') }}
                            </x-dropdown-link>
                            
                            <x-dropdown-link :href="route('settings')">
                                <i class="fas fa-cog mr-2"></i>{{ __('Settings') }}
                            </x-dropdown-link>

                            <!-- Role-specific links -->
                            @if(auth()->user()->isDoctor())
                                <x-dropdown-link :href="route('doctor.settings')">
                                    <i class="fas fa-user-md mr-2"></i>{{ __('Doctor Settings') }}
                                </x-dropdown-link>
                            @elseif(auth()->user()->isPatient())
                                <x-dropdown-link :href="route('patient.settings')">
                                    <i class="fas fa-user-injured mr-2"></i>{{ __('Patient Settings') }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-gray-100 dark:border-gray-600"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @else
                <!-- Guest Navigation -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Log in</a>
                    <a href="{{ route('register') }}" class="ml-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">Register</a>
                </div>
            @endauth

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <i class="fas fa-tachometer-alt mr-2"></i>{{ __('Dashboard') }}
                </x-responsive-nav-link>

                @if(auth()->user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.patients.index')" :active="request()->routeIs('admin.patients.*')">
                        <i class="fas fa-users mr-2"></i>{{ __('Patients') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.doctors.index')" :active="request()->routeIs('admin.doctors.*')">
                        <i class="fas fa-user-md mr-2"></i>{{ __('Doctors') }}
                    </x-responsive-nav-link>
                @elseif(auth()->user()->isDoctor())
                    <x-responsive-nav-link :href="route('doctor.patients.index')" :active="request()->routeIs('doctor.patients.*')">
                        <i class="fas fa-users mr-2"></i>{{ __('My Patients') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('doctor.appointments.index')" :active="request()->routeIs('doctor.appointments.*')">
                        <i class="fas fa-calendar-alt mr-2"></i>{{ __('Appointments') }}
                    </x-responsive-nav-link>
                @elseif(auth()->user()->isPatient())
                    <x-responsive-nav-link :href="route('patient.health.daily')" :active="request()->routeIs('patient.health.*')">
                        <i class="fas fa-heartbeat mr-2"></i>{{ __('Health Data') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('patient.appointments.index')" :active="request()->routeIs('patient.appointments.*')">
                        <i class="fas fa-calendar-alt mr-2"></i>{{ __('Appointments') }}
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        @auth
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        <i class="fas fa-user mr-2"></i>{{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notifications-dropdown');
    dropdown.classList.toggle('hidden');
}

// Close notifications dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notifications-dropdown');
    const button = event.target.closest('[onclick="toggleNotifications()"]');
    
    if (!button && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>