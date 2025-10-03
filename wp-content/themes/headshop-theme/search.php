<?php get_header(); ?>

<header class="mb-8">
    <h1 class="text-4xl font-bold text-gray-900 mb-4">
        <?php
        printf(
            esc_html__('Resultados da busca por: %s', 'headshop-theme'),
            '<span class="text-green-700">"' . get_search_query() . '"</span>'
        );
        ?>
    </h1>
    
    <?php if (have_posts()): ?>
        <p class="text-gray-600">
            <?php
            global $wp_query;
            printf(
                _n('Encontrado %d resultado', 'Encontrados %d resultados', $wp_query->found_posts, 'headshop-theme'),
                $wp_query->found_posts
            );
            ?>
        </p>
    <?php endif; ?>
</header>

<?php if (have_posts()) : ?>
    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3 mb-12">
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden'); ?>>
                <?php if (has_post_thumbnail()): ?>
                    <a href="<?php the_permalink(); ?>" class="block">
                        <?php the_post_thumbnail('medium', ['class' => 'w-full h-48 object-cover']); ?>
                    </a>
                <?php endif; ?>
                
                <div class="p-6">
                    <div class="text-xs text-green-700 font-medium mb-2">
                        <?php
                        $post_type = get_post_type();
                        if ($post_type === 'product') {
                            echo 'Produto';
                        } elseif ($post_type === 'post') {
                            echo 'Post';
                        } else {
                            echo ucfirst($post_type);
                        }
                        ?>
                    </div>
                    
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">
                        <a href="<?php the_permalink(); ?>" class="hover:text-green-700 transition-colors">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    
                    <div class="text-gray-600 text-sm mb-3">
                        <?php echo get_the_date(); ?>
                        <?php if (get_post_type() === 'product' && class_exists('WooCommerce')): ?>
                            <?php
                            $product = wc_get_product(get_the_ID());
                            if ($product): ?>
                                <span class="ml-2 font-bold text-green-700">
                                    <?php echo $product->get_price_html(); ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-gray-700 mb-4">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <a href="<?php the_permalink(); ?>" class="inline-block text-green-700 hover:text-green-800 font-medium">
                        <?php echo (get_post_type() === 'product') ? 'Ver produto' : 'Ler mais'; ?> →
                    </a>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
    
    <!-- Pagination -->
    <div class="mt-12">
        <?php
        the_posts_pagination([
            'prev_text' => '← Anterior',
            'next_text' => 'Próximo →',
            'class' => 'flex justify-center space-x-4'
        ]);
        ?>
    </div>
    
<?php else: ?>
    <div class="text-center py-16">
        <div class="mb-8">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Nenhum resultado encontrado</h2>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                Não encontramos nada para "<strong><?php echo get_search_query(); ?></strong>". 
                Tente usar palavras-chave diferentes ou mais gerais.
            </p>
        </div>
        
        <!-- Search form -->
        <div class="max-w-md mx-auto mb-8">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="flex">
                <input type="search" 
                       name="s" 
                       placeholder="Digite sua busca..." 
                       value="<?php echo get_search_query(); ?>"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-r-lg hover:bg-green-700 transition-colors">
                    Buscar
                </button>
            </form>
        </div>
        
        <div class="space-x-4">
            <?php if (class_exists('WooCommerce')): ?>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" 
                   class="inline-block px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                    Ver produtos
                </a>
            <?php endif; ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" 
               class="inline-block px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
                Voltar ao início
            </a>
        </div>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
