<?php 
/* 
Template Name: 友情链接 
*/ 
?> 

<?php get_header(); ?> 

<style>
/* 友链样式 */
.friend-links-container {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
}

.friend-link-card {
    width: calc(33.33% - 20px);
    margin: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

/* 响应式布局 */
@media (max-width: 992px) {
    .friend-link-card {
        width: calc(50% - 20px);
    }
}

@media (max-width: 576px) {
    .friend-link-card {
        width: calc(100% - 20px);
    }
}

.friend-link-card a {
    display: flex;
    height: 100%;
    padding: 15px;
    text-decoration: none;
    color: ;
    position: relative;
    z-index: 1;
    border-bottom: none !important;
}

/* 悬停效果 - 背景色填充 */
.friend-link-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 0;
    height: 100%;
    background-color: rgb(226,95,79);
    transition: width 0.3s ease;
    z-index: 0;
}

.friend-link-card:hover::before {
    width: 100%;
}

.friend-link-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 15px;
    flex-shrink: 0;
}

.friend-link-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

/* 悬停时头像旋转效果 */
.friend-link-card:hover .friend-link-avatar img {
    transform: rotate(360deg) scale(1.1);
}

.friend-link-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.friend-link-name {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
}

.friend-link-description {
    font-size: 14px;
    color: #666;
    line-height: 1.4;
}

/* 隐藏文章头部 */
.post-header {
    display: none !important;
}
</style>

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

                // 使用钩子在content_page模板中添加友链内容
                function add_friend_links_to_content($content) {
                    ob_start();
                    ?>
                    <div class="friend-links-container">
                    <?php
                    $bookmarks = get_bookmarks(array(
                        'orderby' => 'name',
                        'order'   => 'ASC'
                    ));
                    
                    if ( !empty($bookmarks) ) {
                        foreach ( $bookmarks as $bookmark ) {
                            echo '<div class="friend-link-card">';
                            echo '<a href="' . esc_url($bookmark->link_url) . '" target="_blank" rel="noopener">';
                            echo '<div class="friend-link-avatar">';
                            if (!empty($bookmark->link_image)) {
                                echo '<img src="' . esc_url($bookmark->link_image) . '" alt="' . esc_attr($bookmark->link_name) . '">';
                            } else {
                                echo '<img src="' . esc_url(get_template_directory_uri() . '/images/default-avatar.png') . '" alt="默认头像">';
                            }
                            echo '</div>';
                            echo '<div class="friend-link-info">';
                            echo '<div class="friend-link-name">' . esc_html($bookmark->link_name) . '</div>';
                            echo '<div class="friend-link-description">' . esc_html($bookmark->link_description) . '</div>';
                            echo '</div>';
                            echo '</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>暂无友情链接</p>';
                    }
                    ?>
                    </div>
                    <?php
                    $friend_links = ob_get_clean();
                    return $content . $friend_links;
                }
                
                // 添加过滤器，确保友链内容添加到文章内容中
                add_filter('the_content', 'add_friend_links_to_content');
                
                // 显示文章内容（现在会包含友链）
                get_template_part( 'template-parts/content', 'page' );
                
                // 移除过滤器，避免影响其他内容
                remove_filter('the_content', 'add_friend_links_to_content');

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