{{-- resources/views/doctor/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Doctor Dashboard')
@section('page-title', 'Welcome, Dr. ' . auth()->user()->name)

@section('header-actions')
    <a href="{{ route('doctor.patients.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
        <i class="fas fa-user-plus mr-2"></i>Add Patient
    </a>
    <a href="{{ route('doctor.real-time.monitor') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
        <i class="fas fa-heartbeat mr-2"></i>Monitor
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Critical Alerts Banner -->
    @if($stats['critical_alerts'] > 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        {{ $stats['critical_alerts'] }} Critical Alert{{ $stats['critical_alerts'] > 1 ? 's' : '' }}
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>You have patients with critical vital signs that require immediate attention.</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('doctor.critical-alerts') }}" class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-2 rounded-md text-sm font-medium">
                            View Critical Alerts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Dashboard Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Patients -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">My Patients</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_patients'] }}</p>
                    <p class="text-xs text-gray-500">Active patients</p>
                </div>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-day text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today's Appointments</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['today_appointments'] }}</p>
                    <p class="text-xs text-gray-500">Scheduled today</p>
                </div>
            </div>
        </div>

        <!-- Pending Reviews -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Reviews</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_reviews'] }}</p>
                    <p class="text-xs text-gray-500">Records to review</p>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Critical Alerts</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['critical_alerts'] }}</p>
                    <p class="text-xs text-gray-500">Urgent attention needed</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Appointments -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Today's Appointments</h3>
                <a href="{{ route('doctor.appointments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>

            @forelse($todayAppointments as $appointment)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium">
                            {{ substr($appointment->patient->user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $appointment->patient->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $appointment->reason ?? 'General consultation' }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $appointment->start_time }} - {{ $appointment->end_time }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex px-2 py-1 text-xs rounded-full bg-{{ $appointment->status_color }}-100 text-{{ $appointment->status_color }}-800">
                            {{ ucfirst($appointment->status) }}
                        </span>
                        @if($appointment->is_online)
                            <i class="fas fa-video text-blue-500" title="Online consultation"></i>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-calendar-times text-3xl mb-3"></i>
                    <p>No appointments scheduled for today</p>
                </div>
            @endforelse
        </div>

        <!-- Critical Patients -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Critical Patients</h3>
                <a href="{{ route('doctor.critical-alerts') }}" class="text-red-600 hover:text-red-800 text-sm">View All</a>
            </div>

            @forelse($criticalPatients as $patient)
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center text-white font-medium">
                            {{ substr($patient->user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $patient->user->name }}</p>
                            <p class="text-sm text-red-600">Critical vital signs detected</p>
                            @if($patient->vitalSigns->first())
                                <p class="text-xs text-gray-500">
                                    Last reading: {{ $patient->vitalSigns->first()->created_at->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('patients.show', $patient) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('patients.vitals', $patient) }}" class="text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-heartbeat"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-heartbeat text-3xl mb-3 text-green-500"></i>
                    <p>No critical patients</p>
                    <p class="text-sm">All patients are stable</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Medical Records -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Medical Records</h3>
                <a href="{{ route('doctor.reviews.pending') }}" class="text-blue-600 hover:text-blue-800 text-sm">Review All</a>
            </div>

            @forelse($recentRecords as $record)
                <div class="flex items-start space-x-3 mb-4">
                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white">
                        <i class="fas fa-file-medical text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $record->patient->user->name }}</p>
                        <p class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $record->category) }}</p>
                        @if($record->notes)
                            <p class="text-sm text-gray-500">{{ Str::limit($record->notes, 80) }}</p>
                        @endif
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-xs text-gray-500">
                                {{ $record->created_at->diffForHumans() }}
                                @if($record->recorder)
                                    by {{ $record->recorder->name }}
                                @endif
                            </p>
                            <div class="flex space-x-2">
                                @if($record->is_critical)
                                    <span class="inline-block px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                                        Critical
                                    </span>
                                @endif
                                @if($record->requires_attention)
                                    <span class="inline-block px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                        Review
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-clipboard text-3xl mb-3"></i>
                    <p>No recent medical records</p>
                </div>
            @endforelse
        </div>

        <!-- Recent Notifications -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Notifications</h3>
                <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>

            @forelse($notifications as $notification)
                <div class="flex items-start space-x-3 mb-4 p-3 bg-{{ $notification->priority_color }}-50 rounded-lg">
                    <div class="w-8 h-8 bg-{{ $notification->priority_color }}-500 rounded-full flex items-center justify-center text-white">
                        <i class="fas fa-{{ $notification->priority_icon }} text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $notification->title }}</p>
                        <p class="text-sm text-gray-600">{{ $notification->message }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                            @if($notification->action_url)
                                <a href="{{ $notification->action_url }}" class="text-blue-600 hover:text-blue-800 text-xs">
                                    View <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-bell text-3xl mb-3"></i>
                    <p>No recent notifications</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="{{ route('doctor.patients.create') }}" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-user-plus text-white"></i>
                </div>
                <span class="text-sm font-medium text-blue-700">Add Patient</span>
            </a>
            
            <a href="{{ route('doctor.appointments.create') }}" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-calendar-plus text-white"></i>
                </div>
                <span class="text-sm font-medium text-green-700">Schedule Appointment</span>
            </a>
            
            <a href="{{ route('doctor.medications.prescribe') }}" class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-pills text-white"></i>
                </div>
                <span class="text-sm font-medium text-purple-700">Prescribe</span>
            </a>
            
            <a href="{{ route('doctor.real-time.monitor') }}" class="flex flex-col items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-heartbeat text-white"></i>
                </div>
                <span class="text-sm font-medium text-red-700">Monitor</span>
            </a>
            
            <a href="{{ route('doctor.temp-access.create') }}" class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-share-alt text-white"></i>
                </div>
                <span class="text-sm font-medium text-orange-700">Share Access</span>
            </a>
            
            <a href="{{ route('doctor.analytics') }}" class="flex flex-col items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-gray-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Analytics</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-refresh critical alerts every 2 minutes
setInterval(function() {
    fetch('/dashboard/stats')
        .then(response => response.json())
        .then(data => {
            if (data.critical_alerts > 0 && data.critical_alerts !== {{ $stats['critical_alerts'] }}) {
                // Show notification for new critical alerts
                showCriticalAlertNotification(data.critical_alerts);
            }
        })
        .catch(error => console.error('Error checking for updates:', error));
}, 120000);

function showCriticalAlertNotification(count) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg z-50';
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <div>
                <h4 class="font-medium">New Critical Alert${count > 1 ? 's' : ''}</h4>
                <p class="text-sm">${count} patient${count > 1 ? 's' : ''} require${count === 1 ? 's' : ''} immediate attention</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 10000);
}
</script>
@endpush
@endsection