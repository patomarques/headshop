# Homepage Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reimplementar a homepage no estilo do template Figma SHOP.CO, adaptado para loja de eletrônicos, reaproveitando a lógica PHP existente com novo HTML/SCSS e fontes Google Fonts.

**Architecture:** Cada seção da home vive em um `template-parts/home-*.php` independente. Os estilos ficam em `assets/scss/pages/_home.scss` (seções) e `assets/scss/layout/_header.scss` (header + announcement bar). A lógica PHP WooCommerce existente é mantida; apenas o markup HTML e os estilos são reescritos.

**Tech Stack:** WordPress 6.x · WooCommerce 9.x · Bootstrap 5.3 · Dart Sass (`npm run build`) · Bebas Neue + Inter (Google Fonts) · PHP 8.2

---

## Arquivos afetados

| Arquivo | Operação |
|---|---|
| `assets/scss/abstracts/_variables.scss` | Adicionar variáveis de display font, CTA color, card radius |
| `assets/scss/base/_typography.scss` | Trocar `$font-family-base` → `$font-family-body` no `body` |
| `assets/scss/layout/_header.scss` | Adicionar estilos da `.announcement-bar` |
| `assets/scss/pages/_home.scss` | Reescrever completamente (limpar e reconstruir por tarefa) |
| `functions.php` | Enqueue Google Fonts + preconnect; remover inline style de promo-banner |
| `header.php` | Adicionar announcement bar + reescrever markup do hero/carousel |
| `homepage.php` | Reordenar template parts; remover `home-promo-banner`; adicionar `home-newsletter` |
| `template-parts/home-brands.php` | Reescrever para marquee infinito |
| `template-parts/home-products.php` | Reescrever cards (seção NOVIDADES) |
| `template-parts/home-categories.php` | Reescrever cards (seção EXPLORE POR CATEGORIA) |
| `template-parts/home-promotions.php` | Reescrever cards (seção MAIS VENDIDOS) |
| `template-parts/home-newsletter.php` | Criar novo |

---

## Task 1: Design system — variáveis, tipografia e Google Fonts

**Files:**
- Modify: `assets/scss/abstracts/_variables.scss`
- Modify: `assets/scss/base/_typography.scss`
- Modify: `assets/scss/pages/_home.scss`
- Modify: `functions.php`

- [ ] **Step 1.1: Adicionar variáveis ao `_variables.scss`**

Acrescentar ao final do arquivo `assets/scss/abstracts/_variables.scss`:

```scss
// Design system — homepage redesign
$font-family-display: 'Bebas Neue', sans-serif;
$font-family-body:    'Inter', sans-serif;
$color-cta:           #000000;
$color-cta-text:      #ffffff;
$radius-card:         20px;
$spacing-section:     5rem;
```

- [ ] **Step 1.2: Atualizar fonte do `body` em `_typography.scss`**

Em `assets/scss/base/_typography.scss`, trocar a linha `font-family: $font-family-base;` por `font-family: $font-family-body;`:

```scss
@use 'sass:color';
@use '../abstracts/variables' as *;

body {
  font-family: $font-family-body;
  font-size: $font-size-base;
  font-weight: $font-weight-normal;
  line-height: $line-height-base;
  color: $color-text;
}

h1, h2, h3, h4, h5, h6 {
  font-weight: $font-weight-bold;
  line-height: 1.3;
  color: $color-dark;
}

a {
  color: $color-primary;
  text-decoration: none;
  transition: $transition-base;

  &:hover {
    color: color.adjust($color-primary, $lightness: -10%);
  }
}

p {
  margin-bottom: $spacing-md;
}
```

- [ ] **Step 1.3: Limpar `_home.scss` — apenas imports**

Substituir TODO o conteúdo de `assets/scss/pages/_home.scss` por:

```scss
@use 'sass:color';
@use '../abstracts/variables' as *;
@use '../abstracts/mixins' as *;

// Shared section header (used by products, categories, promotions)
.section-header {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: $spacing-lg;
  border-bottom: 1px solid $color-border;
  padding-bottom: $spacing-md;
}

.section-title-display {
  font-family: $font-family-display;
  font-size: 2.5rem;
  line-height: 1;
  color: $color-dark;
  margin: 0;
}

.section-link-all {
  font-size: $font-size-sm;
  font-weight: 600;
  color: $color-dark;
  text-decoration: underline;
  text-underline-offset: 3px;

  &:hover { color: $color-muted; }
}
```

