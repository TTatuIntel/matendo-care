@extends('layouts.app')

@section('title', 'Patient Dashboard')
@section('page-title', 'Welcome, ' . auth()->user()->name)

@section('content')
<div class="space-y-6">
    <!-- Quick Health Overview -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Health Overview</h3>
            <a href="{{ route('patient.health.daily') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-plus mr-2"></i>Update Health Data
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Latest Vitals -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Latest Vitals</p>
                        @if($latestVitals)
                            <p class="text-lg font-bold text-blue-800">
                                {{ $latestVitals->blood_pressure ?: 'N/A' }}
                            </p>
                            <p class="text-xs text-blue-600">
                                {{ $latestVitals->created_at->diffForHumans() }}
                            </p>
                        @else
                            <p class="text-sm text-blue-600">No vitals recorded</p>
                        @endif
                    </div>
                    <i class="fas fa-heartbeat text-2xl text-blue-500"></i>
                </div>
                @if($latestVitals && $latestVitals->is_critical)
                    <div class="mt-2 text-xs bg-red-100 text-red-700 px-2 py-1 rounded">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Critical values detected
                    </div>
                @endif
            </div>

            <!-- BMI -->
            <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">BMI</p>
                        <p class="text-lg font-bold text-green-800">
                            {{ $patient->bmi ?? 'N/A' }}
                        </p>
                        @if($patient->bmi)
                            <p class="text-xs text-green-600">
                                @if($patient->bmi < 18.5) Underweight
                                @elseif($patient->bmi < 25) Normal
                                @elseif($patient->bmi < 30) Overweight
                                @else Obese
                                @endif
                            </p>
                        @endif
                    </div>
                    <i class="fas fa-weight text-2xl text-green-500"></i>
                </div>
            </div>

            <!-- Risk Level -->
            <div class="bg-gradient-to-r from-{{ $patient->risk_level === 'critical' ? 'red' : ($patient->risk_level === 'high' ? 'orange' : ($patient->risk_level === 'medium' ? 'yellow' : 'green')) }}-50 to-{{ $patient->risk_level === 'critical' ? 'red' : ($patient->risk_level === 'high' ? 'orange' : ($patient->risk_level === 'medium' ? 'yellow' : 'green')) }}-100 p-4 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-{{ $patient->risk_level === 'critical' ? 'red' : ($patient->risk_level === 'high' ? 'orange' : ($patient->risk_level === 'medium' ? 'yellow' : 'green')) }}-600 font-medium">Risk Level</p>
                        <p class="text-lg font-bold text-{{ $patient->risk_level === 'critical' ? 'red' : ($patient->risk_level === 'high' ? 'orange' : ($patient->risk_level === 'medium' ? 'yellow' : 'green')) }}-800 capitalize">
                            {{ $patient->risk_level }}
                        </p>
                    </div>
                    <i class="fas fa-shield-alt text-2xl text-{{ $patient->risk_level === 'critical' ? 'red' : ($patient->risk_level === 'high' ? 'orange' : ($patient->risk_level === 'medium' ? 'yellow' : 'green')) }}-500"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Appointments -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Upcoming Appointments</h3>
                <a href="{{ route('patient.appointments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>

            @forelse($upcomingAppointments as $appointment)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-3">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white mr-3">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $appointment->doctor->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $appointment->doctor->specialization }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $appointment->appointment_date->format('M d, Y') }} at {{ $appointment->start_time }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2 py-1 text-xs rounded-full bg-{{ $appointment->status_color }}-100 text-{{ $appointment->status_color }}-800">
                            {{ ucfirst($appointment->status) }}
                        </span>
                        @if($appointment->is_online)
                            <div class="text-xs text-blue-600 mt-1">
                                <i class="fas fa-video mr-1"></i>Online
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-calendar-times text-3xl mb-3"></i>
                    <p>No upcoming appointments</p>
                    <a href="{{ route('patient.appointments.create') }}" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                        Schedule an appointment
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Active Medications -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Active Medications</h3>
                <a href="{{ route('patient.medications.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>

            @forelse($activeMedications as $medication)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-3">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white mr-3">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $medication->name }}</p>
                            <p class="text-sm text-gray-600">{{ $medication->dosage }} - {{ $medication->frequency }}</p>
                            <p class="text-xs text-gray-500">
                                Prescribed by {{ $medication->prescriber->user->name }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($medication->next_dose_at)
                            <p class="text-xs text-gray-600">Next dose:</p>
                            <p class="text-sm font-medium text-gray-800">
                                {{ $medication->next_dose_at->format('H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-pills text-3xl mb-3"></i>
                    <p>No active medications</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Health Goals -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Health Goals</h3>
                <a href="{{ route('patient.goals.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">Manage Goals</a>
            </div>

            @forelse($activeGoals as $goal)
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-medium text-gray-800">{{ $goal->title }}</p>
                        <span class="text-sm text-gray-600">{{ $goal->progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $goal->progress }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Target: {{ $goal->target_date->format('M d, Y') }}</p>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-bullseye text-3xl mb-3"></i>
                    <p>No active health goals</p>
                    <a href="{{ route('patient.goals.create') }}" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                        Set a health goal
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Medical Records</h3>
                <a href="{{ route('patient.records.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>

            @forelse($recentRecords as $record)
                <div class="flex items-start space-x-3 mb-4">
                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white">
                        <i class="fas fa-file-medical text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $record->category) }}</p>
                        @if($record->notes)
                            <p class="text-sm text-gray-600">{{ Str::limit($record->notes, 50) }}</p>
                        @endif
                        <p class="text-xs text-gray-500">
                            {{ $record->created_at->diffForHumans() }}
                            @if($record->recorder)
                                by {{ $record->recorder->name }}
                            @endif
                        </p>
                        @if($record->is_critical)
                            <span class="inline-block px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full mt-1">
                                Critical
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-clipboard text-3xl mb-3"></i>
                    <p>No recent records</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Health Trends Chart -->
    @if($healthTrends->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Health Trends (Last 30 Days)</h3>
            <div class="h-64">
                <canvas id="healthTrendsChart"></canvas>
            </div>
        </div>
    @endif

    <!-- Recent Documents -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Recent Documents</h3>
            <a href="{{ route('patient.documents.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
        </div>

        @forelse($documents as $document)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-3">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white mr-3">
                        <i class="fas fa-file"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">{{ $document->original_filename }}</p>
                        <p class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $document->category) }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $document->created_at->diffForHumans() }} • {{ $document->formatted_size }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($document->is_verified)
                        <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                            Verified
                        </span>
                    @endif
                    <a href="{{ $document->view_url }}" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ $document->download_url }}" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-6 text-gray-500">
                <i class="fas fa-folder-open text-3xl mb-3"></i>
                <p>No documents uploaded</p>
                <a href="{{ route('patient.documents.upload') }}" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                    Upload a document
                </a>
            </div>
        @endforelse
    </div>

    <!-- Notifications -->
    @if($notifications->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Notifications</h3>
                <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>

            @foreach($notifications as $notification)
                <div class="flex items-start space-x-3 mb-4 p-3 bg-{{ $notification->priority_color }}-50 rounded-lg">
                    <div class="w-8 h-8 bg-{{ $notification->priority_color }}-500 rounded-full flex items-center justify-center text-white">
                        <i class="fas fa-{{ $notification->priority_icon }} text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800">{{ $notification->title }}</p>
                        <p class="text-sm text-gray-600">{{ $notification->message }}</p>
                        <p class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            View
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
@if($healthTrends->count() > 0)
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('healthTrendsChart').getContext('2d');
    
    const data = {
        labels: {!! json_encode($healthTrends->keys()) !!},
        datasets: [{
            label: 'Systolic BP',
            data: {!! json_encode($healthTrends->map(function($vitals) { return $vitals->first()->systolic ?? null; })->values()) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.1
        }, {
            label: 'Heart Rate',
            data: {!! json_encode($healthTrends->map(function($vitals) { return $vitals->first()->heart_rate ?? null; })->values()) !!},
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.1
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endif
@endpush
@endsection