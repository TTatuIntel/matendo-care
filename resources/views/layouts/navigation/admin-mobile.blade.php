{{-- resources/views/layouts/navigation/admin-mobile.blade.php --}}
<a href="{{ route('admin.dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.dashboard') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
</a>
<a href="{{ route('admin.users.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.users.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-users mr-3"></i>Users
</a>
<a href="{{ route('admin.doctors.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.doctors.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-user-md mr-3"></i>Doctors
</a>
<a href="{{ route('admin.patients.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.patients.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-bed mr-3"></i>Patients
</a>
<a href="{{ route('admin.critical-alerts') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.critical-alerts') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-exclamation-triangle mr-3"></i>Critical Alerts
</a>

{{-- resources/views/layouts/navigation/doctor-mobile.blade.php --}}
<a href="{{ route('doctor.dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('doctor.dashboard') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
</a>
<a href="{{ route('doctor.patients.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('doctor.patients.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-users mr-3"></i>My Patients
</a>
<a href="{{ route('doctor.appointments.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('doctor.appointments.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-calendar-alt mr-3"></i>Appointments
</a>
<a href="{{ route('doctor.real-time.monitor') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('doctor.real-time.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-heartbeat mr-3"></i>Real-time Monitor
</a>

{{-- resources/views/layouts/navigation/patient-mobile.blade.php --}}
<a href="{{ route('patient.dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('patient.dashboard') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
</a>
<a href="{{ route('patient.health.daily') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('patient.health.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-heartbeat mr-3"></i>Health Data
</a>
<a href="{{ route('patient.appointments.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('patient.appointments.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-calendar-alt mr-3"></i>Appointments
</a>
<a href="{{ route('patient.medications.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('patient.medications.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
    <i class="fas fa-pills mr-3"></i>Medications
</a>