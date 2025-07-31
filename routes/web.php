<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\VitalSignController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\HealthGoalController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TempAccessLogController;
use App\Http\Controllers\DoctorPatientController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Dashboard Routes (Protected by Auth)
Route::middleware(['auth', 'verified'])->group(function () {
    // Universal Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::post('/notifications/{notification}/read', [DashboardController::class, 'markNotificationAsRead'])->name('notifications.read');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/action', [NotificationController::class, 'takeAction'])->name('notifications.action');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Universal Patient Management (Accessible by Admins and Doctors)
    Route::middleware(['role:admin,doctor'])->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('/patients/{patient}/vitals', [PatientController::class, 'vitals'])->name('patients.vitals');
        Route::get('/patients/{patient}/records', [PatientController::class, 'records'])->name('patients.records');
        Route::get('/patients/{patient}/documents', [PatientController::class, 'documents'])->name('patients.documents');
        Route::get('/patients/{patient}/appointments', [PatientController::class, 'appointments'])->name('patients.appointments');
    });

    // Doctor-Patient Relationship Management
    Route::middleware(['role:admin,doctor'])->group(function () {
        Route::post('/doctor-patients', [DoctorPatientController::class, 'store'])->name('doctor-patients.store');
        Route::patch('/doctor-patients/{doctorPatient}', [DoctorPatientController::class, 'update'])->name('doctor-patients.update');
        Route::delete('/doctor-patients/{doctorPatient}', [DoctorPatientController::class, 'destroy'])->name('doctor-patients.destroy');
        Route::post('/doctor-patients/{doctorPatient}/make-primary', [DoctorPatientController::class, 'makePrimary'])->name('doctor-patients.make-primary');
    });

    // Medical Records
    Route::resource('medical-records', MedicalRecordController::class)->except(['create', 'edit']);
    Route::post('/medical-records/{medicalRecord}/review', [MedicalRecordController::class, 'markAsReviewed'])->name('medical-records.review');

    // Vital Signs
    Route::resource('vital-signs', VitalSignController::class)->except(['create', 'edit']);
    Route::get('/vitals/{vitalSign}', [VitalSignController::class, 'show'])->name('vitals.show');

    // Documents
    Route::resource('documents', DocumentController::class);
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/view', [DocumentController::class, 'view'])->name('documents.view');
    Route::post('/documents/{document}/verify', [DocumentController::class, 'verify'])->name('documents.verify');

    // Appointments
    Route::resource('appointments', AppointmentController::class);
    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');

    // Medications
    Route::resource('medications', MedicationController::class);
    Route::post('/medications/{medication}/take', [MedicationController::class, 'markAsTaken'])->name('medications.take');
    Route::post('/medications/{medication}/skip', [MedicationController::class, 'markAsSkipped'])->name('medications.skip');

    // Health Goals
    Route::resource('health-goals', HealthGoalController::class);
    Route::post('/health-goals/{healthGoal}/update-progress', [HealthGoalController::class, 'updateProgress'])->name('health-goals.update-progress');

    // Comments
    Route::resource('comments', CommentController::class)->except(['index', 'show']);

    // Temporary Access Links
    Route::middleware(['role:admin,doctor'])->group(function () {
        Route::get('/temp-access', [TempAccessLogController::class, 'index'])->name('temp-access.index');
        Route::post('/temp-access', [TempAccessLogController::class, 'store'])->name('temp-access.store');
        Route::get('/temp-access/create', [TempAccessLogController::class, 'create'])->name('temp-access.create');
        Route::delete('/temp-access/{tempAccess}', [TempAccessLogController::class, 'destroy'])->name('temp-access.destroy');
    });

    // Temporary Access Route (No Auth Required)
    Route::get('/temp/{token}', [TempAccessLogController::class, 'access'])->name('temp-access.view')->withoutMiddleware(['auth', 'verified']);
});

