<template>
  <section>
    <BannerSlider
      :slides="slides"
      effect="fade"
      :autoplay="true"
      :speed="5000"
      :show-arrows="true"
      :show-dots="true"
    />

    <div class="container py-16">
      <h2 class="text-3xl md:text-4xl font-bold mb-6">Categorias</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <a v-for="cat in categories" :key="cat.id" href="#" class="group block rounded-2xl overflow-hidden bg-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
          <div class="aspect-[3/2] bg-gray-100 bg-cover bg-center relative" :style="{ backgroundImage: `url(${cat.image})` }">
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
          </div>
          <div class="p-6">
            <h3 class="font-bold text-slate-900 group-hover:text-green-600 transition-colors text-lg">{{ cat.name }}</h3>
          </div>
        </a>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import BannerSlider from '@/components/BannerSlider.vue'
import { getBanners } from '@/services/api'

const slides = ref([])

const categories = [
  { id: 1, name: 'Bongs', image: 'https://images.unsplash.com/photo-1615397349754-2612e42a7fcb?q=80&w=800&auto=format&fit=crop' },
  { id: 2, name: 'Vaporizadores', image: 'https://images.unsplash.com/photo-1525950168793-7189e284bbcc?q=80&w=800&auto=format&fit=crop' },
  { id: 3, name: 'Sedas', image: 'https://images.unsplash.com/photo-1523906630133-f6934a1ab2b9?q=80&w=800&auto=format&fit=crop' },
  { id: 4, name: 'Acessórios', image: 'https://images.unsplash.com/photo-1593095948071-474c5cc2989e?q=80&w=800&auto=format&fit=crop' },
]

async function load() {
  slides.value = await getBanners()
}

onMounted(load)
</script>

<style scoped>
</style>


