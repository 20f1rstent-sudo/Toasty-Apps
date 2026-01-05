<?php
/**
 * Push Notifications admin page template.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get FCM settings
$fcm_settings = get_option( 'toastyapps_fcm_settings', array() );
$fcm_configured = ! empty( $fcm_settings['server_key'] ) && ! empty( $fcm_settings['enabled'] );

// Get database instance
$db = new ToastyApps_Database();
$device_count = $db->get_device_count( true );
$notification_history = $db->get_notification_history( 20 );

// Display any settings errors
settings_errors( 'toastyapps_messages' );
?>

<div class="wrap toastyapps-wrap">
    <h1>
        <span class="dashicons dashicons-megaphone"></span>
        <?php esc_html_e( 'Push Notifications', 'toastyapps-mobile-manager' ); ?>
    </h1>

    <?php if ( ! $fcm_configured ) : ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php esc_html_e( 'Firebase Cloud Messaging (FCM) is not configured.', 'toastyapps-mobile-manager' ); ?></strong>
                <?php esc_html_e( 'Please configure FCM in', 'toastyapps-mobile-manager' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-settings' ) ); ?>">
                    <?php esc_html_e( 'Settings', 'toastyapps-mobile-manager' ); ?>
                </a>
                <?php esc_html_e( 'to send push notifications.', 'toastyapps-mobile-manager' ); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="toastyapps-notifications-grid">
        <!-- Send Notification Form -->
        <div class="toastyapps-card">
            <h2><?php esc_html_e( 'Send Push Notification', 'toastyapps-mobile-manager' ); ?></h2>

            <div class="notification-stats">
                <div class="stat">
                    <span class="stat-number"><?php echo esc_html( number_format( $device_count ) ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Active Devices', 'toastyapps-mobile-manager' ); ?></span>
                </div>
            </div>

            <form id="notification-form" class="notification-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="notification_title"><?php esc_html_e( 'Title', 'toastyapps-mobile-manager' ); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="notification_title" id="notification_title" class="regular-text"
                                   maxlength="65" required>
                            <p class="description"><?php esc_html_e( 'Maximum 65 characters', 'toastyapps-mobile-manager' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="notification_body"><?php esc_html_e( 'Message', 'toastyapps-mobile-manager' ); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <textarea name="notification_body" id="notification_body" class="large-text" rows="4"
                                      maxlength="240" required></textarea>
                            <p class="description"><?php esc_html_e( 'Maximum 240 characters', 'toastyapps-mobile-manager' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="notification_image"><?php esc_html_e( 'Image (Optional)', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <input type="hidden" name="notification_image_id" id="notification_image_id" value="">
                            <input type="text" name="notification_image_url" id="notification_image_url" class="regular-text"
                                   placeholder="https://" readonly>
                            <button type="button" class="button" id="upload-notification-image">
                                <span class="dashicons dashicons-upload"></span>
                                <?php esc_html_e( 'Select Image', 'toastyapps-mobile-manager' ); ?>
                            </button>
                            <button type="button" class="button" id="remove-notification-image" style="display: none;">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                            <p class="description"><?php esc_html_e( 'Rich notification image (shown on expanded notifications)', 'toastyapps-mobile-manager' ); ?></p>
                        </td>
                    </tr>
                </table>

                <div class="notification-preview-section">
                    <h3><?php esc_html_e( 'Preview', 'toastyapps-mobile-manager' ); ?></h3>
                    <div class="notification-preview">
                        <div class="preview-notification">
                            <div class="preview-icon">
                                <span class="dashicons dashicons-smartphone"></span>
                            </div>
                            <div class="preview-content">
                                <div class="preview-app-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
                                <div class="preview-title" id="preview-title"><?php esc_html_e( 'Notification Title', 'toastyapps-mobile-manager' ); ?></div>
                                <div class="preview-body" id="preview-body"><?php esc_html_e( 'Notification message will appear here...', 'toastyapps-mobile-manager' ); ?></div>
                            </div>
                            <div class="preview-image" id="preview-image" style="display: none;">
                                <img src="" alt="">
                            </div>
                        </div>
                    </div>
                </div>

                <p class="submit">
                    <button type="submit" class="button button-primary button-large" id="send-notification-btn"
                            <?php echo ! $fcm_configured || $device_count < 1 ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-megaphone"></span>
                        <?php esc_html_e( 'Send Notification', 'toastyapps-mobile-manager' ); ?>
                    </button>
                    <?php if ( $device_count < 1 ) : ?>
                        <span class="description"><?php esc_html_e( 'No registered devices to send notifications to.', 'toastyapps-mobile-manager' ); ?></span>
                    <?php endif; ?>
                </p>
            </form>

            <div id="notification-result" class="notification-result" style="display: none;"></div>
        </div>

        <!-- Notification History -->
        <div class="toastyapps-card">
            <h2><?php esc_html_e( 'Notification History', 'toastyapps-mobile-manager' ); ?></h2>

            <?php if ( ! empty( $notification_history ) ) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Title', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Sent', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Success', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'toastyapps-mobile-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $notification_history as $notification ) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $notification->title ); ?></strong>
                                    <div class="notification-body-preview">
                                        <?php echo esc_html( wp_trim_words( $notification->body, 10 ) ); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $notification->status === 'sent' ? 'success' : 'warning'; ?>">
                                        <?php echo esc_html( $notification->sent_count ); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $success_rate = $notification->sent_count > 0
                                        ? round( ( $notification->success_count / $notification->sent_count ) * 100 )
                                        : 0;
                                    ?>
                                    <span class="success-rate <?php echo $success_rate >= 90 ? 'high' : ( $success_rate >= 50 ? 'medium' : 'low' ); ?>">
                                        <?php echo esc_html( $success_rate ); ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php if ( $notification->sent_at ) : ?>
                                        <?php echo esc_html( human_time_diff( strtotime( $notification->sent_at ), current_time( 'timestamp' ) ) ); ?> ago
                                    <?php else : ?>
                                        <?php echo esc_html( human_time_diff( strtotime( $notification->created_at ), current_time( 'timestamp' ) ) ); ?> ago
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="no-data"><?php esc_html_e( 'No notifications sent yet.', 'toastyapps-mobile-manager' ); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Live preview
    $('#notification_title').on('input', function() {
        var title = $(this).val() || 'Notification Title';
        $('#preview-title').text(title);
    });

    $('#notification_body').on('input', function() {
        var body = $(this).val() || 'Notification message will appear here...';
        $('#preview-body').text(body);
    });

    // Image upload
    $('#upload-notification-image').on('click', function(e) {
        e.preventDefault();
        var mediaUploader = wp.media({
            title: toastyapps.strings.select_image,
            button: { text: toastyapps.strings.use_image },
            library: { type: 'image' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#notification_image_id').val(attachment.id);
            $('#notification_image_url').val(attachment.url);
            $('#preview-image').show().find('img').attr('src', attachment.url);
            $('#remove-notification-image').show();
        });

        mediaUploader.open();
    });

    $('#remove-notification-image').on('click', function() {
        $('#notification_image_id').val('');
        $('#notification_image_url').val('');
        $('#preview-image').hide();
        $(this).hide();
    });

    // Send notification
    $('#notification-form').on('submit', function(e) {
        e.preventDefault();

        var $btn = $('#send-notification-btn');
        var originalText = $btn.html();

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span> ' + toastyapps.strings.sending);
        $('#notification-result').hide();

        $.post(toastyapps.ajax_url, {
            action: 'toastyapps_send_notification',
            nonce: toastyapps.nonce,
            title: $('#notification_title').val(),
            body: $('#notification_body').val(),
            image_url: $('#notification_image_url').val()
        }, function(response) {
            var $result = $('#notification-result');

            if (response.success) {
                $result.removeClass('error').addClass('success').html(
                    '<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message
                ).show();

                // Clear form
                $('#notification_title').val('');
                $('#notification_body').val('');
                $('#notification_image_id, #notification_image_url').val('');
                $('#preview-title').text('Notification Title');
                $('#preview-body').text('Notification message will appear here...');
                $('#preview-image').hide();
                $('#remove-notification-image').hide();
            } else {
                $result.removeClass('success').addClass('error').html(
                    '<span class="dashicons dashicons-warning"></span> ' + response.data.message
                ).show();
            }

            $btn.prop('disabled', false).html(originalText);
        }).fail(function() {
            $('#notification-result').removeClass('success').addClass('error').html(
                '<span class="dashicons dashicons-warning"></span> An error occurred. Please try again.'
            ).show();
            $btn.prop('disabled', false).html(originalText);
        });
    });
});
</script>
