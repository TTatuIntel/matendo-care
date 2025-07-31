<?php

namespace App\Http\Controllers;

use App\Models\VitalSign;
use App\Models\Patient;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VitalSignController extends Controller
{
    /**
     * Display a listing of vital signs.
     */
    public function index(Request $request)
    {
        $query = VitalSign::with(['patient.user', 'recorder']);

        // For doctors, show only their patients' vitals
        if (auth()->user()->isDoctor()) {
            $patientIds = auth()->user()->doctor->activePatients()->pluck('patients.id');
            $query->whereIn('patient_id', $patientIds);
        }

        // For patients, show only their own vitals
        if (auth()->user()->isPatient()) {
            $query->where('patient_id', auth()->user()->patient->id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by critical status
        if ($request->filled('critical')) {
            $query->where('is_critical', $request->boolean('critical'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $vitalSigns = $query->latest()->paginate(20);

        return view('vital-signs.index', compact('vitalSigns'));
    }

    /**
     * Store a newly created vital sign.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'systolic' => 'nullable|numeric|min:60|max:250',
            'diastolic' => 'nullable|numeric|min:40|max:150',
            'heart_rate' => 'nullable|numeric|min:40|max:200',
            'temperature' => 'nullable|numeric|min:35|max:42',
            'respiratory_rate' => 'nullable|numeric|min:10|max:40',
            'oxygen_saturation' => 'nullable|numeric|min:80|max:100',
            'blood_sugar' => 'nullable|numeric|min:50|max:400',
            'weight' => 'nullable|numeric|min:20|max:300',
            'height' => 'nullable|numeric|min:100|max:250',
            'pain_level' => 'nullable|integer|min:0|max:10',
            'mood' => 'nullable|string|in:excellent,good,okay,poor,terrible',
        ]);

        // Verify patient access
        $patient = Patient::findOrFail($validated['patient_id']);
        $this->authorizePatientAccess($patient);

        DB::beginTransaction();
        try {
            // Create vital sign record
            $vitalSign = VitalSign::create([
                ...$validated,
                'recorded_by' => auth()->id(),
            ]);

            // Calculate BMI if height and weight are provided
            if ($vitalSign->height && $vitalSign->weight) {
                $vitalSign->calculateBmi();
            }

            // Check for critical values and set alerts
            $alertData = $vitalSign->checkVitals();

            // Create notifications for critical values
            if ($alertData['is_critical']) {
                $this->createCriticalVitalNotifications($patient, $vitalSign, $alertData['alerts']);
            }

            // Log activity
            ActivityLog::logCreate($vitalSign, "Recorded vital signs for patient: {$patient->user->name}");

            DB::commit();

            // Real-time notification (if WebSocket is implemented)
            // broadcast(new VitalSignUpdated($vitalSign))->toOthers();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'vital_sign' => $vitalSign->load('patient.user'),
                    'alerts' => $alertData['alerts'],
                    'is_critical' => $alertData['is_critical'],
                ]);
            }

            $message = $alertData['is_critical'] 
                ? 'Vital signs recorded with critical values. Medical team has been notified.'
                : 'Vital signs recorded successfully.';

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to record vital signs. Please try again.'
                ], 500);
            }

            return back()->withInput()->with('error', 'Failed to record vital signs. Please try again.');
        }
    }

    /**
     * Display the specified vital sign.
     */
    public function show(VitalSign $vitalSign)
    {
        $this->authorizePatientAccess($vitalSign->patient);

        $vitalSign->load(['patient.user', 'recorder']);

        // Log view activity
        ActivityLog::logView($vitalSign, "Viewed vital signs for patient: {$vitalSign->patient->user->name}");

        if (request()->ajax()) {
            return response()->json($vitalSign);
        }

        return view('vital-signs.show', compact('vitalSign'));
    }

    /**
     * Update the specified vital sign.
     */
    public function update(Request $request, VitalSign $vitalSign)
    {
        $this->authorize('update', $vitalSign);

        $validated = $request->validate([
            'systolic' => 'nullable|numeric|min:60|max:250',
            'diastolic' => 'nullable|numeric|min:40|max:150',
            'heart_rate' => 'nullable|numeric|min:40|max:200',
            'temperature' => 'nullable|numeric|min:35|max:42',
            'respiratory_rate' => 'nullable|numeric|min:10|max:40',
            'oxygen_saturation' => 'nullable|numeric|min:80|max:100',
            'blood_sugar' => 'nullable|numeric|min:50|max:400',
            'weight' => 'nullable|numeric|min:20|max:300',
            'height' => 'nullable|numeric|min:100|max:250',
            'pain_level' => 'nullable|integer|min:0|max:10',
            'mood' => 'nullable|string|in:excellent,good,okay,poor,terrible',
        ]);

        $oldValues = $vitalSign->toArray();

        $vitalSign->update($validated);

        // Recalculate BMI if height and weight are provided
        if ($vitalSign->height && $vitalSign->weight) {
            $vitalSign->calculateBmi();
        }

        // Recheck for critical values
        $alertData = $vitalSign->checkVitals();

        // Create notifications for new critical values
        if ($alertData['is_critical'] && !$oldValues['is_critical']) {
            $this->createCriticalVitalNotifications($vitalSign->patient, $vitalSign, $alertData['alerts']);
        }

        // Log activity
        ActivityLog::logUpdate($vitalSign, $oldValues, "Updated vital signs for patient: {$vitalSign->patient->user->name}");

        return redirect()->back()->with('success', 'Vital signs updated successfully.');
    }

    /**
     * Remove the specified vital sign.
     */
    public function destroy(VitalSign $vitalSign)
    {
        $this->authorize('delete', $vitalSign);

        $patientName = $vitalSign->patient->user->name;

        // Log activity before deletion
        ActivityLog::logDelete($vitalSign, "Deleted vital signs for patient: {$patientName}");

        $vitalSign->delete();

        return redirect()->back()->with('success', 'Vital signs deleted successfully.');
    }

    /**
     * Patient-specific vital signs index.
     */
    public function patientIndex(Request $request)
    {
        $patient = auth()->user()->patient;
        
        $vitalSigns = $patient->vitalSigns()
            ->with('recorder')
            ->latest()
            ->paginate(20);

        $latestVitals = $patient->latestVitalSigns;

        return view('patient.vitals.index', compact('vitalSigns', 'latestVitals', 'patient'));
    }

    /**
     * Store vital signs from patient.
     */
    public function patientStore(Request $request)
    {
        $validated = $request->validate([
            'systolic' => 'nullable|numeric|min:60|max:250',
            'diastolic' => 'nullable|numeric|min:40|max:150',
            'heart_rate' => 'nullable|numeric|min:40|max:200',
            'temperature' => 'nullable|numeric|min:35|max:42',
            'respiratory_rate' => 'nullable|numeric|min:10|max:40',
            'oxygen_saturation' => 'nullable|numeric|min:80|max:100',
            'blood_sugar' => 'nullable|numeric|min:50|max:400',
            'weight' => 'nullable|numeric|min:20|max:300',
            'pain_level' => 'nullable|integer|min:0|max:10',
            'mood' => 'nullable|string|in:excellent,good,okay,poor,terrible',
        ]);

        $validated['patient_id'] = auth()->user()->patient->id;
        $validated['recorded_by'] = auth()->id();

        return $this->store(new Request($validated));
    }

    /**
     * Real-time monitoring dashboard for doctors.
     */
    public function realTimeMonitor()
    {
        $this->authorize('viewAny', VitalSign::class);

        $doctor = auth()->user()->doctor;
        $patients = $doctor->activePatients()
            ->with(['user', 'vitalSigns' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->get();

        // Add monitoring data
        $monitoringData = $patients->map(function ($patient) {
            $latestVitals = $patient->vitalSigns->first();
            
            return [
                'id' => $patient->id,
                'user' => $patient->user,
                'patient_id' => $patient->patient_id,
                'risk_level' => $patient->risk_level,
                'latest_vitals' => $latestVitals,
                'alert_level' => $latestVitals && $latestVitals->is_critical ? 'critical' : 
                              ($latestVitals && count($latestVitals->alerts ?? []) > 0 ? 'warning' : 'normal'),
                'alert_count' => $latestVitals ? count($latestVitals->alerts ?? []) : 0,
                'online_status' => $this->getPatientOnlineStatus($patient),
            ];
        });

        $counts = [
            'total' => $patients->count(),
            'critical' => $monitoringData->where('alert_level', 'critical')->count(),
            'warning' => $monitoringData->where('alert_level', 'warning')->count(),
            'online' => $monitoringData->where('online_status', true)->count(),
        ];

        if (request()->ajax()) {
            return response()->json([
                'patients' => $monitoringData,
                'counts' => $counts,
            ]);
        }

        return view('doctor.real-time-monitor', compact('monitoringData', 'counts'));
    }

    /**
     * Get latest vital signs for a patient.
     */
    public function getLatest(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $latestVitals = $patient->vitalSigns()->latest()->first();

        return response()->json($latestVitals);
    }

    /**
     * Get vital signs trends for a patient.
     */
    public function getTrends(Patient $patient, Request $request)
    {
        $this->authorizePatientAccess($patient);

        $days = $request->get('days', 30);
        
        $vitals = $patient->vitalSigns()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at')
            ->get();

        return response()->json($vitals);
    }

    /**
     * Create critical vital sign notifications.
     */
    protected function createCriticalVitalNotifications(Patient $patient, VitalSign $vitalSign, array $alerts)
    {
        // Notify all doctors assigned to this patient
        $doctors = $patient->doctors()->wherePivot('status', 'active')->get();

        foreach ($doctors as $doctor) {
            Notification::create([
                'user_id' => $doctor->user_id,
                'patient_id' => $patient->id,
                'type' => 'critical_vital_signs',
                'priority' => 'critical',
                'title' => 'Critical Vital Signs Alert',
                'message' => "Patient {$patient->user->name} has critical vital signs that require immediate attention.",
                'data' => [
                    'vital_sign_id' => $vitalSign->id,
                    'alerts' => $alerts,
                    'patient_id' => $patient->id,
                    'patient_name' => $patient->user->name,
                ],
                'action_url' => route('patients.vitals.show', [$patient->id, $vitalSign->id]),
                'is_actionable' => true,
                'expires_at' => now()->addHours(2),
            ]);
        }

        // Also notify patient if vitals are extremely critical
        $extremelyCritical = collect($alerts)->contains(function ($alert) {
            return str_contains(strtolower($alert['message']), 'immediate') || 
                   str_contains(strtolower($alert['message']), 'crisis');
        });

        if ($extremelyCritical) {
            Notification::create([
                'user_id' => $patient->user_id,
                'patient_id' => $patient->id,
                'type' => 'critical_vital_signs_patient',
                'priority' => 'critical',
                'title' => 'Important Health Alert',
                'message' => 'Your latest vital signs reading shows values that require immediate medical attention. Please contact your doctor immediately.',
                'data' => [
                    'vital_sign_id' => $vitalSign->id,
                    'alerts' => $alerts,
                ],
                'action_url' => route('patient.vitals.index'),
                'is_actionable' => true,
                'expires_at' => now()->addHours(6),
            ]);
        }
    }

    /**
     * Get patient online status (mock implementation).
     */
    protected function getPatientOnlineStatus(Patient $patient)
    {
        // In a real implementation, this would check when the patient last logged in
        // or had activity on the platform
        return $patient->user->updated_at > now()->subMinutes(30);
    }

    /**
     * Authorize patient access.
     */
    protected function authorizePatientAccess(Patient $patient)
    {
        if (auth()->user()->isDoctor()) {
            $hasAccess = $patient->doctors()
                ->where('doctors.id', auth()->user()->doctor->id)
                ->where('doctor_patients.status', 'active')
                ->exists();
            
            if (!$hasAccess) {
                abort(403, 'Unauthorized access to patient vitals.');
            }
        } elseif (auth()->user()->isPatient() && auth()->user()->patient->id !== $patient->id) {
            abort(403, 'Unauthorized access to patient vitals.');
        }
    }
}