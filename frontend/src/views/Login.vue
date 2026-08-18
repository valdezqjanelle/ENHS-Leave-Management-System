<template>
  <div
    class="w-screen h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4"
  >
    <div class="w-full max-w-md">
      <!-- Header -->
      <div class="text-center mb-6">
        <div
          class="mx-auto h-14 w-14 bg-blue-600 rounded-full flex items-center justify-center"
        >
          <span class="text-white text-2xl font-bold">ELS</span>
        </div>

        <h2 class="mt-4 text-3xl font-extrabold text-blue-600">ELS System</h2>

        <p class="mt-2 text-sm text-gray-600">Leave Management System</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white rounded-xl shadow-lg p-8">
        <form class="space-y-6" @submit.prevent="handleLogin">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Email Address
            </label>

            <div class="mt-1 relative">
              <input
                v-model="loginForm.email"
                type="email"
                required
                placeholder="Enter your email"
                class="w-full pl-10 pr-3 py-2 text-black border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />

              <Mail class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              Password
            </label>

            <div class="mt-1 relative">
              <input
                v-model="loginForm.password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="Enter your password"
                class="w-full pl-10 pr-10 py-2 text-black border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />

              <Lock class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" />

              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-2.5"
              >
                <Eye v-if="showPassword" class="w-4 h-4 text-gray-400" />
                <EyeOff v-else class="w-4 h-4 text-gray-400" />
              </button>
            </div>
          </div>

          <!-- Remember + Forgot -->
          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2">
              <input
                v-model="loginForm.rememberMe"
                type="checkbox"
                class="text-blue-600"
              />
              Remember me
            </label>

            <a href="#" class="text-blue-600 hover:text-blue-500">
              Forgot password?
            </a>
          </div>

          <!-- Error -->
          <div
            v-if="errorMessage"
            class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3"
          >
            {{ errorMessage }}
          </div>

          <!-- Login Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
          >
            <span v-if="!isLoading"> Sign in </span>

            <span v-else> Signing in... </span>
          </button>
        </form>
      </div>

      <!-- Footer -->
      <div class="text-center text-sm text-gray-600 mt-6">
        <p>© 2026 ELS System. All rights reserved.</p>

        <div class="mt-2 space-x-4">
          <a href="#" class="hover:text-gray-900"> Privacy Policy </a>

          <a href="#" class="hover:text-gray-900"> Terms of Service </a>

          <a href="#" class="hover:text-gray-900"> Contact Support </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { login } from "@/services/auth";
import { Mail, Lock, Eye, EyeOff } from "lucide-vue-next";

const router = useRouter();

const showPassword = ref(false);
const isLoading = ref(false);
const errorMessage = ref("");

const loginForm = ref({
  email: "",
  password: "",
  rememberMe: false,
});

const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = "";

  try {
    const data = await login(loginForm.value.email, loginForm.value.password);

    // Save token
    localStorage.setItem("token", data.token);

    // Save logged-in user
    localStorage.setItem("user", JSON.stringify(data.user));

    // Redirect based on role
    if (data.user.role === "admin") {
      router.push("/dashboard");
    } else if (data.user.role === "employee") {
      router.push("/dashboard");
    } else {
      errorMessage.value = "Unknown user role.";
    }
  } catch (error: any) {
    if (error.response?.status === 401) {
      errorMessage.value = "Invalid email or password.";
    } else {
      errorMessage.value = "Unable to connect to the server.";
    }
  } finally {
    isLoading.value = false;
  }
};
</script>
