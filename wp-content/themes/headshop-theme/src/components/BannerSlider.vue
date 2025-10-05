<template>
  <div class="relative full-bleed">
    <div class="swiper" ref="swiperEl">
      <div class="swiper-wrapper">
        <div v-for="(slide, idx) in slides" :key="idx" class="swiper-slide">
          <div class="banner-slide relative" :style="{ backgroundImage: `url(${slide.image})` }">
            <div class="absolute inset-0 bg-gradient-to-r from-black/50 to-black/30"></div>
            <div class="absolute inset-0 flex items-center">
              <div class="container">
                <div class="max-w-4xl">
                  <h1 v-if="slide.title" class="text-white text-4xl md:text-6xl lg:text-7xl font-bold leading-tight mb-6 animate-fade-in-up">
                    {{ slide.title }}
                  </h1>
                  <p v-if="slide.subtitle" class="text-white/90 text-xl md:text-2xl lg:text-3xl mb-8 leading-relaxed animate-fade-in-up" style="animation-delay: .15s">
                    {{ slide.subtitle }}
                  </p>
                  <a v-if="slide.ctaText" :href="slide.ctaUrl || '#'" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-300 font-semibold text-lg shadow-lg hover:shadow-xl hover:scale-105 animate-fade-in-up" style="animation-delay: .3s">
                    {{ slide.ctaText }}
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="showDots && slides.length > 1" class="swiper-pagination"></div>
      <div v-if="showArrows && slides.length > 1" class="swiper-button-prev"></div>
      <div v-if="showArrows && slides.length > 1" class="swiper-button-next"></div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import Swiper, { Navigation, Pagination, EffectFade, Autoplay } from 'swiper'
Swiper.use([Navigation, Pagination, EffectFade, Autoplay])

const props = defineProps({
  slides: { type: Array, default: () => [] },
  effect: { type: String, default: 'fade' },
  autoplay: { type: Boolean, default: true },
  speed: { type: Number, default: 5000 },
  showArrows: { type: Boolean, default: true },
  showDots: { type: Boolean, default: true },
})

const swiperEl = ref(null)

onMounted(() => {
  if (!swiperEl.value) return
  const instance = new Swiper(swiperEl.value, {
    effect: props.effect,
    loop: true,
    speed: 1000,
    autoplay: props.autoplay ? { delay: props.speed, disableOnInteraction: false } : false,
    fadeEffect: { crossFade: true },
    navigation: props.showArrows ? { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' } : undefined,
    pagination: props.showDots ? { el: '.swiper-pagination', clickable: true, dynamicBullets: true } : undefined,
  })
})
</script>

<style scoped>
.animate-fade-in-up { animation: fadeInUp .8s ease-out forwards; opacity: 0; }
@keyframes fadeInUp { from { opacity:0; transform: translateY(30px);} to { opacity:1; transform: translateY(0);} }
</style>


