<?php
/**
 * Media Reels admin page template.
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
$reel_id = isset( $_GET['reel_id'] ) ? absint( $_GET['reel_id'] ) : 0;

// Display any settings errors
settings_errors( 'toastyapps_messages' );

// If editing, get the reel
$reel = null;
if ( $reel_id && in_array( $action, array( 'edit', 'add' ), true ) ) {
    $reel = get_post( $reel_id );
}
?>

<div class="wrap toastyapps-wrap">
    <h1>
        <span class="dashicons dashicons-video-alt3"></span>
        <?php
        if ( 'add' === $action || 'edit' === $action ) {
            echo $reel_id ? esc_html__( 'Edit Media Reel', 'toastyapps-mobile-manager' ) : esc_html__( 'Add New Media Reel', 'toastyapps-mobile-manager' );
        } else {
            esc_html_e( 'Media Reels', 'toastyapps-mobile-manager' );
        }
        ?>
        <?php if ( 'list' === $action ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-reels&action=add' ) ); ?>" class="page-title-action">
                <?php esc_html_e( 'Add New', 'toastyapps-mobile-manager' ); ?>
            </a>
        <?php endif; ?>
    </h1>

    <?php if ( 'add' === $action || 'edit' === $action ) : ?>
        <!-- Add/Edit Form -->
        <form method="post" action="">
            <?php wp_nonce_field( 'toastyapps_reel_nonce', 'toastyapps_reel_nonce' ); ?>
            <input type="hidden" name="reel_id" value="<?php echo esc_attr( $reel_id ); ?>">

            <div class="toastyapps-card">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="reel_title"><?php esc_html_e( 'Title', 'toastyapps-mobile-manager' ); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="reel_title" id="reel_title" class="regular-text"
                                   value="<?php echo $reel ? esc_attr( $reel->post_title ) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reel_description"><?php esc_html_e( 'Description', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <textarea name="reel_description" id="reel_description" class="large-text" rows="4"><?php echo $reel ? esc_textarea( get_post_meta( $reel_id, '_toastyapps_description', true ) ) : ''; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Video', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <?php
                            $video_id = $reel ? get_post_meta( $reel_id, '_toastyapps_video_id', true ) : 0;
                            $video_url = $video_id ? wp_get_attachment_url( $video_id ) : '';
                            ?>
                            <input type="hidden" name="reel_video_id" id="reel_video_id" value="<?php echo esc_attr( $video_id ); ?>">

                            <div class="media-upload-field">
                                <div class="media-preview" id="video-preview">
                                    <?php if ( $video_url ) : ?>
                                        <video src="<?php echo esc_url( $video_url ); ?>" controls width="320"></video>
                                    <?php else : ?>
                                        <div class="placeholder">
                                            <span class="dashicons dashicons-video-alt3"></span>
                                            <p><?php esc_html_e( 'No video selected', 'toastyapps-mobile-manager' ); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="button" id="upload-reel-video">
                                    <span class="dashicons dashicons-upload"></span>
                                    <?php echo $video_id ? esc_html__( 'Change Video', 'toastyapps-mobile-manager' ) : esc_html__( 'Upload Video', 'toastyapps-mobile-manager' ); ?>
                                </button>
                                <?php if ( $video_id ) : ?>
                                    <button type="button" class="button" id="remove-reel-video">
                                        <span class="dashicons dashicons-trash"></span>
                                        <?php esc_html_e( 'Remove', 'toastyapps-mobile-manager' ); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <p class="description"><?php esc_html_e( 'Supported formats: MP4, MOV, WebM. Max size: 50MB', 'toastyapps-mobile-manager' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Thumbnail', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <?php
                            $thumbnail_id = $reel ? get_post_meta( $reel_id, '_toastyapps_thumbnail_id', true ) : 0;
                            $thumbnail_url = $thumbnail_id ? wp_get_attachment_url( $thumbnail_id ) : '';
                            ?>
                            <input type="hidden" name="reel_thumbnail_id" id="reel_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>">

                            <div class="media-upload-field">
                                <div class="media-preview thumbnail-preview" id="thumbnail-preview">
                                    <?php if ( $thumbnail_url ) : ?>
                                        <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="Thumbnail">
                                    <?php else : ?>
                                        <div class="placeholder">
                                            <span class="dashicons dashicons-format-image"></span>
                                            <p><?php esc_html_e( 'No thumbnail', 'toastyapps-mobile-manager' ); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="button" id="upload-reel-thumbnail">
                                    <span class="dashicons dashicons-upload"></span>
                                    <?php echo $thumbnail_id ? esc_html__( 'Change Thumbnail', 'toastyapps-mobile-manager' ) : esc_html__( 'Upload Thumbnail', 'toastyapps-mobile-manager' ); ?>
                                </button>
                                <?php if ( $thumbnail_id ) : ?>
                                    <button type="button" class="button" id="remove-reel-thumbnail">
                                        <span class="dashicons dashicons-trash"></span>
                                        <?php esc_html_e( 'Remove', 'toastyapps-mobile-manager' ); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <p class="description"><?php esc_html_e( 'Optional. If not set, a frame from the video will be used.', 'toastyapps-mobile-manager' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reel_duration"><?php esc_html_e( 'Duration', 'toastyapps-mobile-manager' ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="reel_duration" id="reel_duration" class="small-text"
                                   value="<?php echo $reel ? esc_attr( get_post_meta( $reel_id, '_toastyapps_duration', true ) ) : ''; ?>"
                                   placeholder="0:30">
                            <p class="description"><?php esc_html_e( 'Video duration in format M:SS', 'toastyapps-mobile-manager' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <p class="submit">
                <button type="submit" name="toastyapps_save_reel" class="button button-primary button-large">
                    <span class="dashicons dashicons-saved"></span>
                    <?php echo $reel_id ? esc_html__( 'Update Reel', 'toastyapps-mobile-manager' ) : esc_html__( 'Save Reel', 'toastyapps-mobile-manager' ); ?>
                </button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-reels' ) ); ?>" class="button">
                    <?php esc_html_e( 'Cancel', 'toastyapps-mobile-manager' ); ?>
                </a>
            </p>
        </form>

    <?php else : ?>
        <!-- Reels List -->
        <?php
        $reels = get_posts( array(
            'post_type'      => 'toastyapps_reel',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ) );
        ?>

        <?php if ( ! empty( $reels ) ) : ?>
            <div class="toastyapps-card">
                <p class="description" style="margin-bottom: 15px;">
                    <?php esc_html_e( 'Drag and drop to reorder reels. The order will be reflected in the mobile app.', 'toastyapps-mobile-manager' ); ?>
                </p>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 30px;"></th>
                            <th style="width: 100px;"><?php esc_html_e( 'Thumbnail', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Title', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Duration', 'toastyapps-mobile-manager' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'toastyapps-mobile-manager' ); ?></th>
                            <th style="width: 150px;"><?php esc_html_e( 'Actions', 'toastyapps-mobile-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="reels-list" class="sortable-list">
                        <?php foreach ( $reels as $reel ) :
                            $thumbnail_id = get_post_meta( $reel->ID, '_toastyapps_thumbnail_id', true );
                            $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' ) : '';
                            $video_id = get_post_meta( $reel->ID, '_toastyapps_video_id', true );
                            $duration = get_post_meta( $reel->ID, '_toastyapps_duration', true );
                        ?>
                            <tr data-id="<?php echo esc_attr( $reel->ID ); ?>">
                                <td class="drag-handle">
                                    <span class="dashicons dashicons-menu"></span>
                                </td>
                                <td>
                                    <?php if ( $thumbnail_url ) : ?>
                                        <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="" style="width: 80px; height: 60px; object-fit: cover;">
                                    <?php else : ?>
                                        <div class="no-thumbnail">
                                            <span class="dashicons dashicons-video-alt3"></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( $reel->post_title ); ?></strong>
                                    <?php if ( ! $video_id ) : ?>
                                        <span class="no-video-warning">
                                            <span class="dashicons dashicons-warning"></span>
                                            <?php esc_html_e( 'No video', 'toastyapps-mobile-manager' ); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $duration ? esc_html( $duration ) : '-'; ?></td>
                                <td><?php echo esc_html( get_the_date( '', $reel ) ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-reels&action=edit&reel_id=' . $reel->ID ) ); ?>" class="button button-small">
                                        <span class="dashicons dashicons-edit"></span>
                                        <?php esc_html_e( 'Edit', 'toastyapps-mobile-manager' ); ?>
                                    </a>
                                    <button type="button" class="button button-small delete-reel" data-id="<?php echo esc_attr( $reel->ID ); ?>">
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
                <span class="dashicons dashicons-video-alt3"></span>
                <h2><?php esc_html_e( 'No Media Reels Yet', 'toastyapps-mobile-manager' ); ?></h2>
                <p><?php esc_html_e( 'Add your first media reel to showcase in your mobile app.', 'toastyapps-mobile-manager' ); ?></p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-reels&action=add' ) ); ?>" class="button button-primary button-large">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e( 'Add Your First Reel', 'toastyapps-mobile-manager' ); ?>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Video upload
    $('#upload-reel-video').on('click', function(e) {
        e.preventDefault();
        var mediaUploader = wp.media({
            title: toastyapps.strings.select_video,
            button: { text: toastyapps.strings.use_video },
            library: { type: 'video' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#reel_video_id').val(attachment.id);
            $('#video-preview').html('<video src="' + attachment.url + '" controls width="320"></video>');
            $('#upload-reel-video').text('Change Video');
            if ($('#remove-reel-video').length === 0) {
                $('#upload-reel-video').after('<button type="button" class="button" id="remove-reel-video"><span class="dashicons dashicons-trash"></span> Remove</button>');
            }
        });

        mediaUploader.open();
    });

    // Remove video
    $(document).on('click', '#remove-reel-video', function() {
        $('#reel_video_id').val('');
        $('#video-preview').html('<div class="placeholder"><span class="dashicons dashicons-video-alt3"></span><p>No video selected</p></div>');
        $(this).remove();
    });

    // Thumbnail upload
    $('#upload-reel-thumbnail').on('click', function(e) {
        e.preventDefault();
        var mediaUploader = wp.media({
            title: toastyapps.strings.select_image,
            button: { text: toastyapps.strings.use_image },
            library: { type: 'image' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#reel_thumbnail_id').val(attachment.id);
            $('#thumbnail-preview').html('<img src="' + attachment.url + '" alt="Thumbnail">');
            $('#upload-reel-thumbnail').text('Change Thumbnail');
            if ($('#remove-reel-thumbnail').length === 0) {
                $('#upload-reel-thumbnail').after('<button type="button" class="button" id="remove-reel-thumbnail"><span class="dashicons dashicons-trash"></span> Remove</button>');
            }
        });

        mediaUploader.open();
    });

    // Remove thumbnail
    $(document).on('click', '#remove-reel-thumbnail', function() {
        $('#reel_thumbnail_id').val('');
        $('#thumbnail-preview').html('<div class="placeholder"><span class="dashicons dashicons-format-image"></span><p>No thumbnail</p></div>');
        $(this).remove();
    });

    // Sortable reels
    $('#reels-list').sortable({
        handle: '.drag-handle',
        update: function(event, ui) {
            var order = [];
            $('#reels-list tr').each(function() {
                order.push($(this).data('id'));
            });

            $.post(toastyapps.ajax_url, {
                action: 'toastyapps_update_reel_order',
                nonce: toastyapps.nonce,
                order: order
            });
        }
    });

    // Delete reel
    $('.delete-reel').on('click', function() {
        if (!confirm(toastyapps.strings.confirm_delete)) return;

        var $btn = $(this);
        var reelId = $btn.data('id');

        $.post(toastyapps.ajax_url, {
            action: 'toastyapps_delete_reel',
            nonce: toastyapps.nonce,
            reel_id: reelId
        }, function(response) {
            if (response.success) {
                $btn.closest('tr').fadeOut(function() { $(this).remove(); });
            }
        });
    });
});
</script>
