<?php
/**
 * Plugin Name: WP Friend Links
 * Description: 一个美观的WordPress友情链接插件，支持自定义样式和动画效果
 * Version: 1.0.0
 * Author: ZTGD
 * License: GPL2
 * Text Domain: wp-friend-links
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

// 获取友链数据
function wfl_get_friend_links($args = array()) {
    // 检查是否有缓存
    $cache_key = 'wfl_friend_links_data';
    $cache_time = get_option('wfl_cache_time', 3600); // 默认缓存1小时
    
    // 如果缓存存在且未过期，直接返回缓存数据
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false && !isset($_GET['wfl_clear_cache'])) {
        return $cached_data;
    }
    
    // 合并默认参数
    $default_args = array(
        'orderby' => 'name',
        'order' => 'ASC',
        'limit' => -1,
        'category' => ''
    );
    
    $args = wp_parse_args($args, $default_args);
    
    // 构建查询参数
    $query_args = array(
        'orderby' => $args['orderby'],
        'order' => $args['order'],
        'limit' => $args['limit']
    );
    
    // 如果指定了分类
    if (!empty($args['category'])) {
        $category = get_term_by('name', $args['category'], 'link_category');
        if ($category) {
            $query_args['category'] = $category->term_id;
        }
    }
    
    // 获取友链
    $links = get_bookmarks($query_args);
    
    // 缓存结果
    if ($cache_time > 0) {
        set_transient($cache_key, $links, $cache_time);
    }
    
    return $links;
}

// 前端展示逻辑
function wfl_display_friend_links($atts) {
    // 解析短代码属性
    $atts = shortcode_atts(array(
        'orderby' => 'name',
        'order' => 'ASC',
        'limit' => -1,
        'category' => '',
    ), $atts, 'friend_links');
    
    // 确保样式已加载
    wp_enqueue_style('wfl-style');
    
    $cards_per_row = get_option('wfl_cards_per_row', 3);
    
    $output = '<div class="friend-links-container" data-cards-per-row="' . esc_attr($cards_per_row) . '">';
    $friend_links = wfl_get_friend_links($atts);
    
    if (!empty($friend_links)) {
        foreach ($friend_links as $link) {
            $image = !empty($link->link_image) ? $link->link_image : plugins_url('assets/images/default-avatar.png', __FILE__);
            $description = !empty($link->link_description) ? $link->link_description : '这个站点没有描述';
            
            $output .= '<div class="friend-link-card">';
            $output .= '<a href="' . esc_url($link->link_url) . '" target="_blank" rel="noopener">';
            $output .= '<div class="friend-link-avatar"><img src="' . esc_url($image) . '" alt="' . esc_attr($link->link_name) . '" loading="lazy"></div>';
            $output .= '<div class="friend-link-info">';
            $output .= '<div class="friend-link-name">' . esc_html($link->link_name) . '</div>';
            $output .= '<div class="friend-link-description">' . esc_html($description) . '</div>';
            $output .= '</div>';
            $output .= '</a>';
            $output .= '</div>';
        }
    } else {
        $output .= '<p>暂无友情链接</p>';
    }
    
    $output .= '</div>'; // .friend-links-container
    
    // 添加PJAX支持的脚本
    $output .= '<script>
    (function(){
        function initFriendLinksAnimation() {
            // 这里可以添加任何需要在页面加载时执行的友链相关JS
            // 例如：添加动画效果、事件监听等
            console.log("友链卡片已加载");
        }
        
        // 页面首次加载时执行
        initFriendLinksAnimation();
        
        // 为PJAX添加支持
        if (typeof window.pjaxLoaded !== "function") {
            window.pjaxLoaded = initFriendLinksAnimation;
        } else {
            var originalPjaxLoaded = window.pjaxLoaded;
            window.pjaxLoaded = function() {
                originalPjaxLoaded();
                initFriendLinksAnimation();
            };
        }
    })();
    </script>';
    
    return $output;
}
add_shortcode('friend_links', 'wfl_display_friend_links'); // 注册短代码

// 添加插件设置页面
function wfl_add_settings_page() {
    add_options_page(
        '友情链接设置',
        '友情链接',
        'manage_options',
        'wfl-settings',
        'wfl_render_settings_page'
    );
}
add_action('admin_menu', 'wfl_add_settings_page');

// 设置页面渲染
function wfl_render_settings_page() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
    ?>
    <div class="wrap">
        <h1>友情链接设置</h1>
        
        <h2 class="nav-tab-wrapper">
            <a href="?page=wfl-settings&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">基本设置</a>
            <a href="?page=wfl-settings&tab=instructions" class="nav-tab <?php echo $active_tab == 'instructions' ? 'nav-tab-active' : ''; ?>">使用说明</a>
        </h2>
        
        <?php if ($active_tab == 'settings') { ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('wfl_settings_group');
                do_settings_sections('wfl-settings');
                submit_button();
                ?>
            </form>
        <?php } else if ($active_tab == 'instructions') { ?>
            <div class="wfl-instructions">
                <h3>插件使用说明</h3>
                
                <h4>1. 添加友链</h4>
                <p>您可以在WordPress后台的"链接"管理中添加友情链接：</p>
                <ol>
                    <li>在WordPress后台，进入"链接"->"添加链接"</li>
                    <li>填写网站名称、URL、描述和图片</li>
                    <li>选择适当的链接分类</li>
                    <li>保存链接</li>
                </ol>
                
                <h4>2. 在页面中显示友链</h4>
                <p>使用短代码 <code>[friend_links]</code> 在任何页面或文章中显示友情链接。</p>
                <p>您也可以使用以下参数自定义显示：</p>
                <ul>
                    <li><code>[friend_links orderby="name" order="ASC"]</code> - 按名称升序排列</li>
                    <li><code>[friend_links orderby="url" order="DESC"]</code> - 按URL降序排列</li>
                    <li><code>[friend_links limit="10"]</code> - 只显示10个友链</li>
                    <li><code>[friend_links category="推荐"]</code> - 只显示"推荐"分类的友链</li>
                </ul>
                
                <h4>3. 自定义样式</h4>
                <p>插件自带美观的卡片样式，您也可以通过自定义CSS来修改友链卡片的外观。</p>

                <h4>4. 缓存设置</h4>
                <p>为了提高性能，插件会缓存友链数据。您可以在设置页面中：</p>
                <ul>
                    <li>设置缓存时间（默认为1小时）</li>
                    <li>点击"清理友链缓存"按钮手动清理缓存</li>
                </ul>
                <p>当您添加、编辑或删除友链时，缓存会自动清理。但如果您发现更改没有立即生效，可以手动清理缓存。</p>
            </div>
        <?php } ?>
    </div>
    
    <style>
        .wfl-instructions {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
            margin-top: 20px;
        }
        .wfl-instructions h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .wfl-instructions h4 {
            margin-top: 20px;
            color: #23282d;
        }
        .wfl-instructions ul, .wfl-instructions ol {
            margin-left: 20px;
        }
        .wfl-instructions code {
            background: #f5f5f5;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
    <?php
}

function wfl_register_settings() {
    register_setting('wfl_settings_group', 'wfl_cards_per_row', array('default' => 3));
    register_setting('wfl_settings_group', 'wfl_cache_time', array('default' => 3600));

    add_settings_section('wfl_main_section', '基础设置', null, 'wfl-settings');

    add_settings_field(
        'wfl_cards_per_row',
        '每行显示卡片数',
        'wfl_cards_per_row_callback',
        'wfl-settings',
        'wfl_main_section'
    );
    
    add_settings_field(
        'wfl_cache_time',
        '缓存时间（秒）',
        'wfl_cache_time_callback',
        'wfl-settings',
        'wfl_main_section'
    );
}
add_action('admin_init', 'wfl_register_settings');

function wfl_cards_per_row_callback() {
    $value = get_option('wfl_cards_per_row', 3);
    echo '<input type="number" min="1" max="6" name="wfl_cards_per_row" value="' . esc_attr($value) . '" />';
    echo '<p class="description">设置每行显示的友链卡片数量（1-6之间）</p>';
}

function wfl_cache_time_callback() {
    $value = get_option('wfl_cache_time', 3600);
    echo '<input type="number" min="0" name="wfl_cache_time" value="' . esc_attr($value) . '" />';
    echo '<p class="description">设置友链数据缓存的时间（秒），设置为0则禁用缓存。默认3600秒（1小时）</p>';
}

// 添加样式
function wfl_enqueue_styles() {
    // 确保assets/css目录存在
    $css_dir = plugin_dir_path(__FILE__) . 'assets/css';
    if (!file_exists($css_dir)) {
        wp_mkdir_p($css_dir);
    }
    
    // 创建CSS文件（如果不存在）
    $css_file = $css_dir . '/friend-links.css';
    if (!file_exists($css_file)) {
        $css_content = '/* 友链样式 */
