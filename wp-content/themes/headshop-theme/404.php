<?php get_header(); ?>

<div class="text-center py-16">
    <div class="mb-8">
        <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Página não encontrada</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Desculpe, a página que você está procurando não existe ou foi movida.
        </p>
    </div>
    
    <div class="space-y-4">
        <a href="<?php echo esc_url(home_url('/')); ?>" 
           class="inline-block px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
            Voltar ao início
        </a>
        
        <?php if (class_exists('WooCommerce')): ?>
            <div class="mt-4">
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" 
                   class="inline-block px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium ml-4">
                    Ver loja
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Search form -->
    <div class="mt-12 max-w-md mx-auto">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ou tente buscar:</h3>
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
    
    <!-- Recent posts -->
    <?php
    $recent_posts = get_posts([
        'numberposts' => 3,
        'post_status' => 'publish'
    ]);
    if ($recent_posts): ?>
        <div class="mt-16">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Posts recentes:</h3>
            <div class="grid gap-6 md:grid-cols-3 max-w-4xl mx-auto">
                <?php foreach ($recent_posts as $post): setup_postdata($post); ?>
                    <article class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                        <?php if (has_post_thumbnail()): ?>
                            <a href="<?php the_permalink(); ?>" class="block">
                                <?php the_post_thumbnail('medium', ['class' => 'w-full h-32 object-cover']); ?>
                            </a>
                        <?php endif; ?>
                        
                        <div class="p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">
                                <a href="<?php the_permalink(); ?>" class="hover:text-green-700 transition-colors">
                                    <?php the_title(); ?>
                                </a>
                            </h4>
                            <div class="text-gray-600 text-sm">
                                <?php echo get_the_date(); ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
