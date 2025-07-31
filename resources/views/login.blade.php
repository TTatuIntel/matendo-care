{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            <!-- Logo and Header -->
            <div class="text-center mb-6">
                <div class="mx-auto h-16 w-16 bg-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-heartbeat text-white text-2xl"></i>
                </div>
                <h2 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">
                    Welcome to MedMonitor
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Sign in to your medical monitoring account
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <x-text-input id="email" 
                                     class="block w-full pl-10" 
                                     type="email" 
                                     name="email" 
                                     :value="old('email')" 
                                     required 
                                     autofocus 
                                     autocomplete="username"
                                     placeholder="Enter your email address" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <x-text-input id="password" 
                                     class="block w-full pl-10 pr-10"
                                     type="password"
                                     name="password"
                                     required 
                                     autocomplete="current-password"
                                     placeholder="Enter your password" />
                        <button type="button" 
                                onclick="togglePassword()" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i id="passwordIcon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <!-- Login Button -->
                <div class="mt-6">
                    <x-primary-button class="w-full justify-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        {{ __('Sign In') }}
                    </x-primary-button>
                </div>

                <!-- Demo Accounts (Development Only) -->
                @if(app()->environment('local'))
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <p class="text-center text-sm text-gray-600 dark:text-gray-400 mb-4">Demo Accounts (Development Only)</p>
                        <div class="grid grid-cols-1 gap-2">
                            <button type="button" 
                                    onclick="fillCredentials('admin@medmonitor.com', 'password')"
                                    class="w-full py-2 px-4 border border-purple-300 rounded-lg text-sm text-purple-700 bg-purple-50 hover:bg-purple-100 transition duration-200 dark:border-purple-600 dark:text-purple-300 dark:bg-purple-900 dark:hover:bg-purple-800">
                                <i class="fas fa-user-shield mr-2"></i>Login as Admin
                            </button>
                            <button type="button" 
                                    onclick="fillCredentials('doctor@medmonitor.com', 'password')"
                                    class="w-full py-2 px-4 border border-green-300 rounded-lg text-sm text-green-700 bg-green-50 hover:bg-green-100 transition duration-200 dark:border-green-600 dark:text-green-300 dark:bg-green-900 dark:hover:bg-green-800">
                                <i class="fas fa-user-md mr-2"></i>Login as Doctor
                            </button>
                            <button type="button" 
                                    onclick="fillCredentials('patient@medmonitor.com', 'password')"
                                    class="w-full py-2 px-4 border border-blue-300 rounded-lg text-sm text-blue-700 bg-blue-50 hover:bg-blue-100 transition duration-200 dark:border-blue-600 dark:text-blue-300 dark:bg-blue-900 dark:hover:bg-blue-800">
                                <i class="fas fa-user mr-2"></i>Login as Patient
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Register Link -->
                @if (Route::has('register'))
                    <div class="text-center mt-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Sign up here
                            </a>
                        </p>
                    </div>
                @endif
            </form>

            <!-- Features Section -->
            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="w-8 h-8 mx-auto bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-heartbeat text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Real-time Monitoring</p>
                    </div>
                    <div class="text-center">
                        <div class="w-8 h-8 mx-auto bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-shield-alt text-green-600 dark:text-green-400"></i>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Secure & Private</p>
                    </div>
                    <div class="text-center">
                        <div class="w-8 h-8 mx-auto bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-mobile-alt text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Mobile Friendly</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-gray-500 dark:text-gray-400 mt-6">
                <p>&copy; {{ date('Y') }} MedMonitor. All rights reserved.</p>
                <div class="mt-2 space-x-4">
                    <a href="#" class="hover:text-gray-700 dark:hover:text-gray-300">Privacy Policy</a>
                    <a href="#" class="hover:text-gray-700 dark:hover:text-gray-300">Terms of Service</a>
                    <a href="#" class="hover:text-gray-700 dark:hover:text-gray-300">Support</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        @if(app()->environment('local'))
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
        @endif

        // Auto-focus on email field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });

        // Add loading state to login button
        document.querySelector('form').addEventListener('submit', function() {
            const button = document.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing In...';
            button.disabled = true;
            
            // Re-enable button after 5 seconds in case of error
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 5000);
        });
    </script>
</x-guest-layout>