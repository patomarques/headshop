# Design: Deduplicação de Produtos + Atualização de Imagens

**Data:** 2026-06-16  
**Objetivo:** Limpar produtos duplicados no WooCommerce e atribuir imagens relevantes a cada produto único, para apresentação ao cliente.

---

## Contexto

- Loja WooCommerce em ambiente local (`headshop.local`)
- Prefixo de tabelas: `wdevp_`
- 23 produtos únicos, porém com muitas entradas duplicadas no banco (mesmo título, múltiplos IDs)
- Imagens atuais são fotos genéricas do Flickr sem relação com os produtos
- Objetivo é demo para o cliente — imagens finais serão trocadas pelo cliente posteriormente

---

## Parte 1 — Deduplicação de Produtos

### Estratégia

- Manter o **menor ID** por grupo de título
- Mover todos os demais para lixeira (`post_status = 'trash'`) via `wp_trash_post()`
- Mover também as `product_variation` dos duplicados para lixeira
- Mover "Reverse Withdrawal Payment" (ID 371) para lixeira — não é produto real

### Produtos a manter (23 produtos únicos)

| ID  | Título |
|-----|--------|
| 240 | Boné Rasta |
| 241 | Papel de Seda Colorido |
| 242 | Estátua Ganesha |
| 243 | Camiseta Estampa Canábica |
| 244 | Tapete de Meditação |
| 245 | Óleo Essencial de Capim-limão |
| 246 | Vela Aromática Lavanda |
| 247 | Incenso Nag Champa |
| 248 | Spray Energizante |
| 251 | Isqueiro Clipper |
| 254 | Caixa de Seda RAW |
| 255 | Meia Folha Verde |
| 256 | Seda King Size |
| 262 | Mandala de Madeira |
| 264 | Cinzeiro de Vidro |
| 271 | Porta Incenso Elefante |
| 277 | Japamala Rudraksha |
| 80  | Moletom canguru |
| 282 | Regata Jamaica Vibes |
| 283 | Dichavador Metálico |
| 641 | Hambúrguer Vegan de Grão-de-Bico |
| 642 | Nuggets Veganos Crocantes |
| 643 | Coxinha Vegana de Jaca |

### IDs a remover (trash)

- Boné Rasta: 287
- Caixa de Seda RAW: 258, 279
- Camiseta Estampa Canábica: 269, 270, 289 (+ variações: 32 cada)
- Estátua Ganesha: 249, 253, 268, 288
- Incenso Nag Champa: 260
- Isqueiro Clipper: 259, 267
- Japamala Rudraksha: 278, 281
- Mandala de Madeira: 285
- Meia Folha Verde: 265, 275
- Óleo Essencial de Capim-limão: 261, 272
- Papel de Seda Colorido: 273, 280
- Porta Incenso Elefante: 274
- Spray Energizante: 257, 286
- Tapete de Meditação: 250, 252, 266, 284
- Vela Aromática Lavanda: 263, 276
- Reverse Withdrawal Payment: 371

> **Nota:** 2 pedidos de teste referenciam IDs que serão removidos (289, 273, 287). Aceitável em ambiente de demo.

---

## Parte 2 — Download e Atribuição de Imagens

### Fonte de imagens

`https://loremflickr.com/800/800/{keywords}` — gratuito, sem API key, fotos CC do Flickr por palavras-chave. Retorna redirect com imagem relevante.

### Mapeamento produto → keywords

| ID  | Título | Keywords |
|-----|--------|----------|
| 240 | Boné Rasta | `rasta,hat` |
| 241 | Papel de Seda Colorido | `colored,rolling,paper` |
| 242 | Estátua Ganesha | `ganesha,statue` |
| 243 | Camiseta Estampa Canábica | `cannabis,tshirt` |
| 244 | Tapete de Meditação | `meditation,mat` |
| 245 | Óleo Essencial de Capim-limão | `lemongrass,essential,oil` |
| 246 | Vela Aromática Lavanda | `lavender,candle` |
| 247 | Incenso Nag Champa | `incense,sticks` |
| 248 | Spray Energizante | `spray,bottle,energy` |
| 251 | Isqueiro Clipper | `clipper,lighter` |
| 254 | Caixa de Seda RAW | `rolling,papers` |
| 255 | Meia Folha Verde | `leaf,socks` |
| 256 | Seda King Size | `rolling,paper,king` |
| 262 | Mandala de Madeira | `wooden,mandala` |
| 264 | Cinzeiro de Vidro | `glass,ashtray` |
| 271 | Porta Incenso Elefante | `elephant,incense` |
| 277 | Japamala Rudraksha | `rudraksha,mala` |
| 80  | Moletom canguru | `hoodie,sweatshirt` |
| 282 | Regata Jamaica Vibes | `jamaica,tank,top` |
| 283 | Dichavador Metálico | `herb,grinder` |
| 641 | Hambúrguer Vegan de Grão-de-Bico | `vegan,burger` |
| 642 | Nuggets Veganos Crocantes | `vegan,nuggets` |
| 643 | Coxinha Vegana de Jaca | `jackfruit,food` |

### Mecanismo de download

Script PHP CLI (`scripts/update-product-images.php`) que:
1. Faz bootstrap do WordPress (`wp-load.php`)
2. Itera sobre o mapeamento acima
3. Para cada produto, baixa a imagem com `curl` seguindo redirecionamentos (`-L`)
4. Registra na biblioteca de mídia via `media_sideload_image()`
5. Atribui como thumbnail via `set_post_thumbnail($product_id, $attachment_id)`

---

## Ordem de execução

1. Rodar script de deduplicação (PHP CLI)
2. Rodar script de imagens (PHP CLI)
3. Verificar no WooCommerce admin que os 23 produtos têm imagens corretas

---

## Restrições

- Ambiente local apenas — sem impacto em produção
- `loremflickr.com` requer conexão com a internet no servidor
- Imagens são temporárias — cliente vai substituir depois
