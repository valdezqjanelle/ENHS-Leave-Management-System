import { ref } from "vue";

export type NotificationType = "success" | "error" | "warning" | "info";

export interface AppNotification {
  id: number;
  message: string;
  type: NotificationType;
}

export interface ConfirmationOptions {
  title: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  busyLabel?: string;
  variant?: "danger" | "primary";
  onConfirm?: () => Promise<void>;
}

interface ConfirmationRequest extends ConfirmationOptions {
  resolve: (confirmed: boolean) => void;
  previousFocus: HTMLElement | null;
}

const notifications = ref<AppNotification[]>([]);
const activeConfirmation = ref<ConfirmationRequest | null>(null);
const confirmationQueue: ConfirmationRequest[] = [];
const notificationTimers = new Map<number, ReturnType<typeof setTimeout>>();
let nextNotificationId = 0;

const inferNotificationType = (message: string): NotificationType => {
  const normalized = message.toLowerCase();

  if (
    /\b(failed|unable|error|cannot|could not|not found|try again|exception)\b/.test(
      normalized,
    )
  ) {
    return "error";
  }

  if (
    /\b(please|must|required|invalid|select|choose|enter|specify|provide|match|exceeds|at least|greater than|less than)\b/.test(
      normalized,
    )
  ) {
    return "warning";
  }

  if (
    /\b(success|successfully|saved|updated|created|submitted|applied|revoked|restored|uploaded|deleted|cleared|downloaded|exported|sent)\b/.test(
      normalized,
    )
  ) {
    return "success";
  }

  return "info";
};

const userFriendlyMessage = (message: string): string => {
  if (
    /^(?:AxiosError|Network Error|Request failed with status code)\b/i.test(
      message.trim(),
    )
  ) {
    return "Unable to complete the request. Please try again.";
  }

  return message;
};

const showNotification = (
  input: string,
  type?: NotificationType,
): number => {
  const message = userFriendlyMessage(input);
  const notificationType = type ?? inferNotificationType(message);
  const id = ++nextNotificationId;

  notifications.value.push({
    id,
    message,
    type: notificationType,
  });

  const duration = notificationType === "error" ? 7000 : 4500;
  notificationTimers.set(
    id,
    setTimeout(() => dismissNotification(id), duration),
  );

  return id;
};

const dismissNotification = (id: number) => {
  const timer = notificationTimers.get(id);
  if (timer) clearTimeout(timer);
  notificationTimers.delete(id);
  notifications.value = notifications.value.filter(
    (notification) => notification.id !== id,
  );
};

export const notify = Object.assign(
  (message: string, type?: NotificationType) =>
    showNotification(message, type),
  {
    success: (message: string) => showNotification(message, "success"),
    error: (message: string) => showNotification(message, "error"),
    warning: (message: string) => showNotification(message, "warning"),
    info: (message: string) => showNotification(message, "info"),
  },
);

export const confirmAction = (
  options: ConfirmationOptions,
): Promise<boolean> =>
  new Promise((resolve) => {
    const request: ConfirmationRequest = {
      ...options,
      resolve,
      previousFocus:
        document.activeElement instanceof HTMLElement
          ? document.activeElement
          : null,
    };

    if (activeConfirmation.value) {
      confirmationQueue.push(request);
    } else {
      activeConfirmation.value = request;
    }
  });

export const resolveConfirmation = (confirmed: boolean) => {
  const request = activeConfirmation.value;
  if (!request) return;

  activeConfirmation.value = confirmationQueue.shift() ?? null;
  request.resolve(confirmed);

  if (!activeConfirmation.value) {
    window.setTimeout(() => request.previousFocus?.focus(), 0);
  }
};

export const useNotifications = () => ({
  notifications,
  activeConfirmation,
  dismissNotification,
  resolveConfirmation,
});
