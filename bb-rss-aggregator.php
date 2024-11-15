<?php
/*
Plugin Name: Bela Black's RSS Aggregator
Description: Fetches and publishes story posts from various RSS feeds.
Version: 1.3.1
Author: Bela Black
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

function ags_custom_rss_cron_schedule($schedules) {
    $schedules['every_6_hours'] = [
        'interval' => 21600,
        'display'  => __('Every 6 Hours', 'bb-rss-aggregator')
    ];
    $schedules['every_12_hours'] = [
        'interval' => 43200,
        'display'  => __('Every 12 Hours', 'bb-rss-aggregator')
    ];
    $schedules['daily'] = [
        'interval' => 86400,
        'display'  => __('Daily', 'bb-rss-aggregator')
    ];
    return $schedules;
}
add_filter('cron_schedules', 'ags_custom_rss_cron_schedule');

function ags_schedule_rss_cron() {
    $interval = get_option('ags_cron_interval', 'every_6_hours');
    if (!wp_next_scheduled('ags_fetch_rss_posts')) {
        wp_schedule_event(time(), $interval, 'ags_fetch_rss_posts');
    }
}
register_activation_hook(__FILE__, 'ags_schedule_rss_cron');

function ags_clear_rss_cron() {
    $timestamp = wp_next_scheduled('ags_fetch_rss_posts');
    wp_unschedule_event($timestamp, 'ags_fetch_rss_posts');
}
register_deactivation_hook(__FILE__, 'ags_clear_rss_cron');

add_action('update_option_ags_cron_interval', 'ags_reschedule_cron');
function ags_reschedule_cron() {
    ags_clear_rss_cron();
    ags_schedule_rss_cron();
}

function ags_fetch_rss_posts() {
    $feed_urls = get_option('ags_feed_urls', "https://theghostinmymachine.com/feed/\nhttps://feeds.feedburner.com/ParanormalGlobe\nhttps://www.yourghoststories.com/rss.php\nhttps://www.realghoststoriesonline.com/feed/\nhttps://www.thegravetalks.com/feed/\nhttps://www.hauntedrooms.com/feed");
    $feeds = explode("\n", $feed_urls);

    $max_posts = get_option('ags_max_posts', 10);
    $post_status = get_option('ags_post_status', 'draft');
    $default_category = get_option('ags_default_category', 'Uncategorized');
    $categories = get_option('ags_categories', []);

    if (!is_array($categories)) {
        $categories = [];
    }

    $post_count = 0;

    foreach ($feeds as $feed_url) {
        $feed_url = trim($feed_url);
        if (empty($feed_url)) continue;

        $rss = fetch_feed($feed_url);

        if (!is_wp_error($rss)) {
            $max_items = $rss->get_item_quantity(5);
            $rss_items = $rss->get_items(0, $max_items);

            foreach ($rss_items as $item) {
                if ($post_count >= $max_posts) break;

                $title = $item->get_title();
                $content = $item->get_content();
                $link = $item->get_permalink();

                if (!post_exists($title)) {
                    wp_insert_post([
                        'post_title'   => $title,
                        'post_content' => $content . "\n\nSource: " . $link,
                        'post_status'  => $post_status,
                        'post_author'  => 1,
                        'post_category' => !empty($categories) ? $categories : [get_cat_ID($default_category)],
                    ]);
                    $post_count++;
                }
            }
        }
    }
}
add_action('ags_fetch_rss_posts', 'ags_fetch_rss_posts');

function ags_log($message) {
    global $wp_filesystem;
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    WP_Filesystem();

    $file = plugin_dir_path(__FILE__) . 'ags_log.txt';
    $wp_filesystem->put_contents($file, gmdate("Y-m-d H:i:s") . " - " . $message . "\n", FS_CHMOD_FILE);
}

// Admin Settings Page
function ags_register_settings() {
    add_option('ags_max_posts', 10);
    add_option('ags_post_status', 'draft');
    add_option('ags_cron_interval', 'every_6_hours');
    add_option('ags_feed_urls', "https://theghostinmymachine.com/feed/\nhttps://feeds.feedburner.com/ParanormalGlobe\nhttps://www.yourghoststories.com/rss.php\nhttps://www.realghoststoriesonline.com/feed/\nhttps://www.thegravetalks.com/feed/\nhttps://www.hauntedrooms.com/feed");
    add_option('ags_categories', []);
    add_option('ags_default_category', 'Uncategorized');

    register_setting('ags_options_group', 'ags_max_posts', 'intval');
    register_setting('ags_options_group', 'ags_post_status', 'sanitize_text_field');
    register_setting('ags_options_group', 'ags_cron_interval', 'sanitize_text_field');
    register_setting('ags_options_group', 'ags_feed_urls', 'sanitize_textarea_field');
    register_setting('ags_options_group', 'ags_categories', 'sanitize_text_field');
    register_setting('ags_options_group', 'ags_default_category', 'sanitize_text_field');
}
add_action('admin_init', 'ags_register_settings');

function ags_register_options_page() {
    add_options_page("Bela's RSS Aggregator Settings", "Bela's RSS Aggregator", 'manage_options', 'ags-options', 'ags_options_page');
}
add_action('admin_menu', 'ags_register_options_page');

function ags_options_page() {
    ?>
    <div>
        <h2>Bela's RSS Aggregator Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('ags_options_group'); ?>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="ags_max_posts">Max Posts to Fetch</label></th>
                    <td><input type="number" id="ags_max_posts" name="ags_max_posts" value="<?php echo esc_attr(get_option('ags_max_posts')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="ags_post_status">Post Status</label></th>
                    <td>
                        <select id="ags_post_status" name="ags_post_status">
                            <option value="publish" <?php selected(get_option('ags_post_status'), 'publish'); ?>>Publish</option>
                            <option value="draft" <?php selected(get_option('ags_post_status'), 'draft'); ?>>Draft</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="ags_cron_interval">Cron Interval</label></th>
                    <td>
                        <select id="ags_cron_interval" name="ags_cron_interval">
                            <option value="every_6_hours" <?php selected(get_option('ags_cron_interval'), 'every_6_hours'); ?>>Every 6 Hours</option>
                            <option value="every_12_hours" <?php selected(get_option('ags_cron_interval'), 'every_12_hours'); ?>>Every 12 Hours</option>
                            <option value="daily" <?php selected(get_option('ags_cron_interval'), 'daily'); ?>>Daily</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="ags_feed_urls">RSS Feed URLs (one per line)</label></th>
                    <td><textarea id="ags_feed_urls" name="ags_feed_urls" rows="8" cols="50"><?php echo esc_textarea(get_option('ags_feed_urls')); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="ags_categories">Categories</label></th>
                    <td><input type="text" id="ags_categories" name="ags_categories" value="<?php echo esc_attr(implode(',', get_option('ags_categories', []))); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="ags_default_category">Default Category</label></th>
                    <td><input type="text" id="ags_default_category" name="ags_default_category" value="<?php echo esc_attr(get_option('ags_default_category', 'Uncategorized')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

