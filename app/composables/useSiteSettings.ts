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

  // Fetch once per app instance.
  if (settings.value?.store_name && settings.value.store_name !== 'NuxtCommerce') return settings;

  const { data } = await useFetch<SiteSettings>(`${base.replace(/\/$/, '')}/api/settings`, {
    key: 'site-settings-fetch',
  });

  if (data.value?.store_name) {
    settings.value = data.value;
  }

  return settings;
}

