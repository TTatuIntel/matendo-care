@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Settings</h2>
                <p class="text-gray-600">Manage your account preferences and system settings</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Settings Navigation -->
        <div class="lg:col-span-1">
            <nav class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <ul class="space-y-2">
                    <li>
                        <button onclick="showSection('profile')" 
                                class="settings-nav-item active w-full text-left px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100"
                                data-section="profile">
                            <i class="fas fa-user mr-2"></i>Profile Information
                        </button>
                    </li>
                    <li>
                        <button onclick="showSection('notifications')" 
                                class="settings-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100"
                                data-section="notifications">
                            <i class="fas fa-bell mr-2"></i>Notifications
                        </button>
                    </li>
                    <li>
                        <button onclick="showSection('privacy')" 
                                class="settings-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100"
                                data-section="privacy">
                            <i class="fas fa-shield-alt mr-2"></i>Privacy & Security
                        </button>
                    </li>
                    @if(auth()->user()->isDoctor())
                        <li>
                            <button onclick="showSection('consultation')" 
                                    class="settings-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100"
                                    data-section="consultation">
                                <i class="fas fa-stethoscope mr-2"></i>Consultation Settings
                            </button>
                        </li>
                    @endif
                    @if(auth()->user()->isPatient())
                        <li>
                            <button onclick="showSection('health')" 
                                    class="settings-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100"
                                    data-section="health">
                                <i class="fas fa-heartbeat mr-2"></i>Health Preferences
                            </button>
                        </li>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <li>
                            <button onclick="showSection('system')" 
                                    class="settings-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100"
                                    data-section="system">
                                <i class="fas fa-cog mr-2"></i>System Settings
                            </button>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>

        <!-- Settings Content -->
        <div class="lg:col-span-3">
            <!-- Profile Information -->
            <div id="profile-section" class="settings-section bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Profile Information</h3>
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="md:col-span-2">
                            <h4 class="text-md font-medium text-gray-700 mb-3">Basic Information</h4>
                        </div>
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name', auth()->user()->name) }}"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   value="{{ old('email', auth()->user()->email) }}"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" 
                                   name="phone" 
                                   id="phone"
                                   value="{{ old('phone', auth()->user()->phone) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <input type="date" 
                                   name="date_of_birth" 
                                   id="date_of_birth"
                                   value="{{ old('date_of_birth', auth()->user()->date_of_birth?->format('Y-m-d')) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea name="address" 
                                      id="address" 
                                      rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">{{ old('address', auth()->user()->address) }}</textarea>
                        </div>

                        <!-- Emergency Contact -->
                        @if(auth()->user()->isPatient())
                            <div class="md:col-span-2 border-t pt-4">
                                <h4 class="text-md font-medium text-gray-700 mb-3">Emergency Contact</h4>
                            </div>
                            
                            <div>
                                <label for="emergency_contact" class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Name</label>
                                <input type="text" 
                                       name="emergency_contact" 
                                       id="emergency_contact"
                                       value="{{ old('emergency_contact', auth()->user()->emergency_contact) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Phone</label>
                                <input type="tel" 
                                       name="emergency_contact_phone" 
                                       id="emergency_contact_phone"
                                       value="{{ old('emergency_contact_phone', auth()->user()->emergency_contact_phone) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notification Settings -->
            <div id="notifications-section" class="settings-section hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Notification Preferences</h3>
                
                <form action="{{ route('settings.notifications.update') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-6">
                        <!-- Email Notifications -->
                        <div>
                            <h4 class="text-md font-medium text-gray-700 mb-3">Email Notifications</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_critical_alerts" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                    <span class="ml-2 text-sm text-gray-700">Critical health alerts</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_appointment_reminders" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                    <span class="ml-2 text-sm text-gray-700">Appointment reminders</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_medication_reminders" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Medication reminders</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="email_system_updates" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">System updates and news</span>
                                </label>
                            </div>
                        </div>

                        <!-- Push Notifications -->
                        <div>
                            <h4 class="text-md font-medium text-gray-700 mb-3">Push Notifications</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="push_critical_alerts" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                    <span class="ml-2 text-sm text-gray-700">Critical health alerts</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="push_appointment_reminders" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                    <span class="ml-2 text-sm text-gray-700">Appointment reminders</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="push_messages" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">New messages</span>
                                </label>
                            </div>
                        </div>

                        <!-- Notification Frequency -->
                        <div>
                            <label for="notification_frequency" class="block text-sm font-medium text-gray-700 mb-2">Notification Frequency</label>
                            <select name="notification_frequency" id="notification_frequency" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="immediate">Immediate</option>
                                <option value="hourly">Hourly digest</option>
                                <option value="daily">Daily digest</option>
                                <option value="weekly">Weekly digest</option>
                            </select>
                        </div>

                        <!-- Quiet Hours -->
                        <div>
                            <h4 class="text-md font-medium text-gray-700 mb-3">Quiet Hours</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="quiet_start" class="block text-sm text-gray-600 mb-1">Start Time</label>
                                    <input type="time" name="quiet_start" id="quiet_start" value="22:00" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="quiet_end" class="block text-sm text-gray-600 mb-1">End Time</label>
                                    <input type="time" name="quiet_end" id="quiet_end" value="08:00" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Non-critical notifications will be silenced during these hours</p>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-save mr-2"></i>Save Preferences
                        </button>
                    </div>
                </form>
            </div>

            <!-- Privacy & Security -->
            <div id="privacy-section" class="settings-section hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Privacy & Security</h3>
                
                <div class="space-y-6">
                    <!-- Password Change -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Change Password</h4>
                        <form action="{{ route('password.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                    <input type="password" name="current_password" id="current_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                    <input type="password" name="password" id="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                                        <i class="fas fa-key mr-2"></i>Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Two-Factor Authentication -->
                    <div class="border-t pt-6">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Two-Factor Authentication</h4>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Enhanced Security</p>
                                <p class="text-xs text-gray-600">Add an extra layer of security to your account</p>
                            </div>
                            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                                Enable 2FA
                            </button>
                        </div>
                    </div>

                    <!-- Data Privacy -->
                    <div class="border-t pt-6">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Data Privacy</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                <span class="ml-2 text-sm text-gray-700">Allow sharing anonymized data for medical research</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Allow marketing communications</span>
                            </label>
                        </div>
                    </div>

                    <!-- Account Deletion -->
                    <div class="border-t pt-6">
                        <h4 class="text-md font-medium text-red-700 mb-3">Danger Zone</h4>
                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-700 mb-3">Once you delete your account, all of your data will be permanently removed. This action cannot be undone.</p>
                            <button onclick="confirmAccountDeletion()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-trash mr-2"></i>Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctor Consultation Settings -->
            @if(auth()->user()->isDoctor())
                <div id="consultation-section" class="settings-section hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Consultation Settings</h3>
                    
                    <form action="{{ route('doctor.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Availability -->
                            <div>
                                <h4 class="text-md font-medium text-gray-700 mb-3">Availability</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="consultation_start_time" class="block text-sm text-gray-600 mb-1">Start Time</label>
                                        <input type="time" name="consultation_start_time" id="consultation_start_time" value="{{ auth()->user()->doctor->consultation_start_time ?? '09:00' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="consultation_end_time" class="block text-sm text-gray-600 mb-1">End Time</label>
                                        <input type="time" name="consultation_end_time" id="consultation_end_time" value="{{ auth()->user()->doctor->consultation_end_time ?? '17:00' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Consultation Fee -->
                            <div>
                                <label for="consultation_fee" class="block text-sm font-medium text-gray-700 mb-2">Consultation Fee ($)</label>
                                <input type="number" name="consultation_fee" id="consultation_fee" value="{{ auth()->user()->doctor->consultation_fee ?? 0 }}" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Preferences -->
                            <div>
                                <h4 class="text-md font-medium text-gray-700 mb-3">Preferences</h4>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="accepts_emergency" {{ auth()->user()->doctor->accepts_emergency ?? false ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Accept emergency consultations</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_available" {{ auth()->user()->doctor->is_available ?? true ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Currently available for appointments</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                                <i class="fas fa-save mr-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Patient Health Preferences -->
            @if(auth()->user()->isPatient())
                <div id="health-section" class="settings-section hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Health Preferences</h3>
                    
                    <form action="{{ route('patient.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Health Goals -->
                            <div>
                                <h4 class="text-md font-medium text-gray-700 mb-3">Health Goals</h4>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="daily_vitals_reminder" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Daily vitals recording reminder</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="medication_adherence_tracking" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Medication adherence tracking</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="exercise_tracking" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Exercise and activity tracking</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Units Preference -->
                            <div>
                                <h4 class="text-md font-medium text-gray-700 mb-3">Units Preference</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="temperature_unit" class="block text-sm text-gray-600 mb-1">Temperature</label>
                                        <select name="temperature_unit" id="temperature_unit" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="celsius">Celsius (°C)</option>
                                            <option value="fahrenheit">Fahrenheit (°F)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="weight_unit" class="block text-sm text-gray-600 mb-1">Weight</label>
                                        <select name="weight_unit" id="weight_unit" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="kg">Kilograms (kg)</option>
                                            <option value="lbs">Pounds (lbs)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                                <i class="fas fa-save mr-2"></i>Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- System Settings (Admin only) -->
            @if(auth()->user()->isAdmin())
                <div id="system-section" class="settings-section hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">System Settings</h3>
                    
                    <div class="space-y-6">
                        <!-- System Configuration -->
                        <div>
                            <h4 class="text-md font-medium text-gray-700 mb-3">System Configuration</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="system_name" class="block text-sm text-gray-600 mb-1">System Name</label>
                                    <input type="text" name="system_name" id="system_name" value="MedMonitor" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="max_file_size" class="block text-sm text-gray-600 mb-1">Max File Size (MB)</label>
                                    <input type="number" name="max_file_size" id="max_file_size" value="10" min="1" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Backup Settings -->
                        <div class="border-t pt-6">
                            <h4 class="text-md font-medium text-gray-700 mb-3">Backup Settings</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="auto_backup" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                    <span class="ml-2 text-sm text-gray-700">Enable automatic backups</span>
                                </label>
                                <div class="ml-6">
                                    <label for="backup_frequency" class="block text-sm text-gray-600 mb-1">Backup Frequency</label>
                                    <select name="backup_frequency" id="backup_frequency" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Mode -->
                        <div class="border-t pt-6">
                            <h4 class="text-md font-medium text-gray-700 mb-3">Maintenance Mode</h4>
                            <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-yellow-900">System Maintenance</p>
                                    <p class="text-xs text-yellow-700">Enable maintenance mode to perform system updates</p>
                                </div>
                                <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm">
                                    Enable Maintenance
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.settings-section').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Remove active class from all nav items
    document.querySelectorAll('.settings-nav-item').forEach(item => {
        item.classList.remove('active', 'bg-blue-100', 'text-blue-700');
    });
    
    // Show selected section
    document.getElementById(sectionName + '-section').classList.remove('hidden');
    
    // Add active class to selected nav item
    const activeItem = document.querySelector(`[data-section="${sectionName}"]`);
    activeItem.classList.add('active', 'bg-blue-100', 'text-blue-700');
}

function confirmAccountDeletion() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently removed.')) {
        if (confirm('This is your final confirmation. Type "DELETE" to proceed:')) {
            // In a real application, this would submit a form or make an API call
            const deleteConfirmation = prompt('Type "DELETE" to confirm:');
            if (deleteConfirmation === 'DELETE') {
                // Submit account deletion request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("profile.destroy") }}';
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                
                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
}

// Form submission handlers
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        }
    });
});
</script>
@endpush
@endsection