// PATIENT-SPECIFIC ROUTES
Route::middleware(['auth', 'verified', 'role:patient'])->group(function () {
    // Patient Dashboard
    Route::get('/patient/dashboard', [DashboardController::class, 'index'])->name('patient.dashboard');

    // Patient Profile Management (MISSING ROUTES ADDED)
    Route::get('/patient/profile', [PatientController::class, 'profile'])->name('patient.profile.edit');
    Route::patch('/patient/profile', [PatientController::class, 'updateProfile'])->name('patient.profile.update');
    Route::get('/patient/settings', [PatientController::class, 'settings'])->name('patient.settings');
    Route::patch('/patient/settings', [PatientController::class, 'updateSettings'])->name('patient.settings.update');
    Route::get('/patient/privacy', [PatientController::class, 'privacy'])->name('patient.privacy');
    Route::patch('/patient/privacy', [PatientController::class, 'updatePrivacy'])->name('patient.privacy.update');

    // Daily Health Updates
    Route::get('/patient/health/daily', [PatientController::class, 'dailyHealthForm'])->name('patient.health.daily');
    Route::post('/patient/health/daily', [PatientController::class, 'storeDailyHealth'])->name('patient.health.store');

    // Patient Vital Signs
    Route::get('/patient/vitals', [VitalSignController::class, 'patientIndex'])->name('patient.vitals.index');
    Route::post('/patient/vitals', [VitalSignController::class, 'patientStore'])->name('patient.vitals.store');

    // Patient Medications
    Route::get('/patient/medications', [MedicationController::class, 'patientIndex'])->name('patient.medications.index');
    Route::post('/patient/medications/{medication}/adherence', [MedicationController::class, 'recordAdherence'])->name('patient.medications.adherence');

    // Patient Documents
    Route::get('/patient/documents', [DocumentController::class, 'patientIndex'])->name('patient.documents.index');
    Route::get('/patient/documents/upload', [DocumentController::class, 'uploadForm'])->name('patient.documents.upload');
    Route::post('/patient/documents/upload', [DocumentController::class, 'patientStore'])->name('patient.documents.store');

    // Patient Appointments
    Route::get('/patient/appointments', [AppointmentController::class, 'patientIndex'])->name('patient.appointments.index');
    Route::get('/patient/appointments/create', [AppointmentController::class, 'patientCreate'])->name('patient.appointments.create');
    Route::post('/patient/appointments', [AppointmentController::class, 'patientStore'])->name('patient.appointments.store');

    // Patient Health Goals
    Route::get('/patient/goals', [HealthGoalController::class, 'patientIndex'])->name('patient.goals.index');
    Route::get('/patient/goals/create', [HealthGoalController::class, 'patientCreate'])->name('patient.goals.create');
    Route::post('/patient/goals', [HealthGoalController::class, 'patientStore'])->name('patient.goals.store');

    // Patient Medical Records (View Only)
    Route::get('/patient/records', [MedicalRecordController::class, 'patientIndex'])->name('patient.records.index');
});

