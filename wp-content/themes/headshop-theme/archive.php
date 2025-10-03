<?php get_header(); ?>

<header class="mb-8">
    <h1 class="text-4xl font-bold text-gray-900">
        <?php
        if (is_category()) {
            single_cat_title();
        } elseif (is_tag()) {
            single_tag_title();
        } elseif (is_author()) {
            echo 'Autor: ' . get_the_author();
        } elseif (is_date()) {
            echo 'Arquivo: ' . get_the_date('F Y');
        } else {
            echo 'Arquivo';
        }
        ?>
    </h1>
    
    <?php if (is_category() && category_description()): ?>
        <div class="text-gray-600 mt-4">
            <?php echo category_description(); ?>
        </div>
    <?php endif; ?>
</header>

<?php if ( have_posts() ) : ?>
    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        <?php while ( have_posts() ) : the_post(); ?>
            <article <?php post_class('bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden'); ?>>
                <?php if (has_post_thumbnail()): ?>
                    <a href="<?php the_permalink(); ?>" class="block">
                        <?php the_post_thumbnail('medium', ['class' => 'w-full h-48 object-cover']); ?>
                    </a>
                <?php endif; ?>
                
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">
                        <a href="<?php the_permalink(); ?>" class="hover:text-green-700 transition-colors">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    
                    <div class="text-gray-600 text-sm mb-3">
                        <?php echo get_the_date(); ?>
                    </div>
                    
                    <div class="text-gray-700">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <a href="<?php the_permalink(); ?>" class="inline-block mt-4 text-green-700 hover:text-green-800 font-medium">
                        Ler mais →
                    </a>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
    
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
    <div class="text-center py-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-4"><?php _e('Nenhum post encontrado', 'headshop-theme'); ?></h2>
        <p class="text-gray-600"><?php _e('Não há posts nesta categoria ou arquivo.', 'headshop-theme'); ?></p>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
