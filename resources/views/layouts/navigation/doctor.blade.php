{{-- resources/views/layouts/navigation/doctor.blade.php --}}
<a href="{{ route('doctor.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('doctor.dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-tachometer-alt mr-2"></i>
    Dashboard
</a>

<a href="{{ route('doctor.patients.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('doctor.patients.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-users mr-2"></i>
    My Patients
    @if(auth()->user()->doctor && auth()->user()->doctor->activePatients()->count() > 0)
        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {{ auth()->user()->doctor->activePatients()->count() }}
        </span>
    @endif
</a>

<a href="{{ route('doctor.appointments.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('doctor.appointments.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-calendar-alt mr-2"></i>
    Appointments
    @php
        $todayAppointments = auth()->user()->doctor ? auth()->user()->doctor->appointments()->today()->count() : 0;
    @endphp
    @if($todayAppointments > 0)
        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            {{ $todayAppointments }}
        </span>
    @endif
</a>

<a href="{{ route('doctor.critical-alerts') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('doctor.critical-alerts') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    Critical Alerts
    @php
        $criticalAlerts = auth()->user()->notifications()->unread()->critical()->count();
    @endphp
    @if($criticalAlerts > 0)
        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
            {{ $criticalAlerts }}
        </span>
    @endif
</a>

<a href="{{ route('doctor.real-time.monitor') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('doctor.real-time.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-heartbeat mr-2"></i>
    Real-time Monitor
</a>

<a href="{{ route('doctor.reviews.pending') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('doctor.reviews.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
    <i class="fas fa-clipboard-check mr-2"></i>
    Reviews
    @php
        $pendingReviews = auth()->user()->doctor ? 
            \App\Models\MedicalRecord::whereIn('patient_id', auth()->user()->doctor->activePatients()->pluck('patients.id'))
                ->unreviewed()->count() : 0;
    @endphp
    @if($pendingReviews > 0)
        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
            {{ $pendingReviews }}
        </span>
    @endif
</a>