
<template>
        <div v-if="toast.show" class="toast-notification">
            {{ toast.message }}
        </div>
    <div class="login-bg">
        <div class="main-container">
            <div class="left-section">
                <div class="logo-section">
                    <img src="/custom%20login%20page/logo.png" alt="Company Logo" class="logo-image" />
                </div>
                <h1 class="welcome-title">Welcome !</h1>
                <p class="welcome-text">Manage your retirement account, check balances, and<br>monitor investments easily!</p>
                <p class="support-text">Need help? email <a href="mailto:support@example.ph">support@example.ph</a></p>
            </div>
            <div class="login-container">
                <div class="login-register-header-modern" role="tablist">
                    <span
                        :class="['tab-link', !showRegister ? 'active' : '']"
                        @click="showRegister = false"
                        role="tab"
                        :aria-selected="!showRegister"
                        aria-controls="login-panel"
                        tabindex="0"
                    >
                        Login
                    </span>
                    <span class="tab-separator">|</span>
                    <span
                        :class="['tab-link', showRegister ? 'active' : '']"
                        @click="showRegister = true"
                        role="tab"
                        :aria-selected="showRegister"
                        aria-controls="register-panel"
                        tabindex="0"
                    >
                        Register
                    </span>
                </div>
                <div class="login-card-inner">
                    <div class="login-form-content">
                        <template v-if="!showRegister">
                            <h2 class="login-title">Login Account</h2>
                            <form @submit.prevent="handleLogin">
                                <div class="form-group">
                                    <input type="email" class="form-control" v-model="form.email" placeholder="Email" autocomplete="email" />
                                </div>
                                <div class="form-group">
                                    <div class="password-wrapper">
                                        <input :type="showPassword ? 'text' : 'password'" class="form-control" v-model="form.password" placeholder="Password" autocomplete="current-password" />
                                        <button type="button" class="toggle-password" @click="togglePassword">
                                            <span v-if="showPassword">👁️</span>
                                            <span v-else>👁️‍🗨️</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" id="privacy" v-model="agreedToPolicy" />
                                    <label for="privacy">
                                        I have read and agree to the <a href="#">Privacy Policy</a>
                                    </label>
                                </div>
                                <button type="submit" class="btn-signin" :disabled="processing">Sign In</button>
                            </form>
                            <div class="forgot-password">
                                <a href="#">Forgot Password?</a>
                            </div>
                        </template>
                        <template v-else>
                            <h2 class="login-title">Register Account</h2>
                            <form @submit.prevent="handleRegister">
                                <div class="form-group">
                                    <input type="text" class="form-control" v-model="registerForm.firstname" placeholder="First Name" />
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" v-model="registerForm.lastname" placeholder="Last Name" />
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" v-model="registerForm.username" placeholder="Username" />
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" v-model="registerForm.email" placeholder="Email" />
                                </div>
                                <div class="form-group">
                                    <input :type="showPassword ? 'text' : 'password'" class="form-control" v-model="registerForm.password" placeholder="Password" />
                                </div>
                                <div class="form-group">
                                    <input :type="showPassword ? 'text' : 'password'" class="form-control" v-model="registerForm.password_confirmation" placeholder="Confirm Password" />
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" id="terms" v-model="registerForm.agreeToTerms" />
                                    <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
                                </div>
                                <button type="submit" class="btn-signin" :disabled="processing">Register</button>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { reactive, watch } from 'vue'
    // Toast notification state
    const toast = reactive({
        show: false,
        message: '',
        timeout: null
    })

    function showToast(message) {
        toast.message = message
        toast.show = true
        if (toast.timeout) clearTimeout(toast.timeout)
        toast.timeout = setTimeout(() => {
            toast.show = false
        }, 4000)
    }
    import { ref } from 'vue'
    import { useForm, router } from '@inertiajs/vue3'

    const showRegister = ref(false)
    const showPassword = ref(false)
    const agreedToPolicy = ref(false)

    const form = useForm({
        email: '',
        password: '',
        remember: false
    })

    const registerForm = ref({
        firstname: '',
        lastname: '',
        username: '',
        email: '',
        password: '',
        password_confirmation: '',
        agreeToTerms: false
    })

    const processing = ref(false)

    const handleLogin = () => {
        if (!form.email || !form.password) {
            showToast('Please fill in all fields')
            return
        }
        if (!agreedToPolicy.value) {
            showToast('Please agree to the Privacy Policy')
            return
        }
        processing.value = true
        form.post(route('login'), {
            onSuccess: () => {
                processing.value = false
            },
            onError: (errors) => {
                processing.value = false
                // Show first error if available
                if (form.errors && Object.values(form.errors).length > 0) {
                    showToast(Object.values(form.errors)[0])
                } else {
                    showToast('Login failed. Please check your credentials.')
                }
            }
        })
    }

    const handleRegister = () => {
        if (!registerForm.value.firstname || !registerForm.value.lastname || !registerForm.value.username || !registerForm.value.email || !registerForm.value.password || !registerForm.value.password_confirmation) {
            showToast('Please fill in all fields')
            return
        }
        if (registerForm.value.password !== registerForm.value.password_confirmation) {
            showToast('Passwords do not match')
            return
        }
        if (!registerForm.value.agreeToTerms) {
            showToast('You must agree to the Terms and Conditions')
            return
        }
        processing.value = true
        router.post(route('register'), {
            first_name: registerForm.value.firstname,
            last_name: registerForm.value.lastname,
            username: registerForm.value.username,
            email: registerForm.value.email,
            password: registerForm.value.password,
            password_confirmation: registerForm.value.password_confirmation,
            agreeToTerms: registerForm.value.agreeToTerms
        }, {
            onSuccess: () => {
                processing.value = false
                showToast('Registration successful! You can now log in.')
                showRegister.value = false
                // Optionally clear the form
                registerForm.value = {
                    firstname: '',
                    lastname: '',
                    username: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    agreeToTerms: false
                }
            },
            onError: (errors) => {
                processing.value = false
                // Show first error if available
                if (errors && Object.values(errors).length > 0) {
                    showToast(Object.values(errors)[0])
                } else {
                    showToast('Registration failed. Please check your details.')
                }
            }
        })
    }

    const togglePassword = () => {
        showPassword.value = !showPassword.value
    }
