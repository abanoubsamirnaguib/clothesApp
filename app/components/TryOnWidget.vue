<script setup>
import { push } from 'notivue';

const props = defineProps({
  productId: { type: [String, Number], required: true },
  productName: { type: String, default: '' },
});

const userKey = useTryOnUserKey();

const file = ref(null);
const isLoading = ref(false);
const isPopupOpen = computed(() => isLoading.value);
const eligibility = ref({ allowed: true, reason: null });
const resultUrl = ref(null);
const garmentUrl = ref(null);
const errorMsg = ref(null);

const fetchResult = async () => {
  try {
    const res = await $fetch(useApiUrl('/api/tryon/result'), {
      query: { product_id: String(props.productId), user_key: userKey },
    });
    if (res?.status === 'completed' && res?.result_image_url) {
      resultUrl.value = res.result_image_url;
    }
    if (res?.garment_image_url) {
      garmentUrl.value = res.garment_image_url;
    }
  } catch (e) {
    // ignore
  }
};

const fetchEligibility = async () => {
  try {
    eligibility.value = await $fetch(useApiUrl('/api/tryon/eligibility'), {
      query: { product_id: String(props.productId), user_key: userKey },
    });
  } catch (e) {
    // If this fails, don't block UI.
    eligibility.value = { allowed: true, reason: null };
  }
};

const refresh = async () => {
  await Promise.all([fetchResult(), fetchEligibility()]);
};

onMounted(refresh);
watch(() => props.productId, refresh);

const onFile = e => {
  errorMsg.value = null;
  const f = e?.target?.files?.[0] || null;
  file.value = f;
};

const runTryOn = async () => {
  errorMsg.value = null;
  if (!eligibility.value?.allowed) return;
  if (!file.value) {
    errorMsg.value = 'Please upload your photo first.';
    push.error(errorMsg.value);
    return;
  }

  isLoading.value = true;
  try {
    const fd = new FormData();
    fd.set('product_id', String(props.productId));
    fd.set('user_key', userKey);
    fd.set('person_image', file.value);

    const res = await $fetch(useApiUrl('/api/tryon'), { method: 'POST', body: fd });
    resultUrl.value = res?.result_image_url || null;
    garmentUrl.value = res?.garment_image_url || null;
    await refresh();
    if (resultUrl.value) {
      push.success('Try-on ready');
    }
  } catch (e) {
    const msg = e?.data?.message || e?.statusMessage || e?.message || 'Try-on failed. Please try again later.';
    const friendly =
      typeof msg === 'string' && msg.toLowerCase().includes('no gpu')
        ? 'AI is busy right now (no GPU available). Please retry in a minute.'
        : msg;
    errorMsg.value = friendly;
    push.error(friendly);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div v-if="isPopupOpen" class="fixed inset-0 z-[60]">
    <div class="absolute inset-0 bg-black/35 backdrop-blur-md"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-md rounded-[28px] bg-white/90 dark:bg-black/60 border border-black/10 dark:border-white/10 shadow-2xl backdrop-blur-xl p-5">
        <div class="flex items-start gap-4">
          <div class="bg-black/10 dark:bg-white/15 flex rounded-full w-12 h-12 items-center justify-center shrink-0">
            <UIcon name="i-svg-spinners-8-dots-rotate" size="26" class="text-black dark:text-white" />
          </div>
          <div class="flex-1">
            <div class="text-base font-semibold">Generating your try-on…</div>
            <div class="text-sm opacity-70 mt-1">
              This can take up to 1–2 minutes. Please keep this page open.
            </div>
            <div class="mt-3 h-2 w-full rounded-full bg-black/10 dark:bg-white/15 overflow-hidden">
              <div class="h-full w-1/2 rounded-full bg-alizarin-crimson-700 animate-pulse"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4 border-t border-[#efefef] dark:border-[#262626] pt-4 px-3 lg:px-0">
    <div class="text-base mb-2 font-semibold">AI Try-on</div>
    <p class="text-sm opacity-70 mb-3">Upload your photo and try this product once.</p>

    <div class="flex flex-col gap-3">
      <input
        class="block w-full text-sm"
        type="file"
        accept="image/png,image/jpeg,image/webp"
        :disabled="isLoading || !eligibility.allowed"
        @change="onFile" />

      <button
        class="button-bezel w-full h-12 rounded-md relative tracking-wide font-semibold text-white text-sm flex justify-center items-center disabled:opacity-50"
        :disabled="isLoading || !eligibility.allowed"
        @click="runTryOn">
        <span v-if="!isLoading && eligibility.allowed">Try it on</span>
        <span v-else-if="!eligibility.allowed">Already tried today</span>
        <span v-else>Generating…</span>
      </button>

      <div v-if="errorMsg" class="text-sm text-red-600 dark:text-red-400">{{ errorMsg }}</div>

      <div v-if="resultUrl" class="mt-2">
        <div class="text-sm font-semibold mb-2">Result</div>
        <NuxtImg :src="resultUrl" :alt="`Try-on result for ${productName}`" class="w-full rounded-xl bg-neutral-200 dark:bg-neutral-800" />
      </div>

      <div v-else-if="garmentUrl" class="mt-2 text-xs opacity-60">
        Using product image: {{ garmentUrl }}
      </div>
    </div>
  </div>
</template>

