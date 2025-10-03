<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article <?php post_class('prose prose-lg max-w-none'); ?>>
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4"><?php the_title(); ?></h1>
            
            <div class="flex items-center text-gray-600 text-sm space-x-4 mb-6">
                <span><?php echo get_the_date(); ?></span>
                <?php if (get_the_author()): ?>
                    <span>por <?php the_author(); ?></span>
                <?php endif; ?>
                <?php if (get_the_category()): ?>
                    <span>em <?php the_category(', '); ?></span>
                <?php endif; ?>
            </div>
            
            <?php if (has_post_thumbnail()): ?>
                <div class="mb-8">
                    <?php the_post_thumbnail('large', ['class' => 'w-full h-64 md:h-96 object-cover rounded-lg']); ?>
                </div>
            <?php endif; ?>
        </header>
        
        <div class="entry-content">
            <?php the_content(); ?>
        </div>
        
        <?php if (get_the_tags()): ?>
            <div class="mt-8 pt-8 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tags:</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach (get_the_tags() as $tag): ?>
                        <a href="<?php echo get_tag_link($tag->term_id); ?>" 
                           class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">
                            <?php echo $tag->name; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (comments_open() || get_comments_number()): ?>
            <div class="mt-12 pt-8 border-t">
                <?php comments_template(); ?>
            </div>
        <?php endif; ?>
    </article>
    
    <!-- Navigation between posts -->
    <nav class="mt-12 pt-8 border-t">
        <div class="flex justify-between">
            <?php
            $prev_post = get_previous_post();
            $next_post = get_next_post();
            ?>
            
            <?php if ($prev_post): ?>
                <a href="<?php echo get_permalink($prev_post); ?>" 
                   class="flex items-center text-green-700 hover:text-green-800 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <div>
                        <div class="text-sm text-gray-600">Post anterior</div>
                        <div class="font-medium"><?php echo get_the_title($prev_post); ?></div>
                    </div>
                </a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>
            
            <?php if ($next_post): ?>
                <a href="<?php echo get_permalink($next_post); ?>" 
                   class="flex items-center text-green-700 hover:text-green-800 transition-colors text-right">
                    <div>
                        <div class="text-sm text-gray-600">Próximo post</div>
                        <div class="font-medium"><?php echo get_the_title($next_post); ?></div>
                    </div>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </nav>
<?php endwhile; else: ?>
    <div class="text-center py-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-4"><?php _e('Post não encontrado', 'headshop-theme'); ?></h2>
        <p class="text-gray-600"><?php _e('Desculpe, não foi possível encontrar o post solicitado.', 'headshop-theme'); ?></p>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
