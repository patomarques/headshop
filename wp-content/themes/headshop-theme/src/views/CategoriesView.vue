<template>
  <section class="container py-10">
    <h1 class="text-3xl md:text-4xl font-bold mb-6">Categorias</h1>
    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <div v-for="n in 8" :key="n" class="rounded-2xl overflow-hidden bg-white shadow">
        <div class="aspect-[3/2] bg-slate-100 animate-pulse"></div>
        <div class="p-6">
          <div class="h-5 w-3/4 bg-slate-200 rounded"></div>
        </div>
      </div>
    </div>
    <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <a v-for="cat in categories" :key="cat.id" href="#" class="group block rounded-2xl overflow-hidden bg-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
        <div class="aspect-[3/2] bg-gray-100 bg-cover bg-center relative" :style="{ backgroundImage: `url(${cat.image?.src || placeholder})` }">
          <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
        </div>
        <div class="p-6">
          <h3 class="font-bold text-slate-900 group-hover:text-green-600 transition-colors text-lg">{{ cat.name }}</h3>
        </div>
      </a>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getCategories } from '@/services/api'

const loading = ref(true)
const categories = ref([])
const placeholder = 'https://via.placeholder.com/600x400?text=Categoria'

async function load() {
  loading.value = true
  const data = await getCategories({ per_page: 16 })
  categories.value = data
  loading.value = false
}

onMounted(load)
</script>

<style scoped>
</style>


