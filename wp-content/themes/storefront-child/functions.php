<?php
// Minimal Storefront child theme functions

if (!defined('ABSPATH')) { exit; }

add_action('wp_enqueue_scripts', function() {
    // Enqueue parent theme stylesheet first
    wp_enqueue_style('storefront-parent-style', get_template_directory_uri() . '/style.css', [], null);
    // Then enqueue child theme stylesheet
    wp_enqueue_style('storefront-child-style', get_stylesheet_uri(), ['storefront-parent-style'], null);
});
