<?php
/**
 * The Template for displaying all single posts.
 *
 * @package _bootstraps
 * @package _bootstraps - 2013 1.0
 */
get_header();
?>
<div id="primary" class="content-area col-md-12">
    <div id="content" class="site-content" role="main">
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part( 'content', 'page' ); ?>
        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->
<?php //get_sidebar(); ?>
<?php get_footer(); ?>