</script>

<style scoped>
        .toast-notification {
            position: fixed;
            top: 32px;
            right: 32px;
            z-index: 9999;
            min-width: 260px;
            max-width: 350px;
            background: #fff;
            color: #333;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.13);
            padding: 16px 24px;
            font-size: 1rem;
            font-weight: 500;
            border-left: 5px solid #e74c3c;
            animation: toast-fade-in 0.3s;
        }
        @keyframes toast-fade-in {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    .login-container {
        position: relative;
    }

    .login-register-header-modern {
        display: flex;
        width: 100%;
        margin-bottom: 28px;
        padding: 0;
        background: transparent;
        border-radius: 0;
        box-shadow: none;
        /* Remove border-radius and background so card's corners show */
    }
    .tab-link {
        flex: 1 1 50%;
        text-align: center;
        font-size: 1.15rem;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-weight: 600;
        color: #7b8a99;
        background: none;
        border: none;
        cursor: pointer;
        padding: 14px 0 6px 0;
        transition: color 0.2s, border-bottom 0.2s;
        border-bottom: 2.5px solid transparent;
        outline: none;
        user-select: none;
        min-width: 0;
    }
    .tab-link.active {
        color: #2c3e50;
        border-bottom: 2.5px solid #667eea;
        background: #fff;
    }
    .tab-link:hover:not(.active) {
        color: #4a5a6a;
        border-bottom: 2.5px solid #dbeafe;
    }
    .tab-separator {
        display: none;
    }

    .login-bg {
        min-height: 100vh;
        width: 100vw;
        background: #c0d4e8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-container {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .login-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border: 1px solid #e8e8e8;
        padding: 0;
        flex: 1;
        max-width: 450px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
    }

    .login-card-inner {
        padding: 0px 50px 40px 50px;
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }
    .login-form-content {
        margin-top: 18px;
    }

    .left-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        max-width: 500px;
    }

    .logo-section {
        margin-bottom: 15px;
    }

    .logo-image {
        width: 100%;
        max-width: 500px;
        height: auto;
        display: block;
        border-radius: 8px;
    }

    .welcome-title {
        font-size: 42px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .welcome-text {
        font-size: 18px;
        color: #5a6c7d;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .support-text {
        font-size: 14px;
        color: #7f8c8d;
        margin-top: 30px;
    }

    .support-text a {
        color: #2c5f8d;
        text-decoration: none;
        font-weight: 600;
    }

    .login-register-header {
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 2.2rem;
        font-family: 'Permanent Marker', cursive, sans-serif;
        color: #ff6600;
        margin-bottom: 18px;
        user-select: none;
        transition: color 0.2s, text-decoration 0.2s;
    }
    .login-register-header .active {
        color: #ff6600;
        text-decoration: underline;
    }

    .login-title {
        font-size: 28px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .input-wrapper {
        position: relative;
    }

    .form-control {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid #dfe6e9;
        border-radius: 8px;
        font-size: 15px;
        background: #ffffff;
        transition: all 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .password-wrapper {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #95a5a6;
        font-size: 18px;
        padding: 0;
        display: flex;
        align-items: center;
    }
    .toggle-password:hover {
        color: #667eea;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
    }
    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        cursor: pointer;
        accent-color: #667eea;
    }
    .checkbox-group label {
        font-size: 14px;
        color: #5a6c7d;
        cursor: pointer;
    }
    .checkbox-group a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }
    .checkbox-group a:hover {
        text-decoration: underline;
    }

    .btn-signin {
        width: 100%;
        padding: 14px;
        background: #27ae60;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    .btn-signin:hover {
        background: #229954;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
    }
    .btn-signin:active {
        transform: translateY(0);
    }

    .forgot-password {
        text-align: center;
        margin-top: 15px;
    }
    .forgot-password a {
        color: #667eea;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }
    .forgot-password a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .main-container {
            flex-direction: column;
            gap: 40px;
        }
        .login-container {
            max-width: 100%;
            padding: 40px 30px;
        }
    }
</style>



