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
        
        // Agent Self-Update Check
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_updates']);
    }
    
    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $hub_url = 'https://masterhub.olutek.com/api/v1/check-version';
        $plugin_slug = 'olu-agent';
        $plugin_file = 'olu-agent/olu-agent.php';
        
        // Check Master Hub
        $response = wp_remote_post($hub_url, [
            'body' => json_encode(['slug' => $plugin_slug]),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 5
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
            return $transient;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['new_version']) && version_compare($body['new_version'], OLU_AGENT_VERSION, '>')) {
            $obj = new stdClass();
            $obj->slug = $plugin_slug;
            $obj->new_version = $body['new_version'];
            $obj->url = 'https://masterhub.olutek.com';
            $obj->package = $body['package']; // The ZIP url
            
            $transient->response[$plugin_file] = $obj;
        }

        return $transient;
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
        
        // Scan Plugins
        $plugins = $this->scan_plugins();

        // Send Handshake to Master Hub
        $hub_url = 'https://masterhub.olutek.com/api/v1/handshake';
        
        $response = wp_remote_post($hub_url, [
            'body' => json_encode([
                'url' => get_site_url(),
                'public_key' => $keys['public'],
                'wp_version' => get_bloginfo('version'),
                'plugins' => $plugins
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'blocking' => false // Don't block activation
        ]);
    }

    private function scan_plugins() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins');
        
        // Force check for updates (lite check)
        wp_update_plugins();
        $update_transient = get_site_transient('update_plugins');

        $formatted = [];
        foreach ($all_plugins as $path => $data) {
            $slug = dirname($path);
            if ($slug === '.') {
                $slug = basename($path, '.php');
            }
            
            $formatted[] = [
                'name' => $data['Name'],
                'slug' => $slug,
                'version' => $data['Version'],
                'is_active' => in_array($path, $active_plugins),
                'has_update' => isset($update_transient->response[$path])
            ];
        }
        
        return $formatted;
    }

    public function handle_update($request) {
        $params = $request->get_json_params();
        $slug = $params['slug'] ?? '';
        
        if (empty($slug)) {
            return new WP_REST_Response(['status' => 'error', 'message' => 'Missing plugin slug'], 400);
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        if (!function_exists('request_filesystem_credentials')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
        global $wp_filesystem;
        
        $skin = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader($skin);

        // Case 1: Custom/GPL Update (with URL)
        if (!empty($params['download_url'])) {
            $url = $params['download_url'];
            $temp_file = download_url($url);
            
            if (is_wp_error($temp_file)) {
                return new WP_REST_Response(['status' => 'error', 'message' => $temp_file->get_error_message()], 500);
            }

            $result = $upgrader->install($temp_file, ['overwrite_package' => true]);
            @unlink($temp_file);

        } else {
            // Case 2: Standard WP Update (No URL provided)
            // Need to find the plugin file path from slug
            $plugins = get_plugins();
            $plugin_file = '';
            foreach ($plugins as $file => $data) {
                if (dirname($file) === $slug || $file === $slug . '.php') {
                    $plugin_file = $file;
                    break;
                }
            }

            if (!$plugin_file) {
                return new WP_REST_Response(['status' => 'error', 'message' => 'Plugin not found for slug: ' . $slug], 404);
            }
            
            // Ensure WP knows about the update
            wp_update_plugins();
            
            $result = $upgrader->upgrade($plugin_file);
        }

        if (is_wp_error($result)) {
            return new WP_REST_Response(['status' => 'error', 'message' => 'Update Failed: ' . $result->get_error_message()], 500);
        }
        
        // Activate if requested
        if (!empty($params['activate']) && $params['activate']) {
             if (empty($plugin_file)) {
                 // Try to guess again if it was a zip install
                 $plugin_file = $slug . '/' . $slug . '.php'; 
                 $installed = get_plugins('/' . $slug);
                 if (!empty($installed)) {
                     $plugin_file = $slug . '/' . key($installed);
                 }
             }
             activate_plugin($plugin_file);
        }
        
        // --- NEW: Trigger Handshake to refresh Hub Data ---
        $this->send_handshake();
        // --------------------------------------------------

        return new WP_REST_Response(['status' => 'success', 'message' => 'Plugin Updated'], 200);
    }

    public function send_handshake() {
        $keys = get_option('olu_agent_keys', []);
        if (empty($keys['public'])) return;

        $plugins = $this->scan_plugins();
        $hub_url = 'https://masterhub.olutek.com/api/v1/handshake';
        
        wp_remote_post($hub_url, [
            'body' => json_encode([
                'url' => get_site_url(),
                'public_key' => $keys['public'],
                'wp_version' => get_bloginfo('version'),
                'plugins' => $plugins
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'blocking' => false,
            'timeout' => 5
        ]);
    }

    public static function deactivate_agent() {
        $keys = get_option('olu_agent_keys', []);
        if (empty($keys['public'])) {
            return;
        }

        $hub_url = 'https://masterhub.olutek.com/api/v1/disconnect';
        
        wp_remote_post($hub_url, [
            'body' => json_encode([
                'url' => get_site_url(),
                'public_key' => $keys['public']
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'blocking' => false, // Don't block deactivation
            'timeout' => 5
        ]);
        
        delete_option('olu_agent_keys');
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
        
        // Manual Activation - BLOCKING for debug
        $keys = [
            'public' => 'MOCK_PUBLIC_KEY_' . time(),
            'private' => 'MOCK_PRIVATE_KEY_' . time()
        ];
        update_option('olu_agent_keys', $keys);
        
        $plugins = $instance->scan_plugins();
        
        $hub_url = 'https://masterhub.olutek.com/api/v1/handshake';
        
        $response = wp_remote_post($hub_url, [
            'body' => json_encode([
                'url' => get_site_url(),
                'public_key' => $keys['public'],
                'wp_version' => get_bloginfo('version'),
                'plugins' => $plugins
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'blocking' => true, // BLOCKING to see error
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wp_die('Connection Failed: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code !== 200) {
            wp_die("Server Error ($code): " . $body);
        }

        // Add success notice
        wp_redirect(admin_url('admin.php?page=olu-agent&status=success'));
        exit;
    }
}
