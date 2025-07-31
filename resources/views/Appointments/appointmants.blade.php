@extends('layouts.app')

@section('title', 'Appointments')
@section('page-title', 'Appointment Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Appointments</h2>
                <p class="text-gray-600">Manage your appointment schedule</p>
            </div>
            <div class="flex space-x-3">
                @can('create', App\Models\Appointment::class)
                    <button onclick="openAppointmentModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>Schedule Appointment
                    </button>
                @endcan
                <button onclick="toggleCalendarView()" id="calendarToggle" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-calendar-alt mr-2"></i>Calendar View
                </button>
            </div>
        </div>
    </div>

    <!-- Appointment Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Today's Appointments -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-day text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today</p>
                    <p class="text-2xl font-bold text-gray-900" id="todayCount">{{ $todayAppointments ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- This Week -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-week text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">This Week</p>
                    <p class="text-2xl font-bold text-gray-900" id="weekCount">{{ $weekAppointments ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Confirmations -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-900" id="pendingCount">{{ $pendingAppointments ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Completed This Month -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-900" id="completedCount">{{ $completedAppointments ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('appointments.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" 
                       name="search" 
                       id="search"
                       value="{{ request('search') }}"
                       placeholder="Patient name, doctor, reason..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No Show</option>
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" 
                       name="date_from" 
                       id="date_from"
                       value="{{ request('date_from') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" 
                       name="date_to" 
                       id="date_to"
                       value="{{ request('date_to') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Actions -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('appointments.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- List View -->
    <div id="listView" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if(isset($appointments) && $appointments->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Appointment Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($appointments as $appointment)
                            <tr class="hover:bg-gray-50 {{ $appointment->is_today ? 'bg-blue-50' : '' }}">
                                <!-- Checkbox -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="appointment_ids[]" value="{{ $appointment->id }}" class="appointment-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                </td>

                                <!-- Appointment Details -->
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $appointment->reason ?? 'General Consultation' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Duration: {{ $appointment->duration_in_minutes }} minutes
                                        </div>
                                        @if($appointment->is_online)
                                            <div class="text-xs text-blue-600 mt-1">
                                                <i class="fas fa-video mr-1"></i>Online Consultation
                                            </div>
                                        @endif
                                        @if($appointment->notes)
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ Str::limit($appointment->notes, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Patient -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium">
                                                {{ substr($appointment->patient->user->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $appointment->patient->user->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ $appointment->patient->patient_id }}
                                            </div>
                                            @if($appointment->patient->risk_level === 'critical' || $appointment->patient->risk_level === 'high')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $appointment->patient->risk_level === 'critical' ? 'red' : 'orange' }}-100 text-{{ $appointment->patient->risk_level === 'critical' ? 'red' : 'orange' }}-800">
                                                    {{ ucfirst($appointment->patient->risk_level) }} Risk
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Doctor -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $appointment->doctor->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $appointment->doctor->specialization }}</div>
                                </td>

                                <!-- Date & Time -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $appointment->appointment_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $appointment->start_time }} - {{ $appointment->end_time }}
                                    </div>
                                    @if($appointment->is_today)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mt-1">
                                            Today
                                        </span>
                                    @elseif($appointment->appointment_date->isYesterday())
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 mt-1">
                                            Yesterday
                                        </span>
                                    @elseif($appointment->appointment_date->isTomorrow())
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mt-1">
                                            Tomorrow
                                        </span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $appointment->status_color }}-100 text-{{ $appointment->status_color }}-800">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                    </span>
                                    @if($appointment->reminder_sent)
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-bell mr-1"></i>Reminder sent
                                        </div>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <!-- View -->
                                        <button onclick="viewAppointment('{{ $appointment->id }}')" 
                                                class="text-blue-600 hover:text-blue-900" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <!-- Edit -->
                                        @can('update', $appointment)
                                            <button onclick="editAppointment('{{ $appointment->id }}')" 
                                                    class="text-green-600 hover:text-green-900" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endcan

                                        <!-- Status Actions -->
                                        @if($appointment->status === 'scheduled')
                                            <button onclick="confirmAppointment('{{ $appointment->id }}')" 
                                                    class="text-purple-600 hover:text-purple-900" 
                                                    title="Confirm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                            <button onclick="startAppointment('{{ $appointment->id }}')" 
                                                    class="text-yellow-600 hover:text-yellow-900" 
                                                    title="Start">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        @endif

                                        @if($appointment->status === 'in_progress')
                                            <button onclick="completeAppointment('{{ $appointment->id }}')" 
                                                    class="text-green-600 hover:text-green-900" 
                                                    title="Complete">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif

                                        <!-- Cancel -->
                                        @if(!in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
                                            <button onclick="cancelAppointment('{{ $appointment->id }}')" 
                                                    class="text-red-600 hover:text-red-900" 
                                                    title="Cancel">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif

                                        <!-- Video Call -->
                                        @if($appointment->is_online && $appointment->meeting_link && $appointment->status === 'confirmed')
                                            <a href="{{ $appointment->meeting_link }}" 
                                               target="_blank"
                                               class="text-blue-600 hover:text-blue-900" 
                                               title="Join Video Call">
                                                <i class="fas fa-video"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $appointments->links() }}
            </div>

            <!-- Bulk Actions -->
            <div id="bulkActions" class="hidden bg-gray-50 px-6 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        <span id="selectedCount">0</span> appointment(s) selected
                    </span>
                    <div class="flex space-x-2">
                        <button onclick="bulkConfirm()" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                            Confirm Selected
                        </button>
                        <button onclick="bulkCancel()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                            Cancel Selected
                        </button>
                        <button onclick="sendReminders()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            Send Reminders
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No appointments found</h3>
                <p class="text-gray-500 mb-4">
                    @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                        No appointments match your current filters. Try adjusting your search criteria.
                    @else
                        Get started by scheduling your first appointment.
                    @endif
                </p>
                @can('create', App\Models\Appointment::class)
                    <button onclick="openAppointmentModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>Schedule First Appointment
                    </button>
                @endcan
            </div>
        @endif
    </div>

    <!-- Calendar View -->
    <div id="calendarView" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div id="calendar"></div>
    </div>
</div>

<!-- Appointment Modal -->
<div id="appointmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Schedule Appointment</h3>
            <button onclick="closeAppointmentModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="appointmentForm" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Patient Selection -->
                <div class="md:col-span-2">
                    <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
                    <select name="patient_id" id="patient_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Patient</option>
                        <!-- Patients will be loaded via AJAX -->
                    </select>
                </div>

                <!-- Doctor Selection -->
                @if(auth()->user()->isAdmin())
                    <div class="md:col-span-2">
                        <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-2">Doctor</label>
                        <select name="doctor_id" id="doctor_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Doctor</option>
                            <!-- Doctors will be loaded via AJAX -->
                        </select>
                    </div>
                @endif

                <!-- Date -->
                <div>
                    <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                    <input type="date" 
                           name="appointment_date" 
                           id="appointment_date"
                           required
                           min="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Time -->
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                    <input type="time" 
                           name="start_time" 
                           id="start_time"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Duration -->
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                    <select name="duration" id="duration" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="15">15 minutes</option>
                        <option value="30" selected>30 minutes</option>
                        <option value="45">45 minutes</option>
                        <option value="60">1 hour</option>
                        <option value="90">1.5 hours</option>
                        <option value="120">2 hours</option>
                    </select>
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select name="type" id="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="consultation">Consultation</option>
                        <option value="follow_up">Follow-up</option>
                        <option value="check_up">Check-up</option>
                        <option value="emergency">Emergency</option>
                        <option value="procedure">Procedure</option>
                        <option value="therapy">Therapy</option>
                    </select>
                </div>

                <!-- Reason -->
                <div class="md:col-span-2">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Appointment</label>
                    <input type="text" 
                           name="reason" 
                           id="reason"
                           placeholder="Brief description of the appointment purpose"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Online Consultation -->
                <div class="md:col-span-2">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_online" id="is_online" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_online" class="ml-2 text-sm text-gray-700">Online consultation (video call)</label>
                    </div>
                </div>

                <!-- Pre-appointment Notes -->
                <div class="md:col-span-2">
                    <label for="pre_appointment_notes" class="block text-sm font-medium text-gray-700 mb-2">Pre-appointment Notes</label>
                    <textarea name="pre_appointment_notes" 
                              id="pre_appointment_notes" 
                              rows="3"
                              placeholder="Any special instructions or preparations needed..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" 
                        onclick="closeAppointmentModal()"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-calendar-plus mr-2"></i>Schedule Appointment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/index.global.min.js"></script>
<script>
let calendar;
let currentView = 'list';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadPatients();
    @if(auth()->user()->isAdmin())
        loadDoctors();
    @endif
});