- [ ] **Step 1.4: Enqueue Google Fonts em `functions.php`**

Adicionar preconnect + enqueue. Em `functions.php`, logo após a abertura `<?php`:

```php
add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);
```

Dentro do `add_action('wp_enqueue_scripts', function () {`, após a linha do `bootstrap-cdn` style, adicionar:

```php
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap', [], null);
```

Remover o bloco `if (is_front_page())` que adiciona o inline style do `promo_banner_image` (não será mais usado):

```php
// REMOVER este bloco:
    if (is_front_page()) {
        $pb_image = get_theme_mod('promo_banner_image', '');
        if ($pb_image) {
            wp_add_inline_style('storefront-child-main', '.home-promo-banner{background-image:url(' . esc_url($pb_image) . ')}');
        }
    }
```

- [ ] **Step 1.5: Compilar e verificar**

```bash
cd /var/www/html/eletronicos/wp-content/themes/storefront-child && npm run build
```

Esperado: sem erros de compilação. A fonte Inter deve estar carregada na página.

- [ ] **Step 1.6: Commit**

```bash
git add assets/scss/abstracts/_variables.scss assets/scss/base/_typography.scss assets/scss/pages/_home.scss functions.php
git commit -m "feat: add Bebas Neue and Inter fonts, update design system vars"
```

---

## Task 2: Announcement bar

**Files:**
- Modify: `header.php`
- Modify: `assets/scss/layout/_header.scss`

- [ ] **Step 2.1: Adicionar HTML da barra de aviso em `header.php`**

Em `header.php`, ANTES da linha `<header id="masthead" ...>`, inserir:

```php
<div class="announcement-bar" id="announcement-bar">
  <span>Frete grátis para pedidos acima de R$150 &nbsp;·&nbsp; <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Compre agora →</a></span>
  <button class="announcement-close" id="announcement-close" aria-label="Fechar">✕</button>
</div>
<script>
(function(){
  if (localStorage.getItem('ann_closed') === '1') {
    document.getElementById('announcement-bar').style.display = 'none';
  }
  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('announcement-close');
    if (btn) btn.addEventListener('click', function () {
      document.getElementById('announcement-bar').style.display = 'none';
      localStorage.setItem('ann_closed', '1');
    });
  });
})();
</script>
```

- [ ] **Step 2.2: Adicionar estilos em `_header.scss`**

Acrescentar ao final de `assets/scss/layout/_header.scss`:

```scss
// ── Announcement bar ─────────────────────────────────────────────────────────
.announcement-bar {
  background: $color-dark;
  color: $color-white;
  text-align: center;
  font-size: 0.8rem;
  padding: 9px $spacing-md;
  position: relative;
  font-family: $font-family-body;

  a {
    color: $color-white;
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 2px;

    &:hover { opacity: 0.8; }
  }

  .announcement-close {
    position: absolute;
    right: $spacing-md;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: $color-white;
    cursor: pointer;
    font-size: 1rem;
    opacity: 0.7;
    padding: 0;
    line-height: 1;

    &:hover { opacity: 1; }
  }
}
```

- [ ] **Step 2.3: Compilar e verificar**

```bash
npm run build
```

Abrir a homepage no browser. Esperado: faixa preta no topo com o texto de frete grátis e botão ✕ funcional (some e não volta após reload ao clicar).

- [ ] **Step 2.4: Commit**

```bash
git add header.php assets/scss/layout/_header.scss
git commit -m "feat: add announcement bar with localStorage dismiss"
```

---

## Task 3: Hero section — overlay, CTA e stats bar

**Files:**
- Modify: `header.php` (seção `#banner-section`)
- Modify: `assets/scss/pages/_home.scss`

- [ ] **Step 3.1: Reescrever o markup do hero em `header.php`**

Substituir o bloco `<?php if (is_front_page()) :` até `<?php else : ?>` (aproximadamente linhas 90–155) pelo seguinte:

