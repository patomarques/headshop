<template>
  <section class="container py-10">
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-10">
      <div class="aspect-[3/4] bg-slate-100 rounded-2xl animate-pulse"></div>
      <div>
        <div class="h-9 w-2/3 bg-slate-200 rounded mb-4"></div>
        <div class="h-6 w-1/3 bg-slate-200 rounded mb-6"></div>
        <div class="space-y-3">
          <div class="h-4 bg-slate-200 rounded"></div>
          <div class="h-4 bg-slate-200 rounded"></div>
          <div class="h-4 bg-slate-200 rounded"></div>
        </div>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-10">
      <div class="aspect-[3/4] bg-slate-100 rounded-2xl overflow-hidden">
        <img v-if="product.images?.[0]?.src" :src="product.images[0].src" :alt="product.name" class="w-full h-full object-cover"/>
      </div>
      <div>
        <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ product.name }}</h1>
        <div class="mb-6" v-html="product.price_html"></div>
        <div class="prose max-w-none mb-6" v-html="product.description"></div>
        <div class="mt-8">
          <button class="px-6 py-3 rounded-xl bg-green-600 text-white hover:bg-green-700 transition-colors">Adicionar ao carrinho</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { getProduct } from '@/services/api'

const route = useRoute()
const loading = ref(true)
const product = ref({})

async function load() {
  loading.value = true
  product.value = await getProduct(route.params.id)
  loading.value = false
}

onMounted(load)
</script>

<style scoped>
</style>