function setupEventListeners() {
    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.appointment-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('appointment-checkbox')) {
            updateBulkActions();
        }
    });

    // Time picker validation
    document.getElementById('start_time').addEventListener('change', function() {
        const duration = parseInt(document.getElementById('duration').value);
        if (this.value) {
            const endTime = calculateEndTime(this.value, duration);
            document.getElementById('end_time_display').textContent = endTime;
        }
    });

    document.getElementById('duration').addEventListener('change', function() {
        const startTime = document.getElementById('start_time').value;
        if (startTime) {
            const endTime = calculateEndTime(startTime, parseInt(this.value));
            document.getElementById('end_time_display').textContent = endTime;
        }
    });
}

function openAppointmentModal() {
    document.getElementById('appointmentModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Schedule New Appointment';
    document.getElementById('appointmentForm').action = '{{ route("appointments.store") }}';
    document.getElementById('appointmentForm').reset();
}

function closeAppointmentModal() {
    document.getElementById('appointmentModal').classList.add('hidden');
}

function editAppointment(appointmentId) {
    fetch(`/appointments/${appointmentId}/edit`)
        .then(response => response.json())
        .then(appointment => {
            document.getElementById('modalTitle').textContent = 'Edit Appointment';
            document.getElementById('appointmentForm').action = `/appointments/${appointmentId}`;
            
            // Add method spoofing for PUT request
            let methodInput = document.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                document.getElementById('appointmentForm').appendChild(methodInput);
            }

            // Populate form fields
            document.getElementById('patient_id').value = appointment.patient_id;
            document.getElementById('appointment_date').value = appointment.appointment_date;
            document.getElementById('start_time').value = appointment.start_time;
            document.getElementById('duration').value = appointment.duration;
            document.getElementById('type').value = appointment.type;
            document.getElementById('reason').value = appointment.reason || '';
            document.getElementById('is_online').checked = appointment.is_online;
            document.getElementById('pre_appointment_notes').value = appointment.pre_appointment_notes || '';

            @if(auth()->user()->isAdmin())
                document.getElementById('doctor_id').value = appointment.doctor_id;
            @endif

            document.getElementById('appointmentModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading appointment:', error);
            alert('Error loading appointment details.');
        });
}