```php
  <?php if (is_front_page()) :
    $banner_query = new WP_Query([
      'post_type'      => 'banner_home',
      'posts_per_page' => 5,
      'orderby'        => 'menu_order date',
      'order'          => 'ASC',
    ]);
    $banners = $banner_query->have_posts() ? $banner_query->posts : [];
    if (empty($banners)) {
      $banners = [
        ['url' => 'https://via.placeholder.com/1920x600/111111/ffffff?text=Banner', 'alt' => 'Banner', 'link' => '', 'text' => ''],
      ];
    }
  ?>
  <section id="banner-section">
    <div id="banner-carousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <?php for ($j = 0; $j < count($banners); $j++) : ?>
          <button type="button" data-bs-target="#banner-carousel" data-bs-slide-to="<?php echo $j; ?>"
            <?php if ($j === 0) echo 'class="active" aria-current="true"'; ?>
            aria-label="Slide <?php echo $j + 1; ?>"></button>
        <?php endfor; ?>
      </div>
      <div class="carousel-inner">
        <?php $i = 0; foreach ($banners as $banner) :
          if (isset($banner->ID)) {
            $img_url  = get_the_post_thumbnail_url($banner->ID, 'full');
            $img_alt  = esc_attr(get_the_title($banner->ID));
            $img_link = get_post_meta($banner->ID, '_banner_link', true);
            $img_text = get_post_meta($banner->ID, '_banner_text', true);
          } else {
            $img_url  = $banner['url'];
            $img_alt  = $banner['alt'];
            $img_link = $banner['link'];
            $img_text = $banner['text'];
          }
          $hero_title = $img_text ?: 'OS MELHORES COMPONENTES ELETRÔNICOS';
        ?>
        <div class="carousel-item<?php if ($i++ === 0) echo ' active'; ?>">
          <?php if ($img_link) : ?><a href="<?php echo esc_url($img_link); ?>"><?php endif; ?>
            <img src="<?php echo esc_url($img_url); ?>" class="d-block w-100" alt="<?php echo $img_alt; ?>">
          <?php if ($img_link) : ?></a><?php endif; ?>
          <div class="hero-overlay">
            <div class="col-full">
              <h1 class="hero-title"><?php echo esc_html($hero_title); ?></h1>
              <p class="hero-subtitle">Arduino, ESP32, Raspberry Pi e muito mais</p>
              <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="hero-cta">Explorar produtos</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#banner-carousel" data-bs-slide="prev">
        <span class="carousel-arrow-icon" aria-hidden="true">&#x2039;</span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#banner-carousel" data-bs-slide="next">
        <span class="carousel-arrow-icon" aria-hidden="true">&#x203A;</span>
        <span class="visually-hidden">Próximo</span>
      </button>
    </div>

    <div class="hero-stats">
      <div class="col-full">
        <div class="hero-stats-row">
          <div class="hero-stat">
            <span class="stat-num">150+</span>
            <span class="stat-label">Produtos</span>
          </div>
          <div class="hero-stat-divider"></div>
          <div class="hero-stat">
            <span class="stat-num">500+</span>
            <span class="stat-label">Clientes</span>
          </div>
          <div class="hero-stat-divider"></div>
          <div class="hero-stat">
            <span class="stat-num">2.000+</span>
            <span class="stat-label">Pedidos</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php else : ?>
    <div class="col-full">
      <?php do_action('storefront_content_top'); ?>
  <?php endif; ?>
```

- [ ] **Step 3.2: Adicionar estilos do hero em `_home.scss`**

Acrescentar ao final de `assets/scss/pages/_home.scss`:

```scss
// ─── Hero / Banner ────────────────────────────────────────────────────────────
#banner-section {
  .carousel-item {
    position: relative;

    img {
      height: 70vh;
      width: 100%;
      object-fit: cover;
      object-position: center;

      @include respond-to(md) { height: 50vh; }
    }
  }

  .hero-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.48);
    display: flex;
    align-items: center;
    z-index: 2;

    .col-full { width: 100%; }
  }

  .hero-title {
    font-family: $font-family-display;
    font-size: clamp(2.5rem, 6vw, 5rem);
    color: $color-white;
    line-height: 1;
    text-transform: uppercase;
    margin-bottom: $spacing-md;
    max-width: 700px;
  }

  .hero-subtitle {
    color: rgba(255, 255, 255, 0.85);
    font-size: $font-size-lg;
    margin-bottom: $spacing-lg;
    max-width: 500px;
  }

  .hero-cta {
    display: inline-block;
    background: $color-cta;
    color: $color-cta-text;
    padding: 0.9rem 2.5rem;
    border-radius: 62px;
    font-weight: 600;
    font-size: $font-size-base;
    transition: opacity 0.2s ease;
    border: 2px solid transparent;

    &:hover {
      opacity: 0.85;
      color: $color-cta-text;
    }
  }

  .hero-stats {
    background: $color-white;
    border-top: 1px solid $color-border;
  }

  .hero-stats-row {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: $spacing-lg 0;
  }

  .hero-stat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-align: center;
  }

  .hero-stat-divider {
    width: 1px;
    height: 52px;
    background: $color-border;
    flex-shrink: 0;
  }

  .stat-num {
    font-family: $font-family-display;
    font-size: 2.5rem;
    line-height: 1;
    color: $color-dark;
  }

  .stat-label {
    font-size: $font-size-sm;
    color: $color-muted;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .carousel-control-prev,
  .carousel-control-next {
    width: auto;
    padding: 0;
    background: none !important;
    box-shadow: none !important;
    opacity: 1;
    align-items: center;
    z-index: 10;
  }

  .carousel-control-prev { left: 0; }
  .carousel-control-next { right: 0; }

  .carousel-arrow-icon {
    @include flex-center;
    font-size: 5rem;
    font-weight: $font-weight-bold;
    color: $color-white;
    text-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
    line-height: 1;
    width: auto;
    height: auto;
    background: none;
    border: none;
    box-shadow: none;
    padding: 0 $spacing-xs;
  }
}
```