.friend-links-container {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
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
    color: #333 !important;
    position: relative;
    z-index: 1;
    border-bottom: none !important;
}

/* 悬停效果 - 背景色填充 */
.friend-link-card::before {
    content: "";
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
    color: rgb(166, 167, 157);
}

.friend-link-description {
    font-size: 14px;
    color: #666;
    line-height: 1.4;
}

/* 悬停时文字颜色变化 */
.friend-link-card:hover .friend-link-name,
.friend-link-card:hover .friend-link-description {
    color: #fff !important;
    position: relative;
    z-index: 2;
}

/* 根据设置调整每行卡片数 */
.friend-links-container[data-cards-per-row="1"] .friend-link-card {
    width: calc(100% - 20px);
}

.friend-links-container[data-cards-per-row="2"] .friend-link-card {
    width: calc(50% - 20px);
}

.friend-links-container[data-cards-per-row="3"] .friend-link-card {
    width: calc(33.33% - 20px);
}

.friend-links-container[data-cards-per-row="4"] .friend-link-card {
    width: calc(25% - 20px);
}

.friend-links-container[data-cards-per-row="5"] .friend-link-card {
    width: calc(20% - 20px);
}

.friend-links-container[data-cards-per-row="6"] .friend-link-card {
    width: calc(16.66% - 20px);
}';
        file_put_contents($css_file, $css_content);
    }
    
    // 使用版本号避免缓存问题
    $version = filemtime($css_file);
    if (!$version) $version = '1.0.0';
    
    wp_register_style(
        'wfl-style', 
        plugins_url('assets/css/friend-links.css', __FILE__), 
        array(), 
        $version
    );
    
    // 直接加载样式，不再依赖短代码检测
    wp_enqueue_style('wfl-style');
}
add_action('wp_enqueue_scripts', 'wfl_enqueue_styles');

