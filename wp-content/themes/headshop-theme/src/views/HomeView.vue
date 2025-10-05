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

    <div class="container py-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-3xl md:text-4xl font-bold">Destaques</h2>
          <p class="text-slate-600">Produtos mais recentes</p>
        </div>
        <router-link to="/produtos" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium shadow-lg hover:shadow-xl">Ver todos</router-link>
      </div>

      <div v-if="prodLoading" class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div v-for="n in 8" :key="n" class="rounded-2xl overflow-hidden bg-white shadow">
          <div class="aspect-[3/4] bg-slate-100 animate-pulse"></div>
          <div class="p-4">
            <div class="h-5 w-3/4 bg-slate-200 rounded mb-2"></div>
            <div class="h-4 w-1/3 bg-slate-200 rounded"></div>
          </div>
        </div>
      </div>

      <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <router-link :to="`/produto/${p.id}`" v-for="p in products" :key="p.id" class="rounded-2xl overflow-hidden bg-white shadow hover:shadow-lg transition">
          <div class="aspect-[3/4] bg-slate-100">
            <img v-if="p.images?.[0]?.src" :src="p.images[0].src" :alt="p.name" class="w-full h-full object-cover"/>
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-slate-900 mb-2">{{ p.name }}</h3>
            <div class="font-bold text-green-700" v-html="p.price_html"></div>
          </div>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import BannerSlider from '@/components/BannerSlider.vue'
import { getBanners, getProducts } from '@/services/api'

const slides = ref([])
const products = ref([])
const prodLoading = ref(true)

const categories = [
  { id: 1, name: 'Bongs', image: 'https://images.unsplash.com/photo-1615397349754-2612e42a7fcb?q=80&w=800&auto=format&fit=crop' },
  { id: 2, name: 'Vaporizadores', image: 'https://images.unsplash.com/photo-1525950168793-7189e284bbcc?q=80&w=800&auto=format&fit=crop' },
  { id: 3, name: 'Sedas', image: 'https://images.unsplash.com/photo-1523906630133-f6934a1ab2b9?q=80&w=800&auto=format&fit=crop' },
  { id: 4, name: 'Acessórios', image: 'https://images.unsplash.com/photo-1593095948071-474c5cc2989e?q=80&w=800&auto=format&fit=crop' },
]

async function load() {
  slides.value = await getBanners()
  products.value = await getProducts({ per_page: 8, orderby: 'date', order: 'desc' })
  prodLoading.value = false
}

onMounted(load)
</script>

<style scoped>
</style>