- [ ] **Step 3.3: Compilar e verificar**

```bash
npm run build
```

Abrir a homepage. Esperado: imagem do banner com overlay escuro, título grande em Bebas Neue, subtítulo, botão preto arredondado "Explorar produtos", e barra de stats branca abaixo com os 3 números.

- [ ] **Step 3.4: Commit**

```bash
git add header.php assets/scss/pages/_home.scss
git commit -m "feat: add hero overlay, CTA button and stats bar"
```

---

## Task 4: Brand marquee

**Files:**
- Modify: `template-parts/home-brands.php`
- Modify: `assets/scss/pages/_home.scss`

- [ ] **Step 4.1: Reescrever `home-brands.php`**

Substituir TODO o conteúdo de `template-parts/home-brands.php` por:

```php
<?php if ( ! defined( 'ABSPATH' ) ) exit;

$brands = get_posts( [
	'post_type'      => 'brand',
	'posts_per_page' => 20,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
] );

$fallback = [ 'Arduino', 'Raspberry Pi', 'Espressif', 'Minipa', 'Dremel' ];
$items    = ! empty( $brands ) ? $brands : null;
?>
<section class="home-brands">
	<div class="brands-marquee-wrapper">
		<div class="brands-marquee-track">
			<?php
			$render_items = function( $source ) use ( $items, $fallback ) {
				if ( $items ) {
					foreach ( $items as $brand ) {
						$logo = get_the_post_thumbnail_url( $brand->ID, 'medium' );
						echo '<div class="brand-item">';
						if ( $logo ) {
							echo '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $brand->post_title ) . '">';
						} else {
							echo '<span class="brand-name-text">' . esc_html( $brand->post_title ) . '</span>';
						}
						echo '</div>';
					}
				} else {
					foreach ( $fallback as $name ) {
						echo '<div class="brand-item"><span class="brand-name-text">' . esc_html( $name ) . '</span></div>';
					}
				}
			};
			$render_items( 'a' );
			$render_items( 'b' ); // duplicate for seamless loop
			?>
		</div>
	</div>
</section>
```

- [ ] **Step 4.2: Adicionar estilos do marquee em `_home.scss`**

Acrescentar ao final de `assets/scss/pages/_home.scss`:

```scss
// ─── Brand marquee ────────────────────────────────────────────────────────────
.home-brands {
  background: $color-dark;
  overflow: hidden;
  padding: $spacing-lg 0;

  .brands-marquee-wrapper {
    overflow: hidden;
    mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%);
    -webkit-mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%);
  }

  .brands-marquee-track {
    display: flex;
    gap: 3rem;
    width: max-content;
    animation: marquee-scroll 22s linear infinite;

    &:hover { animation-play-state: paused; }
  }

  .brand-item {
    display: flex;
    align-items: center;
    flex-shrink: 0;

    img {
      height: 32px;
      width: auto;
      filter: brightness(0) invert(1);
      opacity: 0.65;
      transition: opacity 0.2s ease;

      &:hover { opacity: 1; }
    }
  }

  .brand-name-text {
    font-family: $font-family-display;
    font-size: 1.6rem;
    color: rgba(255, 255, 255, 0.6);
    white-space: nowrap;
    letter-spacing: 0.05em;
    transition: color 0.2s ease;

    &:hover { color: $color-white; }
  }
}

@keyframes marquee-scroll {
  from { transform: translateX(0); }
  to   { transform: translateX(-50%); }
}
```

- [ ] **Step 4.3: Compilar e verificar**

