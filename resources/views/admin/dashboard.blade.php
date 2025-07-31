{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('header-actions')
    <button onclick="refreshDashboard()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
        <i class="fas fa-sync-alt mr-2"></i>Refresh
    </button>
    <a href="{{ route('admin.settings.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
        <i class="fas fa-cog mr-2"></i>Settings
    </a>
@endsection

@section('content')
<div class="space-y-6">
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
                    <p class="text-sm font-medium text-gray-600">Total Patients</p>
                    <p class="text-2xl font-bold text-gray-900" id="totalPatients">{{ $stats['total_patients'] }}</p>
                    <p class="text-xs text-gray-500">Active patients in system</p>
                </div>
            </div>
        </div>

        <!-- Total Doctors -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-md text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Doctors</p>
                    <p class="text-2xl font-bold text-gray-900" id="totalDoctors">{{ $stats['total_doctors'] }}</p>
                    <p class="text-xs text-gray-500">Registered doctors</p>
                </div>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-day text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today's Appointments</p>
                    <p class="text-2xl font-bold text-gray-900" id="todayAppointments">{{ $stats['today_appointments'] }}</p>
                    <p class="text-xs text-gray-500">Scheduled for today</p>
                </div>
            </div>
        </div>

        <!-- Critical Patients -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Critical Patients</p>
                    <p class="text-2xl font-bold text-gray-900" id="criticalPatients">{{ $stats['critical_patients'] }}</p>
                    <p class="text-xs text-gray-500">Require immediate attention</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="{{ route('admin.users.create') }}" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-user-plus text-white"></i>
                </div>
                <span class="text-sm font-medium text-blue-700">Add User</span>
            </a>
            
            <a href="{{ route('admin.doctors.create') }}" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-user-md text-white"></i>
                </div>
                <span class="text-sm font-medium text-green-700">Add Doctor</span>
            </a>
            
            <a href="{{ route('admin.patients.create') }}" class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-bed text-white"></i>
                </div>
                <span class="text-sm font-medium text-purple-700">Add Patient</span>
            </a>
            
            <a href="{{ route('admin.critical-alerts') }}" class="flex flex-col items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-exclamation-triangle text-white"></i>
                </div>
                <span class="text-sm font-medium text-red-700">View Alerts</span>
            </a>
            
            <a href="{{ route('admin.activity-logs.index') }}" class="flex flex-col items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-gray-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-history text-white"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Activity Logs</span>
            </a>
            
            <a href="{{ route('admin.backups.index') }}" class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-database text-white"></i>
                </div>
                <span class="text-sm font-medium text-orange-700">Backups</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
                <a href="{{ route('admin.activity-logs.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>
            
            <div class="space-y-4">
                @forelse($recentActivities as $activity)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-{{ $activity->type_color }}-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-{{ $activity->type_icon }} text-{{ $activity->type_color }}-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $activity->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $activity->description }}</p>
                            <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-history text-2xl mb-2"></i>
                        <p>No recent activity</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Upcoming Appointments</h3>
                <a href="{{ route('admin.appointments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>
            
            <div class="space-y-4">
                @forelse($upcomingAppointments as $appointment)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white">
                                {{ strtoupper(substr($appointment->patient->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $appointment->patient->user->name }}</p>
                                <p class="text-sm text-gray-600">Dr. {{ $appointment->doctor->user->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $appointment->appointment_date->format('M d, Y') }} at {{ $appointment->start_time }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs rounded-full bg-{{ $appointment->status_color }}-100 text-{{ $appointment->status_color }}-800">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-calendar text-2xl mb-2"></i>
                        <p>No upcoming appointments</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">System Health</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Database Status -->
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-database text-green-600 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-900">Database</p>
                <p class="text-xs text-green-600">Online</p>
            </div>

            <!-- Cache Status -->
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-memory text-blue-600 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-900">Cache</p>
                <p class="text-xs text-blue-600">Active</p>
            </div>

            <!-- Queue Status -->
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-tasks text-yellow-600 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-900">Queue</p>
                <p class="text-xs text-yellow-600">Processing</p>
            </div>

            <!-- Storage Status -->
            <div class="text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-hdd text-purple-600 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-900">Storage</p>
                <p class="text-xs text-purple-600">75% Used</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function refreshDashboard() {
    showLoading();
    
    fetch('/dashboard/stats')
        .then(response => response.json())
        .then(data => {
            // Update statistics
            document.getElementById('totalPatients').textContent = data.total_patients;
            document.getElementById('totalDoctors').textContent = data.total_doctors;
            document.getElementById('todayAppointments').textContent = data.today_appointments;
            document.getElementById('criticalPatients').textContent = data.critical_patients;
            
            hideLoading();
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
            hideLoading();
        });
}

// Auto-refresh every 5 minutes
setInterval(refreshDashboard, 300000);
</script>
@endpush
@endsection