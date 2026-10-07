<script setup lang="ts">
import { nextTick, ref, watch } from "vue";
import {
  AlertTriangle,
  CheckCircle2,
  Info,
  X,
  XCircle,
} from "lucide-vue-next";
import {
  useNotifications,
  notify,
  type NotificationType,
} from "@/composables/useNotifications";

const {
  notifications,
  activeConfirmation,
  dismissNotification,
  resolveConfirmation,
} = useNotifications();

const confirmButton = ref<HTMLButtonElement | null>(null);
const confirmationDialog = ref<HTMLElement | null>(null);
const confirmationBusy = ref(false);

const notificationStyles: Record<
  NotificationType,
  { icon: typeof CheckCircle2; className: string; label: string }
> = {
  success: {
    icon: CheckCircle2,
    className: "notification-success",
    label: "Success",
  },
  error: {
    icon: XCircle,
    className: "notification-error",
    label: "Error",
  },
  warning: {
    icon: AlertTriangle,
    className: "notification-warning",
    label: "Warning",
  },
  info: { icon: Info, className: "notification-info", label: "Information" },
};

watch(activeConfirmation, async (confirmation) => {
  if (confirmation) {
    await nextTick();
    confirmButton.value?.focus();
  }
});

const trapConfirmationFocus = (event: KeyboardEvent) => {
  if (!activeConfirmation.value) return;

  if (event.key === "Escape") {
    event.preventDefault();
    if (!confirmationBusy.value) resolveConfirmation(false);
    return;
  }

  if (event.key !== "Tab") return;
  const buttons =
    confirmationDialog.value?.querySelectorAll<HTMLButtonElement>(
      "button:not(:disabled)",
    );
  if (!buttons?.length) return;

  const first = buttons.item(0);
  const last = buttons.item(buttons.length - 1);
  if (!first || !last) return;

  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault();
    last.focus();
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault();
    first.focus();
  }
};

const cancelConfirmation = () => {
  if (!confirmationBusy.value) resolveConfirmation(false);
};

const submitConfirmation = async () => {
  const confirmation = activeConfirmation.value;
  if (!confirmation || confirmationBusy.value) return;

  if (!confirmation.onConfirm) {
    resolveConfirmation(true);
    return;
  }

  confirmationBusy.value = true;
  try {
    await confirmation.onConfirm();
    resolveConfirmation(true);
  } catch {
    notify.error("Unable to complete this action. Please try again.");
  } finally {
    confirmationBusy.value = false;
  }
};
</script>

<template>
  <Teleport to="body">
    <section
      class="notification-stack"
      aria-label="Notifications"
      aria-live="polite"
      aria-relevant="additions removals"
    >
      <TransitionGroup name="notification">
        <article
          v-for="notification in notifications"
          :key="notification.id"
          class="notification-card"
          :class="notificationStyles[notification.type].className"
          :role="notification.type === 'error' ? 'alert' : 'status'"
        >
          <component
            :is="notificationStyles[notification.type].icon"
            class="notification-icon"
            aria-hidden="true"
          />
          <span class="sr-only">{{ notificationStyles[notification.type].label }}:</span>
          <p class="notification-message">{{ notification.message }}</p>
          <button
            type="button"
            class="notification-close"
            :aria-label="`Dismiss ${notificationStyles[notification.type].label.toLowerCase()} notification`"
            @click="dismissNotification(notification.id)"
          >
            <X :size="18" aria-hidden="true" />
          </button>
        </article>
      </TransitionGroup>
    </section>

    <Transition name="confirmation">
      <div
        v-if="activeConfirmation"
        class="confirmation-backdrop"
        @click.self="cancelConfirmation"
        @keydown="trapConfirmationFocus"
      >
        <section
          ref="confirmationDialog"
          class="confirmation-dialog"
          role="alertdialog"
          aria-modal="true"
          aria-labelledby="app-confirmation-title"
          aria-describedby="app-confirmation-message"
        >
          <div class="confirmation-content">
            <h2 id="app-confirmation-title">{{ activeConfirmation.title }}</h2>
            <p id="app-confirmation-message">{{ activeConfirmation.message }}</p>
          </div>
          <div class="confirmation-actions">
            <button
              type="button"
              class="confirmation-cancel"
              :disabled="confirmationBusy"
              :aria-label="activeConfirmation.cancelLabel || 'Cancel'"
              @click="cancelConfirmation"
            >
              {{ activeConfirmation.cancelLabel || "Cancel" }}
            </button>
            <button
              ref="confirmButton"
              type="button"
              :disabled="confirmationBusy"
              :class="
                activeConfirmation.variant === 'primary'
                  ? 'confirmation-primary'
                  : 'confirmation-destructive'
              "
              @click="submitConfirmation"
            >
              {{
                confirmationBusy
                  ? activeConfirmation.busyLabel || "Working..."
                  : activeConfirmation.confirmLabel || "Confirm"
              }}
            </button>
          </div>
        </section>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.notification-stack {
  position: fixed;
  z-index: 1000;
  top: max(1rem, env(safe-area-inset-top));
  right: max(1rem, env(safe-area-inset-right));
  display: flex;
  width: min(26rem, calc(100vw - 2rem));
  flex-direction: column;
  gap: 0.75rem;
  pointer-events: none;
}

