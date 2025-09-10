<?php

/**
 * Plugin Name: Role Restrictions
 * Description: A plugin to restrict access based on user roles.
 * Version: 1.0
 * Author: mosaab
 * text-domain: role-restrictions
 */

if (! defined('ABSPATH')) {
    exit;
}


add_filter('excerpt_length', 'custom_excerpt_length',);

function custom_excerpt_length($length)
{
    return 10;
}




// Restricts admin menu items based on user role
function restrict_admin_menu()
{
    $current_user = wp_get_current_user();

    // Hide specific menu items for Editors
    if (in_array('editor', $current_user->roles)) {
        //remove page menu
        remove_menu_page('edit.php?post_type=page');
        // remove_menu_page('edit.php');

    }
}

add_action('admin_menu', 'restrict_admin_menu');


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
