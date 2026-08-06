# Deduplicação de Produtos + Imagens WooCommerce — Plano de Implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remover produtos duplicados do WooCommerce e atribuir imagens relevantes (via loremflickr.com) a cada um dos 23 produtos únicos.

**Architecture:** Dois scripts PHP CLI independentes que fazem bootstrap do WordPress e operam diretamente via funções WP/WooCommerce. Primeiro dedup, depois imagens.

**Tech Stack:** PHP 8.3 CLI, WordPress functions (`wp_trash_post`, `media_sideload_image`, `set_post_thumbnail`), loremflickr.com (imagens CC gratuitas por keyword).

---

## Spec de referência

`docs/superpowers/specs/2026-06-16-product-images-dedup-design.md`

---

## Estrutura de arquivos

| Arquivo | Ação | Responsabilidade |
|---------|------|-----------------|
| `scripts/dedup-products.php` | Criar | Mover duplicados e Reverse Withdrawal Payment para lixeira |
| `scripts/update-product-images.php` | Criar | Baixar imagem por keyword e atribuir como thumbnail |

---

## Task 1: Script de deduplicação de produtos

**Files:**
- Create: `scripts/dedup-products.php`

- [ ] **Step 1: Criar o script**

Crie o arquivo `scripts/dedup-products.php` com o conteúdo abaixo:

```php
<?php
/**
 * Dedup products: keeps the lowest ID per title, trashes the rest.
 * Also trashes product_variation children of trashed products.
 * Run: php scripts/dedup-products.php
 */

define('WP_USE_THEMES', false);
require_once __DIR__ . '/../wp-load.php';

$ids_to_keep = [80, 240, 241, 242, 243, 244, 245, 246, 247, 248, 251, 254, 255, 256, 262, 264, 271, 277, 282, 283, 641, 642, 643];

// Trash duplicate products and Reverse Withdrawal Payment (ID 371)
$products = get_posts([
    'post_type'      => 'product',
    'post_status'    => ['publish', 'draft', 'private'],
    'numberposts'    => -1,
    'fields'         => 'ids',
]);

foreach ($products as $id) {
    if (in_array($id, $ids_to_keep)) {
        continue;
    }
    $title = get_the_title($id);
    wp_trash_post($id);
    echo "Trashed product ID $id: $title\n";
}

// Trash variations whose parent was trashed
$variations = get_posts([
    'post_type'      => 'product_variation',
    'post_status'    => ['publish', 'draft', 'private'],
    'numberposts'    => -1,
    'fields'         => 'ids',
]);

foreach ($variations as $var_id) {
    $parent_id = wp_get_post_parent_id($var_id);
    if (!in_array($parent_id, $ids_to_keep)) {
        wp_trash_post($var_id);
        echo "  Trashed variation ID $var_id (parent $parent_id)\n";
    }
}

echo "\nDone. Products kept: " . implode(', ', $ids_to_keep) . "\n";
```

- [ ] **Step 2: Rodar o script**

```bash
cd /var/www/html/headshop && php scripts/dedup-products.php
```

Saída esperada: linhas `Trashed product ID ...` para cada duplicado, terminando com `Done.`

- [ ] **Step 3: Verificar no banco**

```bash
MYSQL_PWD=root mysql -uroot wdevp_headshop -e "
SELECT post_title, COUNT(*) as total
FROM wdevp_posts
WHERE post_type='product' AND post_status='publish'
GROUP BY post_title
ORDER BY post_title;"
```

Resultado esperado: cada título aparece exatamente **1 vez**.

- [ ] **Step 4: Confirmar que duplicados foram para lixeira**

```bash
MYSQL_PWD=root mysql -uroot wdevp_headshop -e "
SELECT COUNT(*) as trashed_products
FROM wdevp_posts
WHERE post_type='product' AND post_status='trash';"
```

Esperado: pelo menos **16** produtos na lixeira (soma de todos os duplicados + Reverse Withdrawal Payment).

- [ ] **Step 5: Commit**

```bash
git add scripts/dedup-products.php
git commit -m "feat: script de deduplicação de produtos WooCommerce"
```

---

## Task 2: Script de download e atribuição de imagens

**Files:**
- Create: `scripts/update-product-images.php`

- [ ] **Step 1: Criar o script**

Crie o arquivo `scripts/update-product-images.php` com o conteúdo abaixo:

```php
<?php
/**
 * Downloads a relevant image from loremflickr.com for each product
 * and sets it as the product thumbnail.
 * Run: php scripts/update-product-images.php
 */

define('WP_USE_THEMES', false);
require_once __DIR__ . '/../wp-load.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$products = [
    80  => 'hoodie,sweatshirt',
    240 => 'rasta,hat',
    241 => 'colored,rolling,paper',
    242 => 'ganesha,statue',
    243 => 'cannabis,tshirt',
    244 => 'meditation,mat',
    245 => 'lemongrass,essential,oil',
    246 => 'lavender,candle',
    247 => 'incense,sticks',
    248 => 'spray,bottle,energy',
    251 => 'clipper,lighter',
    254 => 'rolling,papers',
    255 => 'leaf,socks',
    256 => 'rolling,paper,king',
    262 => 'wooden,mandala',
    264 => 'glass,ashtray',
    271 => 'elephant,incense',
    277 => 'rudraksha,mala',
    282 => 'jamaica,tank,top',
    283 => 'herb,grinder',
    641 => 'vegan,burger',
    642 => 'vegan,nuggets',
    643 => 'jackfruit,food',
];

foreach ($products as $product_id => $keywords) {
    $title = get_the_title($product_id);
    if (!$title) {
        echo "SKIP: product $product_id not found\n";
        continue;
    }

    // loremflickr redirects to a CC-licensed Flickr photo matching the keywords
    $url = "https://loremflickr.com/800/800/$keywords";

    // Download to temp file following the redirect
    $tmp = download_url($url, 30);
    if (is_wp_error($tmp)) {
        echo "ERROR downloading for '$title': " . $tmp->get_error_message() . "\n";
        continue;
    }

    $file_array = [
        'name'     => sanitize_title($title) . '.jpg',
        'tmp_name' => $tmp,
    ];

    $attachment_id = media_handle_sideload($file_array, $product_id, $title);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        echo "ERROR sideloading for '$title': " . $attachment_id->get_error_message() . "\n";
        continue;
    }

    set_post_thumbnail($product_id, $attachment_id);
    echo "OK: '$title' (ID $product_id) → attachment $attachment_id\n";
}

echo "\nDone.\n";
```

- [ ] **Step 2: Rodar o script**

```bash
cd /var/www/html/headshop && php scripts/update-product-images.php
```

Saída esperada: 23 linhas `OK: 'Nome do Produto' (ID X) → attachment Y`, terminando com `Done.`

Se algum produto retornar `ERROR`, rodar novamente só para aquele produto (loremflickr pode ter timeout ocasional).

- [ ] **Step 3: Verificar no banco**

```bash
MYSQL_PWD=root mysql -uroot wdevp_headshop -e "
SELECT p.ID, p.post_title, pm.meta_value as thumbnail_id
FROM wdevp_posts p
JOIN wdevp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
WHERE p.post_type = 'product' AND p.post_status = 'publish'
ORDER BY p.post_title;"
```

Esperado: 23 linhas, cada produto com um `thumbnail_id` não-nulo.

- [ ] **Step 4: Confirmar imagens no disco**

```bash
ls /var/www/html/headshop/wp-content/uploads/$(date +%Y/%m)/ | grep -v -E "\-[0-9]+x[0-9]+" | wc -l
```

Esperado: pelo menos **23** arquivos novos (originals, sem contar os tamanhos gerados pelo WP).

- [ ] **Step 5: Commit**

```bash
git add scripts/update-product-images.php
git commit -m "feat: script de download e atribuição de imagens de produto"
```

---

## Task 3: Verificação final no admin

- [ ] **Step 1: Abrir a lista de produtos no WooCommerce**

Acessar: `http://headshop.local/wp-admin/edit.php?post_type=product`

Confirmar:
- Total de produtos publicados = 23
- Cada produto exibe uma imagem thumbnail na coluna de imagem

- [ ] **Step 2: Abrir a loja no frontend**

Acessar: `http://headshop.local/shop/`

Confirmar:
- Produtos exibem imagens relevantes (nenhum placeholder cinza)
- Sem duplicatas visíveis

- [ ] **Step 3: Commit final de verificação (opcional)**

Se ajustes forem necessários nos keywords após ver as imagens:
1. Editar `scripts/update-product-images.php` com keywords mais específicas
2. Rodar novamente só para o produto problemático (remover os outros do array `$products`)
3. Commitar a versão ajustada

---

## Resumo dos IDs mantidos

| ID  | Produto |
|-----|---------|
| 80  | Moletom canguru |
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
| 282 | Regata Jamaica Vibes |
| 283 | Dichavador Metálico |
| 641 | Hambúrguer Vegan de Grão-de-Bico |
| 642 | Nuggets Veganos Crocantes |
| 643 | Coxinha Vegana de Jaca |