// DOCTOR-SPECIFIC ROUTES
Route::middleware(['auth', 'verified', 'role:doctor'])->group(function () {
    // Doctor Dashboard
    Route::get('/doctor/dashboard', [DashboardController::class, 'index'])->name('doctor.dashboard');

    // Doctor Profile Management
    Route::get('/doctor/profile', [DoctorController::class, 'profile'])->name('doctor.profile.edit');
    Route::patch('/doctor/profile', [DoctorController::class, 'updateProfile'])->name('doctor.profile.update');
    Route::get('/doctor/settings', [DoctorController::class, 'settings'])->name('doctor.settings');
    Route::patch('/doctor/settings', [DoctorController::class, 'updateSettings'])->name('doctor.settings.update');

    // Doctor Patients Management
    Route::get('/doctor/patients', [PatientController::class, 'doctorIndex'])->name('doctor.patients.index');
    Route::get('/doctor/patients/create', [PatientController::class, 'doctorCreate'])->name('doctor.patients.create');
    Route::post('/doctor/patients', [PatientController::class, 'doctorStore'])->name('doctor.patients.store');

    // Doctor Appointments
    Route::get('/doctor/appointments', [AppointmentController::class, 'doctorIndex'])->name('doctor.appointments.index');
    Route::get('/doctor/appointments/create', [AppointmentController::class, 'doctorCreate'])->name('doctor.appointments.create');
    Route::post('/doctor/appointments', [AppointmentController::class, 'doctorStore'])->name('doctor.appointments.store');

    // Critical Alerts
    Route::get('/doctor/critical-alerts', [NotificationController::class, 'criticalAlerts'])->name('doctor.critical-alerts');

    // Pending Reviews
    Route::get('/doctor/reviews/pending', [MedicalRecordController::class, 'pendingReviews'])->name('doctor.reviews.pending');

    // Real-time Monitoring
    Route::get('/doctor/real-time/monitor', [VitalSignController::class, 'realTimeMonitor'])->name('doctor.real-time.monitor');

    // Analytics
    Route::get('/doctor/analytics', [DashboardController::class, 'doctorAnalytics'])->name('doctor.analytics');

    // Temporary Access Management
    Route::get('/doctor/temp-access/create', [TempAccessLogController::class, 'doctorCreate'])->name('doctor.temp-access.create');
    Route::post('/doctor/temp-access', [TempAccessLogController::class, 'doctorStore'])->name('doctor.temp-access.store');

    // Medications Management
    Route::get('/doctor/medications', [MedicationController::class, 'doctorIndex'])->name('doctor.medications.index');
    Route::get('/doctor/medications/prescribe', [MedicationController::class, 'prescribeForm'])->name('doctor.medications.prescribe');
    Route::post('/doctor/medications/prescribe', [MedicationController::class, 'prescribe'])->name('doctor.medications.prescribe.store');

    // Documents Management
    Route::get('/doctor/documents', [DocumentController::class, 'doctorIndex'])->name('doctor.documents.index');

    // Communication
    Route::get('/doctor/messages', [CommentController::class, 'doctorMessages'])->name('doctor.messages.index');
    Route::get('/doctor/consultations', [AppointmentController::class, 'consultations'])->name('doctor.consultations.index');
});

