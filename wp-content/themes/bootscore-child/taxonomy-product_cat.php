<?php
/**
 * Template WooCommerce: Categoria de Produto Full Width (taxonomy-product_cat)
 *
 * @package Bootscore Child
 */

defined('ABSPATH') || exit;

get_header();
?>
<main id="content" class="site-main" role="main">
  <?php
  // Remove container e sidebar
  remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
  remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
  add_action('woocommerce_before_main_content', function() { echo '<div id="primary" class="fullwidth-woocommerce"><div class="row"><div class="col-12">'; }, 10);
  do_action('woocommerce_before_main_content');

  if (woocommerce_product_loop()) {
    do_action('woocommerce_before_shop_loop');
    woocommerce_product_loop_start();
    if (wc_get_loop_prop('total')) {
      while (have_posts()) {
        the_post();
        wc_get_template_part('content', 'product');
      }
    }
    woocommerce_product_loop_end();
    do_action('woocommerce_after_shop_loop');
  } else {
    do_action('woocommerce_no_products_found');
  }

  // Fecha as divs abertas
  echo '</div></div></div>';
  do_action('woocommerce_after_main_content');
  ?>
</main>
<?php get_footer(); ?>
