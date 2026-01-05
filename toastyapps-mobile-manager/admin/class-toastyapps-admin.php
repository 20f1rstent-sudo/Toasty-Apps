<?php
/**
 * Admin functionality for ToastyApps Mobile Manager.
 *
 * Handles all admin pages, menus, and form processing.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/admin
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Class
 */
class ToastyApps_Admin {

    /**
     * Database instance.
     *
     * @var ToastyApps_Database
     */
    private $db;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->db = new ToastyApps_Database();

        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // Enqueue admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Handle form submissions
        add_action( 'admin_init', array( $this, 'handle_form_submissions' ) );

        // Show welcome notice
        add_action( 'admin_notices', array( $this, 'show_welcome_notice' ) );
    }

    /**
     * Add admin menu pages.
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __( 'ToastyApps', 'toastyapps-mobile-manager' ),
            __( 'ToastyApps', 'toastyapps-mobile-manager' ),
            'manage_options',
            'toastyapps',
            array( $this, 'render_dashboard_page' ),
            'dashicons-smartphone',
            30
        );

        // Dashboard submenu (same as main)
        add_submenu_page(
            'toastyapps',
            __( 'Dashboard', 'toastyapps-mobile-manager' ),
            __( 'Dashboard', 'toastyapps-mobile-manager' ),
            'manage_options',
            'toastyapps',
            array( $this, 'render_dashboard_page' )
        );

        // Hero Image submenu
        add_submenu_page(
            'toastyapps',
            __( 'Hero Image', 'toastyapps-mobile-manager' ),
            __( 'Hero Image', 'toastyapps-mobile-manager' ),
            'manage_options',
            'toastyapps-hero',
            array( $this, 'render_hero_page' )
        );

        // Media Reels submenu
        add_submenu_page(
            'toastyapps',
            __( 'Media Reels', 'toastyapps-mobile-manager' ),
            __( 'Media Reels', 'toastyapps-mobile-manager' ),
            'manage_options',
            'toastyapps-reels',
            array( $this, 'render_reels_page' )
        );

        // Push Notifications submenu
        add_submenu_page(
            'toastyapps',
            __( 'Push Notifications', 'toastyapps-mobile-manager' ),
            __( 'Notifications', 'toastyapps-mobile-manager' ),
            'manage_options',
            'toastyapps-notifications',
            array( $this, 'render_notifications_page' )
        );

        // Locations submenu
        add_submenu_page(
            'toastyapps',
            __( 'Locations', 'toastyapps-mobile-manager' ),
            __( 'Locations', 'toastyapps-mobile-manager' ),
            'manage_options',
            'toastyapps-locations',
            array( $this, 'render_locations_page' )
        );

        // Branding submenu
        add_submenu_page(
            'toastyapps',
            __( 'Branding', 'toastyapps-mobile-manager' ),
            __( 'Branding', 'toastyapps-mobile-manager' ),
            'manage_options',
            'toastyapps-branding',
            array( $this, 'render_branding_page' )
        );

        // Settings submenu
        add_submenu_page(
            'toastyapps',
            __( 'Settings', 'toastyapps-mobile-manager' ),
            __( 'Settings', 'toastyapps-mobile-manager' ),
            'manage_options',
            'toastyapps-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on our plugin pages
        if ( strpos( $hook, 'toastyapps' ) === false ) {
            return;
        }

        // WordPress media uploader
        wp_enqueue_media();

        // Color picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        // jQuery UI for sortable
        wp_enqueue_script( 'jquery-ui-sortable' );

        // Our custom styles
        wp_enqueue_style(
            'toastyapps-admin',
            TOASTYAPPS_PLUGIN_URL . 'admin/css/toastyapps-admin.css',
            array(),
            TOASTYAPPS_VERSION
        );

        // Our custom scripts
        wp_enqueue_script(
            'toastyapps-admin',
            TOASTYAPPS_PLUGIN_URL . 'admin/js/toastyapps-admin.js',
            array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ),
            TOASTYAPPS_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'toastyapps-admin', 'toastyapps', array(
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'toastyapps_nonce' ),
            'strings'     => array(
                'select_image'  => __( 'Select Image', 'toastyapps-mobile-manager' ),
                'select_video'  => __( 'Select Video', 'toastyapps-mobile-manager' ),
                'use_image'     => __( 'Use this image', 'toastyapps-mobile-manager' ),
                'use_video'     => __( 'Use this video', 'toastyapps-mobile-manager' ),
                'confirm_delete'=> __( 'Are you sure you want to delete this item?', 'toastyapps-mobile-manager' ),
                'sending'       => __( 'Sending...', 'toastyapps-mobile-manager' ),
                'send'          => __( 'Send Notification', 'toastyapps-mobile-manager' ),
            ),
        ) );
    }

    /**
     * Handle form submissions.
     */
    public function handle_form_submissions() {
        // Hero image save
        if ( isset( $_POST['toastyapps_save_hero'] ) ) {
            $this->save_hero_image();
        }

        // Media reel save
        if ( isset( $_POST['toastyapps_save_reel'] ) ) {
            $this->save_media_reel();
        }

        // Location save
        if ( isset( $_POST['toastyapps_save_location'] ) ) {
            $this->save_location();
        }

        // Branding save
        if ( isset( $_POST['toastyapps_save_branding'] ) ) {
            $this->save_branding();
        }

        // Settings save
        if ( isset( $_POST['toastyapps_save_settings'] ) ) {
            $this->save_settings();
        }

        // FCM settings save
        if ( isset( $_POST['toastyapps_save_fcm'] ) ) {
            $this->save_fcm_settings();
        }
    }

    /**
     * Save hero image.
     */
    private function save_hero_image() {
        if ( ! check_admin_referer( 'toastyapps_hero_nonce', 'toastyapps_hero_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $hero_image_id = isset( $_POST['hero_image_id'] ) ? absint( $_POST['hero_image_id'] ) : 0;
        update_option( 'toastyapps_hero_image_id', $hero_image_id );
        update_option( 'toastyapps_last_updated', current_time( 'c' ) );

        add_settings_error(
            'toastyapps_messages',
            'toastyapps_hero_saved',
            __( 'Hero image saved successfully.', 'toastyapps-mobile-manager' ),
            'success'
        );
    }

    /**
     * Save media reel.
     */
    private function save_media_reel() {
        if ( ! check_admin_referer( 'toastyapps_reel_nonce', 'toastyapps_reel_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $reel_id = isset( $_POST['reel_id'] ) ? absint( $_POST['reel_id'] ) : 0;
        $title = isset( $_POST['reel_title'] ) ? sanitize_text_field( wp_unslash( $_POST['reel_title'] ) ) : '';
        $description = isset( $_POST['reel_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reel_description'] ) ) : '';
        $video_id = isset( $_POST['reel_video_id'] ) ? absint( $_POST['reel_video_id'] ) : 0;
        $thumbnail_id = isset( $_POST['reel_thumbnail_id'] ) ? absint( $_POST['reel_thumbnail_id'] ) : 0;
        $duration = isset( $_POST['reel_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['reel_duration'] ) ) : '';

        if ( empty( $title ) ) {
            add_settings_error(
                'toastyapps_messages',
                'toastyapps_reel_error',
                __( 'Reel title is required.', 'toastyapps-mobile-manager' ),
                'error'
            );
            return;
        }

        $post_data = array(
            'post_title'  => $title,
            'post_type'   => 'toastyapps_reel',
            'post_status' => 'publish',
        );

        if ( $reel_id ) {
            $post_data['ID'] = $reel_id;
            wp_update_post( $post_data );
        } else {
            $reel_id = wp_insert_post( $post_data );
        }

        if ( $reel_id && ! is_wp_error( $reel_id ) ) {
            update_post_meta( $reel_id, '_toastyapps_description', $description );
            update_post_meta( $reel_id, '_toastyapps_video_id', $video_id );
            update_post_meta( $reel_id, '_toastyapps_thumbnail_id', $thumbnail_id );
            update_post_meta( $reel_id, '_toastyapps_duration', $duration );
            update_option( 'toastyapps_last_updated', current_time( 'c' ) );

            add_settings_error(
                'toastyapps_messages',
                'toastyapps_reel_saved',
                __( 'Media reel saved successfully.', 'toastyapps-mobile-manager' ),
                'success'
            );

            // Redirect to list
            wp_safe_redirect( admin_url( 'admin.php?page=toastyapps-reels&saved=1' ) );
            exit;
        }
    }

    /**
     * Save location.
     */
    private function save_location() {
        if ( ! check_admin_referer( 'toastyapps_location_nonce', 'toastyapps_location_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $location_id = isset( $_POST['location_id'] ) ? absint( $_POST['location_id'] ) : 0;
        $name = isset( $_POST['location_name'] ) ? sanitize_text_field( wp_unslash( $_POST['location_name'] ) ) : '';
        $address = isset( $_POST['location_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['location_address'] ) ) : '';
        $embed_url = isset( $_POST['location_embed_url'] ) ? esc_url_raw( wp_unslash( $_POST['location_embed_url'] ) ) : '';
        $phone = isset( $_POST['location_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['location_phone'] ) ) : '';
        $email = isset( $_POST['location_email'] ) ? sanitize_email( wp_unslash( $_POST['location_email'] ) ) : '';
        $website = isset( $_POST['location_website'] ) ? esc_url_raw( wp_unslash( $_POST['location_website'] ) ) : '';
        $hours = isset( $_POST['location_hours'] ) ? sanitize_textarea_field( wp_unslash( $_POST['location_hours'] ) ) : '';
        $latitude = isset( $_POST['location_latitude'] ) ? floatval( $_POST['location_latitude'] ) : 0;
        $longitude = isset( $_POST['location_longitude'] ) ? floatval( $_POST['location_longitude'] ) : 0;
        $is_primary = isset( $_POST['location_is_primary'] ) ? 1 : 0;

        if ( empty( $name ) ) {
            add_settings_error(
                'toastyapps_messages',
                'toastyapps_location_error',
                __( 'Location name is required.', 'toastyapps-mobile-manager' ),
                'error'
            );
            return;
        }

        $post_data = array(
            'post_title'  => $name,
            'post_type'   => 'toastyapps_location',
            'post_status' => 'publish',
        );

        if ( $location_id ) {
            $post_data['ID'] = $location_id;
            wp_update_post( $post_data );
        } else {
            $location_id = wp_insert_post( $post_data );
        }

        if ( $location_id && ! is_wp_error( $location_id ) ) {
            update_post_meta( $location_id, '_toastyapps_address', $address );
            update_post_meta( $location_id, '_toastyapps_embed_url', $embed_url );
            update_post_meta( $location_id, '_toastyapps_phone', $phone );
            update_post_meta( $location_id, '_toastyapps_email', $email );
            update_post_meta( $location_id, '_toastyapps_website', $website );
            update_post_meta( $location_id, '_toastyapps_hours', $hours );
            update_post_meta( $location_id, '_toastyapps_latitude', $latitude );
            update_post_meta( $location_id, '_toastyapps_longitude', $longitude );
            update_post_meta( $location_id, '_toastyapps_is_primary', $is_primary );

            // If this is primary, unset others
            if ( $is_primary ) {
                $other_locations = get_posts( array(
                    'post_type'      => 'toastyapps_location',
                    'posts_per_page' => -1,
                    'post__not_in'   => array( $location_id ),
                ) );
                foreach ( $other_locations as $loc ) {
                    update_post_meta( $loc->ID, '_toastyapps_is_primary', 0 );
                }
            }

            update_option( 'toastyapps_last_updated', current_time( 'c' ) );

            add_settings_error(
                'toastyapps_messages',
                'toastyapps_location_saved',
                __( 'Location saved successfully.', 'toastyapps-mobile-manager' ),
                'success'
            );

            // Redirect to list
            wp_safe_redirect( admin_url( 'admin.php?page=toastyapps-locations&saved=1' ) );
            exit;
        }
    }

    /**
     * Save branding settings.
     */
    private function save_branding() {
        if ( ! check_admin_referer( 'toastyapps_branding_nonce', 'toastyapps_branding_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $branding = array(
            'app_name'         => isset( $_POST['app_name'] ) ? sanitize_text_field( wp_unslash( $_POST['app_name'] ) ) : '',
            'tagline'          => isset( $_POST['tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline'] ) ) : '',
            'primary_color'    => isset( $_POST['primary_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['primary_color'] ) ) : '#1a73e8',
            'secondary_color'  => isset( $_POST['secondary_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['secondary_color'] ) ) : '#34a853',
            'accent_color'     => isset( $_POST['accent_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['accent_color'] ) ) : '#ea4335',
            'text_color'       => isset( $_POST['text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['text_color'] ) ) : '#202124',
            'background_color' => isset( $_POST['background_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['background_color'] ) ) : '#ffffff',
            'logo_id'          => isset( $_POST['logo_id'] ) ? absint( $_POST['logo_id'] ) : 0,
        );

        update_option( 'toastyapps_branding', $branding );
        update_option( 'toastyapps_last_updated', current_time( 'c' ) );

        add_settings_error(
            'toastyapps_messages',
            'toastyapps_branding_saved',
            __( 'Branding settings saved successfully.', 'toastyapps-mobile-manager' ),
            'success'
        );
    }

    /**
     * Save general settings.
     */
    private function save_settings() {
        if ( ! check_admin_referer( 'toastyapps_settings_nonce', 'toastyapps_settings_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = array(
            'api_rate_limit'     => isset( $_POST['api_rate_limit'] ) ? absint( $_POST['api_rate_limit'] ) : 60,
            'enable_logging'     => isset( $_POST['enable_logging'] ) ? true : false,
            'log_retention_days' => isset( $_POST['log_retention_days'] ) ? absint( $_POST['log_retention_days'] ) : 30,
            'max_file_size_mb'   => isset( $_POST['max_file_size_mb'] ) ? absint( $_POST['max_file_size_mb'] ) : 50,
        );

        update_option( 'toastyapps_settings', $settings );

        add_settings_error(
            'toastyapps_messages',
            'toastyapps_settings_saved',
            __( 'Settings saved successfully.', 'toastyapps-mobile-manager' ),
            'success'
        );
    }

    /**
     * Save FCM settings.
     */
    private function save_fcm_settings() {
        if ( ! check_admin_referer( 'toastyapps_fcm_nonce', 'toastyapps_fcm_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $fcm_settings = array(
            'server_key' => isset( $_POST['fcm_server_key'] ) ? sanitize_text_field( wp_unslash( $_POST['fcm_server_key'] ) ) : '',
            'project_id' => isset( $_POST['fcm_project_id'] ) ? sanitize_text_field( wp_unslash( $_POST['fcm_project_id'] ) ) : '',
            'enabled'    => isset( $_POST['fcm_enabled'] ) ? true : false,
        );

        // Validate server key if provided
        if ( ! empty( $fcm_settings['server_key'] ) && $fcm_settings['enabled'] ) {
            $fcm = new ToastyApps_FCM();
            if ( ! $fcm->validate_server_key( $fcm_settings['server_key'] ) ) {
                add_settings_error(
                    'toastyapps_messages',
                    'toastyapps_fcm_error',
                    __( 'Invalid FCM Server Key. Please check your Firebase Console.', 'toastyapps-mobile-manager' ),
                    'error'
                );
                return;
            }
        }

        update_option( 'toastyapps_fcm_settings', $fcm_settings );

        add_settings_error(
            'toastyapps_messages',
            'toastyapps_fcm_saved',
            __( 'FCM settings saved successfully.', 'toastyapps-mobile-manager' ),
            'success'
        );
    }

    /**
     * Show welcome notice after activation.
     */
    public function show_welcome_notice() {
        if ( get_transient( 'toastyapps_activated' ) ) {
            delete_transient( 'toastyapps_activated' );
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <strong><?php esc_html_e( 'ToastyApps Mobile Manager activated!', 'toastyapps-mobile-manager' ); ?></strong>
                    <?php esc_html_e( 'Get started by configuring your', 'toastyapps-mobile-manager' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=toastyapps-settings' ) ); ?>">
                        <?php esc_html_e( 'settings', 'toastyapps-mobile-manager' ); ?>
                    </a>.
                </p>
            </div>
            <?php
        }
    }

    /**
     * Render dashboard page.
     */
    public function render_dashboard_page() {
        include TOASTYAPPS_PLUGIN_DIR . 'admin/partials/toastyapps-admin-dashboard.php';
    }

    /**
     * Render hero image page.
     */
    public function render_hero_page() {
        include TOASTYAPPS_PLUGIN_DIR . 'admin/partials/toastyapps-admin-hero.php';
    }

    /**
     * Render media reels page.
     */
    public function render_reels_page() {
        include TOASTYAPPS_PLUGIN_DIR . 'admin/partials/toastyapps-admin-reels.php';
    }

    /**
     * Render notifications page.
     */
    public function render_notifications_page() {
        include TOASTYAPPS_PLUGIN_DIR . 'admin/partials/toastyapps-admin-notifications.php';
    }

    /**
     * Render locations page.
     */
    public function render_locations_page() {
        include TOASTYAPPS_PLUGIN_DIR . 'admin/partials/toastyapps-admin-locations.php';
    }

    /**
     * Render branding page.
     */
    public function render_branding_page() {
        include TOASTYAPPS_PLUGIN_DIR . 'admin/partials/toastyapps-admin-branding.php';
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        include TOASTYAPPS_PLUGIN_DIR . 'admin/partials/toastyapps-admin-settings.php';
    }
}
