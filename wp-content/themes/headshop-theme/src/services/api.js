import axios from 'axios'

const api = axios.create({
  baseURL: '/', // usa proxy do Vite para /wp-json
  timeout: 15000,
})

// WooCommerce Store API helpers
// Docs: /wp-json/wc/store

export async function getProducts(params = {}) {
  const { page = 1, per_page = 12, category, search, orderby = 'date', order = 'desc' } = params
  const res = await api.get('/wp-json/wc/store/products', {
    params: { page, per_page, category, search, orderby, order }
  })
  return res.data
}

export async function getCategories(params = {}) {
  const { page = 1, per_page = 12, include, parent } = params
  const res = await api.get('/wp-json/wc/store/products/categories', {
    params: { page, per_page, include, parent }
  })
  return res.data
}

export async function getProduct(id) {
  const res = await api.get(`/wp-json/wc/store/products/${id}`)
  return res.data
}

export async function getBanners() {
  const res = await api.get('/wp-json/headshop/v1/banners')
  return res.data
}


