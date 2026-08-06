<?php
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$category_seeds = [
    'Resistores'           => 'resistor,electronics',
    'Capacitores'          => 'capacitor,electronics',
    'Transistores'         => 'transistor,chip',
    'Diodos'               => 'diode,electronics',
    'Circuitos Integrados' => 'circuit,microchip',
    'Conectores'           => 'connector,cable',
    'Sensores'             => 'sensor,electronics',
    'Ferramentas'          => 'tools,soldering',
];

$query = new WP_Query([
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'fields'         => 'ids',
]);

$count = 0;
$seed  = 10;

foreach ($query->posts as $product_id) {
    if (has_post_thumbnail($product_id)) {
        WP_CLI::log("  skip: " . get_the_title($product_id));
        continue;
    }

    $terms    = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
    $cat_name = (!is_wp_error($terms) && !empty($terms)) ? $terms[0] : 'Ferramentas';
    $keyword  = $category_seeds[$cat_name] ?? $category_seeds['Ferramentas'];

    $url = "https://loremflickr.com/400/400/{$keyword}?lock={$seed}";
    $tmp = download_url($url);
    $seed++;

    if (is_wp_error($tmp)) {
        WP_CLI::warning("  download failed: " . get_the_title($product_id) . " — " . $tmp->get_error_message());
        continue;
    }

    $file_array = [
        'name'     => sanitize_title(get_the_title($product_id)) . '-' . $product_id . '.jpg',
        'tmp_name' => $tmp,
    ];

    $attachment_id = media_handle_sideload($file_array, $product_id, get_the_title($product_id));

    if (is_wp_error($attachment_id)) {
        WP_CLI::warning("  sideload failed: " . get_the_title($product_id));
        @unlink($tmp);
        continue;
    }

    set_post_thumbnail($product_id, $attachment_id);
    $count++;
    WP_CLI::log("  [{$count}] " . get_the_title($product_id) . " ({$cat_name})");
}

WP_CLI::success("Done. {$count} products received images.");
