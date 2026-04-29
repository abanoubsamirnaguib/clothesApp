const trimSlashes = (value: string) => value.replace(/\/+$/, '');

export const laravelApi = () => trimSlashes(useRuntimeConfig().laravelApiUrl || '');

export const laravelFetch = (path: string, options: any = {}): Promise<unknown> => {
  const base = laravelApi();
  if (!base) {
    // When building/prerendering (or if env isn't configured yet), avoid crashing the build.
    console.warn('[laravelFetch] LARAVEL_API_URL is not configured');
    return Promise.resolve(null);
  }

  // Avoid unhandled rejections during SSG/prerender when backend isn't ready.
  return $fetch(`${base}${path}`, options).catch((error) => {
    console.warn(`[laravelFetch] Failed to fetch ${base}${path}:`, (error as Error)?.message);
    return null;
  });
};
