const COOKIE_NAME = 'tryon_user_key';

export function getOrCreateTryOnUserKey(event: any): string {
  const existing = getCookie(event, COOKIE_NAME);
  if (existing && typeof existing === 'string' && existing.length >= 10) return existing;

  const value = globalThis.crypto?.randomUUID?.() || `${Date.now()}-${Math.random().toString(16).slice(2)}`;
  setCookie(event, COOKIE_NAME, value, {
    httpOnly: true,
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    path: '/',
    maxAge: 60 * 60 * 24 * 365 * 2,
  });
  return value;
}

