<?php
/*
Plugin Name: Bela Black's RSS Aggregator
Description: Fetches and publishes story posts from various RSS feeds.
Version: 1.0
Author: Bela Black
*/

function ags_custom_rss_cron_schedule($schedules) {
    $schedules['every_6_hours'] = [
        'interval' => 21600, // 6 hours in seconds
        'display'  => __('Every 6 Hours')
    ];
    return $schedules;
}
add_filter('cron_schedules', 'ags_custom_rss_cron_schedule');


function ags_schedule_rss_cron() {
    if (!wp_next_scheduled('ags_fetch_rss_posts')) {
        wp_schedule_event(time(), 'every_6_hours', 'ags_fetch_rss_posts');
    }
}
register_activation_hook(__FILE__, 'ags_schedule_rss_cron');

function ags_clear_rss_cron() {
    $timestamp = wp_next_scheduled('ags_fetch_rss_posts');
    wp_unschedule_event($timestamp, 'ags_fetch_rss_posts');
}
register_deactivation_hook(__FILE__, 'ags_clear_rss_cron');

function ags_fetch_rss_posts() {
    $feeds = [
        'https://example1.com/feed',
        'https://example2.com/feed',
        // Add more RSS feed URLs here
    ];

    foreach ($feeds as $feed_url) {
        $rss = fetch_feed($feed_url);

        if (!is_wp_error($rss)) {
            $max_items = $rss->get_item_quantity(5); // Number of posts to fetch per feed
            $rss_items = $rss->get_items(0, $max_items);

            foreach ($rss_items as $item) {
                $title = $item->get_title();
                $content = $item->get_content();
                $link = $item->get_permalink();

                if (!post_exists($title)) {
                    wp_insert_post([
                        'post_title'   => $title,
                        'post_content' => $content . "\n\nSource: " . $link,
                        'post_status'  => 'publish',
                        'post_author'  => 1, // Replace with desired author ID
                        'post_category' => [get_cat_ID('Ghost Stories')],
                    ]);
                }
            }
        }
    }
}
add_action('ags_fetch_rss_posts', 'ags_fetch_rss_posts');

function ags_log($message) {
    $file = plugin_dir_path(__FILE__) . 'ags_log.txt';
    file_put_contents($file, date("Y-m-d H:i:s") . " - " . $message . "\n", FILE_APPEND);
}