// ADMIN-SPECIFIC ROUTES
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/overview', [DashboardController::class, 'adminOverview'])->name('admin.overview');
    Route::get('/admin/analytics', [DashboardController::class, 'adminAnalytics'])->name('admin.analytics');
    Route::get('/admin/system-status', [DashboardController::class, 'systemStatus'])->name('admin.system-status');

    // Admin Profile Management
    Route::get('/admin/profile', [ProfileController::class, 'adminEdit'])->name('admin.profile.edit');
    Route::patch('/admin/profile', [ProfileController::class, 'adminUpdate'])->name('admin.profile.update');
    Route::get('/admin/settings', [DashboardController::class, 'settings'])->name('admin.settings.index');
    Route::post('/admin/settings', [DashboardController::class, 'updateSettings'])->name('admin.settings.update');

    // User Management
    Route::get('/admin/users', [DashboardController::class, 'users'])->name('admin.users.index');
    Route::get('/admin/users/create', [DashboardController::class, 'createUser'])->name('admin.users.create');
    Route::post('/admin/users', [DashboardController::class, 'storeUser'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [DashboardController::class, 'editUser'])->name('admin.users.edit');
    Route::patch('/admin/users/{user}', [DashboardController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [DashboardController::class, 'destroyUser'])->name('admin.users.destroy');

    // Doctor Management
    Route::get('/admin/doctors', [DoctorController::class, 'adminIndex'])->name('admin.doctors.index');
    Route::get('/admin/doctors/create', [DoctorController::class, 'adminCreate'])->name('admin.doctors.create');
    Route::post('/admin/doctors', [DoctorController::class, 'adminStore'])->name('admin.doctors.store');
    Route::get('/admin/doctors/{doctor}/edit', [DoctorController::class, 'adminEdit'])->name('admin.doctors.edit');
    Route::patch('/admin/doctors/{doctor}', [DoctorController::class, 'adminUpdate'])->name('admin.doctors.update');
    Route::delete('/admin/doctors/{doctor}', [DoctorController::class, 'adminDestroy'])->name('admin.doctors.destroy');

    // Patient Management (Admin Level)
    Route::get('/admin/patients', [PatientController::class, 'adminIndex'])->name('admin.patients.index');
    Route::get('/admin/patients/create', [PatientController::class, 'adminCreate'])->name('admin.patients.create');
    Route::post('/admin/patients', [PatientController::class, 'adminStore'])->name('admin.patients.store');
    Route::get('/admin/patients/critical', [PatientController::class, 'criticalPatients'])->name('admin.patients.critical');

    // Admin Management
    Route::get('/admin/admins', [DashboardController::class, 'admins'])->name('admin.admins.index');
    Route::get('/admin/admins/create', [DashboardController::class, 'createAdmin'])->name('admin.admins.create');
    Route::post('/admin/admins', [DashboardController::class, 'storeAdmin'])->name('admin.admins.store');

    // Appointment Management
    Route::get('/admin/appointments', [AppointmentController::class, 'adminIndex'])->name('admin.appointments.index');
    Route::get('/admin/appointments/create', [AppointmentController::class, 'adminCreate'])->name('admin.appointments.create');
    Route::post('/admin/appointments', [AppointmentController::class, 'adminStore'])->name('admin.appointments.store');

    // Medical Records Management
    Route::get('/admin/medical-records', [MedicalRecordController::class, 'adminIndex'])->name('admin.medical-records.index');

    // Medication Management
    Route::get('/admin/medications', [MedicationController::class, 'adminIndex'])->name('admin.medications.index');

    // Document Management
    Route::get('/admin/documents', [DocumentController::class, 'adminIndex'])->name('admin.documents.index');

    // Notification Management
    Route::get('/admin/notifications', [NotificationController::class, 'adminIndex'])->name('admin.notifications.index');
    Route::post('/admin/notifications/broadcast', [NotificationController::class, 'broadcast'])->name('admin.notifications.broadcast');

    // Activity Logs
    Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    Route::get('/admin/activity-logs/{log}', [ActivityLogController::class, 'show'])->name('admin.activity-logs.show');

    // Temporary Access Management
    Route::get('/admin/temp-access', [TempAccessLogController::class, 'adminIndex'])->name('admin.temp-access.index');

    // Critical Alerts
    Route::get('/admin/critical-alerts', [NotificationController::class, 'adminCriticalAlerts'])->name('admin.critical-alerts');

    // Backup Management
    Route::get('/admin/backups', [DashboardController::class, 'backups'])->name('admin.backups.index');
    Route::post('/admin/backups/create', [DashboardController::class, 'createBackup'])->name('admin.backups.create');
    Route::delete('/admin/backups/{backup}', [DashboardController::class, 'deleteBackup'])->name('admin.backups.delete');

    // Maintenance
    Route::get('/admin/maintenance', [DashboardController::class, 'maintenance'])->name('admin.maintenance');
    Route::post('/admin/maintenance/clear-cache', [DashboardController::class, 'clearCache'])->name('admin.maintenance.clear-cache');
    Route::post('/admin/maintenance/optimize', [DashboardController::class, 'optimize'])->name('admin.maintenance.optimize');
});

// API Routes for Real-time Features
Route::middleware(['auth:sanctum', 'verified'])->prefix('api')->group(function () {
    // Real-time vital signs updates
    Route::get('/patients/{patient}/vitals/latest', [VitalSignController::class, 'getLatest'])->name('api.vitals.latest');
    Route::get('/patients/{patient}/vitals/trends', [VitalSignController::class, 'getTrends'])->name('api.vitals.trends');

    // Real-time notifications
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('api.notifications.unread');
    Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount'])->name('api.notifications.count');

    // Dashboard statistics
    Route::get('/dashboard/stats/{type}', [DashboardController::class, 'getRealtimeStats'])->name('api.dashboard.stats');

    // Patient monitoring
    Route::get('/doctor/patients/monitoring', [PatientController::class, 'getMonitoringData'])->name('api.doctor.patients.monitoring');
});

// WebSocket Broadcasting Routes (if using Laravel Echo)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/broadcasting/auth', function () {
        return auth()->user();
    })->name('broadcasting.auth');
});

// Health Check Route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => config('app.version', '1.0.0')
    ]);
})->name('health-check');

// Fallback Route
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

require __DIR__.'/auth.php';