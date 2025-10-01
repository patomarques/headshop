<?php get_header(); ?>

<main class="min-h-screen">
    <div class="container mx-auto px-4 py-8 prose">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <article <?php post_class(); ?>>
                <h1 class="text-3xl font-bold mb-4"><?php the_title(); ?></h1>
                <div class="entry-content"><?php the_content(); ?></div>
            </article>
        <?php endwhile; else: ?>
            <p><?php _e('Nada encontrado.', 'headshop-theme'); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>


