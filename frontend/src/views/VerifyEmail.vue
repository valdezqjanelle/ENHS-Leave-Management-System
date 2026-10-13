<template>
  <div class="verification-page">
    <section class="verification-card" aria-labelledby="verification-title">
      <div class="verification-icon" aria-hidden="true">✉</div>
      <p class="system-name">ENHS Leave System</p>
      <h1 id="verification-title">Verify your email</h1>
      <p class="intro">
        Before you continue, confirm that you can access the email registered to
        your account.
      </p>
      <p v-if="session" class="email-label">
        Verification email: <strong>{{ session.masked_email }}</strong>
      </p>
      <p class="verification-note">
        This is a one-time verification for this email address. Your current
        password stays the same.
      </p>

      <p v-if="message" class="message" role="status">{{ message }}</p>
      <p v-if="error" class="error" role="alert">{{ error }}</p>
      <template v-if="session && !expired">
        <form @submit.prevent="submitCode">
          <label for="verification-code">6-digit verification code</label>
          <input
            id="verification-code"
            v-model="code"
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            maxlength="6"
            pattern="[0-9]{6}"
            required
            placeholder="000000"
            :disabled="busy"
            @input="code = code.replace(/\D/g, '').slice(0, 6)"
          />
          <p class="hint">
            The code expires after 10 minutes. Check your spam folder if you
            cannot find it.
          </p>
          <button
            type="submit"
            class="primary"
            :disabled="busy || !/^\d{6}$/.test(code)"
          >
            {{ verifying ? "Verifying…" : "Verify and continue" }}
          </button>
        </form>
        <button
          type="button"
          class="resend"
          :disabled="busy || resendSeconds > 0"
          @click="resend"
        >
          {{
            resending
              ? "Sending…"
              : resendSeconds > 0
                ? `Resend code in ${resendSeconds}s`
                : "Resend code"
          }}
        </button>
      </template>
      <p class="hint help">
        If the registered email is incorrect or inaccessible, contact your
        system administrator.
      </p>
      <button type="button" class="back" :disabled="busy" @click="backToLogin">
        Back to sign in
      </button>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { startSession } from "@/services/sessionTimeout";
import {
  getEmailVerification,
  saveEmailVerification,
  clearEmailVerification,
  verifyEmailCode,
  resendEmailVerification,
} from "@/services/emailVerification";

const router = useRouter();
const session = ref(getEmailVerification());
const code = ref("");
const message = ref(session.value?.message || "");
const error = ref("");
const verifying = ref(false);
const resending = ref(false);
const busy = computed(() => verifying.value || resending.value);
const now = ref(Date.now());
const expired = computed(
  () =>
    !session.value ||
    Date.parse(session.value.challenge_expires_at) <= now.value,
);
const resendSeconds = computed(() =>
  Math.max(0, Math.ceil(((session.value?.resend_at || 0) - now.value) / 1000)),
);
let timer: ReturnType<typeof setInterval> | undefined;

const backToLogin = () => {
  clearEmailVerification();
  void router.replace("/login");
};
const handleError = (failure: any) => {
  error.value =
    failure.response?.data?.message || "Unable to connect. Please try again.";
  if (failure.response?.data?.code === "VERIFICATION_SESSION_EXPIRED") {
    clearEmailVerification();
    session.value = null;
  }
  const wait = Number(
    failure.response?.data?.resend_after ||
      failure.response?.data?.retry_after ||
      0,
  );
  if (session.value && wait > 0) {
    session.value.resend_at = Date.now() + wait * 1000;
    saveEmailVerification(session.value);
  }
};
const submitCode = async () => {
  if (
    busy.value ||
    expired.value ||
    !session.value ||
    !/^\d{6}$/.test(code.value)
  )
    return;
  verifying.value = true;
  error.value = "";
  try {
    const data = await verifyEmailCode(
      session.value.challenge_token,
      code.value,
    );
    clearEmailVerification();
    startSession(data.token, data.user);
    await router.replace("/dashboard");
  } catch (failure: any) {
    handleError(failure);
  } finally {
    verifying.value = false;
  }
};
const resend = async () => {
  if (busy.value || expired.value || !session.value || resendSeconds.value > 0)
    return;
  resending.value = true;
  error.value = "";
  try {
    const data = await resendEmailVerification(session.value.challenge_token);
    session.value.resend_at =
      Date.now() + Number(data.resend_after || 60) * 1000;
    if (data.challenge_expires_at)
      session.value.challenge_expires_at = data.challenge_expires_at;
    session.value.message = data.message;
    saveEmailVerification(session.value);
    message.value = data.message;
    code.value = "";
    now.value = Date.now();
  } catch (failure: any) {
    handleError(failure);
  } finally {
    resending.value = false;
  }
};
onMounted(() => {
  if (!session.value) {
    error.value = "Please sign in again to start email verification.";
    message.value = "";
  }
  timer = setInterval(() => {
    now.value = Date.now();
    if (expired.value && session.value) {
      error.value = "Your verification session expired. Please sign in again.";
      clearEmailVerification();
      session.value = null;
      message.value = "";
    }
  }, 1000);
});
onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>

<style scoped>
.verification-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  background: linear-gradient(135deg, #eaf1fc, #f8fafc);
  color: #172033;
}
.verification-card {
  width: 100%;
  max-width: 460px;
  background: #fff;
  border: 1px solid #d8e1eb;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 12px 36px rgba(23, 32, 51, 0.09);
}
.verification-icon {
  width: 52px;
  height: 52px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #eff6ff;
  color: #2563eb;
  border-radius: 50%;
  font-size: 1.65rem;
  margin-bottom: 1rem;
}
.system-name {
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 600;
}
h1 {
  font-size: 1.65rem;
  font-weight: 700;
  margin: 0.35rem 0 0.75rem;
}
.intro,
.email-label,
.verification-note {
  font-size: 0.9rem;
  line-height: 1.6;
  margin-bottom: 0.75rem;
}
.email-label {
  overflow-wrap: anywhere;
}
.verification-note {
  color: #64748b;
}
form {
  margin-top: 1.25rem;
}
label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}
input {
  display: block;
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 0.6rem;
  padding: 0.8rem;
  text-align: center;
  font-size: 1.5rem;
  letter-spacing: 0.3em;
  color: #172033;
  background: #fff;
}
input:focus {
  outline: 3px solid #dbeafe;
  border-color: #2563eb;
}
.hint {
  font-size: 0.8rem;
  line-height: 1.6;
  color: #64748b;
  margin-top: 0.5rem;
}
button {
  border: 0;
  cursor: pointer;
  font-weight: 600;
  border-radius: 0.6rem;
}
button:disabled {
  opacity: 0.55;
  cursor: default;
}
button:focus-visible {
  outline: 3px solid #93c5fd;
  outline-offset: 3px;
}
.primary {
  width: 100%;
  padding: 0.8rem;
  margin-top: 1rem;
  color: #fff;
  background: #2563eb;
}
.resend,
.back {
  display: block;
  width: 100%;
  padding: 0.75rem;
  background: transparent;
  color: #2563eb;
}
.resend {
  margin-top: 0.35rem;
}
.back {
  margin-top: 0.75rem;
  border: 1px solid #d8e1eb;
  color: #475569;
}
.message,
.error {
  padding: 0.75rem;
  border-radius: 0.6rem;
  font-size: 0.85rem;
  line-height: 1.5;
  margin-top: 0.75rem;
}
.message {
  background: #eff6ff;
  color: #1d4ed8;
}
.error {
  background: #fef2f2;
  color: #b91c1c;
}
.help {
  margin-top: 1rem;
}
</style>
