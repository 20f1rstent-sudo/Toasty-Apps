<?php
/**
 * Locations admin page template.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check for action
$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
$location_id = isset( $_GET['location_id'] ) ? absint( $_GET['location_id'] ) : 0;

// Display any settings errors
settings_errors( 'toastyapps_messages' );

// If editing, get the location
$location = null;
if ( $location_id && in_array( $action, array( 'edit', 'add' ), true ) ) {
    $location = get_post( $location_id );
}
?>

<div class="wrap toastyapps-wrap">
    <h1>
        <span class="dashicons dashicons-location"></span>
        <?php
        if ( 'add' === $action || 'edit' === $action ) {
            echo $location_id ? esc_html__( 'Edit Location', 'toastyapps-mobile-manager' ) : esc_html__( 'Add New Location', 'toastyapps-mobile-manager' );
        } else {
            esc_html_e( 'Locations', 'toastyapps-mobile-manager' );
        }
        ?>
        <?php if ( 'list' === $action ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-locations&action=add' ) ); ?>" class="page-title-action">
                <?php esc_html_e( 'Add New', 'toastyapps-mobile-manager' ); ?>
            </a>
        <?php endif; ?>
    </h1>

    <p class="description">
        <?php esc_html_e( 'Manage your dispensary locations. These will be displayed in the mobile app with maps and contact information.', 'toastyapps-mobile-manager' ); ?>
    </p>

    <?php if ( 'add' === $action || 'edit' === $action ) : ?>
        <!-- Add/Edit Form -->
        <form method="post" action="">
            <?php wp_nonce_field( 'toastyapps_location_nonce', 'toastyapps_location_nonce' ); ?>
            <input type="hidden" name="location_id" value="<?php echo esc_attr( $location_id ); ?>">

            <div class="toastyapps-card">
                <h2><?php esc_html_e( 'Location Details', 'toastyapps-mobile-manager' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="location_name"><?php esc_html_e( 'Location Name', 'toastyapps-mobile-manager' ); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="location_name" id="location_name" class="regular-text"
                                   value="<?php echo $location ? esc_attr( $location->post_title ) : ''; ?>" required>
                            <p class="description"><?php esc_html_e( 'e.g., "Main Store" or "Downtown Location"', 'toastyapps-mobile-manager' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="location_address"><?php esc_html_e( 'Address', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <textarea name="location_address" id="location_address" class="large-text" rows="3"><?php echo $location ? esc_textarea( get_post_meta( $location_id, '_toastyapps_address', true ) ) : ''; ?></textarea>
                            <p class="description"><?php esc_html_e( 'Full street address', 'toastyapps-mobile-manager' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="location_is_primary"><?php esc_html_e( 'Primary Location', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="location_is_primary" id="location_is_primary" value="1"
                                       <?php checked( get_post_meta( $location_id, '_toastyapps_is_primary', true ), 1 ); ?>>
                                <?php esc_html_e( 'Set as primary location', 'toastyapps-mobile-manager' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'The primary location is shown first in the app', 'toastyapps-mobile-manager' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="toastyapps-card">
                <h2><?php esc_html_e( 'Contact Information', 'toastyapps-mobile-manager' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="location_phone"><?php esc_html_e( 'Phone Number', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <input type="tel" name="location_phone" id="location_phone" class="regular-text"
                                   value="<?php echo $location ? esc_attr( get_post_meta( $location_id, '_toastyapps_phone', true ) ) : ''; ?>"
                                   placeholder="(555) 123-4567">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="location_email"><?php esc_html_e( 'Email', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <input type="email" name="location_email" id="location_email" class="regular-text"
                                   value="<?php echo $location ? esc_attr( get_post_meta( $location_id, '_toastyapps_email', true ) ) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="location_website"><?php esc_html_e( 'Website', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <input type="url" name="location_website" id="location_website" class="regular-text"
                                   value="<?php echo $location ? esc_attr( get_post_meta( $location_id, '_toastyapps_website', true ) ) : ''; ?>"
                                   placeholder="https://">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="location_hours"><?php esc_html_e( 'Business Hours', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <textarea name="location_hours" id="location_hours" class="large-text" rows="5"
                                      placeholder="Mon-Fri: 9am - 9pm&#10;Sat: 10am - 8pm&#10;Sun: 11am - 6pm"><?php echo $location ? esc_textarea( get_post_meta( $location_id, '_toastyapps_hours', true ) ) : ''; ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="toastyapps-card">
                <h2><?php esc_html_e( 'Map & Embed', 'toastyapps-mobile-manager' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="location_embed_url"><?php esc_html_e( 'Embed URL', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <input type="url" name="location_embed_url" id="location_embed_url" class="large-text"
                                   value="<?php echo $location ? esc_attr( get_post_meta( $location_id, '_toastyapps_embed_url', true ) ) : ''; ?>"
                                   placeholder="https://www.google.com/maps/embed?pb=...">
                            <p class="description">
                                <?php esc_html_e( 'Paste a Google Maps embed URL or any iframe embed URL for this location.', 'toastyapps-mobile-manager' ); ?>
                                <a href="https://support.google.com/maps/answer/144361" target="_blank"><?php esc_html_e( 'How to get embed URL', 'toastyapps-mobile-manager' ); ?></a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Coordinates', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <div class="coordinates-fields">
                                <input type="number" name="location_latitude" id="location_latitude" class="small-text"
                                       step="any"
                                       value="<?php echo $location ? esc_attr( get_post_meta( $location_id, '_toastyapps_latitude', true ) ) : ''; ?>"
                                       placeholder="Latitude">
                                <input type="number" name="location_longitude" id="location_longitude" class="small-text"
                                       step="any"
                                       value="<?php echo $location ? esc_attr( get_post_meta( $location_id, '_toastyapps_longitude', true ) ) : ''; ?>"
                                       placeholder="Longitude">
                            </div>
                            <p class="description">
                                <?php esc_html_e( 'GPS coordinates for native map integration.', 'toastyapps-mobile-manager' ); ?>
                                <a href="https://www.latlong.net/" target="_blank"><?php esc_html_e( 'Find coordinates', 'toastyapps-mobile-manager' ); ?></a>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <p class="submit">
                <button type="submit" name="toastyapps_save_location" class="button button-primary button-large">
                    <span class="dashicons dashicons-saved"></span>
                    <?php echo $location_id ? esc_html__( 'Update Location', 'toastyapps-mobile-manager' ) : esc_html__( 'Save Location', 'toastyapps-mobile-manager' ); ?>
                </button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-locations' ) ); ?>" class="button">
                    <?php esc_html_e( 'Cancel', 'toastyapps-mobile-manager' ); ?>
                </a>
            </p>
        </form>

    <?php else : ?>
        <!-- Locations List -->
        <?php
        $locations = get_posts( array(
            'post_type'      => 'toastyapps_location',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ) );
        ?>

        <?php if ( ! empty( $locations ) ) : ?>
            <div class="toastyapps-card">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Address', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Phone', 'toastyapps-mobile-manager' ); ?></th>
                            <th style="width: 100px;"><?php esc_html_e( 'Primary', 'toastyapps-mobile-manager' ); ?></th>
                            <th style="width: 150px;"><?php esc_html_e( 'Actions', 'toastyapps-mobile-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $locations as $loc ) :
                            $address = get_post_meta( $loc->ID, '_toastyapps_address', true );
                            $phone = get_post_meta( $loc->ID, '_toastyapps_phone', true );
                            $is_primary = get_post_meta( $loc->ID, '_toastyapps_is_primary', true );
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $loc->post_title ); ?></strong>
                                </td>
                                <td><?php echo $address ? esc_html( wp_trim_words( $address, 10 ) ) : '-'; ?></td>
                                <td><?php echo $phone ? esc_html( $phone ) : '-'; ?></td>
                                <td>
                                    <?php if ( $is_primary ) : ?>
                                        <span class="badge badge-primary">
                                            <span class="dashicons dashicons-star-filled"></span>
                                            <?php esc_html_e( 'Primary', 'toastyapps-mobile-manager' ); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-locations&action=edit&location_id=' . $loc->ID ) ); ?>" class="button button-small">
                                        <span class="dashicons dashicons-edit"></span>
                                        <?php esc_html_e( 'Edit', 'toastyapps-mobile-manager' ); ?>
                                    </a>
                                    <button type="button" class="button button-small delete-location" data-id="<?php echo esc_attr( $loc->ID ); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="toastyapps-card empty-state">
                <span class="dashicons dashicons-location"></span>
                <h2><?php esc_html_e( 'No Locations Yet', 'toastyapps-mobile-manager' ); ?></h2>
                <p><?php esc_html_e( 'Add your dispensary locations to display them in the mobile app.', 'toastyapps-mobile-manager' ); ?></p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-locations&action=add' ) ); ?>" class="button button-primary button-large">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e( 'Add Your First Location', 'toastyapps-mobile-manager' ); ?>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Delete location
    $('.delete-location').on('click', function() {
        if (!confirm(toastyapps.strings.confirm_delete)) return;

        var $btn = $(this);
        var locationId = $btn.data('id');

        $.post(toastyapps.ajax_url, {
            action: 'toastyapps_delete_location',
            nonce: toastyapps.nonce,
            location_id: locationId
        }, function(response) {
            if (response.success) {
                $btn.closest('tr').fadeOut(function() { $(this).remove(); });
            }
        });
    });
});
</script>
