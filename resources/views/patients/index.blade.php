{{-- resources/views/patients/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Patients Management')
@section('page-title', 'Patients Management')

@section('header-actions')
    @can('create', App\Models\Patient::class)
        <a href="{{ route('patients.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
            <i class="fas fa-user-plus mr-2"></i>Add New Patient
        </a>
    @endcan
    <button onclick="exportPatients()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
        <i class="fas fa-download mr-2"></i>Export
    </button>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Patient Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                    <p class="text-2xl font-bold text-gray-900">{{ $patients->total() }}</p>
                </div>
            </div>
        </div>

        <!-- Active Patients -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Patients</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $patients->where('user.is_active', true)->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Critical Risk</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $patients->where('risk_level', 'critical')->count() }}</p>
                </div>
            </div>
        </div>

        <!-- High Risk Patients -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">High Risk</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $patients->where('risk_level', 'high')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('patients.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" 
                       name="search" 
                       id="search"
                       value="{{ request('search') }}"
                       placeholder="Name, email, patient ID..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Risk Level Filter -->
            <div>
                <label for="risk_level" class="block text-sm font-medium text-gray-700 mb-1">Risk Level</label>
                <select name="risk_level" id="risk_level" class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Risk Levels</option>
                    <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>Low Risk</option>
                    <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>Medium Risk</option>
                    <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>High Risk</option>
                    <option value="critical" {{ request('risk_level') === 'critical' ? 'selected' : '' }}>Critical Risk</option>
                </select>
            </div>

            <!-- Doctor Filter (Admin only) -->
            @if(auth()->user()->isAdmin())
                <div>
                    <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-1">Assigned Doctor</label>
                    <select name="doctor_id" id="doctor_id" class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Doctors</option>
                        @foreach(\App\Models\Doctor::with('user')->get() as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                Dr. {{ $doctor->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('patients.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Patients List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($patients->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                            @if(auth()->user()->isAdmin())
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Doctor</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Visit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($patients as $patient)
                            <tr class="hover:bg-gray-50 {{ $patient->risk_level === 'critical' ? 'bg-red-50' : '' }}">
                                <!-- Checkbox -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="patient_ids[]" value="{{ $patient->id }}" class="patient-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                </td>

                                <!-- Patient Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium">
                                                {{ strtoupper(substr($patient->user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $patient->user->name }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ $patient->patient_id }}</div>
                                            <div class="text-xs text-gray-400">
                                                {{ $patient->user->age }} years old • {{ ucfirst($patient->user->gender) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $patient->user->email }}</div>
                                    @if($patient->user->phone)
                                        <div class="text-sm text-gray-500">{{ $patient->user->phone }}</div>
                                    @endif
                                </td>

                                <!-- Risk Level -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($patient->risk_level === 'critical') bg-red-100 text-red-800
                                        @elseif($patient->risk_level === 'high') bg-orange-100 text-orange-800
                                        @elseif($patient->risk_level === 'medium') bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ ucfirst($patient->risk_level) }}
                                    </span>
                                    @if($patient->hasCriticalVitals())
                                        <div class="text-xs text-red-600 mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Critical vitals
                                        </div>
                                    @endif
                                </td>

                                <!-- Assigned Doctor (Admin view) -->
                                @if(auth()->user()->isAdmin())
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($patient->doctors->count() > 0)
                                            @foreach($patient->doctors->take(2) as $doctor)
                                                <div class="text-sm text-gray-900">Dr. {{ $doctor->user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $doctor->specialization }}</div>
                                            @endforeach
                                            @if($patient->doctors->count() > 2)
                                                <div class="text-xs text-gray-400">+{{ $patient->doctors->count() - 2 }} more</div>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">No doctor assigned</span>
                                        @endif
                                    </td>
                                @endif

                                <!-- Last Visit -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($patient->lastAppointment())
                                        {{ $patient->lastAppointment()->appointment_date->format('M d, Y') }}
                                    @else
                                        <span class="text-gray-400">No visits</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($patient->user->is_active)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <!-- View -->
                                        <a href="{{ route('patients.show', $patient) }}" 
                                           class="text-blue-600 hover:text-blue-900" 
                                           title="View Patient">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Edit -->
                                        @can('update', $patient)
                                            <a href="{{ route('patients.edit', $patient) }}" 
                                               class="text-green-600 hover:text-green-900" 
                                               title="Edit Patient">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        <!-- Vitals -->
                                        <a href="{{ route('patients.vitals', $patient) }}" 
                                           class="text-purple-600 hover:text-purple-900" 
                                           title="View Vitals">
                                            <i class="fas fa-heartbeat"></i>
                                        </a>

                                        <!-- Documents -->
                                        <a href="{{ route('patients.documents', $patient) }}" 
                                           class="text-orange-600 hover:text-orange-900" 
                                           title="View Documents">
                                            <i class="fas fa-folder"></i>
                                        </a>

                                        <!-- More Actions Dropdown -->
                                        <div class="relative">
                                            <button onclick="toggleActionMenu('{{ $patient->id }}')" 
                                                    class="text-gray-400 hover:text-gray-600" 
                                                    title="More actions">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            
                                            <!-- Dropdown Menu -->
                                            <div id="action-menu-{{ $patient->id }}" 
                                                 class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                                <div class="py-1">
                                                    <a href="{{ route('patients.records', $patient) }}" 
                                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <i class="fas fa-file-medical mr-2"></i>Medical Records
                                                    </a>
                                                    
                                                    @if(auth()->user()->isDoctor())
                                                        <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            <i class="fas fa-calendar-plus mr-2"></i>Schedule Appointment
                                                        </a>
                                                        
                                                        <a href="{{ route('doctor.medications.prescribe', ['patient_id' => $patient->id]) }}" 
                                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            <i class="fas fa-pills mr-2"></i>Prescribe Medication
                                                        </a>
                                                    @endif
                                                    
                                                    @can('delete', $patient)
                                                        <div class="border-t border-gray-100"></div>
                                                        <button onclick="deletePatient('{{ $patient->id }}')" 
                                                                class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                            <i class="fas fa-trash mr-2"></i>Delete Patient
                                                        </button>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $patients->links() }}
            </div>

            <!-- Bulk Actions -->
            <div id="bulkActions" class="hidden bg-gray-50 px-6 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        <span id="selectedCount">0</span> patient(s) selected
                    </span>
                    <div class="flex space-x-2">
                        @can('update', App\Models\Patient::class)
                            <button onclick="bulkUpdateRiskLevel()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                                Update Risk Level
                            </button>
                        @endcan
                        @can('delete', App\Models\Patient::class)
                            <button onclick="bulkDelete()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                Delete Selected
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No patients found</h3>
                <p class="text-gray-500 mb-4">
                    @if(request()->hasAny(['search', 'risk_level', 'doctor_id']))
                        No patients match your current filters. Try adjusting your search criteria.
                    @else
                        Get started by adding your first patient.
                    @endif
                </p>
                @can('create', App\Models\Patient::class)
                    <a href="{{ route('patients.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-user-plus mr-2"></i>Add First Patient
                    </a>
                @endcan
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Toggle select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.patient-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActions();
});

