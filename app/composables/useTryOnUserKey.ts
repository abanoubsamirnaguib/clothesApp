const STORAGE_KEY = 'tryon_user_key';

function createKey() {
  const cryptoAny = globalThis.crypto as any;
  return cryptoAny?.randomUUID?.() || `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

export function useTryOnUserKey(): string {
  if (process.server) return '';

  try {
    const existing = localStorage.getItem(STORAGE_KEY);
    if (existing && existing.length >= 10) return existing;
    const value = createKey();
    localStorage.setItem(STORAGE_KEY, value);
    return value;
  } catch {
    // If storage is blocked, fall back to an in-memory key for this session.
    return createKey();
  }
}

