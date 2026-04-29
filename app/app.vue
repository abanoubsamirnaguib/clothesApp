<!--app/app.vue-->
<script setup lang="ts">
const { site } = useAppConfig();
const { description } = site as any;

const settings = await useSiteSettings();
const storeName = computed(() => settings.value?.store_name || (site as any)?.name || 'NuxtCommerce');

useHead({
  htmlAttrs: { lang: 'en' },
  titleTemplate: (chunk?: string) => (chunk ? `${chunk} - ${storeName.value}` : storeName.value),
  title: storeName,
});

useSeoMeta({
  description,
  ogType: 'website',
  ogSiteName: storeName,
  ogLocale: 'en_US',
  ogImage: 'https://commerce.nuxt.dev/social-card.jpg',
  twitterCard: 'summary_large_image',
  twitterSite: '@zhatlen',
  twitterCreator: '@zhatlen',
  twitterImage: 'https://commerce.nuxt.dev/social-card.jpg',
  keywords: computed(() => `${storeName.value}, ecommerce, nuxt, woocommerce`),
  viewport: 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover',
});
</script>

<template>
  <AppHeader />
  <main class="pt-[72px] lg:pt-20 min-h-[calc(100vh-72px)]">
    <NuxtPage />
  </main>
  <AppFooter />
  <Notivue v-slot="item">
    <Notification :item="item" :theme="materialTheme" />
  </Notivue>
</template>

<style lang="postcss">
.dark {
  @apply bg-black text-neutral-100;
  color-scheme: dark;
}
.dropdown-enter-active {
  @apply transition duration-200 ease-out;
}
.dropdown-enter-from,
.dropdown-leave-to {
  @apply translate-y-5 opacity-0;
}
.dropdown-enter-to,
.dropdown-leave-from {
  @apply transform opacity-100;
}
.dropdown-leave-active {
  @apply transition duration-150 ease-in;
}
</style>
