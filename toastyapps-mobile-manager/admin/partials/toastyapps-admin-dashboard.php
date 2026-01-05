<?php
/**
 * Dashboard admin page template.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get stats
$db = new ToastyApps_Database();
$stats = $db->get_dashboard_stats();
$api_key = get_option( 'toastyapps_api_key', '' );
$fcm_settings = get_option( 'toastyapps_fcm_settings', array() );
$fcm_configured = ! empty( $fcm_settings['server_key'] ) && ! empty( $fcm_settings['enabled'] );
?>

<div class="wrap toastyapps-wrap">
    <h1 class="toastyapps-title">
        <span class="dashicons dashicons-smartphone"></span>
        <?php esc_html_e( 'ToastyApps Mobile Manager', 'toastyapps-mobile-manager' ); ?>
    </h1>

    <div class="toastyapps-dashboard">
        <!-- Quick Stats -->
        <div class="toastyapps-stats-grid">
            <div class="toastyapps-stat-card">
                <div class="stat-icon active-devices">
                    <span class="dashicons dashicons-smartphone"></span>
                </div>
                <div class="stat-content">
                    <h3><?php echo esc_html( number_format( $stats['active_devices'] ) ); ?></h3>
                    <p><?php esc_html_e( 'Active Devices', 'toastyapps-mobile-manager' ); ?></p>
                </div>
            </div>

            <div class="toastyapps-stat-card">
                <div class="stat-icon media-reels">
                    <span class="dashicons dashicons-video-alt3"></span>
                </div>
                <div class="stat-content">
                    <h3><?php echo esc_html( number_format( $stats['media_reels_count'] ) ); ?></h3>
                    <p><?php esc_html_e( 'Media Reels', 'toastyapps-mobile-manager' ); ?></p>
                </div>
            </div>

            <div class="toastyapps-stat-card">
                <div class="stat-icon locations">
                    <span class="dashicons dashicons-location"></span>
                </div>
                <div class="stat-content">
                    <h3><?php echo esc_html( number_format( $stats['locations_count'] ) ); ?></h3>
                    <p><?php esc_html_e( 'Locations', 'toastyapps-mobile-manager' ); ?></p>
                </div>
            </div>

            <div class="toastyapps-stat-card">
                <div class="stat-icon notifications">
                    <span class="dashicons dashicons-megaphone"></span>
                </div>
                <div class="stat-content">
                    <h3><?php echo esc_html( number_format( $stats['notifications_sent']['total'] ) ); ?></h3>
                    <p><?php esc_html_e( 'Notifications Sent', 'toastyapps-mobile-manager' ); ?></p>
                </div>
            </div>
        </div>

        <div class="toastyapps-dashboard-columns">
            <!-- Left Column -->
            <div class="toastyapps-dashboard-column">
                <!-- Quick Actions -->
                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'Quick Actions', 'toastyapps-mobile-manager' ); ?></h2>
                    <div class="quick-actions">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-hero' ) ); ?>" class="quick-action-btn">
                            <span class="dashicons dashicons-format-image"></span>
                            <?php esc_html_e( 'Update Hero Image', 'toastyapps-mobile-manager' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-reels&action=add' ) ); ?>" class="quick-action-btn">
                            <span class="dashicons dashicons-video-alt3"></span>
                            <?php esc_html_e( 'Add Media Reel', 'toastyapps-mobile-manager' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-notifications' ) ); ?>" class="quick-action-btn">
                            <span class="dashicons dashicons-megaphone"></span>
                            <?php esc_html_e( 'Send Notification', 'toastyapps-mobile-manager' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-branding' ) ); ?>" class="quick-action-btn">
                            <span class="dashicons dashicons-art"></span>
                            <?php esc_html_e( 'Update Branding', 'toastyapps-mobile-manager' ); ?>
                        </a>
                    </div>
                </div>

                <!-- Recent Devices -->
                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'Recent Devices', 'toastyapps-mobile-manager' ); ?></h2>
                    <?php if ( ! empty( $stats['recent_devices'] ) ) : ?>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Device', 'toastyapps-mobile-manager' ); ?></th>
                                    <th><?php esc_html_e( 'Type', 'toastyapps-mobile-manager' ); ?></th>
                                    <th><?php esc_html_e( 'Registered', 'toastyapps-mobile-manager' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $stats['recent_devices'] as $device ) : ?>
                                    <tr>
                                        <td>
                                            <?php echo esc_html( $device->device_name ? $device->device_name : __( 'Unknown Device', 'toastyapps-mobile-manager' ) ); ?>
                                            <?php if ( $device->app_version ) : ?>
                                                <small>v<?php echo esc_html( $device->app_version ); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="device-type device-type-<?php echo esc_attr( $device->device_type ); ?>">
                                                <?php echo esc_html( ucfirst( $device->device_type ) ); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html( human_time_diff( strtotime( $device->created_at ), current_time( 'timestamp' ) ) ); ?> ago</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p class="no-data"><?php esc_html_e( 'No devices registered yet.', 'toastyapps-mobile-manager' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column -->
            <div class="toastyapps-dashboard-column">
                <!-- API Configuration -->
                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'API Configuration', 'toastyapps-mobile-manager' ); ?></h2>
                    <div class="api-config">
                        <div class="config-item">
                            <label><?php esc_html_e( 'API Endpoint', 'toastyapps-mobile-manager' ); ?></label>
                            <code><?php echo esc_html( rest_url( 'toastyapps/v1/' ) ); ?></code>
                        </div>
                        <div class="config-item">
                            <label><?php esc_html_e( 'API Key', 'toastyapps-mobile-manager' ); ?></label>
                            <div class="api-key-display">
                                <code id="api-key-value"><?php echo esc_html( $api_key ); ?></code>
                                <button type="button" class="button button-small copy-api-key" data-clipboard-target="#api-key-value">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                        </div>
                        <p class="description">
                            <?php esc_html_e( 'Use this API key in your iOS app to authenticate requests.', 'toastyapps-mobile-manager' ); ?>
                        </p>
                    </div>
                </div>

                <!-- Status Checks -->
                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'Status', 'toastyapps-mobile-manager' ); ?></h2>
                    <ul class="status-checks">
                        <li class="<?php echo $api_key ? 'status-ok' : 'status-error'; ?>">
                            <span class="dashicons <?php echo $api_key ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
                            <?php esc_html_e( 'API Key', 'toastyapps-mobile-manager' ); ?>
                            <?php if ( ! $api_key ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-settings' ) ); ?>">
                                    <?php esc_html_e( 'Configure', 'toastyapps-mobile-manager' ); ?>
                                </a>
                            <?php endif; ?>
                        </li>
                        <li class="<?php echo $fcm_configured ? 'status-ok' : 'status-warning'; ?>">
                            <span class="dashicons <?php echo $fcm_configured ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
                            <?php esc_html_e( 'Push Notifications (FCM)', 'toastyapps-mobile-manager' ); ?>
                            <?php if ( ! $fcm_configured ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-settings' ) ); ?>">
                                    <?php esc_html_e( 'Configure', 'toastyapps-mobile-manager' ); ?>
                                </a>
                            <?php endif; ?>
                        </li>
                        <li class="<?php echo get_option( 'toastyapps_hero_image_id' ) ? 'status-ok' : 'status-warning'; ?>">
                            <span class="dashicons <?php echo get_option( 'toastyapps_hero_image_id' ) ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
                            <?php esc_html_e( 'Hero Image', 'toastyapps-mobile-manager' ); ?>
                            <?php if ( ! get_option( 'toastyapps_hero_image_id' ) ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-hero' ) ); ?>">
                                    <?php esc_html_e( 'Add', 'toastyapps-mobile-manager' ); ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>

                <!-- API Stats -->
                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'API Usage (Last 30 Days)', 'toastyapps-mobile-manager' ); ?></h2>
                    <div class="api-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo esc_html( number_format( $stats['api_stats']['total_requests'] ) ); ?></span>
                            <span class="stat-label"><?php esc_html_e( 'Total Requests', 'toastyapps-mobile-manager' ); ?></span>
                        </div>
                    </div>
                    <?php if ( ! empty( $stats['api_stats']['requests_by_endpoint'] ) ) : ?>
                        <table class="widefat striped" style="margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Endpoint', 'toastyapps-mobile-manager' ); ?></th>
                                    <th><?php esc_html_e( 'Requests', 'toastyapps-mobile-manager' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( array_slice( $stats['api_stats']['requests_by_endpoint'], 0, 5 ) as $endpoint ) : ?>
                                    <tr>
                                        <td><code><?php echo esc_html( $endpoint->endpoint ); ?></code></td>
                                        <td><?php echo esc_html( number_format( $endpoint->count ) ); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
