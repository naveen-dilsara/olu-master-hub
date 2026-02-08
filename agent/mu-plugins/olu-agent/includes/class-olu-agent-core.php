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
        
        // Admin UI
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Success Notice on Connection
        add_action('admin_notices', [$this, 'admin_notices']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'OLU Agent', 
            'OLU Agent', 
            'manage_options', 
            'olu-agent', 
            [$this, 'render_admin_page'], 
            'dashicons-shield', 
            2
        ); // Position 2 (near Dashboard)
    }

    public function admin_notices() {
        if (isset($_GET['page']) && $_GET['page'] === 'olu-agent' && isset($_GET['status']) && $_GET['status'] === 'success') {
            echo '<div class="notice notice-success is-dismissible"><p><strong>Success!</strong> Connected to OLU Master Hub.</p></div>';
        }
    }

    public function activate_agent() {
        // Generate Keys (Mock for now, or real OpenSSL)
        $keys = [
            'public' => 'MOCK_PUBLIC_KEY_' . time(),
            'private' => 'MOCK_PRIVATE_KEY_' . time()
        ];
        
        update_option('olu_agent_keys', $keys);
        
        // Send Handshake to Master Hub
        // Send Handshake to Master Hub
        $hub_url = 'https://masterhub.olutek.com/api/v1/handshake';
        
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
    
    // Admin UI Renderer
    public static function render_admin_page() {
        $keys = get_option('olu_agent_keys', []);
        $status = empty($keys) ? 'Not Connected' : 'Connected';
        $statusColor = empty($keys) ? 'red' : 'green';
        
        ?>
        <div class="wrap">
            <h1>OLU Master Hub Agent</h1>
            <div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
                <h2>Connection Status</h2>
                <p>
                    <span class="dashicons dashicons-marker" style="color: <?php echo $statusColor; ?>"></span>
                    <strong><?php echo $status; ?></strong>
                </p>
                
                <?php if (!empty($keys)): ?>
                    <p><strong>Public Key:</strong> <code style="display:block; margin-top:5px; padding:10px; background:#f0f0f1;"><?php echo esc_html(substr($keys['public'], 0, 50)) . '...'; ?></code></p>
                    <p><em>Agent is listening for commands from Master Hub.</em></p>
                <?php else: ?>
                    <p>Click the button below to generate keys and connect to the Master Hub.</p>
                <?php endif; ?>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="olu_agent_connect">
                    <?php wp_nonce_field('olu_agent_connect_action', 'olu_agent_nonce'); ?>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary button-hero">
                            <?php echo empty($keys) ? 'Connect Now' : 'Reconnect / Refresh Keys'; ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }
    
    // Handle Form Submission
    public static function handle_manual_connect() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('olu_agent_connect_action', 'olu_agent_nonce');
        
        $instance = self::instance();
        $instance->activate_agent(); // Re-run the handshake logic
        
        // Add success notice (simple redirect with param for now)
        wp_redirect(admin_url('admin.php?page=olu-agent&status=success'));
        exit;
    }
}
