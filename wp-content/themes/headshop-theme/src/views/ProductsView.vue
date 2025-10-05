<template>
  <section class="container py-10">
    <h1 class="text-3xl md:text-4xl font-bold mb-6">Produtos</h1>
    <div class="flex items-center justify-between mb-6">
      <input v-model="search" type="search" placeholder="Buscar produtos" class="w-full max-w-xs rounded-md border-slate-300"/>
    </div>

    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-6">
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
          <h3 class="font-semibold text-slate-900 line-clamp-2 mb-2">{{ p.name }}</h3>
          <div class="font-bold text-green-700" v-html="p.price_html"></div>
        </div>
      </router-link>
    </div>

    <div class="mt-8 flex items-center justify-center gap-3" v-if="hasMore || page>1">
      <button @click="prevPage" :disabled="page===1" class="px-4 py-2 rounded-md border disabled:opacity-50">Anterior</button>
      <span>Página {{ page }}</span>
      <button @click="nextPage" :disabled="!hasMore" class="px-4 py-2 rounded-md border disabled:opacity-50">Próxima</button>
    </div>
  </section>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { getProducts } from '@/services/api'

const loading = ref(true)
const products = ref([])
const page = ref(1)
const perPage = 12
const hasMore = ref(false)
const search = ref('')

async function load() {
  loading.value = true
  const data = await getProducts({ page: page.value, per_page: perPage, search: search.value })
  products.value = data
  hasMore.value = data.length === perPage
  loading.value = false
}

function nextPage(){ if (hasMore.value) { page.value++; load() } }
function prevPage(){ if (page.value>1) { page.value--; load() } }

watch(search, () => { page.value = 1; load() })
onMounted(load)
</script>

<style scoped>
</style>


