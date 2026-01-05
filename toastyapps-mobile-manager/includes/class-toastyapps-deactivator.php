<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/includes
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin Deactivator Class
 */
class ToastyApps_Deactivator {

    /**
     * Plugin deactivation tasks.
     *
     * Cleans up scheduled events and transients.
     * Does NOT remove data (use uninstall.php for that).
     */
    public static function deactivate() {
        // Clear any scheduled cron events
        self::clear_scheduled_events();

        // Clear transients
        self::clear_transients();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Clear scheduled cron events.
     */
    private static function clear_scheduled_events() {
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

        // Clear all scheduled notifications
        wp_clear_scheduled_hook( 'toastyapps_send_scheduled_notification' );
    }

    /**
     * Clear plugin transients.
     */
    private static function clear_transients() {
        delete_transient( 'toastyapps_activated' );
        delete_transient( 'toastyapps_dashboard_stats' );
        delete_transient( 'toastyapps_device_count' );
    }
}
