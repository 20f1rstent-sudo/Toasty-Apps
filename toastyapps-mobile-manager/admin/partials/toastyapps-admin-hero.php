<?php
/**
 * Hero Image admin page template.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current hero image
$hero_image_id = get_option( 'toastyapps_hero_image_id', 0 );
$hero_image_url = $hero_image_id ? wp_get_attachment_url( $hero_image_id ) : '';

// Display any settings errors
settings_errors( 'toastyapps_messages' );
?>

<div class="wrap toastyapps-wrap">
    <h1>
        <span class="dashicons dashicons-format-image"></span>
        <?php esc_html_e( 'Hero Image', 'toastyapps-mobile-manager' ); ?>
    </h1>

    <p class="description">
        <?php esc_html_e( 'The hero image appears at the top of your app\'s home screen. Use a high-quality image that represents your brand.', 'toastyapps-mobile-manager' ); ?>
    </p>

    <form method="post" action="">
        <?php wp_nonce_field( 'toastyapps_hero_nonce', 'toastyapps_hero_nonce' ); ?>

        <div class="toastyapps-card">
            <h2><?php esc_html_e( 'Current Hero Image', 'toastyapps-mobile-manager' ); ?></h2>

            <div class="hero-image-wrapper">
                <div class="hero-image-preview" id="hero-image-preview">
                    <?php if ( $hero_image_url ) : ?>
                        <img src="<?php echo esc_url( $hero_image_url ); ?>" alt="Hero Image">
                    <?php else : ?>
                        <div class="placeholder">
                            <span class="dashicons dashicons-format-image"></span>
                            <p><?php esc_html_e( 'No hero image set', 'toastyapps-mobile-manager' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <input type="hidden" name="hero_image_id" id="hero_image_id" value="<?php echo esc_attr( $hero_image_id ); ?>">

                <div class="hero-image-actions">
                    <button type="button" class="button button-primary" id="upload-hero-image">
                        <span class="dashicons dashicons-upload"></span>
                        <?php echo $hero_image_id ? esc_html__( 'Change Image', 'toastyapps-mobile-manager' ) : esc_html__( 'Upload Image', 'toastyapps-mobile-manager' ); ?>
                    </button>

                    <?php if ( $hero_image_id ) : ?>
                        <button type="button" class="button button-secondary" id="remove-hero-image">
                            <span class="dashicons dashicons-trash"></span>
                            <?php esc_html_e( 'Remove Image', 'toastyapps-mobile-manager' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hero-image-tips">
                <h3><?php esc_html_e( 'Image Guidelines', 'toastyapps-mobile-manager' ); ?></h3>
                <ul>
                    <li><?php esc_html_e( 'Recommended size: 1200 x 600 pixels (2:1 ratio)', 'toastyapps-mobile-manager' ); ?></li>
                    <li><?php esc_html_e( 'Maximum file size: 5MB', 'toastyapps-mobile-manager' ); ?></li>
                    <li><?php esc_html_e( 'Supported formats: JPG, PNG, WebP', 'toastyapps-mobile-manager' ); ?></li>
                    <li><?php esc_html_e( 'Use high-contrast images for better visibility', 'toastyapps-mobile-manager' ); ?></li>
                </ul>
            </div>
        </div>

        <p class="submit">
            <button type="submit" name="toastyapps_save_hero" class="button button-primary button-large">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'Save Hero Image', 'toastyapps-mobile-manager' ); ?>
            </button>
        </p>
    </form>

    <!-- Phone Preview - Matches Actual iOS App Design -->
    <div class="toastyapps-card">
        <h2><?php esc_html_e( 'App Preview', 'toastyapps-mobile-manager' ); ?></h2>
        <?php
        $branding = get_option( 'toastyapps_branding', array() );
        $app_name = isset( $branding['app_name'] ) ? $branding['app_name'] : get_bloginfo( 'name' );
        $tagline = isset( $branding['tagline'] ) ? $branding['tagline'] : 'Free Cannabis Delivery';
        $logo_id = isset( $branding['logo_id'] ) ? $branding['logo_id'] : 0;
        $logo_url = $logo_id ? wp_get_attachment_url( $logo_id ) : '';

        // Get first location for badge
        $locations = get_posts( array(
            'post_type'      => 'toastyapps_location',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ) );
        $location_name = ! empty( $locations ) ? $locations[0]->post_title : 'Central Coast';
        ?>
        <div class="phone-preview">
            <div class="phone-frame">
                <div class="phone-notch"></div>
                <div class="phone-screen">
                    <!-- Hero Image -->
                    <div class="app-hero" id="phone-preview-hero">
                        <?php if ( $hero_image_url ) : ?>
                            <img src="<?php echo esc_url( $hero_image_url ); ?>" alt="Hero Preview">
                        <?php else : ?>
                            <div class="preview-placeholder">
                                <span class="dashicons dashicons-format-image"></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- App Icon (Overlapping) -->
                    <div class="app-icon-wrapper">
                        <div class="app-icon">
                            <?php if ( $logo_url ) : ?>
                                <img src="<?php echo esc_url( $logo_url ); ?>" alt="App Icon">
                            <?php else : ?>
                                <span class="dashicons dashicons-store"></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Content Area -->
                    <div class="app-content-preview">
                        <div class="preview-app-name" id="preview-app-name"><?php echo esc_html( $app_name ); ?></div>
                        <div class="preview-tagline" id="preview-tagline"><?php echo esc_html( $tagline ); ?></div>

                        <div class="preview-location-badge">
                            <span class="dashicons dashicons-location"></span>
                            <span><?php echo esc_html( $location_name ); ?></span>
                        </div>

                        <div class="preview-buttons">
                            <button class="preview-btn primary">Shop Now</button>
                            <button class="preview-btn secondary">Call Now 805-310-1078</button>
                        </div>

                        <div class="preview-social-icons">
                            <span class="social-icon instagram">●</span>
                            <span class="social-icon facebook">●</span>
                            <span class="social-icon">✕</span>
                        </div>

                        <div class="preview-license">C10-0001383-LIC</div>
                    </div>

                    <!-- Tab Bar -->
                    <div class="preview-tab-bar">
                        <div class="preview-tab active">
                            <span class="dashicons dashicons-admin-home"></span>
                            <span>Home</span>
                        </div>
                        <div class="preview-tab">
                            <span class="dashicons dashicons-cart"></span>
                            <span>Shop</span>
                        </div>
                        <div class="preview-tab">
                            <span class="dashicons dashicons-video-alt3"></span>
                            <span>Media</span>
                        </div>
                        <div class="preview-tab">
                            <span class="dashicons dashicons-games"></span>
                            <span>Arcade</span>
                        </div>
                        <div class="preview-tab">
                            <span class="dashicons dashicons-email"></span>
                            <span>Inbox</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Upload hero image
    $('#upload-hero-image').on('click', function(e) {
        e.preventDefault();

        var mediaUploader = wp.media({
            title: toastyapps.strings.select_image,
            button: {
                text: toastyapps.strings.use_image
            },
            library: {
                type: 'image'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#hero_image_id').val(attachment.id);
            $('#hero-image-preview').html('<img src="' + attachment.url + '" alt="Hero Image">');
            $('#phone-preview-hero').html('<img src="' + attachment.url + '" alt="Hero Preview">');

            // Update button text and add remove button
            $('#upload-hero-image').html('<span class="dashicons dashicons-upload"></span> Change Image');
            if ($('#remove-hero-image').length === 0) {
                $('#upload-hero-image').after('<button type="button" class="button button-secondary" id="remove-hero-image"><span class="dashicons dashicons-trash"></span> Remove Image</button>');
            }
        });

        mediaUploader.open();
    });

    // Remove hero image
    $(document).on('click', '#remove-hero-image', function(e) {
        e.preventDefault();
        $('#hero_image_id').val('');
        $('#hero-image-preview').html('<div class="placeholder"><span class="dashicons dashicons-format-image"></span><p>No hero image set</p></div>');
        $('#phone-preview-hero').html('<div class="preview-placeholder"><span class="dashicons dashicons-format-image"></span></div>');
        $('#upload-hero-image').html('<span class="dashicons dashicons-upload"></span> Upload Image');
        $(this).remove();
    });
});
</script>
