<?php
/**
 * REST API endpoints for ToastyApps Mobile Manager.
 *
 * Provides REST API endpoints for the mobile app to fetch content and register devices.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/includes
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * REST API Class
 */
class ToastyApps_API {

    /**
     * API namespace.
     *
     * @var string
     */
    private $namespace = 'toastyapps/v1';

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
    }

    /**
     * Register all API routes.
     */
    public function register_routes() {
        // GET /wp-json/toastyapps/v1/config
        register_rest_route( $this->namespace, '/config', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_config' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
        ) );

        // GET /wp-json/toastyapps/v1/hero-image
        register_rest_route( $this->namespace, '/hero-image', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_hero_image' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
        ) );

        // GET /wp-json/toastyapps/v1/media-reels
        register_rest_route( $this->namespace, '/media-reels', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_media_reels' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
        ) );

        // GET /wp-json/toastyapps/v1/media-reels/{id}
        register_rest_route( $this->namespace, '/media-reels/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_media_reel' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
            'args'                => array(
                'id' => array(
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    },
                ),
            ),
        ) );

        // GET /wp-json/toastyapps/v1/locations
        register_rest_route( $this->namespace, '/locations', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_locations' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
        ) );

        // POST /wp-json/toastyapps/v1/register-device
        register_rest_route( $this->namespace, '/register-device', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'register_device' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
            'args'                => array(
                'device_token' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'device_type' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'default'           => 'ios',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'device_name' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'app_version' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'os_version' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ) );

        // POST /wp-json/toastyapps/v1/unregister-device
        register_rest_route( $this->namespace, '/unregister-device', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'unregister_device' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
            'args'                => array(
                'device_token' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ) );

        // GET /wp-json/toastyapps/v1/branding
        register_rest_route( $this->namespace, '/branding', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_branding' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
        ) );

        // GET /wp-json/toastyapps/v1/all-content
        register_rest_route( $this->namespace, '/all-content', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_all_content' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
        ) );

        // POST /wp-json/toastyapps/v1/heartbeat
        register_rest_route( $this->namespace, '/heartbeat', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'heartbeat' ),
            'permission_callback' => array( $this, 'verify_api_key' ),
            'args'                => array(
                'device_token' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ) );
    }

    /**
     * Verify API key from request headers.
     *
     * @param WP_REST_Request $request Request object.
     * @return bool|WP_Error True if valid, WP_Error if invalid.
     */
    public function verify_api_key( $request ) {
        $api_key = get_option( 'toastyapps_api_key', '' );

        if ( empty( $api_key ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'API key not configured.', 'toastyapps-mobile-manager' ),
                array( 'status' => 403 )
            );
        }

        // Check Authorization header
        $auth_header = $request->get_header( 'Authorization' );

        if ( $auth_header ) {
            // Support both "Bearer {key}" and "ApiKey {key}" formats
            if ( preg_match( '/^(Bearer|ApiKey)\s+(.+)$/i', $auth_header, $matches ) ) {
                if ( hash_equals( $api_key, $matches[2] ) ) {
                    $this->log_request( $request );
                    return true;
                }
            }
        }

        // Also check X-API-Key header for flexibility
        $x_api_key = $request->get_header( 'X-API-Key' );
        if ( $x_api_key && hash_equals( $api_key, $x_api_key ) ) {
            $this->log_request( $request );
            return true;
        }

        // Check query parameter as fallback (less secure, but useful for testing)
        $query_key = $request->get_param( 'api_key' );
        if ( $query_key && hash_equals( $api_key, $query_key ) ) {
            $this->log_request( $request );
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            __( 'Invalid or missing API key.', 'toastyapps-mobile-manager' ),
            array( 'status' => 401 )
        );
    }

    /**
     * Log API request.
     *
     * @param WP_REST_Request $request Request object.
     */
    private function log_request( $request ) {
        $settings = get_option( 'toastyapps_settings', array() );

        if ( empty( $settings['enable_logging'] ) ) {
            return;
        }

        $this->db->log_api_request( array(
            'endpoint'     => $request->get_route(),
            'method'       => $request->get_method(),
            'device_token' => $request->get_param( 'device_token' ),
            'ip_address'   => $this->get_client_ip(),
            'user_agent'   => $request->get_header( 'User-Agent' ),
        ) );
    }

    /**
     * Get client IP address.
     *
     * @return string IP address.
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        );

        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
                // Handle comma-separated IPs (X-Forwarded-For)
                if ( strpos( $ip, ',' ) !== false ) {
                    $ips = explode( ',', $ip );
                    $ip = trim( $ips[0] );
                }
                return $ip;
            }
        }

        return '';
    }

    /**
     * GET /config - Get app configuration.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_config( $request ) {
        $branding = get_option( 'toastyapps_branding', array() );
        $hero_image_id = get_option( 'toastyapps_hero_image_id', 0 );

        $locations = get_posts( array(
            'post_type'      => 'toastyapps_location',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ) );

        $locations_data = array();
        foreach ( $locations as $location ) {
            $locations_data[] = array(
                'id'        => $location->ID,
                'name'      => $location->post_title,
                'address'   => get_post_meta( $location->ID, '_toastyapps_address', true ),
                'embed_url' => get_post_meta( $location->ID, '_toastyapps_embed_url', true ),
                'phone'     => get_post_meta( $location->ID, '_toastyapps_phone', true ),
                'hours'     => get_post_meta( $location->ID, '_toastyapps_hours', true ),
                'latitude'  => get_post_meta( $location->ID, '_toastyapps_latitude', true ),
                'longitude' => get_post_meta( $location->ID, '_toastyapps_longitude', true ),
            );
        }

        $response = array(
            'success' => true,
            'data'    => array(
                'app_name'         => isset( $branding['app_name'] ) ? $branding['app_name'] : get_bloginfo( 'name' ),
                'tagline'          => isset( $branding['tagline'] ) ? $branding['tagline'] : '',
                'primary_color'    => isset( $branding['primary_color'] ) ? $branding['primary_color'] : '#1a73e8',
                'secondary_color'  => isset( $branding['secondary_color'] ) ? $branding['secondary_color'] : '#34a853',
                'accent_color'     => isset( $branding['accent_color'] ) ? $branding['accent_color'] : '#ea4335',
                'text_color'       => isset( $branding['text_color'] ) ? $branding['text_color'] : '#202124',
                'background_color' => isset( $branding['background_color'] ) ? $branding['background_color'] : '#ffffff',
                'hero_image_url'   => $hero_image_id ? wp_get_attachment_url( $hero_image_id ) : null,
                'locations'        => $locations_data,
                'site_url'         => home_url(),
            ),
            'meta' => array(
                'version'    => TOASTYAPPS_VERSION,
                'updated_at' => get_option( 'toastyapps_last_updated', current_time( 'c' ) ),
            ),
        );

        return rest_ensure_response( $response );
    }

    /**
     * GET /hero-image - Get hero image.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_hero_image( $request ) {
        $hero_image_id = get_option( 'toastyapps_hero_image_id', 0 );

        if ( ! $hero_image_id ) {
            return rest_ensure_response( array(
                'success' => true,
                'data'    => null,
            ) );
        }

        $image_url = wp_get_attachment_url( $hero_image_id );
        $image_meta = wp_get_attachment_metadata( $hero_image_id );

        // Get different sizes
        $sizes = array();
        if ( $image_meta && isset( $image_meta['sizes'] ) ) {
            $upload_dir = wp_upload_dir();
            $base_url = trailingslashit( $upload_dir['baseurl'] );
            $base_path = trailingslashit( dirname( get_attached_file( $hero_image_id ) ) );
            $base_path = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $base_path );

            foreach ( $image_meta['sizes'] as $size => $data ) {
                $sizes[ $size ] = array(
                    'url'    => $base_path . $data['file'],
                    'width'  => $data['width'],
                    'height' => $data['height'],
                );
            }
        }

        return rest_ensure_response( array(
            'success' => true,
            'data'    => array(
                'id'          => $hero_image_id,
                'url'         => $image_url,
                'width'       => isset( $image_meta['width'] ) ? $image_meta['width'] : null,
                'height'      => isset( $image_meta['height'] ) ? $image_meta['height'] : null,
                'sizes'       => $sizes,
                'alt'         => get_post_meta( $hero_image_id, '_wp_attachment_image_alt', true ),
                'updated_at'  => get_the_modified_date( 'c', $hero_image_id ),
            ),
        ) );
    }

    /**
     * GET /media-reels - Get all media reels.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_media_reels( $request ) {
        $reels = get_posts( array(
            'post_type'      => 'toastyapps_reel',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ) );

        $reels_data = array();
        foreach ( $reels as $reel ) {
            $reels_data[] = $this->format_reel( $reel );
        }

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $reels_data,
            'meta'    => array(
                'total' => count( $reels_data ),
            ),
        ) );
    }

    /**
     * GET /media-reels/{id} - Get single media reel.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_media_reel( $request ) {
        $reel_id = (int) $request->get_param( 'id' );
        $reel = get_post( $reel_id );

        if ( ! $reel || 'toastyapps_reel' !== $reel->post_type ) {
            return new WP_Error(
                'not_found',
                __( 'Media reel not found.', 'toastyapps-mobile-manager' ),
                array( 'status' => 404 )
            );
        }

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $this->format_reel( $reel ),
        ) );
    }

    /**
     * Format reel data for API response.
     *
     * @param WP_Post $reel Reel post object.
     * @return array Formatted reel data.
     */
    private function format_reel( $reel ) {
        $video_id = get_post_meta( $reel->ID, '_toastyapps_video_id', true );
        $thumbnail_id = get_post_meta( $reel->ID, '_toastyapps_thumbnail_id', true );

        return array(
            'id'            => $reel->ID,
            'title'         => $reel->post_title,
            'description'   => get_post_meta( $reel->ID, '_toastyapps_description', true ),
            'video_url'     => $video_id ? wp_get_attachment_url( $video_id ) : null,
            'thumbnail_url' => $thumbnail_id ? wp_get_attachment_url( $thumbnail_id ) : null,
            'duration'      => get_post_meta( $reel->ID, '_toastyapps_duration', true ),
            'order'         => $reel->menu_order,
            'created_at'    => get_the_date( 'c', $reel ),
            'updated_at'    => get_the_modified_date( 'c', $reel ),
        );
    }

    /**
     * GET /locations - Get all locations.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_locations( $request ) {
        $locations = get_posts( array(
            'post_type'      => 'toastyapps_location',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ) );

        $locations_data = array();
        foreach ( $locations as $location ) {
            $locations_data[] = array(
                'id'         => $location->ID,
                'name'       => $location->post_title,
                'address'    => get_post_meta( $location->ID, '_toastyapps_address', true ),
                'embed_url'  => get_post_meta( $location->ID, '_toastyapps_embed_url', true ),
                'phone'      => get_post_meta( $location->ID, '_toastyapps_phone', true ),
                'email'      => get_post_meta( $location->ID, '_toastyapps_email', true ),
                'website'    => get_post_meta( $location->ID, '_toastyapps_website', true ),
                'hours'      => get_post_meta( $location->ID, '_toastyapps_hours', true ),
                'latitude'   => (float) get_post_meta( $location->ID, '_toastyapps_latitude', true ),
                'longitude'  => (float) get_post_meta( $location->ID, '_toastyapps_longitude', true ),
                'is_primary' => (bool) get_post_meta( $location->ID, '_toastyapps_is_primary', true ),
                'order'      => $location->menu_order,
            );
        }

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $locations_data,
            'meta'    => array(
                'total' => count( $locations_data ),
            ),
        ) );
    }

    /**
     * POST /register-device - Register a device for push notifications.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function register_device( $request ) {
        $device_token = $request->get_param( 'device_token' );

        if ( empty( $device_token ) ) {
            return new WP_Error(
                'invalid_token',
                __( 'Device token is required.', 'toastyapps-mobile-manager' ),
                array( 'status' => 400 )
            );
        }

        $device_id = $this->db->register_device( array(
            'device_token' => $device_token,
            'device_type'  => $request->get_param( 'device_type' ),
            'device_name'  => $request->get_param( 'device_name' ),
            'app_version'  => $request->get_param( 'app_version' ),
            'os_version'   => $request->get_param( 'os_version' ),
        ) );

        if ( ! $device_id ) {
            return new WP_Error(
                'registration_failed',
                __( 'Failed to register device.', 'toastyapps-mobile-manager' ),
                array( 'status' => 500 )
            );
        }

        // Subscribe to default topic
        $fcm = new ToastyApps_FCM();
        $fcm->subscribe_to_topic( $device_token, 'all_users' );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => array(
                'device_id' => $device_id,
                'message'   => __( 'Device registered successfully.', 'toastyapps-mobile-manager' ),
            ),
        ) );
    }

    /**
     * POST /unregister-device - Unregister a device.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function unregister_device( $request ) {
        $device_token = $request->get_param( 'device_token' );

        if ( empty( $device_token ) ) {
            return new WP_Error(
                'invalid_token',
                __( 'Device token is required.', 'toastyapps-mobile-manager' ),
                array( 'status' => 400 )
            );
        }

        $result = $this->db->deactivate_device( $device_token );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => array(
                'message' => __( 'Device unregistered successfully.', 'toastyapps-mobile-manager' ),
            ),
        ) );
    }

    /**
     * GET /branding - Get branding configuration.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_branding( $request ) {
        $branding = get_option( 'toastyapps_branding', array() );

        // Get logo if set
        $logo_id = isset( $branding['logo_id'] ) ? $branding['logo_id'] : 0;
        $logo_url = $logo_id ? wp_get_attachment_url( $logo_id ) : null;

        return rest_ensure_response( array(
            'success' => true,
            'data'    => array(
                'app_name'         => isset( $branding['app_name'] ) ? $branding['app_name'] : get_bloginfo( 'name' ),
                'tagline'          => isset( $branding['tagline'] ) ? $branding['tagline'] : '',
                'primary_color'    => isset( $branding['primary_color'] ) ? $branding['primary_color'] : '#1a73e8',
                'secondary_color'  => isset( $branding['secondary_color'] ) ? $branding['secondary_color'] : '#34a853',
                'accent_color'     => isset( $branding['accent_color'] ) ? $branding['accent_color'] : '#ea4335',
                'text_color'       => isset( $branding['text_color'] ) ? $branding['text_color'] : '#202124',
                'background_color' => isset( $branding['background_color'] ) ? $branding['background_color'] : '#ffffff',
                'logo_url'         => $logo_url,
            ),
        ) );
    }

    /**
     * GET /all-content - Get all app content in one request.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_all_content( $request ) {
        // Get config
        $config_response = $this->get_config( $request );
        $config = $config_response->get_data();

        // Get media reels
        $reels_response = $this->get_media_reels( $request );
        $reels = $reels_response->get_data();

        // Get hero image
        $hero_response = $this->get_hero_image( $request );
        $hero = $hero_response->get_data();

        return rest_ensure_response( array(
            'success' => true,
            'data'    => array(
                'config'      => $config['data'],
                'hero_image'  => $hero['data'],
                'media_reels' => $reels['data'],
            ),
            'meta' => array(
                'version'      => TOASTYAPPS_VERSION,
                'generated_at' => current_time( 'c' ),
            ),
        ) );
    }

    /**
     * POST /heartbeat - Update device last active time.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function heartbeat( $request ) {
        $device_token = $request->get_param( 'device_token' );

        if ( empty( $device_token ) ) {
            return new WP_Error(
                'invalid_token',
                __( 'Device token is required.', 'toastyapps-mobile-manager' ),
                array( 'status' => 400 )
            );
        }

        // This will update the last_active timestamp
        $this->db->register_device( array(
            'device_token' => $device_token,
        ) );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => array(
                'message' => 'OK',
            ),
        ) );
    }
}
