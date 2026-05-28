<?php
/**
 * Custom header for Headshop — bootscore child
 * Layout: Nav left | Logo center | Cart+Search right
 * Transparent on homepage, solid on scroll
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
    <div class="container px-4">
      <div class="row align-items-center headshop-header__row">

        <!-- Nav Left -->
        <div class="col headshop-header__nav d-none d-lg-flex">
          <?php
          wp_nav_menu(array(
            'theme_location' => 'main-menu',
            'container'      => false,
            'menu_class'     => 'headshop-nav list-unstyled d-flex align-items-center mb-0',
            'fallback_cb'    => false,
            'depth'          => 2,
          ));
          ?>
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

          <!-- Mobile toggler -->
          <button class="btn headshop-action-btn d-lg-none ms-2 headshop-menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>

      </div><!-- .row -->
    </div><!-- .container-fluid -->
  </header>

  <!-- Mobile Offcanvas Menu -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMenu">
    <div class="offcanvas-header">
      <span class="h5 offcanvas-title">Menu</span>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
      <?php
      wp_nav_menu(array(
        'theme_location' => 'main-menu',
        'container'      => false,
        'menu_class'     => 'navbar-nav',
        'fallback_cb'    => false,
        'depth'          => 2,
      ));
      ?>
    </div>
  </div>