```bash
npm run build
```

Abrir a homepage. Esperado: faixa preta abaixo do hero com os nomes das marcas (ou logos) rolando continuamente da direita para a esquerda. Pausar ao passar o mouse.

- [ ] **Step 4.4: Commit**

```bash
git add template-parts/home-brands.php assets/scss/pages/_home.scss
git commit -m "feat: replace brands grid with animated marquee"
```

---

## Task 5: NOVIDADES — cards de produto

**Files:**
- Modify: `template-parts/home-products.php`
- Modify: `assets/scss/pages/_home.scss`

- [ ] **Step 5.1: Reescrever `home-products.php`**

Substituir TODO o conteúdo de `template-parts/home-products.php` por:

```php
<?php if ( ! defined( 'ABSPATH' ) ) exit;

$products_query = new WP_Query( [
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 4,
	'orderby'        => 'date',
	'order'          => 'DESC',
] );

if ( ! $products_query->have_posts() ) return;

$shop_url = wc_get_page_permalink( 'shop' );
?>
<section class="home-products">
	<div class="col-full">
		<div class="section-header">
			<h2 class="section-title-display">NOVIDADES</h2>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="section-link-all">Ver todos →</a>
		</div>
		<div class="product-cards-grid">
			<?php while ( $products_query->have_posts() ) : $products_query->the_post();
				$product = wc_get_product( get_the_ID() );
				$thumb   = get_the_post_thumbnail_url( get_the_ID(), 'medium' )
				           ?: 'https://via.placeholder.com/300x300/f8f9fa/999?text=Produto';
			?>
			<a href="<?php the_permalink(); ?>" class="product-card">
				<div class="product-card-image">
					<span class="badge-new">NOVO</span>
					<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>">
				</div>
				<div class="product-card-body">
					<div class="product-card-rating"><?php echo wc_get_rating_html( $product->get_average_rating() ); ?></div>
					<h3 class="product-card-name"><?php the_title(); ?></h3>
					<div class="product-card-price"><?php echo $product->get_price_html(); ?></div>
				</div>
			</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
```

- [ ] **Step 5.2: Adicionar estilos dos cards em `_home.scss`**

Acrescentar ao final de `assets/scss/pages/_home.scss`:

```scss
// ─── Product cards (shared by NOVIDADES and MAIS VENDIDOS) ────────────────────
.home-products,
.home-promotions {
  padding: $spacing-section 0;
}

.product-cards-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: $spacing-lg;

  @include respond-to(lg) { grid-template-columns: repeat(2, 1fr); }
  @include respond-to(sm) { grid-template-columns: 1fr; }
}

.product-card {
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: $color-dark;
  border-radius: $radius-card;
  overflow: hidden;
  background: $color-white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.2s ease, transform 0.2s ease;

  &:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    transform: translateY(-4px);
    color: $color-dark;
  }

  .product-card-image {
    position: relative;
    background: $color-light;
    aspect-ratio: 1 / 1;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      padding: $spacing-md;
      transition: transform 0.3s ease;
    }

    &:hover img { transform: scale(1.04); }
  }

  .badge-new,
  .badge-sale {
    position: absolute;
    top: $spacing-sm;
    left: $spacing-sm;
    padding: 4px 12px;
    border-radius: 62px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    z-index: 1;
  }

  .badge-new  { background: $color-dark;   color: $color-white; }
  .badge-sale { background: $color-danger; color: $color-white; }

  .product-card-body {
    padding: $spacing-md;
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
  }

  .product-card-rating .star-rating { font-size: 0.8rem; }

  .product-card-name {
    font-size: $font-size-base;
    font-weight: 600;
    margin: 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .product-card-price {
    font-weight: 700;
    margin-top: auto;
    padding-top: $spacing-xs;

    .woocommerce-Price-amount { color: $color-dark; }
    del .woocommerce-Price-amount { color: $color-muted; font-weight: 400; font-size: 0.85em; }
  }
}
```

- [ ] **Step 5.3: Compilar e verificar**

```bash
npm run build
```

Abrir a homepage. Esperado: seção "NOVIDADES" com 4 cards em grid, imagem quadrada com fundo cinza claro, badge "NOVO" preto, nome do produto, estrelas (se houver avaliações) e preço. Hover levanta o card.

- [ ] **Step 5.4: Commit**

```bash
git add template-parts/home-products.php assets/scss/pages/_home.scss
git commit -m "feat: redesign product cards for NOVIDADES section"
```

