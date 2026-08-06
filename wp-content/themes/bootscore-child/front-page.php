<?php
/**
 * Front Page template for Headshop
 * Displays: Banner Slider → Categories Grid → (optional WooCommerce content)
 *
 * @package Bootscore Child
 */

defined('ABSPATH') || exit;

get_header();
?>

  <!-- Banner Slider -->
  <?php headshop_banner_slider(); ?>

  <!-- Categories Section -->
  <?php headshop_categories_section(); ?>

  <!-- Sale Products Section -->
  <?php headshop_sale_products(); ?>

  <!-- New Products Section -->
  <?php headshop_new_products(); ?>

  <!-- Most Viewed Products Section -->
  <?php headshop_most_viewed_products(); ?>

  <!-- Most Purchased Products Section -->
  <?php headshop_most_purchased_products(); ?>

  <!-- Page content (if any) -->
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <?php
    $content = get_the_content();
    if (trim($content)) :
    ?>
    <div class="container py-5">
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
    </div>
    <?php endif; ?>
  <?php endwhile; endif; ?>

  <!-- Search Overlay -->
  <?php headshop_search_overlay(); ?>

<?php
get_footer();
