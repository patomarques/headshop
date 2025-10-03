<!doctype html>
<html <?php language_attributes(); ?> class="h-full">
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class('min-h-full bg-gray-50 text-gray-900'); ?>>
<header class="bg-white border-b shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="text-2xl font-extrabold text-gray-900 hover:text-green-700 transition-colors">
                    Headshop
                </a>
            </div>
            
            <nav class="hidden md:block">
                <?php if ( has_nav_menu('primary') ) : ?>
                    <?php wp_nav_menu([
                        'theme_location' => 'primary',
                        'menu_class' => 'flex space-x-8',
                        'container' => false,
                        'fallback_cb' => false,
                        'link_before' => '',
                        'link_after' => '',
                        'add_li_class' => 'text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium transition-colors'
                    ]); ?>
                <?php else: ?>
                    <div class="flex space-x-8">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium transition-colors">Início</a>
                        <?php if (class_exists('WooCommerce')): ?>
                            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium transition-colors">Loja</a>
                            <a href="<?php echo esc_url(wc_get_page_permalink('cart')); ?>" class="text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium transition-colors">Carrinho</a>
                            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium transition-colors">Minha Conta</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </nav>
            
            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button type="button" class="text-gray-700 hover:text-green-700 focus:outline-none focus:text-green-700" id="mobile-menu-button">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-700 hover:bg-gray-50">Início</a>
                <?php if (class_exists('WooCommerce')): ?>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-700 hover:bg-gray-50">Loja</a>
                    <a href="<?php echo esc_url(wc_get_page_permalink('cart')); ?>" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-700 hover:bg-gray-50">Carrinho</a>
                    <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-700 hover:bg-gray-50">Minha Conta</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main class="flex-1 min-h-screen">
    <!-- Container wrapper for all content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Content area with proper spacing -->
        <div class="py-8">


