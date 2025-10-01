<!doctype html>
<html <?php language_attributes(); ?> class="h-full">
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class('min-h-full bg-gray-50 text-gray-900'); ?>>
<header class="bg-white border-b">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="text-2xl font-extrabold">Headshop</a>
        <?php if ( has_nav_menu('primary') ) : ?>
            <?php wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class' => 'flex gap-6',
                'container' => false,
                'fallback_cb' => false
            ]); ?>
        <?php endif; ?>
    </div>
</header>


