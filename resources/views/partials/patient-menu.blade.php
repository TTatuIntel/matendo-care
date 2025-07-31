<!-- Patient Navigation Menu -->
<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex space-x-8">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Dashboard
                </a>

                <!-- Health Data -->
                <a href="{{ route('patient.health.daily') }}" 
                   class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.health.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                    <i class="fas fa-heartbeat mr-2"></i>
                    Health Data
                </a>

                <!-- Appointments -->
                <a href="{{ route('patient.appointments.index') }}" 
                   class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.appointments.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Appointments
                    @if(auth()->user()->patient && auth()->user()->patient->upcomingAppointments()->count() > 0)
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ auth()->user()->patient->upcomingAppointments()->count() }}
                        </span>
                    @endif
                </a>

                <!-- Medical Records -->
                <a href="{{ route('patient.records.index') }}" 
                   class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.records.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                    <i class="fas fa-file-medical mr-2"></i>
                    Medical Records
                </a>

                <!-- Documents -->
                <a href="{{ route('patient.documents.index') }}" 
                   class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.documents.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                    <i class="fas fa-folder mr-2"></i>
                    Documents
                </a>

                <!-- Medications -->
                <a href="{{ route('patient.medications.index') }}" 
                   class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.medications.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                    <i class="fas fa-pills mr-2"></i>
                    Medications
                    @if(auth()->user()->patient && auth()->user()->patient->activeMedications()->count() > 0)
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ auth()->user()->patient->activeMedications()->count() }}
                        </span>
                    @endif
                </a>

                <!-- Health Goals -->
                <a href="{{ route('patient.goals.index') }}" 
                   class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.goals.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                    <i class="fas fa-bullseye mr-2"></i>
                    Health Goals
                </a>
            </div>

            <!-- Right side menu -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button type="button" id="notifications-menu" class="relative inline-flex items-center p-2 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">View notifications</span>
                        <i class="fas fa-bell h-5 w-5"></i>
                        @if(auth()->user()->unread_notifications_count > 0)
                            <span class="absolute -top-1 -right-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500 text-white">
                                {{ auth()->user()->unread_notifications_count }}
                            </span>
                        @endif
                    </button>
                </div>

                <!-- Profile dropdown -->
                <div class="relative ml-3">
                    <div>
                        <button type="button" class="bg-white rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                            <span class="sr-only">Open user menu</span>
                            @if(auth()->user()->avatar)
                                <img class="h-8 w-8 rounded-full" src="{{ auth()->user()->avatar }}" alt="">
                            @else
                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </button>
                    </div>

                    <!-- Dropdown menu -->
                    <div class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1" id="user-menu">
                        <!-- Profile Info -->
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                            <p class="text-xs text-gray-400">Patient ID: {{ auth()->user()->patient->patient_id ?? 'N/A' }}</p>
                        </div>

                        <!-- Menu Items -->
                        <a href="{{ route('patient.profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                            <i class="fas fa-user mr-2"></i>Your Profile
                        </a>
                        
                        <a href="{{ route('patient.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                            <i class="fas fa-cog mr-2"></i>Settings
                        </a>
                        
                        <a href="{{ route('patient.privacy') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                            <i class="fas fa-shield-alt mr-2"></i>Privacy
                        </a>
                        
                        <div class="border-t border-gray-100"></div>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                <i class="fas fa-sign-out-alt mr-2"></i>Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Quick Action Buttons (Mobile) -->
<div class="lg:hidden bg-gray-50 border-b border-gray-200 px-4 py-3">
    <div class="flex space-x-3 overflow-x-auto">
        <a href="{{ route('patient.health.daily') }}" class="flex-shrink-0 inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-plus mr-2"></i>Add Health Data
        </a>
        
        <a href="{{ route('patient.appointments.create') }}" class="flex-shrink-0 inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm leading-4 font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
        </a>
        
        <a href="{{ route('patient.documents.upload') }}" class="flex-shrink-0 inline-flex items-center px-3 py-2 border border-orange-300 shadow-sm text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
            <i class="fas fa-upload mr-2"></i>Upload Document
        </a>
    </div>
</div>

<script>
// Toggle user menu
document.getElementById('user-menu-button').addEventListener('click', function() {
    const menu = document.getElementById('user-menu');
    menu.classList.toggle('hidden');
});

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');
    
    if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
        userMenu.classList.add('hidden');
    }
});

// Handle notifications menu (placeholder)
document.getElementById('notifications-menu').addEventListener('click', function() {
    // Add notification dropdown functionality here
    alert('Notifications feature coming soon!');
});
</script>