---

## Task 6: EXPLORE POR CATEGORIA

**Files:**
- Modify: `template-parts/home-categories.php`
- Modify: `assets/scss/pages/_home.scss`

- [ ] **Step 6.1: Reescrever `home-categories.php`**

Substituir TODO o conteúdo de `template-parts/home-categories.php` por:

```php
<?php if ( ! defined( 'ABSPATH' ) ) exit;

$top_cats = get_terms( [
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'orderby'    => 'count',
	'order'      => 'DESC',
	'number'     => 6,
	'exclude'    => get_option( 'default_product_cat' ),
] );

if ( empty( $top_cats ) || is_wp_error( $top_cats ) ) return;

$shop_url = wc_get_page_permalink( 'shop' );
?>
<section class="home-categories">
	<div class="col-full">
		<div class="section-header">
			<h2 class="section-title-display">EXPLORE POR CATEGORIA</h2>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="section-link-all">Ver todas →</a>
		</div>
		<div class="category-cards-grid">
			<?php foreach ( $top_cats as $cat ) :
				$thumb_id  = get_term_meta( $cat->term_id, 'thumbnail_id', true );
				$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
				$cat_url   = get_term_link( $cat );
			?>
			<a href="<?php echo esc_url( $cat_url ); ?>" class="category-card-v2">
				<div class="category-card-inner">
					<?php if ( $thumb_url ) : ?>
						<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>" class="category-card-img">
					<?php else : ?>
						<span class="category-card-icon"><?php echo eletronicos_category_icon( $cat->slug ); ?></span>
					<?php endif; ?>
					<span class="category-card-name"><?php echo esc_html( $cat->name ); ?></span>
					<span class="category-card-count"><?php echo (int) $cat->count; ?> produtos</span>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
```

- [ ] **Step 6.2: Adicionar estilos das categorias em `_home.scss`**

Acrescentar ao final de `assets/scss/pages/_home.scss`:

```scss
// ─── Categories ───────────────────────────────────────────────────────────────
.home-categories {
  padding: $spacing-section 0;
  background: $color-light;
}

.category-cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: $spacing-lg;

  @include respond-to(md) { grid-template-columns: repeat(2, 1fr); }
  @include respond-to(sm) { grid-template-columns: 1fr; }
}

.category-card-v2 {
  display: block;
  text-decoration: none;
  border-radius: $radius-card;
  overflow: hidden;
  transition: box-shadow 0.2s ease, transform 0.2s ease;

  &:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);

    .category-card-inner { background: $color-dark; }
    .category-card-name  { color: $color-white; }
    .category-card-count { color: rgba(255, 255, 255, 0.6); }
  }

  .category-card-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: $spacing-sm;
    padding: $spacing-xl $spacing-lg;
    text-align: center;
    background: $color-white;
    transition: background 0.2s ease;
  }

  .category-card-img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border-radius: 50%;
  }

  .category-card-icon { font-size: 2.5rem; line-height: 1; }

  .category-card-name {
    font-family: $font-family-display;
    font-size: 1.4rem;
    color: $color-dark;
    letter-spacing: 0.05em;
    transition: color 0.2s ease;
  }

  .category-card-count {
    font-size: 0.8rem;
    color: $color-muted;
    transition: color 0.2s ease;
  }
}
```

- [ ] **Step 6.3: Compilar e verificar**

```bash
npm run build
```

Esperado: seção "EXPLORE POR CATEGORIA" com fundo cinza claro e 3 colunas de cards brancos. Hover muda o fundo do card para preto com texto branco.

- [ ] **Step 6.4: Commit**

```bash
git add template-parts/home-categories.php assets/scss/pages/_home.scss
git commit -m "feat: redesign category cards with dark hover effect"
```

---

## Task 7: MAIS VENDIDOS — cards de promoção

**Files:**
- Modify: `template-parts/home-promotions.php`
- Modify: `assets/scss/pages/_home.scss`

- [ ] **Step 7.1: Reescrever `home-promotions.php`**

Substituir TODO o conteúdo de `template-parts/home-promotions.php` por:

