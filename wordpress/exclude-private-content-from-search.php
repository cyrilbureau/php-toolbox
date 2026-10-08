<?php

/**
 * Excludes private content from WordPress front-end search results.
 *
 * Forces the main front-end search query to return published content only,
 * including for logged-in users who would normally be allowed to see
 * private content.
 *
 * Version: 1.0.0
 *
 * Requirements:
 * - PHP 7.1+
 * - WordPress 4.0+
 *
 * Tested with:
 * - PHP 8.4.16
 * - WordPress 7.1.2
 *
 * @param WP_Query $query The WordPress query instance.
 */
if (!function_exists('lbdc_exclude_private_content_from_search')) {
    function lbdc_exclude_private_content_from_search(WP_Query $query): void
    {
        if (!is_admin() && $query->is_main_query() && $query->is_search()) {
            $query->set('post_status', 'publish');
        }
    }
    add_action('pre_get_posts', 'lbdc_exclude_private_content_from_search');
}
