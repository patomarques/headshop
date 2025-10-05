<template>
  <div class="min-h-screen flex flex-col">
    <header class="border-b border-slate-200 bg-white/80 backdrop-blur sticky top-0 z-40">
      <div class="container h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-green-600 text-white grid place-items-center font-extrabold">HS</div>
          <router-link to="/" class="text-lg font-semibold">Headshop</router-link>
        </div>
        <nav class="hidden md:flex items-center gap-6 text-sm">
          <router-link to="/" class="hover:text-green-700">Home</router-link>
          <router-link to="/produtos" class="hover:text-green-700">Produtos</router-link>
          <router-link to="/categorias" class="hover:text-green-700">Categorias</router-link>
        </nav>
      </div>
    </header>

    <main class="flex-1">
      <router-view />
    </main>

    <footer class="mt-16 border-t border-slate-200 py-8 text-sm">
      <div class="container flex items-center justify-between">
        <p>&copy; {{ year }} Headshop. Todos os direitos reservados.</p>
        <p class="text-slate-500">Feito com Vue 3 + Vite</p>
      </div>
    </footer>
  </div>
  
</template>

<script setup>
const year = new Date().getFullYear()
</script>

<style scoped>
</style>


