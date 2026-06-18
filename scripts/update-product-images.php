<?php
/**
 * Downloads a relevant image from loremflickr.com for each product
 * and sets it as the product thumbnail.
 * Run: php scripts/update-product-images.php
 */

define('WP_USE_THEMES', false);
require_once __DIR__ . '/../wp-load.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$products = [
    80  => 'hoodie,sweatshirt',
    240 => 'rasta,hat',
    241 => 'colored,rolling,paper',
    242 => 'ganesha,statue',
    243 => 'cannabis,tshirt',
    244 => 'meditation,mat',
    245 => 'lemongrass,essential,oil',
    246 => 'lavender,candle',
    247 => 'incense,sticks',
    248 => 'spray,bottle,energy',
    251 => 'clipper,lighter',
    254 => 'rolling,papers',
    255 => 'leaf,socks',
    256 => 'rolling,paper,king',
    262 => 'wooden,mandala',
    264 => 'glass,ashtray',
    271 => 'elephant,incense',
    277 => 'rudraksha,mala',
    282 => 'jamaica,tank,top',
    283 => 'herb,grinder',
    641 => 'vegan,burger',
    642 => 'vegan,nuggets',
    643 => 'jackfruit,food',
];

foreach ($products as $product_id => $keywords) {
    $title = get_the_title($product_id);
    if (!$title) {
        echo "SKIP: product $product_id not found\n";
        continue;
    }

    // loremflickr redirects to a CC-licensed Flickr photo matching the keywords
    $url = "https://loremflickr.com/800/800/$keywords";

    // Download to temp file following the redirect
    $tmp = download_url($url, 30);
    if (is_wp_error($tmp)) {
        echo "ERROR downloading for '$title': " . $tmp->get_error_message() . "\n";
        continue;
    }

    $file_array = [
        'name'     => sanitize_title($title) . '.jpg',
        'tmp_name' => $tmp,
    ];

    $attachment_id = media_handle_sideload($file_array, $product_id, $title);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        echo "ERROR sideloading for '$title': " . $attachment_id->get_error_message() . "\n";
        continue;
    }

    set_post_thumbnail($product_id, $attachment_id);
    echo "OK: '$title' (ID $product_id) → attachment $attachment_id\n";
}

echo "\nDone.\n";
