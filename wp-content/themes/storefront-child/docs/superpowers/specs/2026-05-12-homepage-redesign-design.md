---
name: homepage-redesign
description: Redesign completo da home inspirado no template Figma SHOP.CO, adaptado para loja de eletrônicos
metadata:
  type: project
---

# Homepage Redesign — Inspirado no SHOP.CO (Figma)

**Referência:** https://www.figma.com/design/MckrmhRYmBbI6O3JFst3Tn/
**Abordagem:** Opção C — reaproveitamento da lógica PHP + reescrita HTML/SCSS + fontes display do Google Fonts

---

## 1. Sistema visual

### Tipografia
| Papel | Fonte | Fonte anterior |
|---|---|---|
| Display / títulos de seção | **Bebas Neue** (Google Fonts) | Segoe UI bold |
| Corpo | **Inter** (Google Fonts) | Segoe UI |

Carregadas via `wp_enqueue_style` em `functions.php` com um único link preconnect.

### Variáveis SCSS novas (acrescentar em `_variables.scss`)
```scss
$font-family-display: 'Bebas Neue', sans-serif;
$font-family-body:    'Inter', sans-serif;
$color-cta:           #000000;   // botões primários do novo design
$color-cta-text:      #ffffff;
$radius-card:         20px;      // cartões do estilo Figma
$spacing-section:     5rem;      // espaçamento entre seções
```

