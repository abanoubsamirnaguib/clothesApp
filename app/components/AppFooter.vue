<!--app/components/AppFooter.vue-->
<script setup>
const config = useRuntimeConfig();
const colorMode = useColorMode();

const toggleDark = () => {
  colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark';
};

const colorModeIcon = computed(() => (colorMode.preference === 'dark' ? 'i-iconamoon-mode-dark-fill' : 'i-iconamoon-mode-light-fill'));
</script>

<template>
  <footer class="my-5 flex items-center justify-between gap-3 px-5 text-[13px] font-semibold text-secondary-text dark:text-secondary-text-d">
    <div class="truncate">
      <a class="transition-all hover:text-black hover:dark:text-neutral-100" href="https://github.com/abanoubsamirnaguib/clothesApp" target="_blank">
         v{{ config.public.version }}<span v-if="config.public.buildId" class="opacity-60"> ({{ config.public.buildId }})</span>
      </a>
      —
      Developed by
      <a class="transition-all hover:text-black hover:dark:text-neutral-100" href="https://abanoubsamir.com" target="_blank">Abanoub Samir</a>
    </div>

    <div class="flex flex-none items-center gap-3">
      <!-- Theme -->
      <UTooltip text="Toggle theme" :open-delay="800">
        <button
          type="button"
          @click="toggleDark"
          class="flex h-8 items-center gap-1.5 rounded-lg p-2 transition-all bg-neutral-800/5 hover:bg-neutral-800/10 hover:text-black active:scale-95 dark:bg-white/10 hover:dark:bg-white/20 hover:dark:text-neutral-100">
          <UIcon :name="colorModeIcon" size="16" />
          <span class="capitalize leading-3">
            {{ colorMode.preference }}
          </span>
        </button>
      </UTooltip>
    </div>
  </footer>
</template>
