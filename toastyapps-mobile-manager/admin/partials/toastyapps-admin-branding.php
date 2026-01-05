<?php
/**
 * Branding admin page template.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get branding settings
$branding = get_option( 'toastyapps_branding', array() );
$defaults = array(
    'app_name'         => get_bloginfo( 'name' ),
    'tagline'          => '',
    'primary_color'    => '#1a73e8',
    'secondary_color'  => '#34a853',
    'accent_color'     => '#ea4335',
    'text_color'       => '#202124',
    'background_color' => '#ffffff',
    'logo_id'          => 0,
);
$branding = wp_parse_args( $branding, $defaults );

// Display any settings errors
settings_errors( 'toastyapps_messages' );
?>

<div class="wrap toastyapps-wrap">
    <h1>
        <span class="dashicons dashicons-art"></span>
        <?php esc_html_e( 'Branding', 'toastyapps-mobile-manager' ); ?>
    </h1>

    <p class="description">
        <?php esc_html_e( 'Customize the look and feel of your mobile app. These settings control colors, branding, and visual identity.', 'toastyapps-mobile-manager' ); ?>
    </p>

    <form method="post" action="">
        <?php wp_nonce_field( 'toastyapps_branding_nonce', 'toastyapps_branding_nonce' ); ?>

        <div class="toastyapps-branding-grid">
            <!-- Branding Settings -->
            <div class="toastyapps-branding-settings">
                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'App Identity', 'toastyapps-mobile-manager' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="app_name"><?php esc_html_e( 'App Name', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="app_name" id="app_name" class="regular-text"
                                       value="<?php echo esc_attr( $branding['app_name'] ); ?>">
                                <p class="description"><?php esc_html_e( 'The name displayed in the app header', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="tagline"><?php esc_html_e( 'Tagline', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="tagline" id="tagline" class="regular-text"
                                       value="<?php echo esc_attr( $branding['tagline'] ); ?>"
                                       placeholder="Your tagline here...">
                                <p class="description"><?php esc_html_e( 'A short description or slogan', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e( 'Logo', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <?php
                                $logo_url = $branding['logo_id'] ? wp_get_attachment_url( $branding['logo_id'] ) : '';
                                ?>
                                <input type="hidden" name="logo_id" id="logo_id" value="<?php echo esc_attr( $branding['logo_id'] ); ?>">

                                <div class="media-upload-field">
                                    <div class="media-preview logo-preview" id="logo-preview">
                                        <?php if ( $logo_url ) : ?>
                                            <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo">
                                        <?php else : ?>
                                            <div class="placeholder">
                                                <span class="dashicons dashicons-format-image"></span>
                                                <p><?php esc_html_e( 'No logo', 'toastyapps-mobile-manager' ); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="button" id="upload-logo">
                                        <span class="dashicons dashicons-upload"></span>
                                        <?php echo $branding['logo_id'] ? esc_html__( 'Change Logo', 'toastyapps-mobile-manager' ) : esc_html__( 'Upload Logo', 'toastyapps-mobile-manager' ); ?>
                                    </button>
                                    <?php if ( $branding['logo_id'] ) : ?>
                                        <button type="button" class="button" id="remove-logo">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <p class="description"><?php esc_html_e( 'Recommended size: 200x200px. PNG with transparent background works best.', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="toastyapps-card">
                    <h2><?php esc_html_e( 'Colors', 'toastyapps-mobile-manager' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="primary_color"><?php esc_html_e( 'Primary Color', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="primary_color" id="primary_color" class="color-picker"
                                       value="<?php echo esc_attr( $branding['primary_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Main brand color used for buttons and highlights', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="secondary_color"><?php esc_html_e( 'Secondary Color', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="secondary_color" id="secondary_color" class="color-picker"
                                       value="<?php echo esc_attr( $branding['secondary_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Secondary accent color', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="accent_color"><?php esc_html_e( 'Accent Color', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="accent_color" id="accent_color" class="color-picker"
                                       value="<?php echo esc_attr( $branding['accent_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Used for notifications and alerts', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="text_color"><?php esc_html_e( 'Text Color', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="text_color" id="text_color" class="color-picker"
                                       value="<?php echo esc_attr( $branding['text_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Main text color', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="background_color"><?php esc_html_e( 'Background Color', 'toastyapps-mobile-manager' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="background_color" id="background_color" class="color-picker"
                                       value="<?php echo esc_attr( $branding['background_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'App background color', 'toastyapps-mobile-manager' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="toastyapps-branding-preview">
                <div class="toastyapps-card sticky">
                    <h2><?php esc_html_e( 'Live Preview', 'toastyapps-mobile-manager' ); ?></h2>
                    <div class="phone-preview">
                        <div class="phone-frame">
                            <div class="phone-notch"></div>
                            <div class="phone-screen" id="preview-screen" style="background-color: <?php echo esc_attr( $branding['background_color'] ); ?>;">
                                <div class="app-header" id="preview-header" style="background-color: <?php echo esc_attr( $branding['primary_color'] ); ?>;">
                                    <h4 id="preview-app-name" style="color: #fff;"><?php echo esc_html( $branding['app_name'] ); ?></h4>
                                    <?php if ( $branding['tagline'] ) : ?>
                                        <small id="preview-tagline" style="color: rgba(255,255,255,0.8);"><?php echo esc_html( $branding['tagline'] ); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="app-content-preview">
                                    <div class="preview-card" style="background: linear-gradient(135deg, <?php echo esc_attr( $branding['primary_color'] ); ?>, <?php echo esc_attr( $branding['secondary_color'] ); ?>);">
                                        <span style="color: #fff;">Featured</span>
                                    </div>
                                    <div class="preview-buttons">
                                        <button class="preview-btn primary" style="background-color: <?php echo esc_attr( $branding['primary_color'] ); ?>; color: #fff;">Primary</button>
                                        <button class="preview-btn secondary" style="background-color: <?php echo esc_attr( $branding['secondary_color'] ); ?>; color: #fff;">Secondary</button>
                                    </div>
                                    <div class="preview-text" style="color: <?php echo esc_attr( $branding['text_color'] ); ?>;">
                                        <p>Sample text content</p>
                                    </div>
                                    <div class="preview-alert" style="background-color: <?php echo esc_attr( $branding['accent_color'] ); ?>; color: #fff;">
                                        Alert/Notification
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="submit">
            <button type="submit" name="toastyapps_save_branding" class="button button-primary button-large">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'Save Branding', 'toastyapps-mobile-manager' ); ?>
            </button>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize color pickers
    $('.color-picker').wpColorPicker({
        change: function(event, ui) {
            updatePreview();
        }
    });

    // Logo upload
    $('#upload-logo').on('click', function(e) {
        e.preventDefault();
        var mediaUploader = wp.media({
            title: toastyapps.strings.select_image,
            button: { text: toastyapps.strings.use_image },
            library: { type: 'image' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#logo_id').val(attachment.id);
            $('#logo-preview').html('<img src="' + attachment.url + '" alt="Logo">');
            if ($('#remove-logo').length === 0) {
                $('#upload-logo').after('<button type="button" class="button" id="remove-logo"><span class="dashicons dashicons-trash"></span></button>');
            }
        });

        mediaUploader.open();
    });

    $(document).on('click', '#remove-logo', function() {
        $('#logo_id').val('');
        $('#logo-preview').html('<div class="placeholder"><span class="dashicons dashicons-format-image"></span><p>No logo</p></div>');
        $(this).remove();
    });

    // Live preview updates
    $('#app_name').on('input', function() {
        $('#preview-app-name').text($(this).val() || 'App Name');
    });

    $('#tagline').on('input', function() {
        var tagline = $(this).val();
        if (tagline) {
            if ($('#preview-tagline').length === 0) {
                $('#preview-app-name').after('<small id="preview-tagline" style="color: rgba(255,255,255,0.8);"></small>');
            }
            $('#preview-tagline').text(tagline);
        } else {
            $('#preview-tagline').remove();
        }
    });

    function updatePreview() {
        var primaryColor = $('#primary_color').val();
        var secondaryColor = $('#secondary_color').val();
        var accentColor = $('#accent_color').val();
        var textColor = $('#text_color').val();
        var backgroundColor = $('#background_color').val();

        $('#preview-screen').css('background-color', backgroundColor);
        $('#preview-header').css('background-color', primaryColor);
        $('.preview-card').css('background', 'linear-gradient(135deg, ' + primaryColor + ', ' + secondaryColor + ')');
        $('.preview-btn.primary').css('background-color', primaryColor);
        $('.preview-btn.secondary').css('background-color', secondaryColor);
        $('.preview-text').css('color', textColor);
        $('.preview-alert').css('background-color', accentColor);
    }
});
</script>