`$color-primary` (#0d6efd) mantido exclusivamente para elementos WooCommerce (badges, links de preço, etc.).

---

## 2. Seções da homepage — ordem e mapeamento

| # | Seção | Arquivo PHP | Status |
|---|---|---|---|
| 0 | Barra de aviso | `header.php` | novo bloco acima de `#masthead` |
| 1 | Hero / Carousel | `header.php` (`#banner-section`) | reestilizar |
| 2 | Marcas (marquee) | `template-parts/home-brands.php` | reescrever HTML |
| 3 | NOVIDADES | `template-parts/home-products.php` | reescrever HTML |
| 4 | EXPLORE POR CATEGORIA | `template-parts/home-categories.php` | reescrever HTML |
| 5 | MAIS VENDIDOS | `template-parts/home-promotions.php` | reescrever HTML |
| 6 | Newsletter | `template-parts/home-newsletter.php` | novo |
| 7 | Contato | `template-parts/home-contact.php` | reestilizar |

`home-promo-banner.php` removido de `homepage.php` (substituído pelo hero e newsletter).

---

## 3. Especificação por seção

### 3.0 Barra de aviso
- Posição: primeiro elemento do `<body>`, acima do `#masthead`
- HTML: `<div class="announcement-bar">` com texto + link
- Estilo: fundo preto, texto branco, `font-size: 0.8rem`, centrado, `padding: 9px`
- Conteúdo: "Frete grátis para pedidos acima de R$150 &nbsp;·&nbsp; **Compre agora →**"
- Comportamento: botão ✕ à direita que oculta via `localStorage` (JS inline no `header.php`)
- Ajuste no header: adicionar `padding-top` ao `#content` para compensar a barra + header fixo

### 3.1 Hero / Carousel
- Mantém o Bootstrap carousel e o CPT `banner_home` existentes
- Adiciona overlay escuro semi-transparente (`rgba(0,0,0,0.45)`) sobre cada slide
- Sobre o overlay, centralizado verticalmente:
  - Título em Bebas Neue, ~4.5rem, branco, maiúsculas (editável via `_banner_text` do CPT)
  - Fallback do título quando `_banner_text` vazio: nome da loja
  - Botão CTA "Explorar produtos" — preto sólido, branco texto, `border-radius: 62px`
- Stats bar abaixo do carousel (fora do overlay, dentro de `#banner-section`):
  - 3 colunas: `150+ Produtos`, `500+ Clientes`, `2.000+ Pedidos`
  - Separadas por linha vertical `1px solid $color-border`
  - Número em Bebas Neue, label em Inter regular menor
  - Fundo branco, `padding: 1.5rem 0`

### 3.2 Marcas (Marquee)
- HTML: faixa com duas `<div class="marquee-track">` idênticas (loop infinito via CSS)
- Cada track: `display: flex; gap: 3rem; white-space: nowrap`
- Animação: `@keyframes marquee { from { transform: translateX(0) } to { transform: translateX(-50%) } }`
- Conteúdo: CPT `brand` existente; fallback estático se vazio:
  `Arduino · Raspberry Pi · Espressif · Minipa · Dremel`
- Se a marca tem logo: exibe `<img>` com `height: 32px; filter: grayscale(1); opacity: 0.6`
- Se não tem logo: exibe `<span class="brand-name">` em Bebas Neue, `font-size: 1.6rem`
- Fundo: `$color-dark` (preto), itens brancos — igual ao Figma

### 3.3 NOVIDADES
- Query: 4 produtos mais recentes (`orderby: date DESC, posts_per_page: 4`)
- Cabeçalho da seção: `<h2>NOVIDADES</h2>` (Bebas Neue) + link "Ver todos →" à direita (flex space-between)
- Grid: `grid-template-columns: repeat(4, 1fr)` → `repeat(2, 1fr)` em md → `1fr` em sm
- Card (`.product-card`):
  - `border-radius: $radius-card` (20px), sem border, `box-shadow: 0 2px 12px rgba(0,0,0,0.06)`
  - Imagem: `aspect-ratio: 1/1`, `object-fit: contain`, fundo `$color-light`
  - Badge de novo: `<span class="badge-new">NOVO</span>` — preto, canto superior esquerdo
  - Nome do produto: Inter medium, `font-size: 1rem`
  - Estrelas: renderizar com WC `wc_get_rating_html()` se disponível, sentar em ★★★★☆ estático caso vazio
  - Preço: Inter bold, dark; preço riscado em muted se em promoção

### 3.4 EXPLORE POR CATEGORIA
- Cabeçalho: `<h2>EXPLORE POR CATEGORIA</h2>` + subtítulo menor opcional
- Grid: 3 colunas desktop → 2 mobile
- Card (`.category-card-v2`):
  - Fundo: `$color-light` (#f8f9fa)
  - `border-radius: $radius-card`
  - Hover: fundo `$color-dark`, texto branco, `transform: translateY(-4px)`
  - Ícone/imagem no centro, nome em Bebas Neue maiúsculas abaixo
  - Contagem de produtos: `opacity: 0.6; font-size: 0.8rem`

### 3.5 MAIS VENDIDOS
- Query: produtos em promoção (mesma lógica de `home-promotions.php`) — 4 itens
- Cabeçalho: `<h2>MAIS VENDIDOS</h2>` + link "Ver todos →"
- Mesmo layout de card que NOVIDADES; badge `.badge-sale "-XX%"` em vermelho ao invés de preto

### 3.6 Newsletter
- Arquivo: `template-parts/home-newsletter.php` (novo)
- Wrapper: `<section class="home-newsletter">` com `background: #000; color: #fff`
- Layout: flex row (desktop) → coluna (mobile)
  - Esquerda: título "FIQUE POR DENTRO DAS NOSSAS OFERTAS" em Bebas Neue ~2.5rem
  - Direita: `<form>` com `<input type="email">` + `<button>Assinar</button>`
- Input: fundo branco, `border-radius: 62px`, padding generoso
- Botão: branco sólido, texto preto, `border-radius: 62px`
- Action do form: redireciona para `/minha-conta/` (URL WC padrão) ao submeter — implementação mínima, apenas UI

### 3.7 Contato
- Mantém estrutura e dados do `home-contact.php`
- Atualiza apenas: `font-family: $font-family-body`, label em `letter-spacing` menor, botões com `border-radius: 62px`

---

## 4. Fontes Google — carregamento

Em `functions.php`, dentro do hook `wp_enqueue_scripts`:

```php
wp_enqueue_style(
    'google-fonts-display',
    'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap',
    [],
    null
);
```

Adicionar preconnect via `wp_head`:
```php
add_action('wp_head', function() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}, 1);
```

---

## 5. Arquivos afetados

| Arquivo | Operação |
|---|---|
| `assets/scss/abstracts/_variables.scss` | Adicionar vars de tipografia e cor |
| `assets/scss/base/_typography.scss` | Aplicar `$font-family-body` ao `body` |
| `assets/scss/layout/_header.scss` | Estilos da `announcement-bar` + hero overlay + stats bar |
| `assets/scss/pages/_home.scss` | Reescrever todos os estilos das seções |
| `header.php` | Adicionar announcement bar; reescrever markup do hero |
| `homepage.php` | Reordenar template parts; remover `home-promo-banner`; adicionar `home-newsletter` |
| `template-parts/home-brands.php` | Reescrever para marquee |
| `template-parts/home-products.php` | Reescrever cards |
| `template-parts/home-categories.php` | Reescrever cards |
| `template-parts/home-promotions.php` | Reescrever cards |
| `template-parts/home-newsletter.php` | Criar novo |
| `functions.php` | Enqueue Google Fonts + preconnect |

---

## 6. Fora de escopo

- Páginas de produto, categoria, carrinho e checkout (somente home)
- Integração real de newsletter (apenas UI)
- Animações JavaScript complexas (marquee é CSS puro)
- Header e footer (mantidos com ajuste mínimo de tipografia)
