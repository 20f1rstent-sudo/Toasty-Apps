<?php
/**
 * Settings admin page template.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get settings
$api_key = get_option( 'toastyapps_api_key', '' );
$fcm_settings = get_option( 'toastyapps_fcm_settings', array(
    'server_key' => '',
    'project_id' => '',
    'enabled'    => false,
) );
$settings = get_option( 'toastyapps_settings', array(
    'api_rate_limit'     => 60,
    'enable_logging'     => true,
    'log_retention_days' => 30,
    'max_file_size_mb'   => 50,
) );

// Get current tab
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'api';

// Display any settings errors
settings_errors( 'toastyapps_messages' );
?>

<div class="wrap toastyapps-wrap">
    <h1>
        <span class="dashicons dashicons-admin-generic"></span>
        <?php esc_html_e( 'Settings', 'toastyapps-mobile-manager' ); ?>
    </h1>

    <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-settings&tab=api' ) ); ?>"
           class="nav-tab <?php echo 'api' === $current_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'API Settings', 'toastyapps-mobile-manager' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-settings&tab=fcm' ) ); ?>"
           class="nav-tab <?php echo 'fcm' === $current_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'Push Notifications', 'toastyapps-mobile-manager' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-settings&tab=general' ) ); ?>"
           class="nav-tab <?php echo 'general' === $current_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'General', 'toastyapps-mobile-manager' ); ?>
        </a>
    </nav>

    <div class="tab-content">
        <?php if ( 'api' === $current_tab ) : ?>
            <!-- API Settings -->
            <div class="toastyapps-card">
                <h2><?php esc_html_e( 'API Configuration', 'toastyapps-mobile-manager' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'Use these credentials to connect your iOS app to this WordPress site.', 'toastyapps-mobile-manager' ); ?>
                </p>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'API Endpoint', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <code class="large-code"><?php echo esc_html( rest_url( 'toastyapps/v1/' ) ); ?></code>
                            <button type="button" class="button button-small copy-btn" data-copy="<?php echo esc_attr( rest_url( 'toastyapps/v1/' ) ); ?>">
                                <span class="dashicons dashicons-clipboard"></span>
                                <?php esc_html_e( 'Copy', 'toastyapps-mobile-manager' ); ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'API Key', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <div class="api-key-field">
                                <input type="text" id="api_key_display" class="regular-text code" value="<?php echo esc_attr( $api_key ); ?>" readonly>
                                <button type="button" class="button copy-btn" data-copy="<?php echo esc_attr( $api_key ); ?>">
                                    <span class="dashicons dashicons-clipboard"></span>
                                    <?php esc_html_e( 'Copy', 'toastyapps-mobile-manager' ); ?>
                                </button>
                                <button type="button" class="button" id="regenerate-api-key">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php esc_html_e( 'Regenerate', 'toastyapps-mobile-manager' ); ?>
                                </button>
                            </div>
                            <p class="description">
                                <?php esc_html_e( 'Include this key in the Authorization header of your API requests:', 'toastyapps-mobile-manager' ); ?>
                                <br>
                                <code>Authorization: Bearer <?php echo esc_html( substr( $api_key, 0, 8 ) ); ?>...</code>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="toastyapps-card">
                <h2><?php esc_html_e( 'Available Endpoints', 'toastyapps-mobile-manager' ); ?></h2>
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Endpoint', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Method', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'toastyapps-mobile-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>/config</code></td>
                            <td><span class="method-badge get">GET</span></td>
                            <td><?php esc_html_e( 'Get app configuration (branding, locations, colors)', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>/hero-image</code></td>
                            <td><span class="method-badge get">GET</span></td>
                            <td><?php esc_html_e( 'Get hero image URL and metadata', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>/media-reels</code></td>
                            <td><span class="method-badge get">GET</span></td>
                            <td><?php esc_html_e( 'Get list of all media reels', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>/media-reels/{id}</code></td>
                            <td><span class="method-badge get">GET</span></td>
                            <td><?php esc_html_e( 'Get single media reel by ID', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>/locations</code></td>
                            <td><span class="method-badge get">GET</span></td>
                            <td><?php esc_html_e( 'Get all locations', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>/branding</code></td>
                            <td><span class="method-badge get">GET</span></td>
                            <td><?php esc_html_e( 'Get branding configuration', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>/all-content</code></td>
                            <td><span class="method-badge get">GET</span></td>
                            <td><?php esc_html_e( 'Get all content in one request (config, hero, reels)', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>/register-device</code></td>
                            <td><span class="method-badge post">POST</span></td>
                            <td><?php esc_html_e( 'Register device for push notifications', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>/unregister-device</code></td>
                            <td><span class="method-badge post">POST</span></td>
                            <td><?php esc_html_e( 'Unregister device from push notifications', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>/heartbeat</code></td>
                            <td><span class="method-badge post">POST</span></td>
                            <td><?php esc_html_e( 'Update device last active timestamp', 'toastyapps-mobile-manager' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <?php elseif ( 'fcm' === $current_tab ) : ?>
            <!-- FCM Settings -->
            <form method="post" action="">
                <?php wp_nonce_field( 'toastyapps_fcm_nonce', 'toastyapps_fcm_nonce' ); ?>

                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'Firebase Cloud Messaging (FCM)', 'toastyapps-mobile-manager' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Configure Firebase Cloud Messaging to send push notifications to your mobile app users.', 'toastyapps-mobile-manager' ); ?>
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="fcm_enabled"><?php esc_html_e( 'Enable FCM', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fcm_enabled" id="fcm_enabled" value="1"
                                           <?php checked( $fcm_settings['enabled'], true ); ?>>
                                    <?php esc_html_e( 'Enable push notifications', 'toastyapps-mobile-manager' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="fcm_project_id"><?php esc_html_e( 'Firebase Project ID', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="fcm_project_id" id="fcm_project_id" class="regular-text"
                                       value="<?php echo esc_attr( $fcm_settings['project_id'] ); ?>"
                                       placeholder="my-firebase-project">
                                <p class="description">
                                    <?php esc_html_e( 'Found in Firebase Console > Project Settings', 'toastyapps-mobile-manager' ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="fcm_server_key"><?php esc_html_e( 'Server Key', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="password" name="fcm_server_key" id="fcm_server_key" class="large-text"
                                       value="<?php echo esc_attr( $fcm_settings['server_key'] ); ?>"
                                       placeholder="AAAA...">
                                <button type="button" class="button" id="toggle-server-key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    <?php esc_html_e( 'Found in Firebase Console > Project Settings > Cloud Messaging > Server Key', 'toastyapps-mobile-manager' ); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'Setup Instructions', 'toastyapps-mobile-manager' ); ?></h2>
                    <ol class="setup-steps">
                        <li>
                            <?php esc_html_e( 'Go to the', 'toastyapps-mobile-manager' ); ?>
                            <a href="https://console.firebase.google.com/" target="_blank">Firebase Console</a>
                        </li>
                        <li><?php esc_html_e( 'Create a new project or select an existing one', 'toastyapps-mobile-manager' ); ?></li>
                        <li><?php esc_html_e( 'Go to Project Settings (gear icon) > Cloud Messaging', 'toastyapps-mobile-manager' ); ?></li>
                        <li><?php esc_html_e( 'Copy the "Server key" (you may need to enable Cloud Messaging API)', 'toastyapps-mobile-manager' ); ?></li>
                        <li><?php esc_html_e( 'Paste the server key above and save', 'toastyapps-mobile-manager' ); ?></li>
                        <li><?php esc_html_e( 'Add Firebase SDK to your iOS app using the GoogleService-Info.plist file', 'toastyapps-mobile-manager' ); ?></li>
                    </ol>
                </div>

                <p class="submit">
                    <button type="submit" name="toastyapps_save_fcm" class="button button-primary button-large">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e( 'Save FCM Settings', 'toastyapps-mobile-manager' ); ?>
                    </button>
                </p>
            </form>

        <?php elseif ( 'general' === $current_tab ) : ?>
            <!-- General Settings -->
            <form method="post" action="">
                <?php wp_nonce_field( 'toastyapps_settings_nonce', 'toastyapps_settings_nonce' ); ?>

                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'General Settings', 'toastyapps-mobile-manager' ); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="api_rate_limit"><?php esc_html_e( 'API Rate Limit', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="number" name="api_rate_limit" id="api_rate_limit" class="small-text"
                                       value="<?php echo esc_attr( $settings['api_rate_limit'] ); ?>" min="10" max="1000">
                                <?php esc_html_e( 'requests per minute', 'toastyapps-mobile-manager' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="enable_logging"><?php esc_html_e( 'API Logging', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="enable_logging" id="enable_logging" value="1"
                                           <?php checked( $settings['enable_logging'], true ); ?>>
                                    <?php esc_html_e( 'Enable API request logging', 'toastyapps-mobile-manager' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Log API requests for analytics and debugging', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="log_retention_days"><?php esc_html_e( 'Log Retention', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="number" name="log_retention_days" id="log_retention_days" class="small-text"
                                       value="<?php echo esc_attr( $settings['log_retention_days'] ); ?>" min="7" max="365">
                                <?php esc_html_e( 'days', 'toastyapps-mobile-manager' ); ?>
                                <p class="description"><?php esc_html_e( 'Automatically delete logs older than this', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="max_file_size_mb"><?php esc_html_e( 'Max Upload Size', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="number" name="max_file_size_mb" id="max_file_size_mb" class="small-text"
                                       value="<?php echo esc_attr( $settings['max_file_size_mb'] ); ?>" min="5" max="100">
                                <?php esc_html_e( 'MB', 'toastyapps-mobile-manager' ); ?>
                                <p class="description">
                                    <?php
                                    printf(
                                        esc_html__( 'Maximum file size for video uploads. Server max: %s', 'toastyapps-mobile-manager' ),
                                        esc_html( size_format( wp_max_upload_size() ) )
                                    );
                                    ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <p class="submit">
                    <button type="submit" name="toastyapps_save_settings" class="button button-primary button-large">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e( 'Save Settings', 'toastyapps-mobile-manager' ); ?>
                    </button>
                </p>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Copy to clipboard
    $('.copy-btn').on('click', function() {
        var text = $(this).data('copy');
        if (!text) {
            text = $(this).prev('input').val();
        }

        navigator.clipboard.writeText(text).then(function() {
            // Show feedback
        });
    });

    // Regenerate API key
    $('#regenerate-api-key').on('click', function() {
        if (!confirm('<?php esc_html_e( 'Are you sure you want to regenerate the API key? Your mobile app will need to be updated with the new key.', 'toastyapps-mobile-manager' ); ?>')) {
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true);

        $.post(toastyapps.ajax_url, {
            action: 'toastyapps_regenerate_api_key',
            nonce: toastyapps.nonce
        }, function(response) {
            if (response.success) {
                $('#api_key_display').val(response.data.api_key);
                $('.copy-btn[data-copy]').data('copy', response.data.api_key);
                alert(response.data.message);
            }
            $btn.prop('disabled', false);
        });
    });

    // Toggle server key visibility
    $('#toggle-server-key').on('click', function() {
        var $input = $('#fcm_server_key');
        var type = $input.attr('type');
        $input.attr('type', type === 'password' ? 'text' : 'password');
        $(this).find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
    });
});
</script>
