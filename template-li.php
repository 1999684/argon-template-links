<?php 
/* 
Template Name: 友情链接 
*/ 
?> 

<?php get_header(); ?> 

<div class="page-information-card-container">
	<div class="page-information-card card bg-gradient-secondary shadow-lg border-0">
		<div class="card-body">
			<h3 class="text-black"><?php _e('友情链接', 'argon');?></h3>
			<?php if (the_archive_description() != ''){ ?>
				<p class="text-black mt-3">
					<?php the_archive_description(); ?>
				</p>
			<?php } ?>
			<p class="text-black mt-3 mb-0 opacity-8">
				<i class="fa fa-quote-left mr-1"></i>
				<?php
				    global $wpdb;
				    $link_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->links WHERE link_visible = 'Y'");
				    echo absint($link_count);
				?>
				<?php _e('位朋友', 'argon');?>
			</p>
		</div>
	</div>
</div>

<?php get_sidebar(); ?> 

<div id="primary" class="content-area"> 
    <main id="main" class="site-main" role="main"> 
        <?php 
            while ( have_posts() ) : 
                the_post(); 
                
                get_template_part( 'template-parts/content', 'page1' );
                
                if (get_option("argon_show_sharebtn") != 'false') { 
                    get_template_part( 'template-parts/share' ); 
                } 

                if (comments_open() || get_comments_number()) { 
                    comments_template(); 
                } 
            endwhile; 
        ?> 
    </main>

<?php get_footer(); ?>