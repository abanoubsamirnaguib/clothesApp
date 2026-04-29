export type SiteSettings = {
  store_name: string;
  currency: string;
  currency_symbol: string;
  whatsapp_number: string;
};

export async function useSiteSettings() {
  const config = useRuntimeConfig();

  const settings = useState<SiteSettings>('site-settings', () => ({
    store_name: 'NuxtCommerce',
    currency: 'USD',
    currency_symbol: '$',
    whatsapp_number: '',
  }));

  // If API URL isn't configured, keep defaults.
  const base = (config.laravelApiUrl as string) || '';
  if (!base) return settings;

  // During SSG/prerender (build time), we might not have the backend available.
  // Fetch settings on the client only to avoid prerender failures.
  if (!process.client) return settings;

  // Fetch once per app instance.
  if (settings.value?.store_name && settings.value.store_name !== 'NuxtCommerce') return settings;

  try {
    const { data } = await useFetch<SiteSettings>(`${base.replace(/\/$/, '')}/api/settings`, {
      key: 'site-settings-fetch',
    });

    if (data.value?.store_name) {
      settings.value = data.value;
    }
  } catch (error) {
    // If the backend is down/misconfigured, keep defaults and let the app load.
    console.warn('[useSiteSettings] Failed to fetch settings:', (error as Error)?.message);
  }

  return settings;
}

