<?php
/**
 * Firebase Cloud Messaging integration for ToastyApps Mobile Manager.
 *
 * Handles sending push notifications via FCM.
 *
 * @package    ToastyApps_Mobile_Manager
 * @subpackage ToastyApps_Mobile_Manager/includes
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FCM Class
 */
class ToastyApps_FCM {

    /**
     * FCM API URL.
     *
     * @var string
     */
    private $fcm_url = 'https://fcm.googleapis.com/fcm/send';

    /**
     * FCM v1 API URL template.
     *
     * @var string
     */
    private $fcm_v1_url = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';

    /**
     * FCM settings.
     *
     * @var array
     */
    private $settings;

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
        $this->settings = get_option( 'toastyapps_fcm_settings', array() );
        $this->db = new ToastyApps_Database();
    }

    /**
     * Check if FCM is configured.
     *
     * @return bool Whether FCM is configured.
     */
    public function is_configured() {
        return ! empty( $this->settings['server_key'] ) && ! empty( $this->settings['enabled'] );
    }

    /**
     * Send push notification to all devices.
     *
     * @param string $title     Notification title.
     * @param string $body      Notification body.
     * @param string $image_url Optional image URL.
     * @param array  $data      Optional additional data.
     * @return array Result with success status and counts.
     */
    public function send_notification( $title, $body, $image_url = '', $data = array() ) {
        if ( ! $this->is_configured() ) {
            return array(
                'success' => false,
                'message' => __( 'FCM is not configured. Please add your Firebase Server Key in Settings.', 'toastyapps-mobile-manager' ),
            );
        }

        $tokens = $this->db->get_device_tokens();

        if ( empty( $tokens ) ) {
            return array(
                'success' => false,
                'message' => __( 'No registered devices found.', 'toastyapps-mobile-manager' ),
            );
        }

        // Log the notification
        $notification_id = $this->db->log_notification( array(
            'title'      => $title,
            'body'       => $body,
            'image_url'  => $image_url,
            'data'       => $data,
            'sent_count' => count( $tokens ),
            'status'     => 'sending',
        ) );

        $success_count = 0;
        $failure_count = 0;
        $invalid_tokens = array();

        // Send in batches of 500 (FCM limit for multicast)
        $batches = array_chunk( $tokens, 500 );

        foreach ( $batches as $batch ) {
            $result = $this->send_batch( $batch, $title, $body, $image_url, $data );

            $success_count += $result['success'];
            $failure_count += $result['failure'];

            // Collect invalid tokens for cleanup
            if ( ! empty( $result['invalid_tokens'] ) ) {
                $invalid_tokens = array_merge( $invalid_tokens, $result['invalid_tokens'] );
            }
        }

        // Deactivate invalid tokens
        foreach ( $invalid_tokens as $token ) {
            $this->db->deactivate_device( $token );
        }

        // Update notification log
        $this->db->update_notification( $notification_id, array(
            'success_count' => $success_count,
            'failure_count' => $failure_count,
            'status'        => 'sent',
            'sent_at'       => current_time( 'mysql' ),
        ) );

        return array(
            'success'       => true,
            'message'       => sprintf(
                __( 'Notification sent. Success: %d, Failed: %d', 'toastyapps-mobile-manager' ),
                $success_count,
                $failure_count
            ),
            'sent_count'    => count( $tokens ),
            'success_count' => $success_count,
            'failure_count' => $failure_count,
        );
    }

    /**
     * Send notification to a batch of tokens.
     *
     * @param array  $tokens    Array of device tokens.
     * @param string $title     Notification title.
     * @param string $body      Notification body.
     * @param string $image_url Optional image URL.
     * @param array  $data      Optional additional data.
     * @return array Result with success/failure counts.
     */
    private function send_batch( $tokens, $title, $body, $image_url = '', $data = array() ) {
        $notification = array(
            'title' => $title,
            'body'  => $body,
            'sound' => 'default',
        );

        if ( ! empty( $image_url ) ) {
            $notification['image'] = $image_url;
        }

        // Add click action for iOS
        $notification['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

        $payload = array(
            'registration_ids' => $tokens,
            'notification'     => $notification,
            'priority'         => 'high',
            'content_available' => true,
        );

        if ( ! empty( $data ) ) {
            $payload['data'] = $data;
        }

        // Add iOS specific settings
        $payload['apns'] = array(
            'payload' => array(
                'aps' => array(
                    'sound'             => 'default',
                    'badge'             => 1,
                    'content-available' => 1,
                ),
            ),
        );

        $response = $this->make_request( $payload );

        if ( is_wp_error( $response ) ) {
            return array(
                'success'        => 0,
                'failure'        => count( $tokens ),
                'invalid_tokens' => array(),
            );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        $invalid_tokens = array();

        // Check for invalid tokens
        if ( isset( $body['results'] ) ) {
            foreach ( $body['results'] as $index => $result ) {
                if ( isset( $result['error'] ) ) {
                    $error = $result['error'];
                    // These errors indicate the token is no longer valid
                    if ( in_array( $error, array( 'InvalidRegistration', 'NotRegistered', 'MismatchSenderId' ), true ) ) {
                        $invalid_tokens[] = $tokens[ $index ];
                    }
                }
            }
        }

        return array(
            'success'        => isset( $body['success'] ) ? (int) $body['success'] : 0,
            'failure'        => isset( $body['failure'] ) ? (int) $body['failure'] : count( $tokens ),
            'invalid_tokens' => $invalid_tokens,
        );
    }

    /**
     * Send notification to a single device.
     *
     * @param string $token     Device token.
     * @param string $title     Notification title.
     * @param string $body      Notification body.
     * @param string $image_url Optional image URL.
     * @param array  $data      Optional additional data.
     * @return bool Success status.
     */
    public function send_to_device( $token, $title, $body, $image_url = '', $data = array() ) {
        if ( ! $this->is_configured() ) {
            return false;
        }

        $notification = array(
            'title' => $title,
            'body'  => $body,
            'sound' => 'default',
        );

        if ( ! empty( $image_url ) ) {
            $notification['image'] = $image_url;
        }

        $payload = array(
            'to'           => $token,
            'notification' => $notification,
            'priority'     => 'high',
        );

        if ( ! empty( $data ) ) {
            $payload['data'] = $data;
        }

        $response = $this->make_request( $payload );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return isset( $body['success'] ) && $body['success'] > 0;
    }

    /**
     * Send notification to a topic.
     *
     * @param string $topic     Topic name.
     * @param string $title     Notification title.
     * @param string $body      Notification body.
     * @param string $image_url Optional image URL.
     * @param array  $data      Optional additional data.
     * @return bool Success status.
     */
    public function send_to_topic( $topic, $title, $body, $image_url = '', $data = array() ) {
        if ( ! $this->is_configured() ) {
            return false;
        }

        $notification = array(
            'title' => $title,
            'body'  => $body,
            'sound' => 'default',
        );

        if ( ! empty( $image_url ) ) {
            $notification['image'] = $image_url;
        }

        $payload = array(
            'to'           => '/topics/' . $topic,
            'notification' => $notification,
            'priority'     => 'high',
        );

        if ( ! empty( $data ) ) {
            $payload['data'] = $data;
        }

        $response = $this->make_request( $payload );

        return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
    }

    /**
     * Make request to FCM API.
     *
     * @param array $payload Request payload.
     * @return array|WP_Error Response or error.
     */
    private function make_request( $payload ) {
        $headers = array(
            'Authorization' => 'key=' . $this->settings['server_key'],
            'Content-Type'  => 'application/json',
        );

        return wp_remote_post( $this->fcm_url, array(
            'headers' => $headers,
            'body'    => wp_json_encode( $payload ),
            'timeout' => 30,
        ) );
    }

    /**
     * Validate server key by making a test request.
     *
     * @param string $server_key Server key to validate.
     * @return bool Whether the key is valid.
     */
    public function validate_server_key( $server_key ) {
        $headers = array(
            'Authorization' => 'key=' . $server_key,
            'Content-Type'  => 'application/json',
        );

        // Make a dry run request
        $payload = array(
            'dry_run' => true,
            'to'      => 'test_token',
            'notification' => array(
                'title' => 'Test',
                'body'  => 'Test',
            ),
        );

        $response = wp_remote_post( $this->fcm_url, array(
            'headers' => $headers,
            'body'    => wp_json_encode( $payload ),
            'timeout' => 10,
        ) );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );

        // 200 = valid key (even if token is invalid)
        // 401 = invalid key
        return $code === 200;
    }

    /**
     * Subscribe device to topic.
     *
     * @param string $token Device token.
     * @param string $topic Topic name.
     * @return bool Success status.
     */
    public function subscribe_to_topic( $token, $topic ) {
        if ( ! $this->is_configured() ) {
            return false;
        }

        $url = 'https://iid.googleapis.com/iid/v1/' . $token . '/rel/topics/' . $topic;

        $headers = array(
            'Authorization' => 'key=' . $this->settings['server_key'],
            'Content-Type'  => 'application/json',
        );

        $response = wp_remote_post( $url, array(
            'headers' => $headers,
            'body'    => '',
            'timeout' => 10,
        ) );

        return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
    }

    /**
     * Unsubscribe device from topic.
     *
     * @param string $token Device token.
     * @param string $topic Topic name.
     * @return bool Success status.
     */
    public function unsubscribe_from_topic( $token, $topic ) {
        if ( ! $this->is_configured() ) {
            return false;
        }

        $url = 'https://iid.googleapis.com/iid/v1:batchRemove';

        $headers = array(
            'Authorization' => 'key=' . $this->settings['server_key'],
            'Content-Type'  => 'application/json',
        );

        $payload = array(
            'to'                  => '/topics/' . $topic,
            'registration_tokens' => array( $token ),
        );

        $response = wp_remote_post( $url, array(
            'headers' => $headers,
            'body'    => wp_json_encode( $payload ),
            'timeout' => 10,
        ) );

        return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
    }
}
