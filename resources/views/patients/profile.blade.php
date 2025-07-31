@extends('layouts.app')

@section('title', 'Patient Profile - ' . $patient->user->name)
@section('page-title', 'Patient Profile')

@section('content')
<div class="space-y-6">
    <!-- Patient Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start">
                <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center text-white text-2xl font-medium mr-6">
                    {{ substr($patient->user->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $patient->user->name }}</h1>
                    <p class="text-lg text-gray-600">Patient ID: {{ $patient->patient_id }}</p>
                    <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                        <span>{{ $patient->user->age }} years old</span>
                        <span>•</span>
                        <span class="capitalize">{{ $patient->user->gender }}</span>
                        @if($patient->user->blood_group)
                            <span>•</span>
                            <span>{{ $patient->user->blood_group }}</span>
                        @endif
                        @if($patient->bmi)
                            <span>•</span>
                            <span>BMI: {{ $patient->bmi }}</span>
                        @endif
                    </div>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $patient->risk_level === 'critical' ? 'red' : ($patient->risk_level === 'high' ? 'orange' : ($patient->risk_level === 'medium' ? 'yellow' : 'green')) }}-100 text-{{ $patient->risk_level === 'critical' ? 'red' : ($patient->risk_level === 'high' ? 'orange' : ($patient->risk_level === 'medium' ? 'yellow' : 'green')) }}-800">
                            {{ ucfirst($patient->risk_level) }} Risk
                        </span>
                        @if($patient->user->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                Inactive
                            </span>
                        @endif
                        @if($patient->hasCriticalVitals())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Critical Vitals
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex space-x-3">
                @can('update', $patient)
                    <a href="{{ route('patients.edit', $patient) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                @endcan
                @if(auth()->user()->isDoctor())
                    <button onclick="openAddNoteModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>Add Note
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Alert for Critical Vitals -->
    @if($latestVitals && $latestVitals->is_critical)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Critical Vital Signs Alert</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>Latest vital signs reading contains critical values recorded {{ $latestVitals->created_at->diffForHumans() }}.</p>
                        @if($latestVitals->alerts)
                            <ul class="list-disc list-inside mt-2">
                                @foreach($latestVitals->alerts as $alert)
                                    <li>{{ $alert['message'] }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('patients.vitals', $patient) }}" class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-2 rounded-md text-sm font-medium">
                            View All Vitals
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Latest Vitals -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-gray-600">Latest Vitals</h4>
                        <i class="fas fa-heartbeat text-red-500"></i>
                    </div>
                    @if($latestVitals)
                        <p class="text-lg font-bold text-gray-900">{{ $latestVitals->blood_pressure ?: 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $latestVitals->created_at->diffForHumans() }}</p>
                        @if($latestVitals->heart_rate)
                            <p class="text-xs text-gray-600">HR: {{ $latestVitals->heart_rate }} bpm</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">No vitals recorded</p>
                    @endif
                </div>

                <!-- Upcoming Appointments -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-gray-600">Next Appointment</h4>
                        <i class="fas fa-calendar-alt text-blue-500"></i>
                    </div>
                    @if($upcomingAppointments->first())
                        @php $nextAppt = $upcomingAppointments->first(); @endphp
                        <p class="text-lg font-bold text-gray-900">{{ $nextAppt->appointment_date->format('M d') }}</p>
                        <p class="text-xs text-gray-500">{{ $nextAppt->start_time }}</p>
                        <p class="text-xs text-gray-600">Dr. {{ $nextAppt->doctor->user->name }}</p>
                    @else
                        <p class="text-sm text-gray-500">No upcoming appointments</p>
                    @endif
                </div>

                <!-- Active Medications -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-gray-600">Active Medications</h4>
                        <i class="fas fa-pills text-green-500"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $activeMedications->count() }}</p>
                    <p class="text-xs text-gray-500">prescribed medications</p>
                    @if($activeMedications->count() > 0)
                        <p class="text-xs text-gray-600">{{ $activeMedications->first()->name }}</p>
                    @endif
                </div>
            </div>

            <!-- Latest Vitals Detail -->
            @if($latestVitals)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Latest Vital Signs</h3>
                        <span class="text-sm text-gray-500">{{ $latestVitals->created_at->format('M d, Y - H:i') }}</span>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @if($latestVitals->systolic && $latestVitals->diastolic)
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Blood Pressure</p>
                                <p class="text-xl font-bold text-gray-900">{{ $latestVitals->blood_pressure }}</p>
                                <p class="text-xs text-gray-500">mmHg</p>
                            </div>
                        @endif
                        
                        @if($latestVitals->heart_rate)
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Heart Rate</p>
                                <p class="text-xl font-bold text-gray-900">{{ $latestVitals->heart_rate }}</p>
                                <p class="text-xs text-gray-500">bpm</p>
                            </div>
                        @endif
                        
                        @if($latestVitals->temperature)
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Temperature</p>
                                <p class="text-xl font-bold text-gray-900">{{ $latestVitals->temperature }}</p>
                                <p class="text-xs text-gray-500">°C</p>
                            </div>
                        @endif
                        
                        @if($latestVitals->oxygen_saturation)
                            <div class="text-center">
                                <p class="text-sm text-gray-600">O2 Saturation</p>
                                <p class="text-xl font-bold text-gray-900">{{ $latestVitals->oxygen_saturation }}</p>
                                <p class="text-xs text-gray-500">%</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('patients.vitals', $patient) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            View All Vitals <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Recent Medical Records -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Medical Records</h3>
                    <a href="{{ route('patients.records', $patient) }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
                </div>

                @forelse($recentRecords as $record)
                    <div class="flex items-start space-x-3 mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white">
                            <i class="fas fa-file-medical text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $record->category) }}</p>
                            @if($record->notes)
                                <p class="text-sm text-gray-600">{{ Str::limit($record->notes, 100) }}</p>
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
                                            Attention Required
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-500">
                        <i class="fas fa-clipboard text-3xl mb-3"></i>
                        <p>No medical records found</p>
                    </div>
                @endforelse
            </div>

            <!-- Recent Documents -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Documents</h3>
                    <a href="{{ route('patients.documents', $patient) }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
                </div>

                @forelse($recentDocuments as $document)
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
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-500">
                        <i class="fas fa-folder-open text-3xl mb-3"></i>
                        <p>No documents uploaded</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Patient Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Patient Information</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-gray-600">Email:</span>
                        <p class="font-medium">{{ $patient->user->email }}</p>
                    </div>
                    @if($patient->user->phone)
                        <div>
                            <span class="text-gray-600">Phone:</span>
                            <p class="font-medium">{{ $patient->user->phone }}</p>
                        </div>
                    @endif
                    <div>
                        <span class="text-gray-600">Date of Birth:</span>
                        <p class="font-medium">{{ $patient->user->date_of_birth->format('M d, Y') }}</p>
                    </div>
                    @if($patient->user->address)
                        <div>
                            <span class="text-gray-600">Address:</span>
                            <p class="font-medium">{{ $patient->user->address }}</p>
                        </div>
                    @endif
                    @if($patient->insurance_provider)
                        <div>
                            <span class="text-gray-600">Insurance:</span>
                            <p class="font-medium">{{ $patient->insurance_provider }}</p>
                            @if($patient->insurance_number)
                                <p class="text-xs text-gray-500">{{ $patient->insurance_number }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Emergency Contact -->
            @if($patient->user->emergency_contact)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Emergency Contact</h3>
                    <div class="text-sm">
                        <p class="font-medium">{{ $patient->user->emergency_contact }}</p>
                        @if($patient->user->emergency_contact_phone)
                            <p class="text-gray-600">{{ $patient->user->emergency_contact_phone }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Assigned Doctors -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Medical Team</h3>
                
                @forelse($patient->doctors as $doctor)
                    <div class="flex items-center space-x-3 mb-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white">
                            {{ substr($doctor->user->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">{{ $doctor->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $doctor->specialization }}</p>
                            @if($doctor->pivot->is_primary)
                                <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full mt-1">
                                    Primary Doctor
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No doctors assigned</p>
                @endforelse
            </div>

            <!-- Medical History -->
            @if($patient->medical_history || $patient->allergies || $patient->chronic_conditions)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Medical History</h3>
                    <div class="space-y-3 text-sm">
                        @if($patient->allergies)
                            <div>
                                <span class="text-red-600 font-medium">Allergies:</span>
                                <p class="mt-1">{{ $patient->allergies }}</p>
                            </div>
                        @endif
                        @if($patient->chronic_conditions)
                            <div>
                                <span class="text-orange-600 font-medium">Chronic Conditions:</span>
                                <p class="mt-1">{{ $patient->chronic_conditions }}</p>
                            </div>
                        @endif
                        @if($patient->medical_history)
                            <div>
                                <span class="text-gray-600 font-medium">Medical History:</span>
                                <p class="mt-1">{{ $patient->medical_history }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('patients.vitals', $patient) }}" class="block w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm transition-colors">
                        <i class="fas fa-heartbeat mr-2"></i>View Vitals
                    </a>
                    <a href="{{ route('patients.documents', $patient) }}" class="block w-full bg-green-50 hover:bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm transition-colors">
                        <i class="fas fa-file-medical mr-2"></i>View Documents
                    </a>
                    <a href="{{ route('patients.records', $patient) }}" class="block w-full bg-purple-50 hover:bg-purple-100 text-purple-700 px-4 py-2 rounded-lg text-sm transition-colors">
                        <i class="fas fa-clipboard mr-2"></i>Medical Records
                    </a>
                    @if(auth()->user()->isDoctor())
                        <button onclick="openAppointmentModal()" class="block w-full bg-yellow-50 hover:bg-yellow-100 text-yellow-700 px-4 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-calendar-plus mr-2"></i>Schedule Appointment
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div id="addNoteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add Medical Note</h3>
                <button onclick="closeAddNoteModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('comments.store') }}" method="POST" id="noteForm">
                @csrf
                <input type="hidden" name="commentable_type" value="App\Models\Patient">
                <input type="hidden" name="commentable_id" value="{{ $patient->id }}">
                <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                
                <div class="space-y-4">
                    <div>
                        <label for="note_type" class="block text-sm font-medium text-gray-700 mb-2">Note Type</label>
                        <select name="type" id="note_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Type</option>
                            <option value="observation">Observation</option>
                            <option value="treatment">Treatment</option>
                            <option value="diagnosis">Diagnosis</option>
                            <option value="follow_up">Follow Up</option>
                            <option value="general">General Note</option>
                        </select>
                    </div>

                    <div>
                        <label for="note_comment" class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                        <textarea name="comment" 
                                  id="note_comment" 
                                  rows="4"
                                  required
                                  placeholder="Enter your medical note..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_important" id="is_important" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_important" class="ml-2 text-sm text-gray-700">Mark as important</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_private" id="is_private" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_private" class="ml-2 text-sm text-gray-700">Private note (doctor only)</label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" 
                            onclick="closeAddNoteModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-save mr-2"></i>Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAddNoteModal() {
    document.getElementById('addNoteModal').classList.remove('hidden');
}

function closeAddNoteModal() {
    document.getElementById('addNoteModal').classList.add('hidden');
    document.getElementById('noteForm').reset();
}

function openAppointmentModal() {
    // Redirect to appointment creation with patient pre-selected
    window.location.href = '{{ route("appointments.create") }}?patient_id={{ $patient->id }}';
}

// Close modals when clicking outside
document.getElementById('addNoteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddNoteModal();
    }
});

// Real-time updates for critical vitals (if WebSocket is implemented)
// Echo.channel('patient.{{ $patient->id }}')
//     .listen('VitalSignUpdated', (e) => {
//         if (e.vitalSign.is_critical) {
//             showCriticalAlert(e.vitalSign);
//         }
//     });

function showCriticalAlert(vitalSign) {
    // Show real-time critical alert
    const alertHtml = `
        <div class="fixed top-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg z-50" id="criticalAlert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <div>
                    <h4 class="font-medium">Critical Vital Signs Alert</h4>
                    <p class="text-sm">{{ $patient->user->name }} has new critical vital signs.</p>
                </div>
                <button onclick="document.getElementById('criticalAlert').remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        const alert = document.getElementById('criticalAlert');
        if (alert) alert.remove();
    }, 10000);
}
</script>
@endpush
@endsection