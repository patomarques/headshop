<!doctype html>
<html <?php language_attributes(); ?> class="h-full">
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class('min-h-full bg-gray-50 text-gray-900 antialiased'); ?>>
<!-- Modern Header -->
<header class="bg-white/95 backdrop-blur-sm border-b border-gray-200/50 shadow-sm sticky top-0 z-40">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Cart Icon -->
            <div class="w-12 flex justify-start">
                <?php if (class_exists('WooCommerce')): ?>
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="relative p-2 rounded-xl text-gray-700 hover:text-green-600 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 group">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"/>
                        </svg>
                        <?php
                        $cart_count = WC()->cart->get_cart_contents_count();
                        if ($cart_count > 0): ?>
                            <span class="cart-counter absolute -top-1 -right-1 bg-green-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium group-hover:bg-green-700 transition-colors">
                                <?php echo $cart_count; ?>
                            </span>
                        <?php else: ?>
                            <span class="cart-counter absolute -top-1 -right-1 bg-green-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium group-hover:bg-green-700 transition-colors hidden">
                                0
                            </span>
                        <?php endif; ?>
                        <span class="sr-only">Carrinho de compras</span>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Centered Logo -->
            <div class="flex-1 flex justify-center">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v-.07zM17.9 17.39c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent group-hover:from-green-600 group-hover:to-green-700 transition-all duration-300">
                            Headshop
                        </span>
                    </div>
                </a>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <?php if ( has_nav_menu('primary') ) : ?>
                    <?php wp_nav_menu([
                        'theme_location' => 'primary',
                        'menu_class' => 'flex items-center space-x-6',
                        'container' => false,
                        'fallback_cb' => false,
                        'link_before' => '',
                        'link_after' => '',
                        'walker' => new Headshop_Desktop_Menu_Walker()
                    ]); ?>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="text-gray-700 hover:text-green-600 transition-colors font-medium">Início</a>
                    <?php if (class_exists('WooCommerce')): ?>
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="text-gray-700 hover:text-green-600 transition-colors font-medium">Loja</a>
                        <a href="<?php echo esc_url(wc_get_page_permalink('cart')); ?>" class="text-gray-700 hover:text-green-600 transition-colors font-medium">Carrinho</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Right Menu Button -->
            <div class="w-12 flex justify-end md:hidden">
                <button type="button" 
                        aria-controls="mobile-menu-overlay" 
                        aria-expanded="false" 
                        class="relative p-2 rounded-xl text-gray-700 hover:text-green-600 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200" 
                        id="mobile-menu-button">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="sr-only">Abrir menu</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu Fullscreen Overlay -->
    <div class="fixed inset-0 z-50 hidden" id="mobile-menu-overlay" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
        <div class="relative h-full w-full bg-white flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 h-20 border-b border-gray-200">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v-.07zM17.9 17.39c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">Headshop</span>
                </a>
                <button type="button" 
                        aria-label="Fechar menu" 
                        class="p-2 rounded-xl text-gray-700 hover:text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200" 
                        id="mobile-menu-close">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto">
                <nav class="px-8 py-12">
                    <?php if ( has_nav_menu('primary') ) : ?>
                        <?php wp_nav_menu([
                            'theme_location' => 'primary',
                            'menu_class' => 'space-y-6 text-xl',
                            'container' => false,
                            'fallback_cb' => false,
                            'link_before' => '',
                            'link_after' => '',
                            'walker' => new Headshop_Mobile_Menu_Walker()
                        ]); ?>
                    <?php else: ?>
                        <div class="space-y-6 text-xl">
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="block text-gray-900 hover:text-green-600 transition-colors font-medium">Início</a>
                            <?php if (class_exists('WooCommerce')): ?>
                                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="block text-gray-900 hover:text-green-600 transition-colors font-medium">Loja</a>
                                <a href="<?php echo esc_url(wc_get_page_permalink('cart')); ?>" class="block text-gray-900 hover:text-green-600 transition-colors font-medium">Carrinho</a>
                                <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="block text-gray-900 hover:text-green-600 transition-colors font-medium">Minha Conta</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Footer Actions -->
            <?php if (class_exists('WooCommerce')): ?>
                <div class="px-6 py-8 border-t border-gray-200">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="<?php echo esc_url(wc_get_page_permalink('cart')); ?>" 
                           class="inline-flex items-center justify-center px-6 py-4 rounded-xl bg-gradient-to-r from-green-600 to-green-700 text-white font-medium hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"/>
                            </svg>
                            Carrinho
                        </a>
                        <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" 
                           class="inline-flex items-center justify-center px-6 py-4 rounded-xl border-2 border-gray-300 text-gray-900 font-medium hover:border-green-500 hover:text-green-600 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Conta
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Cart Counter Update Script -->
<script>
// Cache buster: <?php echo time(); ?>
document.addEventListener('DOMContentLoaded', function() {
    // Update cart counter
    function updateCartCounter() {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'get_cart_count'
            })
        })
        .then(response => response.json())
        .then(data => {
            const cartCounter = document.querySelector('.cart-counter');
            if (cartCounter) {
                if (data.count > 0) {
                    cartCounter.textContent = data.count;
                    cartCounter.classList.remove('hidden');
                } else {
                    cartCounter.classList.add('hidden');
                }
            }
        })
        .catch(error => console.log('Cart update error:', error));
    }
    
    // Listen for WooCommerce cart updates
    document.body.addEventListener('added_to_cart', function() {
        setTimeout(updateCartCounter, 500);
    });
    
    document.body.addEventListener('removed_from_cart', function() {
        setTimeout(updateCartCounter, 500);
    });
    
    // Update on page load
    updateCartCounter();
    
    // Update every 5 seconds as fallback
    setInterval(updateCartCounter, 5000);
});
</script>

<!-- Main Content -->
<main class="min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">