.notification-card {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  border: 1px solid var(--border);
  border-left-width: 4px;
  border-radius: 0.75rem;
  background: var(--surface);
  padding: 1rem;
  color: var(--text);
  box-shadow: 0 12px 32px rgb(15 23 42 / 16%);
  pointer-events: auto;
}

.notification-success {
  border-left-color: #15803d;
}

.notification-error {
  border-left-color: #b91c1c;
}

.notification-warning {
  border-left-color: #a16207;
}

.notification-info {
  border-left-color: var(--primary);
}

.notification-icon {
  width: 1.25rem;
  height: 1.25rem;
  flex: none;
}

.notification-success .notification-icon {
  color: #15803d;
}

.notification-error .notification-icon {
  color: #b91c1c;
}

.notification-warning .notification-icon {
  color: #a16207;
}

.notification-info .notification-icon {
  color: var(--primary);
}

.notification-message {
  flex: 1;
  margin: 0;
  overflow-wrap: anywhere;
  white-space: pre-line;
  font-size: 0.9rem;
  line-height: 1.4;
}

.notification-close {
  display: grid;
  flex: none;
  place-items: center;
  border: 0;
  border-radius: 0.375rem;
  background: transparent;
  padding: 0.125rem;
  color: var(--text-muted);
  cursor: pointer;
}

.notification-close:hover {
  background: var(--surface-soft);
  color: var(--text);
}

.confirmation-backdrop {
  position: fixed;
  z-index: 1100;
  inset: 0;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgb(15 23 42 / 55%);
}

.confirmation-dialog {
  width: min(100%, 30rem);
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: 1rem;
  background: var(--surface);
  color: var(--text);
  box-shadow: 0 24px 64px rgb(15 23 42 / 25%);
}

.confirmation-content {
  padding: 1.5rem;
}

.confirmation-content h2 {
  margin: 0 0 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
}

.confirmation-content p {
  margin: 0;
  color: var(--text-muted);
  line-height: 1.6;
  white-space: pre-line;
}

.confirmation-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  border-top: 1px solid var(--border);
  background: var(--surface-muted);
  padding: 1rem 1.5rem;
}

.confirmation-actions button {
  min-height: 2.5rem;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font: inherit;
  font-weight: 600;
  cursor: pointer;
}

.confirmation-cancel {
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
}

.confirmation-cancel:hover {
  background: var(--surface-soft);
}

.confirmation-destructive {
  border: 1px solid #b91c1c;
  background: #b91c1c;
  color: white;
}

.confirmation-destructive:hover {
  border-color: #991b1b;
  background: #991b1b;
}

.confirmation-primary {
  border: 1px solid var(--primary);
  background: var(--primary);
  color: white;
}

.confirmation-primary:hover {
  border-color: var(--primary-hover);
  background: var(--primary-hover);
}

.notification-enter-active,
.notification-leave-active,
.confirmation-enter-active,
.confirmation-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.notification-enter-from,
.notification-leave-to {
  opacity: 0;
  transform: translateX(1rem);
}

.notification-leave-active {
  position: absolute;
  right: 0;
  left: 0;
}

.confirmation-enter-from,
.confirmation-leave-to {
  opacity: 0;
  transform: scale(0.98);
}

@media (max-width: 480px) {
  .notification-stack {
    top: max(0.75rem, env(safe-area-inset-top));
    right: max(0.75rem, env(safe-area-inset-right));
    width: calc(100vw - 1.5rem);
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
  }
}
</style>
