<?php
/**
 * Plugin Name: ToastyApps Mobile Manager
 * Plugin URI: https://toastyapps.com
 * Description: Manage your mobile app content directly from your WordPress admin dashboard. Perfect for dispensary owners who want to control their app's hero images, media reels, push notifications, and branding.
 * Version: 1.1.0
 * Author: Toasty Apps
 * Author URI: https://toastyapps.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: toastyapps-mobile-manager
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin Constants
 */
define( 'TOASTYAPPS_VERSION', '1.1.0' );
define( 'TOASTYAPPS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TOASTYAPPS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TOASTYAPPS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'TOASTYAPPS_DB_VERSION', '1.0.0' );

/**
 * Activation hook
 * Creates database tables and sets default options
 */
function toastyapps_activate() {
    require_once TOASTYAPPS_PLUGIN_DIR . 'includes/class-toastyapps-activator.php';
    ToastyApps_Activator::activate();
}
register_activation_hook( __FILE__, 'toastyapps_activate' );

/**
 * Deactivation hook
 * Cleans up scheduled tasks
 */
function toastyapps_deactivate() {
    require_once TOASTYAPPS_PLUGIN_DIR . 'includes/class-toastyapps-deactivator.php';
    ToastyApps_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'toastyapps_deactivate' );

/**
 * Load plugin text domain for translations
 */
function toastyapps_load_textdomain() {
    load_plugin_textdomain(
        'toastyapps-mobile-manager',
        false,
        dirname( TOASTYAPPS_PLUGIN_BASENAME ) . '/languages'
    );
}
add_action( 'plugins_loaded', 'toastyapps_load_textdomain' );

/**
 * Include required files
 */
require_once TOASTYAPPS_PLUGIN_DIR . 'includes/class-toastyapps-database.php';
require_once TOASTYAPPS_PLUGIN_DIR . 'includes/class-toastyapps-api.php';
require_once TOASTYAPPS_PLUGIN_DIR . 'includes/class-toastyapps-fcm.php';

/**
 * Initialize admin functionality
 */
function toastyapps_admin_init() {
    if ( is_admin() ) {
        require_once TOASTYAPPS_PLUGIN_DIR . 'admin/class-toastyapps-admin.php';
        new ToastyApps_Admin();
    }
}
add_action( 'plugins_loaded', 'toastyapps_admin_init' );

/**
 * Initialize REST API
 */
function toastyapps_rest_api_init() {
    $api = new ToastyApps_API();
    $api->register_routes();
}
add_action( 'rest_api_init', 'toastyapps_rest_api_init' );

/**
 * Register custom post types for media reels and locations
 */
function toastyapps_register_post_types() {
    // Media Reels Custom Post Type
    register_post_type( 'toastyapps_reel', array(
        'labels' => array(
            'name'               => __( 'Media Reels', 'toastyapps-mobile-manager' ),
            'singular_name'      => __( 'Media Reel', 'toastyapps-mobile-manager' ),
            'add_new'            => __( 'Add New Reel', 'toastyapps-mobile-manager' ),
            'add_new_item'       => __( 'Add New Media Reel', 'toastyapps-mobile-manager' ),
            'edit_item'          => __( 'Edit Media Reel', 'toastyapps-mobile-manager' ),
            'new_item'           => __( 'New Media Reel', 'toastyapps-mobile-manager' ),
            'view_item'          => __( 'View Media Reel', 'toastyapps-mobile-manager' ),
            'search_items'       => __( 'Search Media Reels', 'toastyapps-mobile-manager' ),
            'not_found'          => __( 'No media reels found', 'toastyapps-mobile-manager' ),
            'not_found_in_trash' => __( 'No media reels found in trash', 'toastyapps-mobile-manager' ),
        ),
        'public'             => false,
        'show_ui'            => false,
        'show_in_menu'       => false,
        'capability_type'    => 'post',
        'supports'           => array( 'title', 'thumbnail' ),
        'has_archive'        => false,
        'rewrite'            => false,
    ) );

    // Locations Custom Post Type
    register_post_type( 'toastyapps_location', array(
        'labels' => array(
            'name'               => __( 'Locations', 'toastyapps-mobile-manager' ),
            'singular_name'      => __( 'Location', 'toastyapps-mobile-manager' ),
            'add_new'            => __( 'Add New Location', 'toastyapps-mobile-manager' ),
            'add_new_item'       => __( 'Add New Location', 'toastyapps-mobile-manager' ),
            'edit_item'          => __( 'Edit Location', 'toastyapps-mobile-manager' ),
            'new_item'           => __( 'New Location', 'toastyapps-mobile-manager' ),
            'view_item'          => __( 'View Location', 'toastyapps-mobile-manager' ),
            'search_items'       => __( 'Search Locations', 'toastyapps-mobile-manager' ),
            'not_found'          => __( 'No locations found', 'toastyapps-mobile-manager' ),
            'not_found_in_trash' => __( 'No locations found in trash', 'toastyapps-mobile-manager' ),
        ),
        'public'             => false,
        'show_ui'            => false,
        'show_in_menu'       => false,
        'capability_type'    => 'post',
        'supports'           => array( 'title' ),
        'has_archive'        => false,
        'rewrite'            => false,
    ) );
}
add_action( 'init', 'toastyapps_register_post_types' );

/**
 * Add settings link to plugins page
 */
function toastyapps_plugin_action_links( $links ) {
    $settings_link = '<a href="' . admin_url( 'admin.php?page=toastyapps' ) . '">' .
                     __( 'Settings', 'toastyapps-mobile-manager' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . TOASTYAPPS_PLUGIN_BASENAME, 'toastyapps_plugin_action_links' );

/**
 * Check for database updates
 */
function toastyapps_check_db_update() {
    $current_db_version = get_option( 'toastyapps_db_version', '0' );

    if ( version_compare( $current_db_version, TOASTYAPPS_DB_VERSION, '<' ) ) {
        require_once TOASTYAPPS_PLUGIN_DIR . 'includes/class-toastyapps-activator.php';
        ToastyApps_Activator::create_tables();
        update_option( 'toastyapps_db_version', TOASTYAPPS_DB_VERSION );
    }
}
add_action( 'plugins_loaded', 'toastyapps_check_db_update' );

/**
 * AJAX handler for sending push notifications
 */
function toastyapps_ajax_send_notification() {
    // Verify nonce
    if ( ! check_ajax_referer( 'toastyapps_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'toastyapps-mobile-manager' ) ) );
    }

    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'toastyapps-mobile-manager' ) ) );
    }

    $title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
    $body = isset( $_POST['body'] ) ? sanitize_textarea_field( wp_unslash( $_POST['body'] ) ) : '';
    $image_url = isset( $_POST['image_url'] ) ? esc_url_raw( wp_unslash( $_POST['image_url'] ) ) : '';

    if ( empty( $title ) || empty( $body ) ) {
        wp_send_json_error( array( 'message' => __( 'Title and body are required.', 'toastyapps-mobile-manager' ) ) );
    }

    $fcm = new ToastyApps_FCM();
    $result = $fcm->send_notification( $title, $body, $image_url );

    if ( $result['success'] ) {
        wp_send_json_success( array(
            'message' => sprintf(
                __( 'Notification sent successfully to %d devices.', 'toastyapps-mobile-manager' ),
                $result['sent_count']
            ),
            'sent_count' => $result['sent_count']
        ) );
    } else {
        wp_send_json_error( array( 'message' => $result['message'] ) );
    }
}
add_action( 'wp_ajax_toastyapps_send_notification', 'toastyapps_ajax_send_notification' );

