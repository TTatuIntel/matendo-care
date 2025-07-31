@extends('layouts.app')

@section('title', 'Real-time Patient Monitor')
@section('page-title', 'Real-time Patient Monitor')

@section('content')
<div class="space-y-6">
    <!-- Monitor Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Real-time Patient Monitor</h2>
                <p class="text-gray-600">Monitor vital signs and alerts for all your patients in real-time</p>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Connection Status -->
                <div class="flex items-center">
                    <div id="connectionStatus" class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-600">Connected</span>
                </div>
                
                <!-- Auto Refresh Toggle -->
                <label class="flex items-center">
                    <input type="checkbox" id="autoRefresh" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-600">Auto-refresh</span>
                </label>
                
                <!-- Refresh Button -->
                <button onclick="refreshAllData()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Critical Alerts -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-800">Critical Alerts</p>
                    <p class="text-2xl font-bold text-red-900" id="criticalCount">0</p>
                </div>
            </div>
        </div>

        <!-- Warning Alerts -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-800">Warning Alerts</p>
                    <p class="text-2xl font-bold text-yellow-900" id="warningCount">0</p>
                </div>
            </div>
        </div>

        <!-- Online Patients -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wifi text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-800">Online Patients</p>
                    <p class="text-2xl font-bold text-green-900" id="onlineCount">0</p>
                </div>
            </div>
        </div>

        <!-- Total Monitored -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-800">Total Patients</p>
                    <p class="text-2xl font-bold text-blue-900" id="totalCount">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Controls -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div class="flex items-center space-x-4">
                <!-- Filter by Alert Level -->
                <div>
                    <label for="alertFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Alert Level</label>
                    <select id="alertFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Patients</option>
                        <option value="critical">Critical Only</option>
                        <option value="warning">Warning Only</option>
                        <option value="normal">Normal Only</option>
                    </select>
                </div>

                <!-- Filter by Risk Level -->
                <div>
                    <label for="riskFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Risk Level</label>
                    <select id="riskFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Risk Levels</option>
                        <option value="critical">Critical Risk</option>
                        <option value="high">High Risk</option>
                        <option value="medium">Medium Risk</option>
                        <option value="low">Low Risk</option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label for="patientSearch" class="block text-sm font-medium text-gray-700 mb-1">Search Patient</label>
                    <input type="text" id="patientSearch" placeholder="Search by name..." class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <!-- View Toggle -->
                <button onclick="toggleView('grid')" id="gridViewBtn" class="view-toggle active p-2 rounded bg-blue-500 text-white">
                    <i class="fas fa-th-large"></i>
                </button>
                <button onclick="toggleView('list')" id="listViewBtn" class="view-toggle p-2 rounded bg-gray-200 text-gray-700">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Patient Monitor Grid -->
    <div id="gridView" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" data-patients='[]'>
        <!-- Patient cards will be dynamically loaded here -->
    </div>

    <!-- Patient Monitor List -->
    <div id="listView" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Latest Vitals</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Update</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alerts</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="listViewBody" class="bg-white divide-y divide-gray-200">
                    <!-- List items will be dynamically loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- No Data State -->
    <div id="noDataState" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Patients to Monitor</h3>
        <p class="text-gray-500">You don't have any patients assigned for monitoring.</p>
    </div>
</div>

<!-- Patient Detail Modal -->
<div id="patientDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-4 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Patient Monitor Details</h3>
            <button onclick="closePatientModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="patientDetailContent">
            <!-- Patient details will be loaded here -->
        </div>
    </div>
</div>

<!-- Critical Alert Modal -->
<div id="criticalAlertModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-red-800">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Critical Alert
            </h3>
            <button onclick="closeCriticalAlert()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="criticalAlertContent">
            <!-- Alert content will be loaded here -->
        </div>
    </div>
</div>

@push('scripts')
<script>
let patients = [];
let refreshInterval;
let currentView = 'grid';

// Initialize the monitor
document.addEventListener('DOMContentLoaded', function() {
    loadPatients();
    setupEventListeners();
    startAutoRefresh();
});

