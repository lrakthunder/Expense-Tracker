
<template>
        <div v-if="toast.show" class="toast-notification">
                {{ toast.message }}
            </div>
        <FlashToast />
    <div class="login-bg">
        <div class="main-container">
            <div class="left-section">
                <div class="header-row">
                    <div class="logo-section">
                        <img src="/custom%20login%20page/logo.png" alt="Company Logo" class="logo-image" />
                    </div>
                    <h1 class="welcome-title">Welcome !</h1>
                </div>
                <p class="welcome-text">Manage your balances and monitor investments easily!</p>
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
                                    <input type="text" class="form-control" v-model="form.email" placeholder="Username or Email" autocomplete="username" />
                                </div>
                                <div class="form-group">
                                    <div class="password-wrapper">
                                        <input :type="showPassword ? 'text' : 'password'" class="form-control" v-model="form.password" placeholder="Password" autocomplete="current-password" @focus="onPasswordFocus" />
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
import '../../css/login.css'
import { reactive, ref, watch, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import FlashToast from '../Components/FlashToast.vue'

// Force light mode for login/register pages
onMounted(() => {
    // Aggressively remove dark mode
    document.documentElement.classList.remove('dark')
    document.documentElement.style.colorScheme = 'light'
    // Clear localStorage to prevent persistence
    try { localStorage.removeItem('theme') } catch (e) {}
})

// Define refs first
const showRegister = ref(false)
const showPassword = ref(false)
const agreedToPolicy = ref(false)

// Toast notification state (for client-side validation errors only)
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

const form = useForm({
    email: '',
    password: '',
    remember: false,
    agreed_to_terms: false
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
    // include the privacy checkbox value so backend can persist consent
    form.agreed_to_terms = agreedToPolicy.value
    form.post(route('login'), {
        onError: () => {
            processing.value = false
            showToast('Login failed. Please check your credentials.')
        },
        onFinish: () => {
            // Let Inertia follow server redirects so any server-side flash is preserved.
            processing.value = false
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
        onError: (errors) => {
            processing.value = false
            if (errors && Object.values(errors).length > 0) {
                showToast(Object.values(errors)[0])
            } else {
                showToast('Registration failed. Please check your details.')
            }
        },
        onFinish: () => {
            processing.value = false
        }
    })
}

const togglePassword = () => {
    showPassword.value = !showPassword.value
}

const onPasswordFocus = async () => {
    // If user already entered a username or email, check whether they previously agreed to terms
    if (!form.email) return
    try {
        // Determine if the input is an email or username, use appropriate parameter
        const isEmail = form.email.includes('@')
        const param = isEmail ? `email=${encodeURIComponent(form.email)}` : `username=${encodeURIComponent(form.email)}`
        const res = await fetch(`/test/user?${param}`)
        if (!res.ok) return
        const data = await res.json()
        if (data && data.agreed_to_terms) {
            agreedToPolicy.value = true
            form.agreed_to_terms = true
        }
    } catch (e) {
        // ignore network errors — do not block login
    }
}
</script>



