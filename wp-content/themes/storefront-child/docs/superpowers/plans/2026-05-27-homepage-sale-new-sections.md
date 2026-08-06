# Homepage Sale & New Products Sections — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add two new product sections to the homepage — "PRODUTOS EM OFERTA" (on-sale products, 8 items) and "PRODUTOS NOVOS" (newest by date, 8 items) — positioned after categories and before the existing "MAIS VENDIDOS" section.

**Architecture:** Two new PHP template parts following the existing `home-products.php` / `home-promotions.php` pattern. Both are added to `homepage.php` via `get_template_part()`. CSS for the two new sections added to `_home.scss` and compiled via `npm run build`.

**Tech Stack:** PHP 8.2 · WooCommerce 9.x · Dart Sass · no JS changes required

---

## File Map

| Action  | File                                              | Responsibility                              |
|---------|---------------------------------------------------|---------------------------------------------|
| Create  | `template-parts/home-sale.php`                   | "PRODUTOS EM OFERTA" — on-sale query + HTML |
| Create  | `template-parts/home-new-products.php`           | "PRODUTOS NOVOS" — newest-by-date query + HTML |
| Modify  | `homepage.php`                                   | Register the two new template parts in page order |
| Modify  | `assets/scss/pages/_home.scss`                   | `.home-sale` and `.home-new-products` CSS   |
| Build   | `assets/css/main.css` (generated)                | Compiled output — never edit directly       |

---

## Task 1: CSS for the two new sections

**Files:**
- Modify: `assets/scss/pages/_home.scss` (after the `.home-products, .home-promotions` block, around line 222)

- [ ] **Step 1: Add section CSS**

Open `assets/scss/pages/_home.scss`. Find the block:

```scss
// ─── Product cards (shared by NOVIDADES and MAIS VENDIDOS) ────────────────────
.home-products,
.home-promotions {
  padding: $spacing-section 0;
}
```

Replace it with:

```scss
// ─── Product cards (shared across all product sections) ───────────────────────
.home-products,
.home-promotions,
.home-sale,
.home-new-products {
  padding: $spacing-section 0;
}

.home-new-products {
  background: $color-light;
}
```

- [ ] **Step 2: Compile SCSS**

```bash
cd /var/www/html/headshop/wp-content/themes/storefront-child
npm run build
```

Expected: exits with no errors, `assets/css/main.css` updated timestamp.

- [ ] **Step 3: Commit**

```bash
git add assets/scss/pages/_home.scss
git commit -m "style: add home-sale and home-new-products section CSS"
```

---

## Task 2: "Produtos em Oferta" template part

**Files:**
- Create: `template-parts/home-sale.php`

- [ ] **Step 1: Create the template part**

Create `/var/www/html/headshop/wp-content/themes/storefront-child/template-parts/home-sale.php` with:

```php
<?php if ( ! defined( 'ABSPATH' ) ) exit;

$on_sale_ids = wc_get_product_ids_on_sale();

if ( empty( $on_sale_ids ) ) return;

$sale_query = new WP_Query( [
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 8,
	'post__in'       => $on_sale_ids,
	'orderby'        => 'rand',
] );

if ( ! $sale_query->have_posts() ) return;

$shop_url = wc_get_page_permalink( 'shop' );
?>
<section class="home-sale">
	<div class="col-full">
		<div class="section-header">
			<h2 class="section-title-display">PRODUTOS EM OFERTA</h2>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="section-link-all">Ver todos →</a>
		</div>
		<div class="product-cards-grid">
			<?php while ( $sale_query->have_posts() ) : $sale_query->the_post();
				$product = wc_get_product( get_the_ID() );
				$regular = (float) $product->get_regular_price();
				$sale    = (float) $product->get_sale_price();
				$discount = $regular > 0 ? round( ( 1 - $sale / $regular ) * 100 ) : 0;
				$thumb   = get_the_post_thumbnail_url( get_the_ID(), 'medium' )
				           ?: wc_placeholder_img_src( 'medium' );
			?>
			<a href="<?php the_permalink(); ?>" class="product-card">
				<div class="product-card-image">
					<?php if ( $discount > 0 ) : ?>
						<span class="badge-sale">-<?php echo $discount; ?>%</span>
					<?php else : ?>
						<span class="badge-sale">OFERTA</span>
					<?php endif; ?>
					<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
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

- [ ] **Step 2: Commit**

```bash
git add template-parts/home-sale.php
git commit -m "feat: add home-sale template part for on-sale products"
```

---

## Task 3: "Produtos Novos" template part

**Files:**
- Create: `template-parts/home-new-products.php`

- [ ] **Step 1: Create the template part**

Create `/var/www/html/headshop/wp-content/themes/storefront-child/template-parts/home-new-products.php` with:

```php
<?php if ( ! defined( 'ABSPATH' ) ) exit;

$new_query = new WP_Query( [
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 8,
	'orderby'        => 'date',
	'order'          => 'DESC',
] );

if ( ! $new_query->have_posts() ) return;

$shop_url = wc_get_page_permalink( 'shop' );
?>
<section class="home-new-products">
	<div class="col-full">
		<div class="section-header">
			<h2 class="section-title-display">PRODUTOS NOVOS</h2>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="section-link-all">Ver todos →</a>
		</div>
		<div class="product-cards-grid">
			<?php while ( $new_query->have_posts() ) : $new_query->the_post();
				$product = wc_get_product( get_the_ID() );
				$thumb   = get_the_post_thumbnail_url( get_the_ID(), 'medium' )
				           ?: wc_placeholder_img_src( 'medium' );
			?>
			<a href="<?php the_permalink(); ?>" class="product-card">
				<div class="product-card-image">
					<span class="badge-new">NOVO</span>
					<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
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

- [ ] **Step 2: Commit**

```bash
git add template-parts/home-new-products.php
git commit -m "feat: add home-new-products template part"
```

---

## Task 4: Register sections in homepage.php

**Files:**
- Modify: `homepage.php`

- [ ] **Step 1: Add the two new template parts**

Open `homepage.php`. The current content is:

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

Replace with:

```php
<?php
/* Template Name: Home Custom Eletronicos */
get_header();

get_template_part( 'template-parts/home-brands' );
get_template_part( 'template-parts/home-products' );
get_template_part( 'template-parts/home-categories' );
get_template_part( 'template-parts/home-sale' );
get_template_part( 'template-parts/home-new-products' );
get_template_part( 'template-parts/home-promotions' );
get_template_part( 'template-parts/home-newsletter' );
get_template_part( 'template-parts/home-contact' );

get_footer();
```

- [ ] **Step 2: Verify the page renders without PHP errors**

```bash
php -l /var/www/html/headshop/wp-content/themes/storefront-child/homepage.php
php -l /var/www/html/headshop/wp-content/themes/storefront-child/template-parts/home-sale.php
php -l /var/www/html/headshop/wp-content/themes/storefront-child/template-parts/home-new-products.php
```

Expected: `No syntax errors detected` for each file.

- [ ] **Step 3: Check Apache error log for runtime errors**

```bash
tail -20 /var/log/apache2/error.log
```

Expected: no new PHP errors after loading the homepage in a browser.

- [ ] **Step 4: Commit**

```bash
git add homepage.php
git commit -m "feat: register home-sale and home-new-products sections in homepage"
```
