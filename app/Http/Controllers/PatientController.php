<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\VitalSign;
use App\Models\Appointment;
use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $query = Patient::with(['user', 'doctors.user']);

        // For doctors, show only their patients
        if (auth()->user()->isDoctor()) {
            $doctorId = auth()->user()->doctor->id;
            $query->whereHas('doctors', function ($q) use ($doctorId) {
                $q->where('doctors.id', $doctorId)
                    ->where('doctor_patients.status', 'active');
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('patient_id', 'like', "%{$search}%");
        }

        // Filter by risk level
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        $patients = $query->paginate(15);

        return view('patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        $doctors = Doctor::with('user')->available()->get();
        return view('patients.create', compact('doctors'));
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'address' => 'nullable|string',
            'blood_group' => 'nullable|string|max:10',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'insurance_number' => 'nullable|string|max:50',
            'insurance_provider' => 'nullable|string|max:100',
            'medical_history' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'primary_physician' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'assigned_doctor_id' => 'nullable|exists:doctors,id',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'usertype' => 'patient',
                'phone' => $validated['phone'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'blood_group' => $validated['blood_group'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
            ]);

            // Create patient profile
            $patient = Patient::create([
                'user_id' => $user->id,
                'patient_id' => Patient::generatePatientId(),
                'insurance_number' => $validated['insurance_number'] ?? null,
                'insurance_provider' => $validated['insurance_provider'] ?? null,
                'medical_history' => $validated['medical_history'] ?? null,
                'current_medications' => $validated['current_medications'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'chronic_conditions' => $validated['chronic_conditions'] ?? null,
                'primary_physician' => $validated['primary_physician'] ?? null,
                'height' => $validated['height'] ?? null,
                'weight' => $validated['weight'] ?? null,
            ]);

            // Assign doctor if provided
            if (isset($validated['assigned_doctor_id'])) {
                $patient->doctors()->attach($validated['assigned_doctor_id'], [
                    'status' => 'active',
                    'assigned_date' => now(),
                    'is_primary' => true,
                ]);
            }

            // Log activity
            ActivityLog::logCreate($patient, "Created new patient: {$user->name}");

            DB::commit();

            return redirect()->route('patients.show', $patient)
                ->with('success', 'Patient created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create patient. Please try again.');
        }
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient)
    {
        // Check access
        if (auth()->user()->isDoctor()) {
            $hasAccess = $patient->doctors()
                ->where('doctors.id', auth()->user()->doctor->id)
                ->where('doctor_patients.status', 'active')
                ->exists();
            
            if (!$hasAccess) {
                abort(403, 'Unauthorized access to patient record.');
            }
        }

        $patient->load(['user', 'doctors.user']);

        // Get latest vital signs
        $latestVitals = $patient->vitalSigns()->latest()->first();

        // Get recent medical records
        $recentRecords = $patient->medicalRecords()
            ->with('recorder')
            ->latest()
            ->limit(10)
            ->get();

        // Get upcoming appointments
        $upcomingAppointments = $patient->appointments()
            ->with('doctor.user')
            ->upcoming()
            ->orderBy('appointment_date')
            ->get();

        // Get active medications
        $activeMedications = $patient->medications()
            ->active()
            ->with('prescriber.user')
            ->get();

        // Get recent documents
        $recentDocuments = $patient->documents()
            ->latest()
            ->limit(5)
            ->get();

        // Log view activity
        ActivityLog::logView($patient, "Viewed patient profile: {$patient->user->name}");

        return view('patients.show', compact(
            'patient',
            'latestVitals',
            'recentRecords',
            'upcomingAppointments',
            'activeMedications',
            'recentDocuments'
        ));
    }

    /**
     * Show the form for editing patient.
     */
    public function edit(Patient $patient)
    {
        $this->authorize('update', $patient);
        
        $patient->load('user');
        $doctors = Doctor::with('user')->available()->get();
        $assignedDoctors = $patient->doctors()->wherePivot('status', 'active')->pluck('doctors.id')->toArray();

        return view('patients.edit', compact('patient', 'doctors', 'assignedDoctors'));
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, Patient $patient)
    {
        $this->authorize('update', $patient);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $patient->user_id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'address' => 'nullable|string',
            'blood_group' => 'nullable|string|max:10',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'insurance_number' => 'nullable|string|max:50',
            'insurance_provider' => 'nullable|string|max:100',
            'medical_history' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'primary_physician' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'risk_level' => 'required|in:low,medium,high,critical',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $patient->toArray();

            // Update user
            $patient->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'blood_group' => $validated['blood_group'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
            ]);

            // Update patient
            $patient->update([
                'insurance_number' => $validated['insurance_number'],
                'insurance_provider' => $validated['insurance_provider'],
                'medical_history' => $validated['medical_history'],
                'current_medications' => $validated['current_medications'],
                'allergies' => $validated['allergies'],
                'chronic_conditions' => $validated['chronic_conditions'],
                'primary_physician' => $validated['primary_physician'],
                'height' => $validated['height'],
                'weight' => $validated['weight'],
                'risk_level' => $validated['risk_level'],
            ]);

            // Log activity
            ActivityLog::logUpdate($patient, $oldValues, "Updated patient profile: {$patient->user->name}");

            DB::commit();

            return redirect()->route('patients.show', $patient)
                ->with('success', 'Patient updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update patient. Please try again.');
        }
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(Patient $patient)
    {
        $this->authorize('delete', $patient);

        $patientName = $patient->user->name;

        DB::beginTransaction();
        try {
            // Log activity before deletion
            ActivityLog::logDelete($patient, "Deleted patient: {$patientName}");

            // Soft delete patient and user
            $patient->delete();
            $patient->user->delete();

            DB::commit();

            return redirect()->route('patients.index')
                ->with('success', 'Patient deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete patient. Please try again.');
        }
    }

    /**
     * Show patient's vital signs.
     */
    public function vitals(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $vitalSigns = $patient->vitalSigns()
            ->with('recorder')
            ->latest()
            ->paginate(20);

        return view('patients.vitals', compact('patient', 'vitalSigns'));
    }

    /**
     * Show patient's medical records.
     */
    public function records(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $records = $patient->medicalRecords()
            ->with(['recorder', 'reviewer'])
            ->latest()
            ->paginate(20);

        return view('patients.records', compact('patient', 'records'));
    }

    /**
     * Show patient's documents.
     */
    public function documents(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $documents = $patient->documents()
            ->with('uploader')
            ->latest()
            ->paginate(20);

        return view('patients.documents', compact('patient', 'documents'));
    }

    /**
     * Authorize patient access for doctors.
     */
    protected function authorizePatientAccess(Patient $patient)
    {
        if (auth()->user()->isDoctor()) {
            $hasAccess = $patient->doctors()
                ->where('doctors.id', auth()->user()->doctor->id)
                ->where('doctor_patients.status', 'active')
                ->exists();
            
            if (!$hasAccess) {
                abort(403, 'Unauthorized access to patient record.');
            }
        } elseif (auth()->user()->isPatient() && auth()->user()->patient->id !== $patient->id) {
            abort(403, 'Unauthorized access to patient record.');
        }
    }
}