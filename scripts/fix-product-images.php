<?php
/**
 * Re-downloads images for products with red backgrounds or duplicate photos.
 * Run: php8.2 scripts/fix-product-images.php
 */

define('WP_USE_THEMES', false);
require_once __DIR__ . '/../wp-load.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// Red background offenders + identical-photo offenders, with improved keywords.
// loremflickr uses comma-separated keywords and an optional ?lock=N seed.
$products = [
    // Red background
    277 => ['keywords' => 'prayer,beads,hand',      'seed' => 42],  // Japamala Rudraksha
    262 => ['keywords' => 'mandala,wood,carved',    'seed' => 7],   // Mandala de Madeira
    255 => ['keywords' => 'green,socks,fashion',    'seed' => 13],  // Meia Folha Verde
    244 => ['keywords' => 'yoga,mat,studio',        'seed' => 21],  // Tapete de Meditação
    246 => ['keywords' => 'lavender,candle,aroma',  'seed' => 99],  // Vela Aromática Lavanda
    // Identical gray photo
    245 => ['keywords' => 'essential,oil,dropper',  'seed' => 55],  // Óleo Essencial de Capim-limão
    241 => ['keywords' => 'rolling,paper,tobacco',  'seed' => 33],  // Papel de Seda Colorido
    282 => ['keywords' => 'reggae,jamaica,rasta',   'seed' => 77],  // Regata Jamaica Vibes
    256 => ['keywords' => 'cigarette,paper,filter', 'seed' => 11],  // Seda King Size
    248 => ['keywords' => 'energy,spray,bottle',    'seed' => 66],  // Spray Energizante
];

foreach ($products as $product_id => $cfg) {
    $title = get_the_title($product_id);
    if (!get_post($product_id)) {
        echo "SKIP: product $product_id not found\n";
        continue;
    }

    $url = "https://loremflickr.com/800/800/{$cfg['keywords']}?lock={$cfg['seed']}";

    $tmp = download_url($url, 30);
    if (is_wp_error($tmp)) {
        echo "ERROR downloading '$title': " . $tmp->get_error_message() . "\n";
        continue;
    }

    $file_array = [
        'name'     => sanitize_title($title) . '-fixed.jpg',
        'tmp_name' => $tmp,
    ];

    $attachment_id = media_handle_sideload($file_array, $product_id, $title);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        echo "ERROR sideloading '$title': " . $attachment_id->get_error_message() . "\n";
        continue;
    }

    $result = set_post_thumbnail($product_id, $attachment_id);
    if (!$result) {
        echo "ERROR setting thumbnail for '$title' (ID $product_id)\n";
    } else {
        echo "OK: '$title' (ID $product_id) → attachment $attachment_id\n";
    }
}

echo "\nDone.\n";
