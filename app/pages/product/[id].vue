<!--app/pages/product/[id].vue-->
<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination, Thumbs } from 'swiper/modules';
const { isOpenImageSliderModal } = useComponents();

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

const thumbsSwiper = ref(null);
const setThumbsSwiper = swiper => {
  thumbsSwiper.value = swiper;
};

const modules = [Navigation, Pagination, Thumbs];

const route = useRoute();
const id = computed(() => route.params.id);
const slug = computed(() => String(id.value));

const productResult = ref(null);
const selectedVariation = ref(null);
const relatedProducts = ref([]);

const normalizeRelated = (payload) => {
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload?.data)) return payload.data;
  return [];
};

const fetchProduct = async () => {
  const data = await $fetch(useApiUrl(`/api/products/${encodeURIComponent(slug.value)}`));
  productResult.value = data?.data || null;
  relatedProducts.value = normalizeRelated(data?.related);
};

onMounted(fetchProduct);

watch(slug, () => {
  fetchProduct();
});

const product = computed(() => productResult.value);

const sizeOrder = ['xxs', 'xs', 's', 'm', 'l', 'xl', '2xl', '23-24', '25', '26-27', '28-29', '30', '31-32', '33', '34-35'];

const sortedSizes = computed(() => {
  if (!product.value?.sizes) return [];
  return product.value.sizes.slice().sort((a, b) => {
    const aSize = a.toLowerCase();
    const bSize = b.toLowerCase();
    return sizeOrder.indexOf(aSize) - sizeOrder.indexOf(bSize);
  });
});

watchEffect(() => {
  if (sortedSizes.value.length > 0 && !selectedVariation.value) {
    selectedVariation.value = sortedSizes.value[0];
  }
});

const { handleAddToCart, addToCartButtonStatus } = useCart();
</script>

<template>
  <ProductSeo v-if="product?.name" :info="product" />
  <ProductSkeleton v-if="!product?.name" />
  <div v-else class="justify-center flex flex-col lg:flex-row lg:mx-5">
    <ButtonBack />
    <div class="mr-6 mt-5 pt-2.5 max-xl:hidden">
      <swiper :modules="modules" @swiper="setThumbsSwiper" class="product-images-thumbs w-14">
        <swiper-slide class="cursor-pointer rounded-xl overflow-hidden border-2 border-white dark:border-black">
          <NuxtImg
            :alt="product.name"
            class="h-full w-full border-2 border-white bg-neutral-200 dark:bg-neutral-800 dark:border-black rounded-[10px]"
            :src="product.image?.sourceUrl" />
        </swiper-slide>
        <swiper-slide class="cursor-pointer rounded-xl overflow-hidden border-2 border-white dark:border-black" v-for="(image, i) in product.images?.slice(1)" :key="i">
          <NuxtImg :alt="product.name" class="h-full w-full border-2 border-white bg-neutral-200 dark:bg-neutral-800 dark:border-black rounded-[10px]" :src="image" />
        </swiper-slide>
      </swiper>
    </div>
    <div
      class="flex lg:p-5 lg:gap-5 flex-col lg:flex-row lg:border lg:border-transparent lg:dark:border-[#262626] lg:rounded-[32px] lg:shadow-[0_1px_20px_rgba(0,0,0,.15)] lg:mt-2.5 select-none">
      <div class="relative">
        <swiper
          :style="{
            '--swiper-navigation-color': '#000',
            '--swiper-pagination-color': 'rgb(0 0 0 / 50%)',
          }"
          :spaceBetween="4"
          :slidesPerView="1.5"
          :pagination="{
            dynamicBullets: true,
          }"
          :navigation="true"
          :modules="modules"
          :thumbs="{ swiper: thumbsSwiper }"
          class="lg:w-[530px] lg:h-[530px] xl:w-[600px] xl:h-[600px] lg:rounded-2xl">
          <swiper-slide @click="isOpenImageSliderModal = true">
            <NuxtImg :alt="product.name" class="h-full w-full bg-neutral-200 dark:bg-neutral-800" :src="product.image?.sourceUrl" />
          </swiper-slide>
          <swiper-slide @click="isOpenImageSliderModal = true" v-for="(image, i) in product.images?.slice(1)" :key="i">
            <NuxtImg :alt="product.name" class="h-full w-full bg-neutral-200 dark:bg-neutral-800" :src="image" />
          </swiper-slide>
        </swiper>
      </div>
      <ImageSliderWithModal :product="product" v-model="isOpenImageSliderModal" />
      <div class="w-full lg:max-w-[28rem]">
        <div class="flex-col flex gap-4 lg:max-h-[530px] xl:max-h-[600px] overflow-auto">
          <div class="p-3 lg:pb-4 lg:p-0 border-b border-[#efefef] dark:border-[#262626]">
            <h1 class="text-2xl font-semibold mb-1">{{ product.name }}</h1>
            <ProductPrice :sale-price="product.salePrice" :regular-price="product.regularPrice" />
          </div>
          <div class="px-3 lg:px-0 text-sm font-semibold text-neutral-600 dark:text-neutral-300" v-if="product.color">
            Color: {{ product.color }}
          </div>

          <div class="pb-4 px-3 lg:px-0 border-b border-[#efefef] dark:border-[#262626]">
            <div class="text-sm font-semibold leading-5 opacity-50 flex gap-1">
              Size:
              <div class="uppercase">{{ selectedVariation }}</div>
            </div>
            <div class="flex gap-2 mt-2 mb-4 flex-wrap">
              <label
                class="py-1 px-3 rounded-md cursor-pointer select-varitaion border-2 border-[#9b9b9b] dark:border-[#8c8c8c] transition-all duration-200"
                v-for="size in sortedSizes"
                :key="size"
                :class="[selectedVariation === size ? 'selected-varitaion' : '']">
                <input type="radio" class="hidden" name="variation" :value="size" v-model="selectedVariation" />
                <span class="font-semibold uppercase" :title="`Size: ${size}`">{{ size }}</span>
              </label>
            </div>
            <div class="flex">
              <button
                @click="handleAddToCart(product, selectedVariation)"
                :disabled="addToCartButtonStatus !== 'add' || !selectedVariation"
                class="button-bezel w-full h-12 rounded-md relative tracking-wide font-semibold text-white text-sm flex justify-center items-center">
                <Transition name="slide-up">
                  <div v-if="addToCartButtonStatus === 'add'" class="absolute">Add to cart</div>
                  <UIcon v-else-if="addToCartButtonStatus === 'loading'" class="absolute" name="i-svg-spinners-90-ring-with-bg" size="22" />
                  <div v-else-if="addToCartButtonStatus === 'added'" class="absolute">Added to cart!</div>
                </Transition>
              </button>
              <ButtonWishlist :product="product" />
            </div>
          </div>
          <div class="px-3 lg:px-0">
            <div class="text-base mb-2 font-semibold">Featured information</div>
            <div class="description leading-7 text-sm">
              <ul>
                <li>
                  Free returns. <a class="underline" href="#">More information</a>
                </li>
                <li>SKU: {{ product.sku }}</li>
                <div v-html="product.description"></div>
              </ul>
            </div>
          </div>

          <TryOnWidget v-if="product?.id" :product-id="product.id" :product-name="product.name" />
        </div>
      </div>
    </div>
  </div>
  <div class="text-lg lg:text-xl lg:text-center font-semibold mt-4 pt-4 px-3 border-t border-[#efefef] dark:border-[#262626] lg:border-none">Shop similar</div>
  <div class="grid grid-cols-1 xs:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 3xl:grid-cols-7 gap-4 px-3 lg:px-5 xl:px-8 mt-4 lg:mt-5">
    <ProductCard :products="relatedProducts" />
    <ProductsSkeleton v-if="!product?.name" />
  </div>