function loadPatients() {
    showLoading();
    
    fetch('/api/doctor/patients/monitoring')
        .then(response => response.json())
        .then(data => {
            patients = data.patients || [];
            updateCounts(data.counts || {});
            renderPatients();
        })
        .catch(error => {
            console.error('Error loading patients:', error);
            showError('Failed to load patient data');
        });
}

function updateCounts(counts) {
    document.getElementById('criticalCount').textContent = counts.critical || 0;
    document.getElementById('warningCount').textContent = counts.warning || 0;
    document.getElementById('onlineCount').textContent = counts.online || 0;
    document.getElementById('totalCount').textContent = counts.total || 0;
}

function renderPatients() {
    const filteredPatients = filterPatients();
    
    if (filteredPatients.length === 0) {
        showNoData();
        return;
    }
    
    hideNoData();
    
    if (currentView === 'grid') {
        renderGridView(filteredPatients);
    } else {
        renderListView(filteredPatients);
    }
}

function renderGridView(patientList) {
    const container = document.getElementById('gridView');
    container.innerHTML = '';
    
    patientList.forEach(patient => {
        const card = createPatientCard(patient);
        container.appendChild(card);
    });
}

function createPatientCard(patient) {
    const card = document.createElement('div');
    const alertClass = getAlertClass(patient.alert_level);
    const lastUpdate = patient.latest_vitals ? new Date(patient.latest_vitals.created_at).toLocaleString() : 'No data';
    
    card.className = `bg-white rounded-lg shadow-sm border-2 ${alertClass} p-4 hover:shadow-md transition-shadow cursor-pointer`;
    card.onclick = () => showPatientDetail(patient.id);
    
    card.innerHTML = `
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium mr-3">
                    ${patient.user.name.charAt(0)}
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">${patient.user.name}</h4>
                    <p class="text-sm text-gray-600">ID: ${patient.patient_id}</p>
                    <span class="inline-flex px-2 py-1 text-xs rounded-full ${getRiskClass(patient.risk_level)}">
                        ${patient.risk_level.charAt(0).toUpperCase() + patient.risk_level.slice(1)} Risk
                    </span>
                </div>
            </div>
            <div class="flex flex-col items-end">
                <div class="flex items-center mb-1">
                    <div class="w-2 h-2 ${getStatusColor(patient.online_status)} rounded-full mr-2"></div>
                    <span class="text-xs text-gray-500">${patient.online_status ? 'Online' : 'Offline'}</span>
                </div>
                ${patient.alert_count > 0 ? `
                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                        ${patient.alert_count} alerts
                    </span>
                ` : ''}
            </div>
        </div>
        
        ${patient.latest_vitals ? `
            <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                ${patient.latest_vitals.systolic ? `
                    <div class="text-center">
                        <p class="text-gray-600">BP</p>
                        <p class="font-bold ${patient.latest_vitals.is_critical ? 'text-red-600' : 'text-gray-900'}">
                            ${patient.latest_vitals.systolic}/${patient.latest_vitals.diastolic}
                        </p>
                    </div>
                ` : ''}
                ${patient.latest_vitals.heart_rate ? `
                    <div class="text-center">
                        <p class="text-gray-600">HR</p>
                        <p class="font-bold ${patient.latest_vitals.is_critical ? 'text-red-600' : 'text-gray-900'}">
                            ${patient.latest_vitals.heart_rate}
                        </p>
                    </div>
                ` : ''}
                ${patient.latest_vitals.temperature ? `
                    <div class="text-center">
                        <p class="text-gray-600">Temp</p>
                        <p class="font-bold ${patient.latest_vitals.is_critical ? 'text-red-600' : 'text-gray-900'}">
                            ${patient.latest_vitals.temperature}°C
                        </p>
                    </div>
                ` : ''}
                ${patient.latest_vitals.oxygen_saturation ? `
                    <div class="text-center">
                        <p class="text-gray-600">O2</p>
                        <p class="font-bold ${patient.latest_vitals.is_critical ? 'text-red-600' : 'text-gray-900'}">
                            ${patient.latest_vitals.oxygen_saturation}%
                        </p>
                    </div>
                ` : ''}
            </div>
        ` : `
            <div class="text-center py-4 text-gray-500">
                <i class="fas fa-heartbeat text-2xl mb-2"></i>
                <p class="text-sm">No vitals data</p>
            </div>
        `}
        
        <div class="text-xs text-gray-500 text-center">
            Last update: ${lastUpdate}
        </div>
        
        ${patient.latest_vitals && patient.latest_vitals.alerts && patient.latest_vitals.alerts.length > 0 ? `
            <div class="mt-3 space-y-1">
                ${patient.latest_vitals.alerts.slice(0, 2).map(alert => `
                    <div class="text-xs px-2 py-1 bg-${alert.type === 'danger' ? 'red' : 'yellow'}-100 text-${alert.type === 'danger' ? 'red' : 'yellow'}-800 rounded">
                        ${alert.message}
                    </div>
                `).join('')}
                ${patient.latest_vitals.alerts.length > 2 ? `
                    <div class="text-xs text-gray-500 text-center">
                        +${patient.latest_vitals.alerts.length - 2} more alerts
                    </div>
                ` : ''}
            </div>
        ` : ''}
    `;
    
    return card;
}

