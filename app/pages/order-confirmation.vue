<script setup>
const route = useRoute();
const orderNumber = computed(() => route.query.order || 'your order');

useSeoMeta({
  title: 'Order Confirmation',
  robots: 'noindex, nofollow',
});
</script>

<template>
  <div class="min-h-[calc(100vh-152px)] flex items-center justify-center px-5">
    <div class="max-w-lg rounded-[2rem] bg-black/5 dark:bg-white/10 p-8 text-center shadow-xl">
      <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300">
        <UIcon name="i-iconamoon-check-circle-1-fill" size="34" />
      </div>
      <h1 class="mb-2 text-3xl font-black">Order saved</h1>
      <p class="mb-6 text-neutral-600 dark:text-neutral-300">
        {{ orderNumber }} has been saved in the admin dashboard. WhatsApp was opened with your order message ready to send.
      </p>
      <NuxtLink to="/" class="inline-flex rounded-xl bg-black px-5 py-3 font-bold text-white dark:bg-white dark:text-black">Continue shopping</NuxtLink>
    </div>
  </div>
</template>
