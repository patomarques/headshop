<?php
/**
 * Empty cart page — Headshop override
 *
 * @package Bootscore Child
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

do_action('woocommerce_cart_is_empty');
?>

<div class="headshop-empty-cart">
  <div class="headshop-empty-cart__message">
    <?php if (wc_get_page_id('shop') > 0) : ?>
      <a class="headshop-empty-cart__btn" href="<?= esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
        <?= esc_html(apply_filters('woocommerce_return_to_shop_text', __('Return to shop', 'woocommerce'))); ?>
      </a>
    <?php endif; ?>
  </div>
</div>
