{{-- resources/views/layouts/navigation/admin.blade.php --}}
<a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-tachometer-alt mr-2"></i>
    Dashboard
</a>

<a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.users.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-users mr-2"></i>
    Users
</a>

<a href="{{ route('admin.doctors.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.doctors.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-user-md mr-2"></i>
    Doctors
</a>

<a href="{{ route('admin.patients.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.patients.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-bed mr-2"></i>
    Patients
</a>

<a href="{{ route('admin.appointments.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.appointments.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-calendar-alt mr-2"></i>
    Appointments
</a>

<a href="{{ route('admin.critical-alerts') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.critical-alerts') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    Critical Alerts
    @php
        $criticalCount = \App\Models\Notification::critical()->unread()->count();
    @endphp
    @if($criticalCount > 0)
        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
            {{ $criticalCount }}
        </span>
    @endif
</a>

<a href="{{ route('admin.analytics') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.analytics') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-chart-line mr-2"></i>
    Analytics
</a>