<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the appropriate dashboard based on user type.
     */
    public function index()
    {
        $user = auth()->user();

        switch ($user->usertype) {
            case 'admin':
                return $this->adminDashboard();
            case 'doctor':
                return $this->doctorDashboard();
            case 'patient':
                return $this->patientDashboard();
            default:
                return redirect()->route('login');
        }
    }

    /**
     * Admin dashboard.
     */
    protected function adminDashboard()
    {
        $stats = [
            'total_patients' => Patient::count(),
            'total_doctors' => Doctor::count(),
            'total_appointments' => Appointment::count(),
            'today_appointments' => Appointment::today()->count(),
            'active_patients' => Patient::whereHas('doctors', function ($q) {
                $q->where('doctor_patients.status', 'active');
            })->count(),
            'critical_patients' => Patient::where('risk_level', 'critical')->count(),
        ];

        $recentActivities = \App\Models\ActivityLog::with('user')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $upcomingAppointments = Appointment::with(['patient.user', 'doctor.user'])
            ->upcoming()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivities', 'upcomingAppointments'));
    }

    /**
     * Doctor dashboard.
     */
    protected function doctorDashboard()
    {
        $doctor = auth()->user()->doctor;

        // Auto-create doctor record if it doesn't exist
        if (!$doctor) {
            $doctor = Doctor::create([
                'user_id' => auth()->id(),
                'specialization' => 'General Practice',
                'license_number' => 'DOC' . str_pad(Doctor::count() + 1, 6, '0', STR_PAD_LEFT),
                'years_of_experience' => 0,
                'consultation_fee' => 0,
                'is_available' => true,
                'accepts_emergency' => false,
            ]);
        }

        $stats = [
            'total_patients' => $doctor->activePatients()->count(),
            'today_appointments' => $doctor->appointments()->today()->count(),
            'pending_reviews' => MedicalRecord::whereIn('patient_id', $doctor->activePatients()->pluck('patients.id'))
                ->unreviewed()
                ->count(),
            'critical_alerts' => Notification::where('user_id', auth()->id())
                ->unread()
                ->critical()
                ->count(),
        ];

        $todayAppointments = $doctor->appointments()
            ->with(['patient.user'])
            ->today()
            ->orderBy('start_time')
            ->get();

        $criticalPatients = $doctor->activePatients()
            ->where('risk_level', 'critical')
            ->orWhereHas('vitalSigns', function ($q) {
                $q->where('is_critical', true)
                    ->where('created_at', '>=', now()->subHours(24));
            })
            ->with(['user', 'vitalSigns' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->get();

        $recentRecords = MedicalRecord::whereIn('patient_id', $doctor->activePatients()->pluck('patients.id'))
            ->with(['patient.user', 'recorder'])
            ->requiresAttention()
            ->latest()
            ->limit(10)
            ->get();

        $notifications = Notification::where('user_id', auth()->id())
            ->unread()
            ->active()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('doctor.dashboard', compact(
            'stats',
            'todayAppointments',
            'criticalPatients',
            'recentRecords',
            'notifications'
        ));
    }

    /**
     * Patient dashboard.
     */
    protected function patientDashboard()
    {
        // Get or create patient record if it doesn't exist
        $patient = auth()->user()->patient;
        
        if (!$patient) {
            // Auto-create patient record for this user
            $patient = Patient::create([
                'user_id' => auth()->id(),
                'patient_id' => 'PAT' . str_pad(Patient::count() + 1, 6, '0', STR_PAD_LEFT),
                'risk_level' => 'low',
            ]);
        }

        $latestVitals = $patient->vitalSigns()->latest()->first();
        $upcomingAppointments = $patient->appointments()
            ->with(['doctor.user'])
            ->upcoming()
            ->orderBy('appointment_date')
            ->limit(3)
            ->get();

        $activeMedications = $patient->medications()
            ->active()
            ->with('prescriber.user')
            ->get();

        $activeGoals = $patient->healthGoals()
            ->active()
            ->orderBy('priority', 'desc')
            ->get();

        $recentRecords = $patient->medicalRecords()
            ->with('recorder')
            ->latest()
            ->limit(5)
            ->get();

        $documents = $patient->documents()
            ->latest()
            ->limit(5)
            ->get();

        // Get health trends for the last 30 days
        $healthTrends = $patient->vitalSigns()
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });

        $notifications = Notification::where('user_id', auth()->id())
            ->unread()
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('patient.dashboard', compact(
            'patient',
            'latestVitals',
            'upcomingAppointments',
            'activeMedications',
            'activeGoals',
            'recentRecords',
            'documents',
            'healthTrends',
            'notifications'
        ));
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Get dashboard stats for real-time updates.
     */
    public function getStats()
    {
        $user = auth()->user();

        if ($user->isDoctor()) {
            $doctor = $user->doctor;
            
            if (!$doctor) {
                return response()->json([]);
            }
            
            return response()->json([
                'critical_alerts' => Notification::where('user_id', $user->id)
                    ->unread()
                    ->critical()
                    ->count(),
                'pending_reviews' => MedicalRecord::whereIn('patient_id', $doctor->activePatients()->pluck('patients.id'))
                    ->unreviewed()
                    ->count(),
            ]);
        }

        return response()->json([]);
    }
}