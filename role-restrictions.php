<?php
/**
 * Plugin Name: Role Restrictions
 * Description: Restrict Editor access in admin menu and URLs.
 * Version: 1.1
 * Author: mosaab
 * Text-domain: role-restrictions
 */

if (! defined('ABSPATH')) exit;

// Hide admin menu items for Editors
function rr_restrict_admin_menu() {
    $current_user = wp_get_current_user();

    if (in_array('editor', $current_user->roles)) {
        remove_menu_page('edit.php'); // Posts
        remove_menu_page('edit.php?post_type=page'); // Pages
        remove_menu_page('upload.php'); // Media (optional)
    }
}
add_action('admin_menu', 'rr_restrict_admin_menu', 999);

// Prevent access via URL directly
function rr_block_admin_pages() {
    $current_user = wp_get_current_user();

    if (in_array('editor', $current_user->roles)) {
        global $pagenow;

        $restricted_pages = [
            'edit.php', // Posts
            'edit.php?post_type=page', // Pages
            'upload.php', // Media
        ];

        $current_url = $pagenow;
        if (isset($_GET['post_type'])) {
            $current_url .= '?post_type=' . sanitize_text_field($_GET['post_type']);
        }

        if (in_array($current_url, $restricted_pages)) {
            wp_redirect(admin_url()); // Redirect to dashboard
            exit;
        }
    }
}
add_action('admin_init', 'rr_block_admin_pages');

// Remove editor capabilities on activation
register_activation_hook(__FILE__, function () {
    $role = get_role('editor');
    if ($role) {
        $role->remove_cap('create_posts');
        $role->remove_cap('edit_published_posts');
        $role->remove_cap('delete_posts');
        $role->remove_cap('delete_published_posts');
        $role->remove_cap('publish_posts');
    }
});

// Restore editor capabilities on deactivation
register_deactivation_hook(__FILE__, function () {
    $role = get_role('editor');
    if ($role) {
        $role->add_cap('create_posts');
        $role->add_cap('edit_published_posts');
        $role->add_cap('delete_posts');
        $role->add_cap('delete_published_posts');
        $role->add_cap('publish_posts');
    }
});