```php
<?php if ( ! defined( 'ABSPATH' ) ) exit;

$promo_query = new WP_Query( [
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 4,
	'meta_query'     => [
		'relation' => 'AND',
		[ 'key' => '_sale_price', 'value' => '', 'compare' => '!=' ],
		[ 'key' => '_sale_price', 'compare' => 'EXISTS' ],
	],
] );

if ( ! $promo_query->have_posts() ) return;

$shop_url = wc_get_page_permalink( 'shop' );
?>
<section class="home-promotions">
	<div class="col-full">
		<div class="section-header">
			<h2 class="section-title-display">MAIS VENDIDOS</h2>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="section-link-all">Ver todos →</a>
		</div>
		<div class="product-cards-grid">
			<?php while ( $promo_query->have_posts() ) : $promo_query->the_post();
				$product  = wc_get_product( get_the_ID() );
				$regular  = (float) $product->get_regular_price();
				$sale     = (float) $product->get_sale_price();
				$discount = $regular > 0 ? round( ( 1 - $sale / $regular ) * 100 ) : 0;
				$thumb    = get_the_post_thumbnail_url( get_the_ID(), 'medium' )
				            ?: 'https://via.placeholder.com/300x300/f8f9fa/999?text=Produto';
			?>
			<a href="<?php the_permalink(); ?>" class="product-card">
				<div class="product-card-image">
					<?php if ( $discount > 0 ) : ?>
						<span class="badge-sale">-<?php echo $discount; ?>%</span>
					<?php endif; ?>
					<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>">
				</div>
				<div class="product-card-body">
					<div class="product-card-rating"><?php echo wc_get_rating_html( $product->get_average_rating() ); ?></div>
					<h3 class="product-card-name"><?php the_title(); ?></h3>
					<div class="product-card-price"><?php echo $product->get_price_html(); ?></div>
				</div>
			</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
```

- [ ] **Step 7.2: Compilar e verificar**

Os estilos `.product-card` já foram adicionados na Task 5. Apenas compilar:

```bash
npm run build
```

Esperado: seção "MAIS VENDIDOS" com 4 cards idênticos em layout aos de NOVIDADES, mas com badge vermelho "-XX%" no canto.

- [ ] **Step 7.3: Commit**

```bash
git add template-parts/home-promotions.php
git commit -m "feat: redesign promotions as MAIS VENDIDOS with sale badge"
```

---

## Task 8: Newsletter

**Files:**
- Create: `template-parts/home-newsletter.php`
- Modify: `assets/scss/pages/_home.scss`

- [ ] **Step 8.1: Criar `home-newsletter.php`**

Criar novo arquivo `template-parts/home-newsletter.php`:

```php
<?php if ( ! defined( 'ABSPATH' ) ) exit;

$account_url = wc_get_page_permalink( 'myaccount' );
?>
<section class="home-newsletter">
	<div class="col-full">
		<div class="newsletter-inner">
			<h2 class="newsletter-title">FIQUE POR DENTRO DAS NOSSAS OFERTAS</h2>
			<form class="newsletter-form" action="<?php echo esc_url( $account_url ); ?>" method="get">
				<div class="newsletter-field-group">
					<span class="newsletter-icon" aria-hidden="true">✉</span>
					<input
						type="email"
						name="newsletter_email"
						placeholder="Digite seu endereço de email"
						class="newsletter-input"
						autocomplete="email"
					>
				</div>
				<button type="submit" class="newsletter-btn">Assinar</button>
			</form>
		</div>
	</div>
</section>
```

- [ ] **Step 8.2: Adicionar estilos da newsletter em `_home.scss`**

Acrescentar ao final de `assets/scss/pages/_home.scss`:

```scss
// ─── Newsletter ───────────────────────────────────────────────────────────────
.home-newsletter {
  background: $color-dark;
  padding: $spacing-section 0;

  .newsletter-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: $spacing-xl;

    @include respond-to(md) {
      flex-direction: column;
      align-items: flex-start;
    }
  }

  .newsletter-title {
    font-family: $font-family-display;
    font-size: 2.5rem;
    line-height: 1.1;
    color: $color-white;
    max-width: 460px;
    margin: 0;

    @include respond-to(md) { font-size: 2rem; }
  }

  .newsletter-form {
    display: flex;
    gap: $spacing-sm;
    flex-wrap: wrap;
    flex-shrink: 0;

    @include respond-to(md) { width: 100%; }
  }

  .newsletter-field-group {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 62px;
    padding: 0 $spacing-lg;
    gap: $spacing-sm;
    flex: 1;
    min-width: 260px;

    .newsletter-icon { opacity: 0.55; font-size: 1rem; }
  }

  .newsletter-input {
    flex: 1;
    background: none;
    border: none;
    color: $color-white;
    font-family: $font-family-body;
    font-size: $font-size-base;
    padding: 0.9rem 0;
    outline: none;

    &::placeholder { color: rgba(255, 255, 255, 0.45); }
  }

  .newsletter-btn {
    background: $color-white;
    color: $color-dark;
    border: none;
    border-radius: 62px;
    padding: 0.9rem 2rem;
    font-family: $font-family-body;
    font-weight: 600;
    font-size: $font-size-base;
    cursor: pointer;
    transition: opacity 0.2s ease;
    white-space: nowrap;

    &:hover { opacity: 0.85; }
  }
}
```

