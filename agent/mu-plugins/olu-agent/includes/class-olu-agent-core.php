<?php

if (!defined('ABSPATH')) {
    exit;
}

class Olu_Agent_Core {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Register REST API endpoints
        add_action('rest_api_init', [$this, 'register_routes']);
        
        // Auto-connect on activation
        register_activation_hook(OLU_AGENT_PATH . 'olu-agent.php', [$this, 'activate_agent']);
    }

    public function activate_agent() {
        // Generate Keys (Mock for now, or real OpenSSL)
        $keys = [
            'public' => 'MOCK_PUBLIC_KEY_' . time(),
            'private' => 'MOCK_PRIVATE_KEY_' . time()
        ];
        
        update_option('olu_agent_keys', $keys);
        
        // Send Handshake to Master Hub
        $hub_url = 'http://localhost:8000/api/v1/handshake'; // Hardcoded for local dev
        
        $response = wp_remote_post($hub_url, [
            'body' => json_encode([
                'url' => get_site_url(),
                'public_key' => $keys['public'],
                'wp_version' => get_bloginfo('version')
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'blocking' => false // Don't block activation
        ]);
    }

    public function register_routes() {
        register_rest_route('olu/v1', '/handshake', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_handshake'],
            'permission_callback' => '__return_true', // Validation happens inside via signature
        ]);
        
        register_rest_route('olu/v1', '/update', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_update'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function handle_handshake($request) {
        return new WP_REST_Response(['status' => 'success', 'message' => 'Agent Ready'], 200);
    }

    public function handle_update($request) {
        // Todo: Verify Signature
        return new WP_REST_Response(['status' => 'pending', 'message' => 'Update Queued'], 200);
    }
}
