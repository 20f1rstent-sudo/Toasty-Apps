<?php
/**
 * Uninstall ToastyApps Mobile Manager
 *
 * Fired when the plugin is uninstalled. Removes all data stored by the plugin.
 *
 * @package    ToastyApps_Mobile_Manager
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

/**
 * Remove custom database tables
 */
$tables = array(
    $wpdb->prefix . 'toastyapps_devices',
    $wpdb->prefix . 'toastyapps_notifications',
    $wpdb->prefix . 'toastyapps_api_logs',
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

/**
 * Remove plugin options
 */
$options = array(
    'toastyapps_api_key',
    'toastyapps_branding',
    'toastyapps_fcm_settings',
    'toastyapps_settings',
    'toastyapps_hero_image_id',
    'toastyapps_db_version',
    'toastyapps_last_updated',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

/**
 * Remove custom post types and their data
 */
$post_types = array( 'toastyapps_reel', 'toastyapps_location' );

foreach ( $post_types as $post_type ) {
    $posts = get_posts( array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    ) );

    foreach ( $posts as $post_id ) {
        wp_delete_post( $post_id, true );
    }
}

/**
 * Remove custom capabilities
 */
$admin_role = get_role( 'administrator' );

if ( $admin_role ) {
    $admin_role->remove_cap( 'manage_toastyapps' );
    $admin_role->remove_cap( 'edit_toastyapps_content' );
    $admin_role->remove_cap( 'send_toastyapps_notifications' );
}

/**
 * Clear any transients
 */
delete_transient( 'toastyapps_activated' );
delete_transient( 'toastyapps_dashboard_stats' );
delete_transient( 'toastyapps_device_count' );

/**
 * Clear scheduled cron events
 */
$scheduled_events = array(
    'toastyapps_cleanup_logs',
    'toastyapps_cleanup_inactive_devices',
    'toastyapps_scheduled_notification',
);

foreach ( $scheduled_events as $event ) {
    $timestamp = wp_next_scheduled( $event );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, $event );
    }
}

wp_clear_scheduled_hook( 'toastyapps_send_scheduled_notification' );
