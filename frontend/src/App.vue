<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import NotificationHost from "./components/NotificationHost.vue";
import {
  setupSessionTimeout,
  hasExpiredNotice,
  dismissExpiredNotice,
  expiredSessionMessage,
} from "@/services/sessionTimeout";

const router = useRouter();
const showSessionExpired = ref(hasExpiredNotice());
const expiryMessage = ref(expiredSessionMessage());
let stopTimeout: (() => void) | undefined;
const showExpiry = () => {
  expiryMessage.value = expiredSessionMessage();
  showSessionExpired.value = true;
};
const closeExpiry = () => {
  showSessionExpired.value = false;
  dismissExpiredNotice();
};
onMounted(() => {
  window.addEventListener("els-session-expired", showExpiry);
  stopTimeout = setupSessionTimeout(router);
  showSessionExpired.value = hasExpiredNotice();
});
onUnmounted(() => {
  stopTimeout?.();
  window.removeEventListener("els-session-expired", showExpiry);
});
</script>

<template>
  <NotificationHost />
  <router-view />
  <Teleport to="body">
    <div v-if="showSessionExpired" class="session-expired-overlay">
      <section
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="session-expired-title"
        aria-describedby="session-expired-description"
        class="session-expired-dialog"
      >
        <h2 id="session-expired-title">Session expired</h2>
        <p id="session-expired-description">{{ expiryMessage }}</p>
        <button type="button" autofocus @click="closeExpiry">
          Back to sign in
        </button>
      </section>
    </div>
  </Teleport>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family:
    "Inter",
    -apple-system,
    BlinkMacSystemFont,
    "Segoe UI",
    Roboto,
    sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

#app {
  min-height: 100vh;
}
</style>

<style>
.session-expired-overlay {
  position: fixed;
  inset: 0;
  z-index: 100000;
  background: rgba(15, 23, 42, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
}
.session-expired-dialog {
  width: 100%;
  max-width: 420px;
  padding: 1.75rem;
  border-radius: 1rem;
  background: #fff;
  color: #172033;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
}
.session-expired-dialog h2 {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
}
.session-expired-dialog p {
  line-height: 1.6;
  color: #475569;
}
.session-expired-dialog button {
  display: block;
  width: 100%;
  margin-top: 1.5rem;
  padding: 0.75rem 1rem;
  border: 0;
  border-radius: 0.6rem;
  background: #2563eb;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}
.session-expired-dialog button:focus-visible {
  outline: 3px solid #93c5fd;
  outline-offset: 3px;
}
</style>