// Individual checkboxes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('patient-checkbox')) {
        updateBulkActions();
    }
});

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.patient-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');

    if (checkboxes.length > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = checkboxes.length;
    } else {
        bulkActions.classList.add('hidden');
    }
}

function toggleActionMenu(patientId) {
    const menu = document.getElementById(`action-menu-${patientId}`);
    const allMenus = document.querySelectorAll('[id^="action-menu-"]');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m.id !== `action-menu-${patientId}`) {
            m.classList.add('hidden');
        }
    });
    
    // Toggle current menu
    menu.classList.toggle('hidden');
}

function deletePatient(patientId) {
    if (confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/patients/${patientId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function bulkDelete() {
    const selected = document.querySelectorAll('.patient-checkbox:checked');
    if (selected.length === 0) return;
    
    if (confirm(`Are you sure you want to delete ${selected.length} patient(s)? This action cannot be undone.`)) {
        // Implementation for bulk delete
        console.log('Bulk delete:', Array.from(selected).map(cb => cb.value));
    }
}

function bulkUpdateRiskLevel() {
    const selected = document.querySelectorAll('.patient-checkbox:checked');
    if (selected.length === 0) return;
    
    const riskLevel = prompt('Enter new risk level (low, medium, high, critical):');
    if (riskLevel && ['low', 'medium', 'high', 'critical'].includes(riskLevel)) {
        // Implementation for bulk risk level update
        console.log('Bulk update risk level:', riskLevel, Array.from(selected).map(cb => cb.value));
    }
}

function exportPatients() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = `${window.location.pathname}?${params.toString()}`;
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('[id^="action-menu-"]') && !event.target.closest('button[onclick^="toggleActionMenu"]')) {
        document.querySelectorAll('[id^="action-menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});
</script>
@endpush
@endsection