/**
 * AJAX handler for generating new API key
 */
function toastyapps_ajax_regenerate_api_key() {
    // Verify nonce
    if ( ! check_ajax_referer( 'toastyapps_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'toastyapps-mobile-manager' ) ) );
    }

    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'toastyapps-mobile-manager' ) ) );
    }

    $new_key = wp_generate_password( 32, false );
    update_option( 'toastyapps_api_key', $new_key );

    wp_send_json_success( array(
        'api_key' => $new_key,
        'message' => __( 'API key regenerated successfully.', 'toastyapps-mobile-manager' )
    ) );
}
add_action( 'wp_ajax_toastyapps_regenerate_api_key', 'toastyapps_ajax_regenerate_api_key' );

/**
 * AJAX handler for deleting media reels
 */
function toastyapps_ajax_delete_reel() {
    // Verify nonce
    if ( ! check_ajax_referer( 'toastyapps_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'toastyapps-mobile-manager' ) ) );
    }

    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'toastyapps-mobile-manager' ) ) );
    }

    $reel_id = isset( $_POST['reel_id'] ) ? absint( $_POST['reel_id'] ) : 0;

    if ( ! $reel_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid reel ID.', 'toastyapps-mobile-manager' ) ) );
    }

    $result = wp_delete_post( $reel_id, true );

    if ( $result ) {
        wp_send_json_success( array( 'message' => __( 'Reel deleted successfully.', 'toastyapps-mobile-manager' ) ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Failed to delete reel.', 'toastyapps-mobile-manager' ) ) );
    }
}
add_action( 'wp_ajax_toastyapps_delete_reel', 'toastyapps_ajax_delete_reel' );

/**
 * AJAX handler for deleting locations
 */
function toastyapps_ajax_delete_location() {
    // Verify nonce
    if ( ! check_ajax_referer( 'toastyapps_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'toastyapps-mobile-manager' ) ) );
    }

    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'toastyapps-mobile-manager' ) ) );
    }

    $location_id = isset( $_POST['location_id'] ) ? absint( $_POST['location_id'] ) : 0;

    if ( ! $location_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid location ID.', 'toastyapps-mobile-manager' ) ) );
    }

    $result = wp_delete_post( $location_id, true );

    if ( $result ) {
        wp_send_json_success( array( 'message' => __( 'Location deleted successfully.', 'toastyapps-mobile-manager' ) ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Failed to delete location.', 'toastyapps-mobile-manager' ) ) );
    }
}
add_action( 'wp_ajax_toastyapps_delete_location', 'toastyapps_ajax_delete_location' );

/**
 * AJAX handler for updating reel order
 */
function toastyapps_ajax_update_reel_order() {
    // Verify nonce
    if ( ! check_ajax_referer( 'toastyapps_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'toastyapps-mobile-manager' ) ) );
    }

    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'toastyapps-mobile-manager' ) ) );
    }

    $order = isset( $_POST['order'] ) ? array_map( 'absint', $_POST['order'] ) : array();

    if ( empty( $order ) ) {
        wp_send_json_error( array( 'message' => __( 'No order data provided.', 'toastyapps-mobile-manager' ) ) );
    }

    foreach ( $order as $position => $reel_id ) {
        wp_update_post( array(
            'ID'         => $reel_id,
            'menu_order' => $position,
        ) );
    }

    wp_send_json_success( array( 'message' => __( 'Order updated successfully.', 'toastyapps-mobile-manager' ) ) );
}
add_action( 'wp_ajax_toastyapps_update_reel_order', 'toastyapps_ajax_update_reel_order' );
