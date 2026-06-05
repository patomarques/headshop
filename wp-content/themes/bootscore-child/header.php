<?php
/**
 * Custom header for Headshop — bootscore child
 * Layout: Bar icon left | Logo center | Actions right | Categories row below
 *
 * @package Bootscore Child
 */

defined('ABSPATH') || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?= esc_attr(get_bloginfo('charset')); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<div id="page" class="site">

  <header id="masthead" class="site-header headshop-header<?php if (is_front_page()) echo ' headshop-header--home'; ?> px-4 px-md-5">
    <div class="container">

      <!-- Row 1: Bar icon | Logo | Actions -->
      <div class="row align-items-center headshop-header__row">

        <!-- Bar icon (left) -->
        <div class="col headshop-header__left d-flex align-items-center">
          <button id="navBarsBtn" class="headshop-action-btn headshop-bars-btn" type="button" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>

        <!-- Logo Center -->
        <div class="col-auto headshop-header__brand text-center">
          <a href="<?= esc_url(home_url('/')); ?>" class="headshop-logo-link">
            <?php
            $custom_logo_id = get_theme_mod('custom_logo');
            if ($custom_logo_id) {
              echo wp_get_attachment_image($custom_logo_id, 'full', false, array('class' => 'headshop-logo'));
            } else {
              $fallback = get_stylesheet_directory_uri() . '/assets/img/logo.jpg';
              echo '<img src="' . esc_url($fallback) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="headshop-logo" />';
            }
            ?>
          </a>
        </div>

        <!-- Actions Right -->
        <div class="col headshop-header__actions d-flex align-items-center justify-content-end">
          <!-- Search -->
          <button id="searchToggleBtn" class="headshop-action-btn headshop-search-btn" type="button" aria-label="Pesquisar">
            <span class="headshop-search-icon" aria-hidden="true"></span>
          </button>

          <!-- Login / Account -->
          <?php
            $is_logged     = is_user_logged_in();
            $account_url   = $is_logged ? wc_get_account_endpoint_url('dashboard') : wc_get_page_permalink('myaccount');
            $account_label = $is_logged ? 'Minha conta' : 'Entrar';
          ?>
          <a href="<?= esc_url($account_url); ?>"
             class="headshop-action-btn headshop-user-btn<?= $is_logged ? ' is-logged-in' : ''; ?>"
             aria-label="<?= esc_attr($account_label); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
            <?php if ($is_logged) : ?>
            <span class="headshop-user-btn__dot" aria-hidden="true"></span>
            <?php endif; ?>
          </a>

          <?php if (class_exists('WooCommerce')) : ?>
          <!-- Cart -->
          <?php
            $count    = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
            $cart_url = wc_get_cart_url();
            $bag_url  = get_stylesheet_directory_uri() . '/assets/img/bag.png';
            $nonce    = wp_create_nonce('headshop_cart');
          ?>
          <div class="headshop-cart position-relative" id="headshopCart">
            <a class="headshop-cart__link" href="<?= esc_url($cart_url); ?>" aria-label="Carrinho">
              <img class="headshop-cart__icon" src="<?= esc_url($bag_url); ?>" alt="" width="40" height="40" loading="lazy" />
              <span class="headshop-cart__count"><?= intval($count); ?></span>
            </a>
            <div class="headshop-cart__dropdown" id="cartDropdown" style="display:none;"
                 data-ajax-url="<?= esc_url(admin_url('admin-ajax.php')); ?>"
                 data-nonce="<?= esc_attr($nonce); ?>">
              <div class="headshop-cart__dropdown-inner">
                <?php headshop_render_cart_dropdown(); ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>

      </div><!-- Row 1 -->

      <!-- Row 2: Categories nav (desktop only) -->
      <div class="row headshop-header__cats-row d-none d-lg-flex">
        <div class="col">
          <ul class="headshop-cats-nav list-unstyled d-flex align-items-center justify-content-center mb-0">

            <!-- "Todas as Categorias" — primeiro item com submenu dinâmico -->
            <?php
            $all_cats = get_terms(array(
              'taxonomy'   => 'product_cat',
              'hide_empty' => true,
              'parent'     => 0,
              'exclude'    => array(get_option('default_product_cat')),
              'orderby'    => 'name',
              'order'      => 'ASC',
            ));
            if (!empty($all_cats) && !is_wp_error($all_cats)) :
            ?>
            <li class="menu-item menu-item-has-children headshop-cats-nav__all">
              <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>">Todas as Categorias</a>
              <ul class="sub-menu">
                <?php foreach ($all_cats as $cat) : ?>
                <li class="menu-item">
                  <a href="<?= esc_url(get_term_link($cat)); ?>"><?= esc_html($cat->name); ?></a>
                </li>
                <?php endforeach; ?>
              </ul>
            </li>
            <?php endif; ?>

            <!-- Demais itens do main-menu -->
            <?php
            wp_nav_menu(array(
              'theme_location' => 'main-menu',
              'container'      => false,
              'items_wrap'     => '%3$s',
              'fallback_cb'    => false,
              'depth'          => 2,
            ));
            ?>

          </ul>
        </div>
      </div><!-- Row 2 -->

    </div><!-- .container -->
  </header>

  <!-- Fullscreen Nav Overlay -->
  <div id="navBarsOverlay" class="headshop-nav-overlay" aria-hidden="true">
    <button id="navBarsClose" class="headshop-nav-overlay__close" aria-label="Fechar menu">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <nav class="headshop-nav-overlay__inner">
      <?php
      $overlay_cats = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => 0,
        'exclude'    => array(get_option('default_product_cat')),
        'orderby'    => 'name',
        'order'      => 'ASC',
      ));
      if (!empty($overlay_cats) && !is_wp_error($overlay_cats)) :
      ?>
      <ul class="headshop-nav-overlay__menu list-unstyled mb-0">
        <?php foreach ($overlay_cats as $cat) :
          $sub_cats = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => $cat->term_id,
            'orderby'    => 'name',
            'order'      => 'ASC',
          ));
          $has_children = !empty($sub_cats) && !is_wp_error($sub_cats);
        ?>
        <li class="headshop-overlay-item<?= $has_children ? ' headshop-overlay-item--has-sub' : ''; ?>">
          <div class="headshop-overlay-item__row">
            <a class="headshop-overlay-item__link" href="<?= esc_url(get_term_link($cat)); ?>"><?= esc_html($cat->name); ?></a>
            <?php if ($has_children) : ?>
            <button type="button" class="headshop-overlay-item__toggle" aria-expanded="false" aria-label="Expandir <?= esc_attr($cat->name); ?>">
              <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
            </button>
            <?php endif; ?>
          </div>
          <?php if ($has_children) : ?>
          <ul class="headshop-overlay-item__sub list-unstyled">
            <?php foreach ($sub_cats as $sub) : ?>
            <li>
              <a href="<?= esc_url(get_term_link($sub)); ?>"><?= esc_html($sub->name); ?></a>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </nav>
  </div>

