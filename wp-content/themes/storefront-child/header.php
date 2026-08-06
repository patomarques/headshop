<?php if (!defined('ABSPATH')) exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php do_action('storefront_before_site'); ?>

<div id="page" class="hfeed site">

  <?php do_action('storefront_before_header'); ?>

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

  <header id="masthead" class="site-header" role="banner">

    <div class="header-main">
      <div class="col-full">
        <div class="header-main-row">

          <a class="site-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?>">
            <?php if (has_custom_logo()) : the_custom_logo(); else : ?>
              <span class="site-logo-text"><?php bloginfo('name'); ?></span>
            <?php endif; ?>
          </a>

          <div class="header-actions">
            <button id="search-toggle" class="header-icon-btn" type="button" aria-label="Buscar" aria-expanded="false" aria-controls="header-search">
              <svg class="icon-search" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
              </svg>
              <svg class="icon-close" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
              </svg>
            </button>
            <?php
              $is_logged     = is_user_logged_in();
              $account_url   = $is_logged ? wc_get_account_endpoint_url('dashboard') : wc_get_page_permalink('myaccount');
              $account_label = $is_logged ? 'Minha conta' : 'Entrar';
            ?>
            <a href="<?php echo esc_url($account_url); ?>" class="header-icon-btn header-user-btn<?php echo $is_logged ? ' is-logged-in' : ''; ?>" aria-label="<?php echo esc_attr($account_label); ?>">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
              </svg>
              <?php if ($is_logged) : ?>
              <span class="user-logged-dot" aria-hidden="true"></span>
              <?php endif; ?>
            </a>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="header-cart-link" aria-label="Carrinho">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
              </svg>
              <?php $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
              <span class="cart-count"><?php echo $count > 0 ? esc_html($count) : ''; ?></span>
            </a>
            <button id="nav-toggle" class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Menu">
              <span class="nav-toggle-bar"></span>
              <span class="nav-toggle-bar"></span>
              <span class="nav-toggle-bar"></span>
            </button>
          </div>

        </div>
      </div>
    </div>

    <nav id="site-nav" class="header-nav-bar" aria-label="Menu principal">
      <?php
        wp_nav_menu([
          'theme_location'  => 'primary',
          'container'       => 'div',
          'container_class' => 'col-full',
          'menu_class'      => 'header-menu',
          'fallback_cb'     => false,
        ]);
      ?>
    </nav>

    <div id="header-search" class="header-search-panel" hidden>
      <div class="col-full">
        <?php get_product_search_form(); ?>
      </div>
    </div>

  </header>

  <div id="content" class="site-content" tabindex="-1">

  <?php do_action('storefront_before_content'); ?>

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
            $img_id     = get_post_thumbnail_id($banner->ID);
            $img_url    = $img_id ? wp_get_attachment_image_url($img_id, 'full') : '';
            $img_alt    = esc_attr(get_the_title($banner->ID));
            $img_link   = get_post_meta($banner->ID, '_banner_link', true);
            $img_text   = get_post_meta($banner->ID, '_banner_text', true);
            $mobile_att = (int) get_post_meta($banner->ID, '_banner_mobile_id', true);
            $mobile_url = $mobile_att
              ? wp_get_attachment_image_url($mobile_att, 'full')
              : ($img_id ? wp_get_attachment_image_url($img_id, 'banner_mobile') : '');
          } else {
            $img_url    = $banner['url'];
            $img_alt    = $banner['alt'];
            $img_link   = $banner['link'];
            $img_text   = $banner['text'];
            $mobile_url = '';
          }
          $is_first = ($i++ === 0);
        ?>
        <div class="carousel-item<?php if ($is_first) echo ' active'; ?>">
          <?php if ($img_link) : ?><a href="<?php echo esc_url($img_link); ?>"><?php endif; ?>
          <picture>
            <?php if ($mobile_url && $mobile_url !== $img_url) : ?>
            <source media="(max-width: 768px)" srcset="<?php echo esc_url($mobile_url); ?>">
            <?php endif; ?>
            <img src="<?php echo esc_url($img_url); ?>" class="d-block w-100" alt="<?php echo $img_alt; ?>"<?php echo $is_first ? '' : ' loading="lazy"'; ?>>
          </picture>
          <?php if ($img_link) : ?></a><?php endif; ?>
          <?php if ($img_text) : ?>
          <div class="hero-overlay">
            <div class="col-full">
              <h1 class="hero-title"><?php echo esc_html($img_text); ?></h1>
              <?php if ($img_link) : ?>
              <a href="<?php echo esc_url($img_link); ?>" class="hero-cta">Ver mais</a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
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

  </section>

  <?php else : ?>
    <div class="col-full">
      <?php do_action('storefront_content_top'); ?>
  <?php endif; ?>
