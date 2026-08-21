<template>
  <div class="fixed inset-0 overflow-auto">

    <!-- Background -->
    <div
      class="fixed inset-0 bg-cover bg-center bg-no-repeat"
      style="background-image: url('/background.jpg');"
    ></div>

    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black/20"></div>

    <!-- Main Content -->
    <div
      class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-8"
    >

      <!-- Header -->
      <div class="text-center mb-6">

        <h2
          class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-md"
        >
          Echague National High School
        </h2>

        <p class="mt-2 text-base text-white font-medium drop-shadow">
          Leave Management System
        </p>

      </div>


      <!-- Glass Login Card -->
      <div
        class="w-full max-w-md rounded-2xl
               bg-white/15 backdrop-blur-s
               border border-white/40
               shadow-2xl
               p-5"
      >

        <form
          class="space-y-6"
          @submit.prevent="handleLogin"
        >

          <!-- Email -->
          <div>

            <label class="block text-sm font-semibold text-gray-900">
              Email Address
            </label>

            <div class="mt-2 relative">

              <input
                v-model="loginForm.email"
                type="email"
                required
                placeholder="Enter your email"
                class="w-full pl-10 pr-3 py-3
                       text-gray-900
                       bg-white/50
                       backdrop-blur-md
                       border border-white/50
                       rounded-xl
                       placeholder-gray-600
                       focus:bg-white/70
                       focus:ring-2
                       focus:ring-blue-500
                       focus:outline-none
                       transition"
              />

              <Mail
                class="absolute left-3 top-3.5 w-5 h-5 text-gray-600"
              />

            </div>

          </div>


          <!-- Password -->
          <div>

            <label class="block text-sm font-semibold text-gray-900">
              Password
            </label>

            <div class="mt-2 relative">

              <input
                v-model="loginForm.password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="Enter your password"
                class="w-full pl-10 pr-10 py-3
                       text-gray-900
                       bg-white/50
                       backdrop-blur-md
                       border border-white/50
                       rounded-xl
                       placeholder-gray-600
                       focus:bg-white/70
                       focus:ring-2
                       focus:ring-blue-500
                       focus:outline-none
                       transition"
              />

              <Lock
                class="absolute left-3 top-3.5 w-5 h-5 text-gray-600"
              />

              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-3.5"
              >

                <Eye
                  v-if="showPassword"
                  class="w-5 h-5 text-gray-600 hover:text-blue-600"
                />

                <EyeOff
                  v-else
                  class="w-5 h-5 text-gray-600 hover:text-blue-600"
                />

              </button>

            </div>

          </div>


          <!-- Remember + Forgot -->
          <div class="flex items-center justify-between text-sm">

            <label class="flex items-center gap-2 text-gray-900">

              <input
                v-model="loginForm.rememberMe"
                type="checkbox"
                class="w-4 h-4 text-blue-600 rounded"
              />

              <span>
                Remember me
              </span>

            </label>

            <a
              href="#"
              class="font-medium text-white hover:text-blue-900"
            >
              Forgot password?
            </a>

          </div>


          <!-- Error -->
          <div
            v-if="errorMessage"
            class="bg-red-100/70
                   border border-red-300
                   text-red-700
                   text-sm
                   rounded-xl
                   p-3
                   backdrop-blur-md"
          >
            {{ errorMessage }}
          </div>


          <!-- Login Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full
                   bg-blue-600/90
                   text-white
                   font-semibold
                   py-3
                   rounded-xl
                   shadow-lg
                   hover:bg-blue-700
                   hover:shadow-xl
                   transition
                   disabled:opacity-50"
          >

            <span v-if="!isLoading">
              Sign in
            </span>

            <span v-else>
              Signing in...
            </span>

          </button>

        </form>

      </div>


      <!-- Footer -->
      <div
        class="text-center text-sm text-white mt-6 drop-shadow"
      >

        <p>
          © 2026 E-LMS System. All rights reserved.
        </p>

        <div class="mt-2 flex justify-center gap-4">

          <a
            href="#"
            class="hover:text-blue-700 transition"
          >
            Privacy Policy
          </a>

          <a
            href="#"
            class="hover:text-blue-700 transition"
          >
            Terms of Service
          </a>

          <a
            href="#"
            class="hover:text-blue-700 transition"
          >
            Contact Support
          </a>

        </div>

      </div>

    </div>

  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '@/services/auth'
import {
  Mail,
  Lock,
  Eye,
  EyeOff
} from 'lucide-vue-next'

const router = useRouter()

const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

const loginForm = ref({
  email: '',
  password: '',
  rememberMe: false
})

const handleLogin = async () => {

  isLoading.value = true
  errorMessage.value = ''

  try {

    const data = await login(
      loginForm.value.email,
      loginForm.value.password
    )

    // Save token
    localStorage.setItem('token', data.token)

    // Save logged-in user
    localStorage.setItem(
      'user',
      JSON.stringify(data.user)
    )

    // Redirect based on role
    if (data.user.role === 'admin') {
      router.push('/dashboard')
    } else if (data.user.role === 'employee') {
      router.push('/dashboard')
    } else {
      errorMessage.value = 'Unknown user role.'
    }

  } catch (error: any) {

    if (error.response?.status === 401) {

      errorMessage.value = 'Invalid email or password.'

    } else {

      errorMessage.value =
        'Unable to connect to the server.'

    }

  } finally {

    isLoading.value = false

  }

}
</script>