import api from "./api";
import { clearSession } from "./sessionTimeout";

const KEY = "els-email-verification";
export interface VerificationSession {
  challenge_token: string;
  masked_email: string;
  challenge_expires_at: string;
  resend_at: number;
  message: string;
}
export function beginEmailVerification(data: {
  challenge_token: string;
  masked_email: string;
  challenge_expires_at: string;
  resend_after?: number;
  message?: string;
}): void {
  clearSession();
  const session: VerificationSession = {
    challenge_token: data.challenge_token,
    masked_email: data.masked_email,
    challenge_expires_at: data.challenge_expires_at,
    resend_at: Date.now() + Number(data.resend_after || 0) * 1000,
    message:
      data.message ||
      "Check your inbox and spam folder for your verification code.",
  };
  sessionStorage.setItem(KEY, JSON.stringify(session));
}
export function getEmailVerification(): VerificationSession | null {
  try {
    const session = JSON.parse(
      sessionStorage.getItem(KEY) || "null",
    ) as VerificationSession | null;
    if (
      !session ||
      typeof session.challenge_token !== "string" ||
      session.challenge_token.length !== 64 ||
      !Number.isFinite(Date.parse(session.challenge_expires_at)) ||
      Date.parse(session.challenge_expires_at) <= Date.now()
    ) {
      clearEmailVerification();
      return null;
    }
    return session;
  } catch {
    clearEmailVerification();
    return null;
  }
}
export function saveEmailVerification(session: VerificationSession): void {
  sessionStorage.setItem(KEY, JSON.stringify(session));
}
export function clearEmailVerification(): void {
  sessionStorage.removeItem(KEY);
}
export async function resendEmailVerification(challenge_token: string) {
  return (await api.post("/email-verification/resend", { challenge_token }))
    .data;
}
export async function verifyEmailCode(challenge_token: string, code: string) {
  return (
    await api.post("/email-verification/verify", { challenge_token, code })
  ).data;
}
