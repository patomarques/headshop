<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article <?php post_class('prose prose-lg max-w-none'); ?>>
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900"><?php the_title(); ?></h1>
        </header>
        
        <div class="entry-content">
            <?php the_content(); ?>
        </div>
        
        <?php if (comments_open() || get_comments_number()): ?>
            <div class="mt-12">
                <?php comments_template(); ?>
            </div>
        <?php endif; ?>
    </article>
<?php endwhile; else: ?>
    <div class="text-center py-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-4"><?php _e('Página não encontrada', 'headshop-theme'); ?></h2>
        <p class="text-gray-600"><?php _e('Desculpe, não foi possível encontrar a página solicitada.', 'headshop-theme'); ?></p>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
