const trimTrailingSlashes = (value: string) => value.replace(/\/+$/, '');

export function useApiBaseUrl() {
  const config = useRuntimeConfig();
  const base = (config.public?.laravelApiUrl || '') as string;
  return trimTrailingSlashes(base || '');
}

export function useApiUrl(path: string) {
  const base = useApiBaseUrl();
  if (!base) return path;
  const normalizedPath = path.startsWith('/') ? path : `/${path}`;
  return `${base}${normalizedPath}`;
}

