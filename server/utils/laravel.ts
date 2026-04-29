const trimSlashes = (value: string) => value.replace(/\/+$/, '');

export const laravelApi = () => trimSlashes(useRuntimeConfig().laravelApiUrl || '');

export const laravelFetch = (path: string, options: any = {}): Promise<unknown> => {
  const base = laravelApi();
  if (!base) {
    throw createError({ statusCode: 500, statusMessage: 'LARAVEL_API_URL is not configured' });
  }

  return $fetch(`${base}${path}`, options);
};
