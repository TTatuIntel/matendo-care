@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications Center')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Notifications Center</h2>
                <p class="text-gray-600">Stay updated with important alerts and messages</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="markAllAsRead()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-check-double mr-2"></i>Mark All Read
                </button>
                <button onclick="showNotificationSettings()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-cog mr-2"></i>Settings
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Unread Count -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bell text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Unread</p>
                    <p class="text-2xl font-bold text-gray-900" id="unreadCount">{{ $unreadCount ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Critical</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $criticalCount ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Today's Notifications -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-day text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $todayCount ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Actionable Items -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tasks text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Actionable</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $actionableCount ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex" aria-label="Tabs">
                <button onclick="filterNotifications('all')" 
                        class="filter-tab active w-1/4 py-4 px-1 text-center border-b-2 border-blue-500 font-medium text-sm text-blue-600" 
                        data-filter="all">
                    All Notifications
                </button>
                <button onclick="filterNotifications('unread')" 
                        class="filter-tab w-1/4 py-4 px-1 text-center border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        data-filter="unread">
                    Unread
                </button>
                <button onclick="filterNotifications('critical')" 
                        class="filter-tab w-1/4 py-4 px-1 text-center border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        data-filter="critical">
                    Critical
                </button>
                <button onclick="filterNotifications('actionable')" 
                        class="filter-tab w-1/4 py-4 px-1 text-center border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        data-filter="actionable">
                    Actionable
                </button>
            </nav>
        </div>

        <!-- Notifications List -->
        <div class="divide-y divide-gray-200" id="notificationsList">
            @forelse($notifications ?? [] as $notification)
                <div class="notification-item p-6 hover:bg-gray-50 {{ !$notification->is_read ? 'bg-blue-50' : '' }}" 
                     data-id="{{ $notification->id }}"
                     data-read="{{ $notification->is_read ? 'true' : 'false' }}"
                     data-priority="{{ $notification->priority }}"
                     data-actionable="{{ $notification->is_actionable ? 'true' : 'false' }}">
                    
                    <div class="flex items-start">
                        <!-- Notification Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-{{ $notification->priority_color }}-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-{{ $notification->priority_icon }} text-{{ $notification->priority_color }}-600"></i>
                            </div>
                            @if(!$notification->is_read)
                                <div class="w-3 h-3 bg-blue-500 rounded-full absolute -mt-8 -mr-1 border-2 border-white"></div>
                            @endif
                        </div>

                        <!-- Notification Content -->
                        <div class="ml-4 flex-1">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $notification->title }}</h4>
                                    <p class="mt-1 text-sm text-gray-600">{{ $notification->message }}</p>
                                    
                                    <!-- Notification Details -->
                                    <div class="mt-2 flex items-center text-xs text-gray-500 space-x-4">
                                        <span>
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        @if($notification->patient)
                                            <span>
                                                <i class="fas fa-user mr-1"></i>
                                                {{ $notification->patient->user->name }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $notification->priority_color }}-100 text-{{ $notification->priority_color }}-800">
                                            {{ ucfirst($notification->priority) }}
                                        </span>
                                        @if($notification->expires_at)
                                            <span class="text-orange-600">
                                                <i class="fas fa-hourglass-half mr-1"></i>
                                                Expires {{ $notification->expires_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    @if($notification->is_actionable && !$notification->action_taken)
                                        <div class="mt-3 flex space-x-2">
                                            @if($notification->action_url)
                                                <a href="{{ $notification->action_url }}" 
                                                   class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                    <i class="fas fa-external-link-alt mr-1"></i>
                                                    View Details
                                                </a>
                                            @endif
                                            
                                            @if($notification->type === 'critical_vital_signs')
                                                <button onclick="acknowledgeAlert('{{ $notification->id }}')" 
                                                        class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Acknowledge
                                                </button>
                                            @endif

                                            @if($notification->type === 'appointment_reminder')
                                                <button onclick="confirmAppointment('{{ $notification->data['appointment_id'] ?? '' }}')" 
                                                        class="inline-flex items-center px-3 py-1 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100">
                                                    <i class="fas fa-calendar-check mr-1"></i>
                                                    Confirm
                                                </button>
                                            @endif
                                        </div>
                                    @elseif($notification->action_taken)
                                        <div class="mt-2 text-xs text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Action taken: {{ $notification->action_taken }}
                                            @if($notification->action_taken_at)
                                                ({{ $notification->action_taken_at->diffForHumans() }})
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Actions Menu -->
                                <div class="flex items-center space-x-2 ml-4">
                                    @if(!$notification->is_read)
                                        <button onclick="markAsRead('{{ $notification->id }}')" 
                                                class="text-blue-600 hover:text-blue-800 text-sm" 
                                                title="Mark as read">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    
                                    <div class="relative">
                                        <button onclick="toggleNotificationMenu('{{ $notification->id }}')" 
                                                class="text-gray-400 hover:text-gray-600" 
                                                title="More actions">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        
                                        <!-- Dropdown Menu -->
                                        <div id="menu-{{ $notification->id }}" 
                                             class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                            <div class="py-1">
                                                @if(!$notification->is_read)
                                                    <button onclick="markAsRead('{{ $notification->id }}')" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <i class="fas fa-check mr-2"></i>Mark as read
                                                    </button>
                                                @else
                                                    <button onclick="markAsUnread('{{ $notification->id }}')" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <i class="fas fa-undo mr-2"></i>Mark as unread
                                                    </button>
                                                @endif
                                                
                                                @if($notification->action_url)
                                                    <a href="{{ $notification->action_url }}" 
                                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <i class="fas fa-external-link-alt mr-2"></i>Open link
                                                    </a>
                                                @endif
                                                
                                                <button onclick="deleteNotification('{{ $notification->id }}')" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                    <i class="fas fa-trash mr-2"></i>Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <i class="fas fa-bell-slash text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                    <p class="text-gray-500">You're all caught up! Check back later for new updates.</p>
                </div>
            @endforelse
        </div>

        <!-- Load More / Pagination -->
        @if(isset($notifications) && $notifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Notification Settings Modal -->
<div id="settingsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Notification Settings</h3>
            <button onclick="closeSettingsModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="settingsForm">
            <div class="space-y-4">
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="emailNotifications" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Email notifications</span>
                    </label>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="criticalAlerts" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Critical alerts</span>
                    </label>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="appointmentReminders" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Appointment reminders</span>
                    </label>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="medicationReminders" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Medication reminders</span>
                    </label>
                </div>
                
                <div>
                    <label for="notificationFrequency" class="block text-sm font-medium text-gray-700 mb-1">Notification frequency</label>
                    <select id="notificationFrequency" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="immediate">Immediate</option>
                        <option value="hourly">Hourly digest</option>
                        <option value="daily">Daily digest</option>
                        <option value="weekly">Weekly digest</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" 
                        onclick="closeSettingsModal()"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentFilter = 'all';

// Filter notifications
function filterNotifications(filter) {
    currentFilter = filter;
    
    // Update tab appearance
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    const activeTab = document.querySelector(`[data-filter="${filter}"]`);
    activeTab.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    
    // Filter notification items
    const items = document.querySelectorAll('.notification-item');
    items.forEach(item => {
        let show = true;
        
        switch(filter) {
            case 'unread':
                show = item.dataset.read === 'false';
                break;
            case 'critical':
                show = item.dataset.priority === 'critical';
                break;
            case 'actionable':
                show = item.dataset.actionable === 'true';
                break;
            case 'all':
            default:
                show = true;
        }
        
        item.style.display = show ? 'block' : 'none';
    });
}

// Mark individual notification as read
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-id="${notificationId}"]`);
            item.classList.remove('bg-blue-50');
            item.dataset.read = 'true';
            
            // Remove unread indicator
            const indicator = item.querySelector('.bg-blue-500.rounded-full');
            if (indicator) indicator.remove();
            
            updateUnreadCount();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// Mark notification as unread
function markAsUnread(notificationId) {
    // Implementation similar to markAsRead but opposite
    console.log('Mark as unread:', notificationId);
}

// Mark all notifications as read
function markAllAsRead() {
    if (confirm('Mark all notifications as read?')) {
        fetch('/notifications/mark-all-read', {
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
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
        });
    }
}

// Delete notification
function deleteNotification(notificationId) {
    if (confirm('Delete this notification?')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-id="${notificationId}"]`).remove();
                updateUnreadCount();
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
        });
    }
}