function renderListView(patientList) {
    const tbody = document.getElementById('listViewBody');
    tbody.innerHTML = '';
    
    patientList.forEach(patient => {
        const row = createPatientRow(patient);
        tbody.appendChild(row);
    });
}

function createPatientRow(patient) {
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50 cursor-pointer';
    row.onclick = () => showPatientDetail(patient.id);
    
    const lastUpdate = patient.latest_vitals ? new Date(patient.latest_vitals.created_at).toLocaleString() : 'No data';
    
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium">
                        ${patient.user.name.charAt(0)}
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">${patient.user.name}</div>
                    <div class="text-sm text-gray-500">ID: ${patient.patient_id}</div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
                <div class="w-2 h-2 ${getStatusColor(patient.online_status)} rounded-full mr-2"></div>
                <span class="text-sm text-gray-900">${patient.online_status ? 'Online' : 'Offline'}</span>
            </div>
            <span class="inline-flex px-2 py-1 text-xs rounded-full ${getRiskClass(patient.risk_level)}">
                ${patient.risk_level.charAt(0).toUpperCase() + patient.risk_level.slice(1)}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            ${patient.latest_vitals ? `
                <div class="space-y-1">
                    ${patient.latest_vitals.systolic ? `<div>BP: ${patient.latest_vitals.systolic}/${patient.latest_vitals.diastolic}</div>` : ''}
                    ${patient.latest_vitals.heart_rate ? `<div>HR: ${patient.latest_vitals.heart_rate} bpm</div>` : ''}
                    ${patient.latest_vitals.temperature ? `<div>Temp: ${patient.latest_vitals.temperature}°C</div>` : ''}
                </div>
            ` : '<span class="text-gray-500">No data</span>'}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            ${lastUpdate}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            ${patient.alert_count > 0 ? `
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                    ${patient.alert_count} alerts
                </span>
            ` : `
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Normal
                </span>
            `}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <button onclick="event.stopPropagation(); showPatientDetail(${patient.id})" class="text-blue-600 hover:text-blue-900 mr-2">
                <i class="fas fa-eye"></i>
            </button>
            <a href="/patients/${patient.id}/vitals" class="text-green-600 hover:text-green-900">
                <i class="fas fa-heartbeat"></i>
            </a>
        </td>
    `;
    
    return row;
}

function getAlertClass(alertLevel) {
    switch(alertLevel) {
        case 'critical': return 'border-red-500';
        case 'warning': return 'border-yellow-500';
        default: return 'border-gray-200';
    }
}

function getRiskClass(riskLevel) {
    switch(riskLevel) {
        case 'critical': return 'bg-red-100 text-red-800';
        case 'high': return 'bg-orange-100 text-orange-800';
        case 'medium': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-green-100 text-green-800';
    }
}

function getStatusColor(isOnline) {
    return isOnline ? 'bg-green-500' : 'bg-gray-400';
}

function filterPatients() {
    const alertFilter = document.getElementById('alertFilter').value;
    const riskFilter = document.getElementById('riskFilter').value;
    const searchTerm = document.getElementById('patientSearch').value.toLowerCase();
    
    return patients.filter(patient => {
        // Alert filter
        if (alertFilter !== 'all') {
            if (alertFilter === 'critical' && patient.alert_level !== 'critical') return false;
            if (alertFilter === 'warning' && patient.alert_level !== 'warning') return false;
            if (alertFilter === 'normal' && patient.alert_count > 0) return false;
        }
        
        // Risk filter
        if (riskFilter !== 'all' && patient.risk_level !== riskFilter) return false;
        
        // Search filter
        if (searchTerm && !patient.user.name.toLowerCase().includes(searchTerm)) return false;
        
        return true;
    });
}

function toggleView(view) {
    currentView = view;
    
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    
    if (view === 'grid') {
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        gridBtn.classList.add('active', 'bg-blue-500', 'text-white');
        gridBtn.classList.remove('bg-gray-200', 'text-gray-700');
        listBtn.classList.remove('active', 'bg-blue-500', 'text-white');
        listBtn.classList.add('bg-gray-200', 'text-gray-700');
    } else {
        gridView.classList.add('hidden');
        listView.classList.remove('hidden');
        listBtn.classList.add('active', 'bg-blue-500', 'text-white');
        listBtn.classList.remove('bg-gray-200', 'text-gray-700');
        gridBtn.classList.remove('active', 'bg-blue-500', 'text-white');
        gridBtn.classList.add('bg-gray-200', 'text-gray-700');
    }
    
    renderPatients();
}

function showPatientDetail(patientId) {
    const patient = patients.find(p => p.id === patientId);
    if (!patient) return;
    
    const content = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-900 mb-4">${patient.user.name}</h4>
                <div class="space-y-2 text-sm">
                    <div><span class="text-gray-600">Patient ID:</span> ${patient.patient_id}</div>
                    <div><span class="text-gray-600">Age:</span> ${patient.user.age} years</div>
                    <div><span class="text-gray-600">Risk Level:</span> <span class="${getRiskClass(patient.risk_level)} px-2 py-1 rounded">${patient.risk_level}</span></div>
                    <div><span class="text-gray-600">Status:</span> ${patient.online_status ? 'Online' : 'Offline'}</div>
                </div>
            </div>
            
            ${patient.latest_vitals ? `
                <div>
                    <h4 class="font-medium text-gray-900 mb-4">Latest Vital Signs</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        ${patient.latest_vitals.systolic ? `
                            <div class="text-center p-2 bg-gray-50 rounded">
                                <p class="text-gray-600">Blood Pressure</p>
                                <p class="font-bold">${patient.latest_vitals.systolic}/${patient.latest_vitals.diastolic}</p>
                            </div>
                        ` : ''}
                        ${patient.latest_vitals.heart_rate ? `
                            <div class="text-center p-2 bg-gray-50 rounded">
                                <p class="text-gray-600">Heart Rate</p>
                                <p class="font-bold">${patient.latest_vitals.heart_rate} bpm</p>
                            </div>
                        ` : ''}
                        ${patient.latest_vitals.temperature ? `
                            <div class="text-center p-2 bg-gray-50 rounded">
                                <p class="text-gray-600">Temperature</p>
                                <p class="font-bold">${patient.latest_vitals.temperature}°C</p>
                            </div>
                        ` : ''}
                        ${patient.latest_vitals.oxygen_saturation ? `
                            <div class="text-center p-2 bg-gray-50 rounded">
                                <p class="text-gray-600">O2 Saturation</p>
                                <p class="font-bold">${patient.latest_vitals.oxygen_saturation}%</p>
                            </div>
                        ` : ''}
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Recorded: ${new Date(patient.latest_vitals.created_at).toLocaleString()}
                    </p>
                </div>
            ` : '<div><p class="text-gray-500">No vital signs data available</p></div>'}
        </div>
        
        ${patient.latest_vitals && patient.latest_vitals.alerts && patient.latest_vitals.alerts.length > 0 ? `
            <div class="mt-6">
                <h4 class="font-medium text-gray-900 mb-4">Active Alerts</h4>
                <div class="space-y-2">
                    ${patient.latest_vitals.alerts.map(alert => `
                        <div class="p-3 bg-${alert.type === 'danger' ? 'red' : 'yellow'}-50 border border-${alert.type === 'danger' ? 'red' : 'yellow'}-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-${alert.type === 'danger' ? 'exclamation-triangle' : 'exclamation'} text-${alert.type === 'danger' ? 'red' : 'yellow'}-500 mr-2"></i>
                                <span class="text-${alert.type === 'danger' ? 'red' : 'yellow'}-800">${alert.message}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        ` : ''}
        
        <div class="flex justify-end space-x-3 mt-6">
            <a href="/patients/${patient.id}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                View Full Profile
            </a>
            <a href="/patients/${patient.id}/vitals" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                View All Vitals
            </a>
        </div>
    `;
    
    document.getElementById('patientDetailContent').innerHTML = content;
    document.getElementById('patientDetailModal').classList.remove('hidden');
}

function closePatientModal() {
    document.getElementById('patientDetailModal').classList.add('hidden');
}

function refreshAllData() {
    loadPatients();
}

function setupEventListeners() {
    // Filter event listeners
    document.getElementById('alertFilter').addEventListener('change', renderPatients);
    document.getElementById('riskFilter').addEventListener('change', renderPatients);
    document.getElementById('patientSearch').addEventListener('input', renderPatients);
    
    // Auto-refresh toggle
    document.getElementById('autoRefresh').addEventListener('change', function() {
        if (this.checked) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    });
}

function startAutoRefresh() {
    stopAutoRefresh(); // Clear any existing interval
    refreshInterval = setInterval(loadPatients, 30000); // Refresh every 30 seconds
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
}

function showLoading() {
    // Show loading state
}

function showError(message) {
    console.error(message);
    // Show error notification
}

function showNoData() {
    document.getElementById('noDataState').classList.remove('hidden');
    document.getElementById('gridView').classList.add('hidden');
    document.getElementById('listView').classList.add('hidden');
}

function hideNoData() {
    document.getElementById('noDataState').classList.add('hidden');
}

// Real-time WebSocket integration (if available)
// Echo.channel('doctor-monitor')
//     .listen('VitalSignUpdated', (e) => {
//         updatePatientInList(e.patient_id, e.vitalSign);
//         if (e.vitalSign.is_critical) {
//             showCriticalAlert(e.patient, e.vitalSign);
//         }
//     })
//     .listen('PatientStatusChanged', (e) => {
//         updatePatientStatus(e.patient_id, e.status);
//     });

function showCriticalAlert(patient, vitalSign) {
    const content = `
        <div class="text-center mb-4">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="mt-2 text-lg font-medium text-red-800">Critical Vital Signs</h3>
        </div>
        
        <div class="text-sm text-red-700">
            <p class="font-medium">${patient.user.name}</p>
            <p class="text-xs text-red-600">Patient ID: ${patient.patient_id}</p>
            
            <div class="mt-4 space-y-2">
                ${vitalSign.alerts.map(alert => `
                    <div class="p-2 bg-red-100 rounded">
                        ${alert.message}
                    </div>
                `).join('')}
            </div>
        </div>
        
        <div class="flex justify-center space-x-3 mt-6">
            <button onclick="closeCriticalAlert()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                Dismiss
            </button>
            <a href="/patients/${patient.id}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                View Patient
            </a>
        </div>
    `;
    
    document.getElementById('criticalAlertContent').innerHTML = content;
    document.getElementById('criticalAlertModal').classList.remove('hidden');
}

function closeCriticalAlert() {
    document.getElementById('criticalAlertModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('patientDetailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePatientModal();
    }
});

document.getElementById('criticalAlertModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCriticalAlert();
    }
});
</script>
@endpush
@endsection