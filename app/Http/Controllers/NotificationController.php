<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Patient;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::where('user_id', auth()->id())
            ->with(['patient.user', 'actionTaker']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by read status
        if ($request->filled('read')) {
            $query->where('is_read', $request->boolean('read'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $notifications = $query->latest()->paginate(20);

        // Get summary counts
        $unreadCount = Notification::where('user_id', auth()->id())->unread()->count();
        $criticalCount = Notification::where('user_id', auth()->id())->critical()->unread()->count();
        $todayCount = Notification::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->count();
        $actionableCount = Notification::where('user_id', auth()->id())
            ->actionable()
            ->count();

        return view('notifications.index', compact(
            'notifications', 
            'unreadCount', 
            'criticalCount', 
            'todayCount', 
            'actionableCount'
        ));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        // Log activity
        ActivityLog::log(
            'notification_read',
            "Marked notification as read: {$notification->title}",
            $notification
        );

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $count = Notification::where('user_id', auth()->id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Log activity
        ActivityLog::log(
            'notifications_bulk_read',
            "Marked {$count} notifications as read"
        );

        if (request()->ajax()) {
            return response()->json(['success' => true, 'count' => $count]);
        }

        return redirect()->back()->with('success', "{$count} notifications marked as read.");
    }

    /**
     * Take action on a notification.
     */
    public function takeAction(Request $request, Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => 'required|string|max:255',
        ]);

        $notification->takeAction($validated['action']);

        // Mark as read if not already
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        // Log activity
        ActivityLog::log(
            'notification_action',
            "Took action on notification: {$notification->title} - Action: {$validated['action']}",
            $notification
        );

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Action recorded successfully.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $title = $notification->title;

        // Log activity before deletion
        ActivityLog::logDelete($notification, "Deleted notification: {$title}");

        $notification->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification deleted successfully.');
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', auth()->id())->unread()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get unread notifications.
     */
    public function getUnread(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $notifications = Notification::where('user_id', auth()->id())
            ->unread()
            ->active()
            ->with(['patient.user'])
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json($notifications);
    }

    /**
     * Critical alerts for doctors.
     */
    public function criticalAlerts()
    {
        $this->authorize('viewAny', Notification::class);

        $query = Notification::where('user_id', auth()->id())
            ->critical()
            ->active()
            ->with(['patient.user', 'actionTaker']);

        $criticalAlerts = $query->latest()->paginate(20);

        $stats = [
            'total_critical' => Notification::where('user_id', auth()->id())->critical()->count(),
            'unread_critical' => Notification::where('user_id', auth()->id())->critical()->unread()->count(),
            'today_critical' => Notification::where('user_id', auth()->id())->critical()->whereDate('created_at', today())->count(),
            'actionable_critical' => Notification::where('user_id', auth()->id())->critical()->actionable()->count(),
        ];

        return view('doctor.critical-alerts', compact('criticalAlerts', 'stats'));
    }

    /**
     * Admin notifications index.
     */
    public function adminIndex(Request $request)
    {
        $this->authorize('viewAny', Notification::class);

        $query = Notification::with(['user', 'patient.user', 'actionTaker']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by user type
        if ($request->filled('user_type')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('usertype', $request->user_type);
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $notifications = $query->latest()->paginate(20);

        // Get summary statistics
        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::unread()->count(),
            'critical' => Notification::critical()->count(),
            'today' => Notification::whereDate('created_at', today())->count(),
            'actionable' => Notification::actionable()->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Admin critical alerts.
     */
    public function adminCriticalAlerts()
    {
        $this->authorize('viewAny', Notification::class);

        $criticalAlerts = Notification::critical()
            ->active()
            ->with(['user', 'patient.user'])
            ->latest()
            ->paginate(20);

        $criticalPatients = Patient::where('risk_level', 'critical')
            ->orWhereHas('vitalSigns', function ($q) {
                $q->where('is_critical', true)
                    ->where('created_at', '>=', now()->subHours(24));
            })
            ->with(['user', 'doctors.user', 'vitalSigns' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->get();

        $stats = [
            'total_critical_alerts' => Notification::critical()->count(),
            'unread_critical_alerts' => Notification::critical()->unread()->count(),
            'critical_patients' => $criticalPatients->count(),
            'today_critical_alerts' => Notification::critical()->whereDate('created_at', today())->count(),
        ];

        return view('admin.critical-alerts', compact('criticalAlerts', 'criticalPatients', 'stats'));
    }

    /**
     * Broadcast notification to multiple users.
     */
    public function broadcast(Request $request)
    {
        $this->authorize('create', Notification::class);

        $validated = $request->validate([
            'user_type' => 'required|in:all,admin,doctor,patient',
            'priority' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'action_url' => 'nullable|url',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Get target users
        $usersQuery = \App\Models\User::query();
        
        if ($validated['user_type'] !== 'all') {
            $usersQuery->where('usertype', $validated['user_type']);
        }

        $users = $usersQuery->active()->get();

        $createdCount = 0;

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'system_broadcast',
                    'priority' => $validated['priority'],
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'action_url' => $validated['action_url'],
                    'expires_at' => $validated['expires_at'],
                    'is_actionable' => !empty($validated['action_url']),
                ]);
                
                $createdCount++;
            }

            // Log activity
            ActivityLog::log(
                'notification_broadcast',
                "Broadcasted notification to {$createdCount} users: {$validated['title']}"
            );

            DB::commit();

            return redirect()->back()->with('success', "Notification broadcasted to {$createdCount} users successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to broadcast notification. Please try again.');
        }
    }

    /**
     * Create system notification.
     */
    public static function createSystemNotification(
        $userId, 
        $type, 
        $title, 
        $message, 
        $priority = 'medium', 
        $actionUrl = null, 
        $data = null,
        $patientId = null,
        $expiresAt = null
    ) {
        return Notification::create([
            'user_id' => $userId,
            'patient_id' => $patientId,
            'type' => $type,
            'priority' => $priority,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'is_actionable' => !empty($actionUrl),
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Create appointment reminder notifications.
     */
    public static function createAppointmentReminder($appointment)
    {
        // Notify patient
        self::createSystemNotification(
            $appointment->patient->user_id,
            'appointment_reminder',
            'Appointment Reminder',
            "You have an appointment with Dr. {$appointment->doctor->user->name} on {$appointment->appointment_date->format('M d, Y')} at {$appointment->start_time}.",
            'medium',
            route('appointments.show', $appointment->id),
            ['appointment_id' => $appointment->id],
            $appointment->patient_id
        );

        // Notify doctor
        self::createSystemNotification(
            $appointment->doctor->user_id,
            'appointment_reminder',
            'Appointment Reminder',
            "You have an appointment with {$appointment->patient->user->name} on {$appointment->appointment_date->format('M d, Y')} at {$appointment->start_time}.",
            'medium',
            route('appointments.show', $appointment->id),
            ['appointment_id' => $appointment->id],
            $appointment->patient_id
        );
    }

    /**
     * Create medication reminder notification.
     */
    public static function createMedicationReminder($medication)
    {
        self::createSystemNotification(
            $medication->patient->user_id,
            'medication_reminder',
            'Medication Reminder',
            "It's time to take your medication: {$medication->name} ({$medication->dosage}).",
            'medium',
            route('patient.medications.index'),
            ['medication_id' => $medication->id],
            $medication->patient_id,
            now()->addHours(2)
        );
    }

    /**
     * Create document verification notification.
     */
    public static function createDocumentVerificationNotification($document)
    {
        $message = $document->is_verified 
            ? "Your document '{$document->original_filename}' has been verified by {$document->verifier->name}."
            : "Your document '{$document->original_filename}' requires verification.";

        self::createSystemNotification(
            $document->patient->user_id,
            'document_verification',
            'Document Update',
            $message,
            'low',
            route('patient.documents.index'),
            ['document_id' => $document->id],
            $document->patient_id
        );
    }
}