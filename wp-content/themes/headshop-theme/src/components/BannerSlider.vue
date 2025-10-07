<template>
  <div class="relative full-bleed">
    <div class="swiper" ref="swiperEl">
      <div class="swiper-wrapper">
        <div v-for="(slide, idx) in processedSlides" :key="idx" class="swiper-slide">
          <div class="banner-slide relative" :style="{ backgroundImage: `url(${slide.image})` }">
            <div class="absolute inset-0 bg-gradient-to-r from-black/50 to-black/30" style="pointer-events: none;"></div>
            <div class="absolute inset-0 flex items-center" style="pointer-events: none;">
              <div class="container" style="pointer-events: none;">
                <div class="max-w-4xl">
                  <h1 v-if="slide.title" class="text-white text-4xl md:text-6xl lg:text-7xl font-bold leading-tight mb-6 animate-fade-in-up">
                    {{ slide.title }}
                  </h1>
                  <p v-if="slide.subtitle" class="text-white/90 text-xl md:text-2xl lg:text-3xl mb-8 leading-relaxed animate-fade-in-up" style="animation-delay: .15s">
                    {{ slide.subtitle }}
                  </p>
                  <a v-if="slide.ctaText" :href="slide.ctaUrl || '#'" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-300 font-semibold text-lg shadow-lg hover:shadow-xl hover:scale-105 animate-fade-in-up" style="animation-delay: .3s; pointer-events: auto;">
                    {{ slide.ctaText }}
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="showDots && processedSlides.length > 1" class="swiper-pagination"></div>
      <button v-if="showArrows && processedSlides.length > 1" ref="prevRef" class="swiper-button-prev" type="button" aria-label="Anterior" @click="goPrev"></button>
      <button v-if="showArrows && processedSlides.length > 1" ref="nextRef" class="swiper-button-next" type="button" aria-label="Próximo" @click="goNext"></button>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, computed, nextTick } from 'vue'
import Swiper from 'swiper'
import { Navigation, Pagination, EffectFade, Autoplay } from 'swiper/modules'

const props = defineProps({
  slides: { type: Array, default: () => [] },
  effect: { type: String, default: 'fade' },
  autoplay: { type: Boolean, default: true },
  speed: { type: Number, default: 5000 },
  showArrows: { type: Boolean, default: true },
  showDots: { type: Boolean, default: true },
})

const swiperEl = ref(null)
const nextRef = ref(null)
const prevRef = ref(null)
let swiperInstance = null

const processedSlides = computed(() => {
  const result = []
  props.slides.forEach(banner => {
    const images = banner.images || (banner.image ? [banner.image] : [])
    images.forEach((image, index) => {
      result.push({
        ...banner,
        image,
        key: `${banner.id || 'banner'}-${index}`
      })
    })
  })
  return result
})

function goNext() { if (swiperInstance) swiperInstance.slideNext(); }
function goPrev() { if (swiperInstance) swiperInstance.slidePrev(); }

onMounted(async () => {
  await nextTick()
  if (!swiperEl.value) return
  const paginationEl = swiperEl.value.querySelector('.swiper-pagination')

  swiperInstance = new Swiper(swiperEl.value, {
    modules: [Navigation, Pagination, EffectFade, Autoplay],
    effect: props.effect,
    // Infinite loop when there is more than one slide
    loop: processedSlides.value.length > 1,
    loopAdditionalSlides: 2,
    // Draggable / mouse & touch
    allowTouchMove: true,
    simulateTouch: true,
    grabCursor: true,
    // Smoothness
    speed: 800,
    autoplay: props.autoplay ? { delay: props.speed, disableOnInteraction: false } : false,
    fadeEffect: { crossFade: true },
    // Dots
    pagination: props.showDots ? { el: paginationEl, clickable: true, dynamicBullets: true } : undefined,
    // Arrows
    navigation: props.showArrows ? { nextEl: nextRef.value, prevEl: prevRef.value } : undefined,
    // Ensure it reacts to DOM updates
    observer: true,
    observeParents: true,
  })
})

onBeforeUnmount(() => { if (swiperInstance) { swiperInstance.destroy(true, true); swiperInstance = null } })
</script>

<style scoped>
.animate-fade-in-up { animation: fadeInUp .8s ease-out forwards; opacity: 0; }
@keyframes fadeInUp { from { opacity:0; transform: translateY(30px);} to { opacity:1; transform: translateY(0);} }

:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
  z-index: 20;
  pointer-events: auto;
}
</style>