- [ ] **Step 8.3: Compilar e verificar**

```bash
npm run build
```

Esperado: bloco preto com título grande branco em Bebas Neue à esquerda e campo de email + botão branco arredondado à direita.

- [ ] **Step 8.4: Commit**

```bash
git add template-parts/home-newsletter.php assets/scss/pages/_home.scss
git commit -m "feat: add newsletter section with dark background"
```

---

## Task 9: Contact restyle + homepage.php — montagem final

**Files:**
- Modify: `homepage.php`
- Modify: `assets/scss/pages/_home.scss`

- [ ] **Step 9.1: Atualizar `homepage.php`**

Substituir TODO o conteúdo de `homepage.php` por:

```php
<?php
/* Template Name: Home Custom Eletronicos */
get_header();

get_template_part( 'template-parts/home-brands' );
get_template_part( 'template-parts/home-products' );
get_template_part( 'template-parts/home-categories' );
get_template_part( 'template-parts/home-promotions' );
get_template_part( 'template-parts/home-newsletter' );
get_template_part( 'template-parts/home-contact' );

get_footer();
```

- [ ] **Step 9.2: Adicionar estilos do contato em `_home.scss`**

Acrescentar ao final de `assets/scss/pages/_home.scss`:

```scss
// ─── Contact bar ──────────────────────────────────────────────────────────────
.home-contact {
  background: $color-white;
  padding: $spacing-section 0;
  border-top: 1px solid $color-border;

  .contact-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: $spacing-lg $spacing-xl;
    border-right: 1px solid $color-border;

    &:last-child { border-right: none; }

    @include respond-to(md) {
      border-right: none;
      border-bottom: 1px solid $color-border;
      padding: $spacing-lg;

      &:last-child { border-bottom: none; }
    }
  }

  .contact-icon {
    margin-bottom: $spacing-md;

    img { width: 40px; height: 40px; object-fit: contain; }
  }

  .contact-label {
    font-family: $font-family-display;
    font-size: 1.2rem;
    letter-spacing: 0.05em;
    color: $color-dark;
    margin-bottom: $spacing-md;
  }

  .contact-value {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: $spacing-sm;
    font-size: $font-size-base;
    color: $color-dark;
    line-height: $line-height-base;
  }

  .contact-address {
    font-style: normal;
    color: $color-text;
  }

  .contact-btn {
    display: inline-block;
    padding: 0.7rem 1.8rem;
    background: $color-dark;
    border: none;
    border-radius: 62px;
    color: $color-white;
    font-size: $font-size-sm;
    font-weight: 600;
    transition: opacity 0.2s ease;

    &:hover { opacity: 0.8; color: $color-white; }

    &--whatsapp { background: #25d366; }

    &--maps {
      background: $color-white;
      border: 1.5px solid $color-border;
      color: $color-dark;

      &:hover { opacity: 1; border-color: $color-dark; }
    }
  }
}
```

- [ ] **Step 9.3: Compilar e fazer build final**

```bash
npm run build
```

Esperado: homepage completa com todas as seções na ordem: Hero → Marcas → Novidades → Categorias → Mais Vendidos → Newsletter → Contato.

- [ ] **Step 9.4: Revisão visual completa**

Verificar no browser:
- [ ] Announcement bar aparece no topo e fecha ao clicar ✕
- [ ] Hero com overlay escuro, título Bebas Neue, botão preto arredondado, stats bar abaixo
- [ ] Marquee de marcas rolando (fundo preto)
- [ ] 4 cards NOVIDADES em grid
- [ ] 3 colunas de categorias (hover preto)
- [ ] 4 cards MAIS VENDIDOS com badge de desconto
- [ ] Newsletter preta com campo email + botão branco
- [ ] Barra de contato com botões arredondados
- [ ] Responsivo: mobile sem quebras óbvias

- [ ] **Step 9.5: Commit final**

```bash
git add homepage.php assets/scss/pages/_home.scss
git commit -m "feat: assemble final homepage layout with contact restyle"
```