// Acknowledge alert
function acknowledgeAlert(notificationId) {
    fetch(`/notifications/${notificationId}/action`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'acknowledged' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error acknowledging alert:', error);
    });
}

// Confirm appointment from notification
function confirmAppointment(appointmentId) {
    if (appointmentId) {
        fetch(`/appointments/${appointmentId}/confirm`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Toggle notification dropdown menu
function toggleNotificationMenu(notificationId) {
    const menu = document.getElementById(`menu-${notificationId}`);
    const allMenus = document.querySelectorAll('[id^="menu-"]');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m.id !== `menu-${notificationId}`) {
            m.classList.add('hidden');
        }
    });
    
    // Toggle current menu
    menu.classList.toggle('hidden');
}

// Show notification settings modal
function showNotificationSettings() {
    document.getElementById('settingsModal').classList.remove('hidden');
}

// Close settings modal
function closeSettingsModal() {
    document.getElementById('settingsModal').classList.add('hidden');
}

// Update unread count
function updateUnreadCount() {
    const unreadItems = document.querySelectorAll('.notification-item[data-read="false"]');
    document.getElementById('unreadCount').textContent = unreadItems.length;
}

// Close menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="menu-"]') && !e.target.closest('button[onclick^="toggleNotificationMenu"]')) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

// Close settings modal when clicking outside
document.getElementById('settingsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSettingsModal();
    }
});

// Auto-refresh notifications every 30 seconds
setInterval(function() {
    // In a real application, you might want to fetch new notifications
    // and update the display without full page reload
}, 30000);

// Settings form submission
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        email_notifications: document.getElementById('emailNotifications').checked,
        critical_alerts: document.getElementById('criticalAlerts').checked,
        appointment_reminders: document.getElementById('appointmentReminders').checked,
        medication_reminders: document.getElementById('medicationReminders').checked,
        notification_frequency: document.getElementById('notificationFrequency').value
    };
    
    fetch('/settings/notifications', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeSettingsModal();
            // Show success message
        }
    })
    .catch(error => {
        console.error('Error saving settings:', error);
    });
});
</script>
@endpush
@endsection