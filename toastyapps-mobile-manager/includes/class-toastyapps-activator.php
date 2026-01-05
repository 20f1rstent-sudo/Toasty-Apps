<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/includes
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin Activator Class
 */
class ToastyApps_Activator {

    /**
     * Plugin activation tasks.
     *
     * Creates database tables, sets default options, and initializes the plugin.
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
        self::create_capabilities();

        // Flush rewrite rules for custom post types
        flush_rewrite_rules();

        // Set activation flag for welcome message
        set_transient( 'toastyapps_activated', true, 30 );
    }

    /**
     * Create custom database tables.
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table for storing registered devices (FCM tokens)
        $devices_table = $wpdb->prefix . 'toastyapps_devices';

        $devices_sql = "CREATE TABLE IF NOT EXISTS $devices_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            device_token varchar(500) NOT NULL,
            device_type varchar(20) NOT NULL DEFAULT 'ios',
            device_name varchar(255) DEFAULT NULL,
            app_version varchar(20) DEFAULT NULL,
            os_version varchar(20) DEFAULT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            last_active datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY device_token (device_token(255)),
            KEY device_type (device_type),
            KEY is_active (is_active),
            KEY last_active (last_active)
        ) $charset_collate;";

        // Table for storing notification logs
        $notifications_table = $wpdb->prefix . 'toastyapps_notifications';

        $notifications_sql = "CREATE TABLE IF NOT EXISTS $notifications_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            body text NOT NULL,
            image_url varchar(500) DEFAULT NULL,
            data longtext DEFAULT NULL,
            sent_count int(11) NOT NULL DEFAULT 0,
            success_count int(11) NOT NULL DEFAULT 0,
            failure_count int(11) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'pending',
            scheduled_at datetime DEFAULT NULL,
            sent_at datetime DEFAULT NULL,
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_by (created_by),
            KEY sent_at (sent_at)
        ) $charset_collate;";

        // Table for API request logs (for analytics)
        $api_logs_table = $wpdb->prefix . 'toastyapps_api_logs';

        $api_logs_sql = "CREATE TABLE IF NOT EXISTS $api_logs_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            endpoint varchar(100) NOT NULL,
            method varchar(10) NOT NULL DEFAULT 'GET',
            device_token varchar(500) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent varchar(500) DEFAULT NULL,
            response_code int(11) NOT NULL DEFAULT 200,
            request_time float NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY endpoint (endpoint),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $devices_sql );
        dbDelta( $notifications_sql );
        dbDelta( $api_logs_sql );

        // Update database version
        update_option( 'toastyapps_db_version', TOASTYAPPS_DB_VERSION );
    }

    /**
     * Set default plugin options.
     */
    private static function set_default_options() {
        // Generate API key if not exists
        if ( ! get_option( 'toastyapps_api_key' ) ) {
            update_option( 'toastyapps_api_key', wp_generate_password( 32, false ) );
        }

        // Default branding options
        $default_branding = array(
            'primary_color'   => '#1a73e8',
            'secondary_color' => '#34a853',
            'accent_color'    => '#ea4335',
            'text_color'      => '#202124',
            'background_color'=> '#ffffff',
            'tagline'         => '',
            'app_name'        => get_bloginfo( 'name' ),
        );

        if ( ! get_option( 'toastyapps_branding' ) ) {
            update_option( 'toastyapps_branding', $default_branding );
        }

        // Default FCM settings
        $default_fcm = array(
            'server_key'  => '',
            'project_id'  => '',
            'enabled'     => false,
        );

        if ( ! get_option( 'toastyapps_fcm_settings' ) ) {
            update_option( 'toastyapps_fcm_settings', $default_fcm );
        }

        // Default general settings
        $default_settings = array(
            'api_rate_limit'     => 60, // requests per minute
            'enable_logging'     => true,
            'log_retention_days' => 30,
            'allowed_file_types' => array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'webm' ),
            'max_file_size_mb'   => 50,
        );

        if ( ! get_option( 'toastyapps_settings' ) ) {
            update_option( 'toastyapps_settings', $default_settings );
        }

        // Hero image placeholder
        if ( ! get_option( 'toastyapps_hero_image_id' ) ) {
            update_option( 'toastyapps_hero_image_id', 0 );
        }
    }

    /**
     * Create custom capabilities.
     */
    private static function create_capabilities() {
        $admin_role = get_role( 'administrator' );

        if ( $admin_role ) {
            $admin_role->add_cap( 'manage_toastyapps' );
            $admin_role->add_cap( 'edit_toastyapps_content' );
            $admin_role->add_cap( 'send_toastyapps_notifications' );
        }
    }
}