function viewAppointment(appointmentId) {
    window.location.href = `/appointments/${appointmentId}`;
}

function confirmAppointment(appointmentId) {
    if (confirm('Confirm this appointment?')) {
        fetch(`/appointments/${appointmentId}/confirm`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error confirming appointment.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error confirming appointment.');
        });
    }
}

function startAppointment(appointmentId) {
    if (confirm('Start this appointment?')) {
        updateAppointmentStatus(appointmentId, 'in_progress');
    }
}

function completeAppointment(appointmentId) {
    const notes = prompt('Enter post-appointment notes (optional):');
    
    fetch(`/appointments/${appointmentId}/complete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error completing appointment.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error completing appointment.');
    });
}

function cancelAppointment(appointmentId) {
    const reason = prompt('Please provide a reason for cancellation:');
    if (reason) {
        fetch(`/appointments/${appointmentId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error cancelling appointment.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error cancelling appointment.');
        });
    }
}

function updateAppointmentStatus(appointmentId, status) {
    fetch(`/appointments/${appointmentId}`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating appointment status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating appointment status.');
    });
}

function toggleCalendarView() {
    const listView = document.getElementById('listView');
    const calendarView = document.getElementById('calendarView');
    const toggleButton = document.getElementById('calendarToggle');

    if (currentView === 'list') {
        listView.classList.add('hidden');
        calendarView.classList.remove('hidden');
        toggleButton.innerHTML = '<i class="fas fa-list mr-2"></i>List View';
        currentView = 'calendar';
        initializeCalendar();
    } else {
        listView.classList.remove('hidden');
        calendarView.classList.add('hidden');
        toggleButton.innerHTML = '<i class="fas fa-calendar-alt mr-2"></i>Calendar View';
        currentView = 'list';
    }
}

function initializeCalendar() {
    if (calendar) {
        calendar.destroy();
    }

    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: '/api/appointments/calendar',
        eventClick: function(info) {
            viewAppointment(info.event.id);
        },
        dateClick: function(info) {
            document.getElementById('appointment_date').value = info.dateStr;
            openAppointmentModal();
        }
    });

    calendar.render();
}

function loadPatients() {
    fetch('/api/patients/search')
        .then(response => response.json())
        .then(patients => {
            const select = document.getElementById('patient_id');
            select.innerHTML = '<option value="">Select Patient</option>';
            patients.forEach(patient => {
                select.innerHTML += `<option value="${patient.id}">${patient.user.name} (${patient.patient_id})</option>`;
            });
        });
}

@if(auth()->user()->isAdmin())
function loadDoctors() {
    fetch('/api/doctors/search')
        .then(response => response.json())
        .then(doctors => {
            const select = document.getElementById('doctor_id');
            select.innerHTML = '<option value="">Select Doctor</option>';
            doctors.forEach(doctor => {
                select.innerHTML += `<option value="${doctor.id}">Dr. ${doctor.user.name} - ${doctor.specialization}</option>`;
            });
        });
}
@endif

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.appointment-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');

    if (checkboxes.length > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = checkboxes.length;
    } else {
        bulkActions.classList.add('hidden');
    }
}

function calculateEndTime(startTime, durationMinutes) {
    const [hours, minutes] = startTime.split(':').map(Number);
    const startDate = new Date();
    startDate.setHours(hours, minutes, 0, 0);
    
    const endDate = new Date(startDate.getTime() + durationMinutes * 60000);
    
    return endDate.toTimeString().slice(0, 5);
}

// Close modal when clicking outside
document.getElementById('appointmentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAppointmentModal();
    }
});
</script>
@endpush
@endsection