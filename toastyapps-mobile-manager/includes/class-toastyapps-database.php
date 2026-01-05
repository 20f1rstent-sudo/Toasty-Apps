<?php
/**
 * Database operations for ToastyApps Mobile Manager.
 *
 * Handles all database interactions for devices, notifications, and logs.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/includes
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Database Class
 */
class ToastyApps_Database {

    /**
     * WordPress database object.
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Table names.
     *
     * @var array
     */
    private $tables;

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->tables = array(
            'devices'       => $wpdb->prefix . 'toastyapps_devices',
            'notifications' => $wpdb->prefix . 'toastyapps_notifications',
            'api_logs'      => $wpdb->prefix . 'toastyapps_api_logs',
        );
    }

    /**
     * ==========================================
     * DEVICE METHODS
     * ==========================================
     */

    /**
     * Register or update a device.
     *
     * @param array $device_data Device data.
     * @return int|false Device ID or false on failure.
     */
    public function register_device( $device_data ) {
        $defaults = array(
            'device_token' => '',
            'device_type'  => 'ios',
            'device_name'  => null,
            'app_version'  => null,
            'os_version'   => null,
            'is_active'    => 1,
            'last_active'  => current_time( 'mysql' ),
        );

        $data = wp_parse_args( $device_data, $defaults );

        // Check if device already exists
        $existing = $this->get_device_by_token( $data['device_token'] );

        if ( $existing ) {
            // Update existing device
            $this->wpdb->update(
                $this->tables['devices'],
                array(
                    'device_name' => $data['device_name'],
                    'app_version' => $data['app_version'],
                    'os_version'  => $data['os_version'],
                    'is_active'   => 1,
                    'last_active' => current_time( 'mysql' ),
                ),
                array( 'id' => $existing->id ),
                array( '%s', '%s', '%s', '%d', '%s' ),
                array( '%d' )
            );

            return $existing->id;
        }

        // Insert new device
        $result = $this->wpdb->insert(
            $this->tables['devices'],
            array(
                'device_token' => $data['device_token'],
                'device_type'  => $data['device_type'],
                'device_name'  => $data['device_name'],
                'app_version'  => $data['app_version'],
                'os_version'   => $data['os_version'],
                'is_active'    => $data['is_active'],
                'last_active'  => $data['last_active'],
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get device by token.
     *
     * @param string $token Device token.
     * @return object|null Device object or null.
     */
    public function get_device_by_token( $token ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tables['devices']} WHERE device_token = %s",
                $token
            )
        );
    }

    /**
     * Get all active devices.
     *
     * @param string $device_type Optional device type filter.
     * @return array Array of device objects.
     */
    public function get_active_devices( $device_type = null ) {
        $sql = "SELECT * FROM {$this->tables['devices']} WHERE is_active = 1";

        if ( $device_type ) {
            $sql .= $this->wpdb->prepare( " AND device_type = %s", $device_type );
        }

        $sql .= " ORDER BY last_active DESC";

        return $this->wpdb->get_results( $sql );
    }

    /**
     * Get all device tokens for push notifications.
     *
     * @param string $device_type Optional device type filter.
     * @return array Array of device tokens.
     */
    public function get_device_tokens( $device_type = null ) {
        $devices = $this->get_active_devices( $device_type );
        return wp_list_pluck( $devices, 'device_token' );
    }

    /**
     * Deactivate a device.
     *
     * @param string $token Device token.
     * @return bool Success status.
     */
    public function deactivate_device( $token ) {
        return (bool) $this->wpdb->update(
            $this->tables['devices'],
            array( 'is_active' => 0 ),
            array( 'device_token' => $token ),
            array( '%d' ),
            array( '%s' )
        );
    }

    /**
     * Get device count.
     *
     * @param bool $active_only Count only active devices.
     * @return int Device count.
     */
    public function get_device_count( $active_only = true ) {
        $sql = "SELECT COUNT(*) FROM {$this->tables['devices']}";

        if ( $active_only ) {
            $sql .= " WHERE is_active = 1";
        }

        return (int) $this->wpdb->get_var( $sql );
    }

    /**
     * Get devices with pagination.
     *
     * @param int   $page     Page number.
     * @param int   $per_page Items per page.
     * @param array $filters  Optional filters.
     * @return array Array with 'items' and 'total'.
     */
    public function get_devices_paginated( $page = 1, $per_page = 20, $filters = array() ) {
        $offset = ( $page - 1 ) * $per_page;

        $where = array( '1=1' );

        if ( isset( $filters['is_active'] ) ) {
            $where[] = $this->wpdb->prepare( "is_active = %d", $filters['is_active'] );
        }

        if ( isset( $filters['device_type'] ) ) {
            $where[] = $this->wpdb->prepare( "device_type = %s", $filters['device_type'] );
        }

        $where_sql = implode( ' AND ', $where );

        $items = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tables['devices']}
                WHERE $where_sql
                ORDER BY created_at DESC
                LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        $total = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->tables['devices']} WHERE $where_sql"
        );

        return array(
            'items' => $items,
            'total' => (int) $total,
        );
    }

    /**
     * ==========================================
     * NOTIFICATION METHODS
     * ==========================================
     */

    /**
     * Log a sent notification.
     *
     * @param array $notification_data Notification data.
     * @return int|false Notification ID or false on failure.
     */
    public function log_notification( $notification_data ) {
        $defaults = array(
            'title'         => '',
            'body'          => '',
            'image_url'     => null,
            'data'          => null,
            'sent_count'    => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'status'        => 'pending',
            'scheduled_at'  => null,
            'sent_at'       => null,
            'created_by'    => get_current_user_id(),
        );

        $data = wp_parse_args( $notification_data, $defaults );

        if ( is_array( $data['data'] ) ) {
            $data['data'] = wp_json_encode( $data['data'] );
        }

        $result = $this->wpdb->insert(
            $this->tables['notifications'],
            array(
                'title'         => $data['title'],
                'body'          => $data['body'],
                'image_url'     => $data['image_url'],
                'data'          => $data['data'],
                'sent_count'    => $data['sent_count'],
                'success_count' => $data['success_count'],
                'failure_count' => $data['failure_count'],
                'status'        => $data['status'],
                'scheduled_at'  => $data['scheduled_at'],
                'sent_at'       => $data['sent_at'],
                'created_by'    => $data['created_by'],
            ),
            array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d' )
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update notification after sending.
     *
     * @param int   $notification_id Notification ID.
     * @param array $update_data     Data to update.
     * @return bool Success status.
     */
    public function update_notification( $notification_id, $update_data ) {
        return (bool) $this->wpdb->update(
            $this->tables['notifications'],
            $update_data,
            array( 'id' => $notification_id )
        );
    }

    /**
     * Get notification history.
     *
     * @param int $limit Number of notifications to retrieve.
     * @return array Array of notification objects.
     */
    public function get_notification_history( $limit = 50 ) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT n.*, u.display_name as created_by_name
                FROM {$this->tables['notifications']} n
                LEFT JOIN {$this->wpdb->users} u ON n.created_by = u.ID
                ORDER BY n.created_at DESC
                LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Get notification stats.
     *
     * @return array Notification statistics.
     */
    public function get_notification_stats() {
        $total = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->tables['notifications']}"
        );

        $total_sent = $this->wpdb->get_var(
            "SELECT SUM(sent_count) FROM {$this->tables['notifications']} WHERE status = 'sent'"
        );

        $this_month = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->tables['notifications']}
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
            AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        );

        return array(
            'total'       => (int) $total,
            'total_sent'  => (int) $total_sent,
            'this_month'  => (int) $this_month,
        );
    }

    /**
     * ==========================================
     * API LOG METHODS
     * ==========================================
     */

    /**
     * Log an API request.
     *
     * @param array $log_data Log data.
     * @return int|false Log ID or false on failure.
     */
    public function log_api_request( $log_data ) {
        $defaults = array(
            'endpoint'      => '',
            'method'        => 'GET',
            'device_token'  => null,
            'ip_address'    => null,
            'user_agent'    => null,
            'response_code' => 200,
            'request_time'  => 0,
        );

        $data = wp_parse_args( $log_data, $defaults );

        $result = $this->wpdb->insert(
            $this->tables['api_logs'],
            array(
                'endpoint'      => $data['endpoint'],
                'method'        => $data['method'],
                'device_token'  => $data['device_token'],
                'ip_address'    => $data['ip_address'],
                'user_agent'    => $data['user_agent'],
                'response_code' => $data['response_code'],
                'request_time'  => $data['request_time'],
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%d', '%f' )
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get API usage stats.
     *
     * @param int $days Number of days to look back.
     * @return array API usage statistics.
     */
    public function get_api_stats( $days = 30 ) {
        $total_requests = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->tables['api_logs']}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );

        $requests_by_endpoint = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT endpoint, COUNT(*) as count
                FROM {$this->tables['api_logs']}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY endpoint
                ORDER BY count DESC",
                $days
            )
        );

        $requests_by_day = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT DATE(created_at) as date, COUNT(*) as count
                FROM {$this->tables['api_logs']}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC",
                $days
            )
        );

        return array(
            'total_requests'       => (int) $total_requests,
            'requests_by_endpoint' => $requests_by_endpoint,
            'requests_by_day'      => $requests_by_day,
        );
    }

    /**
     * Clean up old API logs.
     *
     * @param int $days Days to retain logs.
     * @return int Number of deleted rows.
     */
    public function cleanup_api_logs( $days = 30 ) {
        return $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM {$this->tables['api_logs']}
                WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
    }

    /**
     * Clean up inactive devices.
     *
     * @param int $days Days of inactivity.
     * @return int Number of deactivated devices.
     */
    public function cleanup_inactive_devices( $days = 90 ) {
        return $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$this->tables['devices']}
                SET is_active = 0
                WHERE last_active < DATE_SUB(NOW(), INTERVAL %d DAY)
                AND is_active = 1",
                $days
            )
        );
    }

    /**
     * ==========================================
     * DASHBOARD STATS
     * ==========================================
     */

    /**
     * Get dashboard statistics.
     *
     * @return array Dashboard statistics.
     */
    public function get_dashboard_stats() {
        // Check cache first
        $cached = get_transient( 'toastyapps_dashboard_stats' );
        if ( $cached ) {
            return $cached;
        }

        $stats = array(
            'active_devices'        => $this->get_device_count( true ),
            'total_devices'         => $this->get_device_count( false ),
            'notifications_sent'    => $this->get_notification_stats(),
            'api_stats'             => $this->get_api_stats( 30 ),
            'media_reels_count'     => $this->get_media_reels_count(),
            'locations_count'       => $this->get_locations_count(),
            'recent_devices'        => $this->get_recent_devices( 5 ),
        );

        // Cache for 5 minutes
        set_transient( 'toastyapps_dashboard_stats', $stats, 5 * MINUTE_IN_SECONDS );

        return $stats;
    }

    /**
     * Get media reels count.
     *
     * @return int Media reels count.
     */
    private function get_media_reels_count() {
        $counts = wp_count_posts( 'toastyapps_reel' );
        return isset( $counts->publish ) ? (int) $counts->publish : 0;
    }

    /**
     * Get locations count.
     *
     * @return int Locations count.
     */
    private function get_locations_count() {
        $counts = wp_count_posts( 'toastyapps_location' );
        return isset( $counts->publish ) ? (int) $counts->publish : 0;
    }

    /**
     * Get recent devices.
     *
     * @param int $limit Number of devices.
     * @return array Recent devices.
     */
    private function get_recent_devices( $limit = 5 ) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT device_type, device_name, app_version, created_at
                FROM {$this->tables['devices']}
                ORDER BY created_at DESC
                LIMIT %d",
                $limit
            )
        );
    }
}
