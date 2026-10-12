<template>
  <div class="recovery-page">
    <main class="recovery-card">
      <h1>Forgot Password</h1>
      <p v-if="step === 'email'">
        Enter your registered email to request a verification code.
      </p>
      <p v-else-if="step === 'code'">
        Check your email for a six-digit code. Enter it below within 10 minutes.
      </p>
      <p v-else-if="step === 'password'">
        Create a new password with at least 12 characters.
      </p>
      <p v-else>
        Your password has been updated. Sign in using your new password.
      </p>

      <div v-if="message" class="message" role="status">{{ message }}</div>
      <div v-if="error" class="error" role="alert">{{ error }}</div>

      <form v-if="step !== 'done'" @submit.prevent="submit">
        <label for="recovery-email">Registered Email</label>
        <input
          id="recovery-email"
          v-model="email"
          type="email"
          autocomplete="email"
          required
          maxlength="255"
          :readonly="step !== 'email'"
          :disabled="busy"
        />

        <template v-if="step === 'code'">
          <label for="recovery-code">Verification Code</label>
          <input
            id="recovery-code"
            v-model="code"
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            pattern="[0-9]{6}"
            minlength="6"
            maxlength="6"
            required
            :disabled="busy"
          />
          <button
            type="button"
            class="secondary"
            :disabled="busy || resendSeconds > 0"
            @click="resend"
          >
            {{
              resendSeconds > 0
                ? `Resend code in ${resendSeconds}s`
                : "Resend Code"
            }}
          </button>
        </template>

        <template v-if="step === 'password'">
          <label for="recovery-password">New Password</label>
          <input
            id="recovery-password"
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
            minlength="12"
            maxlength="128"
            required
            :disabled="busy"
          />
          <label for="recovery-confirmation">Confirm New Password</label>
          <input
            id="recovery-confirmation"
            v-model="confirmation"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
            minlength="12"
            maxlength="128"
            required
            :disabled="busy"
          />
          <label class="show-password"
            ><input v-model="showPassword" type="checkbox" /> Show
            passwords</label
          >
        </template>

        <button type="submit" class="primary" :disabled="busy">
          {{
            busy
              ? "Please wait…"
              : step === "email"
                ? "Send Code"
                : step === "code"
                  ? "Verify Code"
                  : "Save New Password"
          }}
        </button>
        <button
          v-if="step !== 'email'"
          type="button"
          class="secondary"
          :disabled="busy"
          @click="startOver"
        >
          Start Over / Change Email
        </button>
      </form>
      <router-link to="/login" class="back-link">Back to Login</router-link>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onUnmounted } from "vue";
import {
  requestPasswordCode,
  verifyPasswordCode,
  resetForgottenPassword,
} from "@/services/auth";

const step = ref<"email" | "code" | "password" | "done">("email");
const email = ref("");
const code = ref("");
const password = ref("");
const confirmation = ref("");
// Never persist recovery secrets in localStorage, URLs, or logs.
const resetToken = ref("");
const message = ref("");
const error = ref("");
const busy = ref(false);
const showPassword = ref(false);
const resendSeconds = ref(0);
let timer: ReturnType<typeof setInterval> | undefined;

const stopTimer = () => {
  if (timer !== undefined) clearInterval(timer);
  timer = undefined;
};
const startTimer = () => {
  stopTimer();
  resendSeconds.value = 60;
  timer = setInterval(() => {
    resendSeconds.value -= 1;
    if (resendSeconds.value <= 0) stopTimer();
  }, 1000);
};
const showError = (e: any) => {
  const errors = e.response?.data?.errors;
  const first = errors ? Object.values(errors)[0] : undefined;
  error.value =
    e.response?.status === 429
      ? "Too many requests. Please wait before trying again."
      : Array.isArray(first)
        ? String(first[0])
        : e.response?.data?.message || "Unable to connect. Please try again.";
};
const resend = async () => {
  if (busy.value || resendSeconds.value > 0) return;
  busy.value = true;
  error.value = "";
  message.value = "";
  try {
    const data = await requestPasswordCode(email.value.trim());
    message.value = data.message;
    code.value = "";
    startTimer();
  } catch (e) {
    showError(e);
  } finally {
    busy.value = false;
  }
};
const startOver = () => {
  step.value = "email";
  code.value = "";
  resetToken.value = "";
  password.value = "";
  confirmation.value = "";
  showPassword.value = false;
  message.value = "";
  error.value = "";
  stopTimer();
  resendSeconds.value = 0;
};
const submit = async () => {
  if (busy.value) return;
  error.value = "";
  message.value = "";
  if (step.value === "password" && password.value !== confirmation.value) {
    error.value = "Passwords do not match.";
    return;
  }
  busy.value = true;
  try {
    if (step.value === "email") {
      email.value = email.value.trim();
      const data = await requestPasswordCode(email.value);
      message.value = data.message;
      step.value = "code";
      startTimer();
    } else if (step.value === "code") {
      const data = await verifyPasswordCode(email.value, code.value);
      resetToken.value = data.reset_token;
      code.value = "";
      step.value = "password";
      stopTimer();
    } else if (step.value === "password") {
      const data = await resetForgottenPassword(
        email.value,
        resetToken.value,
        password.value,
        confirmation.value,
      );
      resetToken.value = "";
      password.value = "";
      confirmation.value = "";
      // The backend revokes all existing tokens, including any token in this browser.
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      sessionStorage.removeItem("els-enhancement-notice-dismissed");
      step.value = "done";
      message.value = data.message;
    }
  } catch (e) {
    showError(e);
  } finally {
    busy.value = false;
  }
};
onUnmounted(stopTimer);
</script>

<style scoped>
.recovery-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  background:
    linear-gradient(rgba(15, 23, 42, 0.78), rgba(15, 23, 42, 0.78)),
    url("/background.jpg") center / cover;
}
.recovery-card {
  width: 100%;
  max-width: 440px;
  padding: 2rem;
  border-radius: 1rem;
  background: #fff;
  color: #172033;
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.25);
}
h1 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}
p {
  font-size: 0.9rem;
  color: #475569;
  line-height: 1.5;
  margin-bottom: 1rem;
}
form {
  display: grid;
  gap: 0.75rem;
}
label {
  font-size: 0.875rem;
  font-weight: 600;
}
input:not([type="checkbox"]) {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #94a3b8;
  border-radius: 0.5rem;
  color: #172033;
  background: #fff;
}
input[readonly] {
  background: #f1f5f9;
}
input:focus-visible,
button:focus-visible,
a:focus-visible {
  outline: 2px solid #2563eb;
  outline-offset: 2px;
}
button {
  padding: 0.75rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
}
.primary {
  background: #2563eb;
  color: #fff;
}
.secondary {
  background: #f1f5f9;
  color: #1e40af;
}
button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
.message,
.error {
  padding: 0.75rem;
  margin-bottom: 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  line-height: 1.5;
}
.message {
  background: #eff6ff;
  color: #1e40af;
}
.error {
  background: #fef2f2;
  color: #991b1b;
}
.show-password {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}
.back-link {
  display: block;
  text-align: center;
  margin-top: 1rem;
  color: #1d4ed8;
}
</style>