// 添加内联样式以应用每行卡片数设置
function wfl_add_inline_styles() {
    $cards_per_row = get_option('wfl_cards_per_row', 3);
    $custom_css = "
        @media (min-width: 992px) {
            .friend-links-container .friend-link-card {
                width: calc(" . (100/$cards_per_row) . "% - 20px) !important;
            }
        }
    ";
    
    wp_add_inline_style('wfl-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'wfl_add_inline_styles', 20); // 提高优先级，确保在主样式后加载

// 添加PJAX支持的全局脚本
function wfl_add_pjax_support() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 如果页面包含友链卡片，初始化它们
        if (document.querySelector('.friend-links-container')) {
            if (typeof window.pjaxLoaded === 'function') {
                window.pjaxLoaded();
            }
        }
    });
    
    // 为PJAX加载添加全局事件监听
    document.addEventListener('pjax:complete', function() {
        // PJAX加载完成后，检查是否有友链容器
        if (document.querySelector('.friend-links-container')) {
            if (typeof window.pjaxLoaded === 'function') {
                window.pjaxLoaded();
            }
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'wfl_add_pjax_support');

// 创建默认头像文件
function wfl_create_default_avatar() {
    // 确保assets/images目录存在
    $images_dir = plugin_dir_path(__FILE__) . 'assets/images';
    if (!file_exists($images_dir)) {
        wp_mkdir_p($images_dir);
    }
    
    $avatar_path = $images_dir . '/default-avatar.png';
    
    // 如果默认头像不存在，创建一个简单的默认头像
    if (!file_exists($avatar_path)) {
        // 尝试复制WordPress默认头像或创建一个简单的头像
        $wp_avatar = ABSPATH . 'wp-includes/images/blank.png';
        if (file_exists($wp_avatar)) {
            copy($wp_avatar, $avatar_path);
        } else {
            // 创建一个简单的默认头像
            $img = imagecreatetruecolor(60, 60);
            $bg_color = imagecolorallocate($img, 240, 240, 240);
            $text_color = imagecolorallocate($img, 180, 180, 180);
            imagefilledrectangle($img, 0, 0, 60, 60, $bg_color);
            imagestring($img, 5, 10, 20, 'Avatar', $text_color);
            imagepng($img, $avatar_path);
            imagedestroy($img);
        }
    }
}
register_activation_hook(__FILE__, 'wfl_create_default_avatar');

// 添加清理缓存按钮
function wfl_add_clear_cache_button() {
    if (isset($_GET['page']) && $_GET['page'] == 'wfl-settings') {
        ?>
        <div class="wrap" style="margin-top: 20px;">
            <a href="<?php echo admin_url('options-general.php?page=wfl-settings&wfl_clear_cache=1'); ?>" class="button button-secondary">
                清理友链缓存
            </a>
            <p class="description">点击此按钮可以立即清理友链数据缓存，使更改立即生效。</p>
        </div>
        <?php
    }
    
    // 处理缓存清理请求
    if (isset($_GET['wfl_clear_cache'])) {
        delete_transient('wfl_friend_links_data');
        add_action('admin_notices', 'wfl_cache_cleared_notice');
    }
}
add_action('admin_footer', 'wfl_add_clear_cache_button');

// 显示缓存已清理的通知
function wfl_cache_cleared_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>友链缓存已成功清理！</p>
    </div>
    <?php
}

// 当友链或设置更改时自动清理缓存
function wfl_clear_cache_on_update($data, $postarr) {
    if ($data['post_type'] == 'link') {
        delete_transient('wfl_friend_links_data');
    }
    return $data;
}
add_filter('wp_insert_post_data', 'wfl_clear_cache_on_update', 10, 2);

// 当设置更改时清理缓存
function wfl_clear_cache_on_settings_change($old_value, $new_value) {
    delete_transient('wfl_friend_links_data');
}
add_action('update_option_wfl_cache_time', 'wfl_clear_cache_on_settings_change', 10, 2);
add_action('update_option_wfl_cards_per_row', 'wfl_clear_cache_on_settings_change', 10, 2);

// 添加链接更新时的钩子
function wfl_clear_cache_on_link_update() {
    delete_transient('wfl_friend_links_data');
}
add_action('add_link', 'wfl_clear_cache_on_link_update');
add_action('edit_link', 'wfl_clear_cache_on_link_update');
add_action('delete_link', 'wfl_clear_cache_on_link_update');
