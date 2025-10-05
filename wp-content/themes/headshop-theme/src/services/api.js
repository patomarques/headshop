import axios from 'axios'

const api = axios.create({
  baseURL: '/',
  timeout: 15000,
})

async function tryEndpoints(paths, config) {
  let lastError
  for (const path of paths) {
    try {
      const res = await api.get(path, config)
      return res.data
    } catch (e) {
      lastError = e
    }
  }
  // eslint-disable-next-line no-console
  console.error('Store API request failed', lastError)
  return []
}

// WooCommerce Store API (prefer v1 paths)
export async function getProducts(params = {}) {
  const { page = 1, per_page = 12, category, search, orderby = 'date', order = 'desc' } = params
  const config = { params: { page, per_page, category, search, orderby, order } }
  return await tryEndpoints([
    '/wp-json/wc/store/v1/products',
    '/wp-json/wc/store/products',
    '/index.php?rest_route=/wc/store/v1/products',
  ], config)
}

export async function getCategories(params = {}) {
  const { page = 1, per_page = 12, include, parent } = params
  const config = { params: { page, per_page, include, parent } }
  return await tryEndpoints([
    '/wp-json/wc/store/v1/products/categories',
    '/wp-json/wc/store/products/categories',
    '/index.php?rest_route=/wc/store/v1/products/categories',
  ], config)
}

export async function getProduct(id) {
  const data = await tryEndpoints([
    `/wp-json/wc/store/v1/products/${id}`,
    `/wp-json/wc/store/products/${id}`,
    `/index.php?rest_route=/wc/store/v1/products/${id}`,
  ])
  return data
}

export async function getBanners() {
  return await tryEndpoints([
    '/wp-json/headshop/v1/banners',
    '/index.php?rest_route=/headshop/v1/banners',
  ])
}


