{{-- resources/views/layouts/navigation/patient.blade.php --}}
<a href="{{ route('patient.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-tachometer-alt mr-2"></i>
    Dashboard
</a>

<a href="{{ route('patient.health.daily') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.health.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-heartbeat mr-2"></i>
    Health Data
</a>

<a href="{{ route('patient.appointments.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.appointments.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-calendar-alt mr-2"></i>
    Appointments
    @if(auth()->user()->patient && auth()->user()->patient->upcomingAppointments()->count() > 0)
        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {{ auth()->user()->patient->upcomingAppointments()->count() }}
        </span>
    @endif
</a>

<a href="{{ route('patient.medications.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.medications.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-pills mr-2"></i>
    Medications
    @if(auth()->user()->patient && auth()->user()->patient->activeMedications()->count() > 0)
        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            {{ auth()->user()->patient->activeMedications()->count() }}
        </span>
    @endif
</a>

<a href="{{ route('patient.records.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.records.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-file-medical mr-2"></i>
    Medical Records
</a>

<a href="{{ route('patient.documents.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('patient.documents.*') ? 'border-blue-500 text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-folder mr-2"></i>
    Documents
</a>