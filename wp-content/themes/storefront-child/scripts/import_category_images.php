<?php
/**
 * Generate placeholder images for WooCommerce product categories.
 *
 * Run from the repository root with:
 * php wp-content/themes/storefront-child/scripts/importar_imagens_categorias.php
 */

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

$root = dirname(__DIR__, 4);
if (!file_exists($root . '/wp-load.php')) {
    echo "Could not find wp-load.php in {$root}.\n";
    exit(1);
}

require_once $root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$category_seeds = [
    'Acessórios Diversos'    => 'pipe,accessories',
    'Bem-estar'              => 'herbs,wellness',
    'Branqueadas'            => 'cigarette,paper',
    'Celulose'               => 'paper,roll',
    'Hinduísmo / Esotérico'  => 'incense,spiritual',
    'Kombuchas'              => 'kombucha,jar',
    'Marrom'                 => 'tobacco,leaf',
    'Moletom'                => 'hoodie,streetwear',
    'Orgânicas'              => 'organic,herb',
    'Patês'                  => 'vegan,spread',
    'Presentes e Decoração'  => 'gift,shop',
    'Rangos Vegan'           => 'vegan,food',
    'Roupas Canábicas'       => 'cannabis,leaf',
    'Sedas'                  => 'smoke,paper',
    'Tabacaria'              => 'tobacco,pipe',
    'Tofus'                  => 'tofu,soy',
    'Vestuário'              => 'fashion,green',
    'Vidro'                  => 'glass,smoke',
];

$categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
]);

if (is_wp_error($categories)) {
    echo "Failed to retrieve categories: " . $categories->get_error_message() . "\n";
    exit(1);
}

$count = 0;
$seed  = 1000;

foreach ($categories as $category) {
    if (!is_object($category) || empty($category->term_id)) {
        continue;
    }

    $cat_id      = $category->term_id;
    $cat_name    = $category->name;
    $thumbnail_id = get_term_meta($cat_id, 'thumbnail_id', true);

    if ($thumbnail_id) {
        echo "skip: {$cat_name} (already has thumbnail)\n";
        continue;
    }

    $keyword = $category_seeds[$cat_name] ?? sanitize_title($cat_name);
    $url     = "https://loremflickr.com/1200/1200/{$keyword}?lock={$seed}";
    $tmp     = download_url($url);

    if (is_wp_error($tmp)) {
        echo "download failed: {$cat_name} — " . $tmp->get_error_message() . "\n";
        continue;
    }

    $file_array = [
        'name'     => sanitize_title($cat_name) . '-' . $cat_id . '.jpg',
        'tmp_name' => $tmp,
    ];

    $attachment_id = media_handle_sideload($file_array, 0, "Category image for {$cat_name}");
    if (is_wp_error($attachment_id)) {
        echo "sideload failed: {$cat_name} — " . $attachment_id->get_error_message() . "\n";
        @unlink($tmp);
        continue;
    }

    update_term_meta($cat_id, 'thumbnail_id', $attachment_id);
    $count++;
    echo "created: {$cat_name} (attachment {$attachment_id})\n";
    $seed++;
}

echo "Done. {$count} categories updated.\n";