</template>

<style lang="postcss">
.product-images-thumbs .swiper-wrapper {
  @apply flex-col gap-3;
}
.product-images-thumbs .swiper-slide-thumb-active {
  @apply border-black dark:border-white;
}
.swiper-button-next,
.swiper-button-prev {
  @apply bg-white/50 hover:bg-white p-3.5 m-2 rounded-full flex items-center justify-center shadow transition backdrop-blur-sm;
}

.swiper-button-prev.swiper-button-disabled,
.swiper-button-next.swiper-button-disabled {
  @apply hidden;
}

.swiper-pagination {
  @apply bg-white/50 shadow-sm rounded-full py-1 backdrop-blur-sm;
}

.selected-varitaion,
.select-varitaion:hover:not(.disabled) {
  @apply border-alizarin-crimson-700 dark:border-alizarin-crimson-700 text-alizarin-crimson-700 bg-red-700/10;
}

.disabled {
  @apply opacity-40 cursor-default;
}

.button-bezel {
  box-shadow: 0 0 0 var(--button-outline, 0px) rgb(222, 92, 92, 0.3), inset 0 -1px 1px 0 rgba(0, 0, 0, 0.25), inset 0 1px 0 0 rgba(255, 255, 255, 0.3),
    0 1px 2px 0 rgba(0, 0, 0, 0.5);
  @apply bg-alizarin-crimson-700 outline-none tracking-[-0.125px] transition scale-[var(--button-scale,1)] duration-200;
  &:hover {
    @apply bg-alizarin-crimson-600;
  }
  &:active {
    --button-outline: 4px;
    --button-scale: 0.975;
  }
}

.description ul li {
  background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxlbGxpcHNlIHJ5PSIzIiByeD0iMyIgY3k9IjMiIGN4PSIzIiBmaWxsPSIjYzljOWM5Ii8+PC9zdmc+)
    no-repeat 0 0.7rem;
  padding-left: 0.938rem;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease 0s, opacity 0.3s ease 0s;
}

.slide-up-enter-from {
  opacity: 0;
  transform: translateY(-30px) scale(0);
}

.slide-up-leave-to {
  opacity: 0;
  transform: translateY(30px) scale(0);
}
</style>
