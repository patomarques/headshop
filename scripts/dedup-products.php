<?php
/**
 * Dedup products: keeps the lowest ID per title, trashes the rest.
 * Also trashes product_variation children of trashed products.
 * Run: php scripts/dedup-products.php
 */

define('WP_USE_THEMES', false);
require_once __DIR__ . '/../wp-load.php';

$ids_to_keep = [80, 240, 241, 242, 243, 244, 245, 246, 247, 248, 251, 254, 255, 256, 262, 264, 271, 277, 282, 283, 641, 642, 643];

// Trash duplicate products and Reverse Withdrawal Payment (ID 371)
$products = get_posts([
    'post_type'      => 'product',
    'post_status'    => ['publish', 'draft', 'private'],
    'numberposts'    => -1,
    'fields'         => 'ids',
]);

foreach ($products as $id) {
    if (in_array($id, $ids_to_keep)) {
        continue;
    }
    $title = get_the_title($id);
    wp_trash_post($id);
    echo "Trashed product ID $id: $title\n";
}

// Trash variations whose parent was trashed
$variations = get_posts([
    'post_type'      => 'product_variation',
    'post_status'    => ['publish', 'draft', 'private'],
    'numberposts'    => -1,
    'fields'         => 'ids',
]);

foreach ($variations as $var_id) {
    $parent_id = wp_get_post_parent_id($var_id);
    if (!in_array($parent_id, $ids_to_keep)) {
        wp_trash_post($var_id);
        echo "  Trashed variation ID $var_id (parent $parent_id)\n";
    }
}

echo "\nDone. Products kept: " . implode(', ', $ids_to_keep) . "\n";
