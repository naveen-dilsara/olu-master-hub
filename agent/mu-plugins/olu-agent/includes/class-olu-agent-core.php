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
        add_action('rest_api_init', function() {
            register_rest_route('olu/v1', '/update', [
                'methods' => 'POST',
                'callback' => [$this, 'handle_update'],
                'permission_callback' => '__return_true' // TODO: Verify Signature
            ]);
            register_rest_route('olu/v1', '/configure', [
                'methods' => 'POST',
                'callback' => [$this, 'handle_configure'],
                'permission_callback' => '__return_true'
            ]);
        });
        
        // Auto-connect on activation
        register_activation_hook(OLU_AGENT_PATH . 'olu-agent.php', [$this, 'activate_agent']);
        
        // Admin UI
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Success Notice on Connection
        add_action('admin_notices', [$this, 'admin_notices']);
        
        // Agent Self-Update Check
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_updates']);
        
        // Handle Repo Install
        add_action('admin_post_olu_agent_install', [$this, 'handle_repo_install']);
        
        // Manual Auto-Update Check
        add_action('admin_post_olu_agent_force_update', [$this, 'handle_force_update']);
        
        // Custom Cron Schedules
        add_filter('cron_schedules', [$this, 'add_cron_intervals']);
        
        // Heartbeat Event
        add_action('olu_agent_heartbeat', [$this, 'process_heartbeat']);
        
        // Reschedule on init if missing
        add_action('init', [$this, 'schedule_heartbeat']);
    }

    public function add_cron_intervals($schedules) {
        $schedules['every_minute'] = [
            'interval' => 60,
            'display'  => __('Every Minute')
        ];
        return $schedules;
    }

    public function schedule_heartbeat() {
        if (!wp_next_scheduled('olu_agent_heartbeat')) {
            wp_schedule_event(time(), 'every_minute', 'olu_agent_heartbeat');
        }
    }

    public function process_heartbeat() {
        // Logic: Check if it's time to run auto-update based on configured interval
        $interval = (int)get_option('olu_agent_update_interval', 86400); // Default 1 day
        $last_run = (int)get_option('olu_agent_last_auto_update', 0);
        
        if ((time() - $last_run) >= $interval) {
            $this->run_auto_updates_gpl();
            update_option('olu_agent_last_auto_update', time());
        }
    }

    private function run_auto_updates_gpl() {
        $this->log("Starting GPL Auto-Update Check...");
        
        // 1. Fetch Repo
        $hub_url = 'https://masterhub.olutek.com/api/v1/repo';
        $response = wp_remote_get($hub_url, ['timeout' => 10]);
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            $this->log("Failed to fetch repo: " . (is_wp_error($response) ? $response->get_error_message() : 'HTTP Error'));
            return;
        }

        $repo_plugins = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($repo_plugins)) {
             $this->log("Repo empty or decode failed.");
             return;
        }

        // 2. Scan Installed
        if (!function_exists('get_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $installed_plugins = get_plugins();

        // 3. Compare, Download, Install
        foreach ($repo_plugins as $repo_plugin) {
            $slug = $repo_plugin['slug'];
            
            // Find installed match
            $local_file = '';
            foreach ($installed_plugins as $file => $data) {
                if (dirname($file) === $slug || $file === $slug . '.php') {
                    $local_file = $file;
                    $local_version = $data['Version'];
                    break;
                }
            }

            if ($local_file && version_compare($repo_plugin['version'], $local_version, '>')) {
                $this->log("Update found for $slug: Local $local_version < Remote {$repo_plugin['version']}");
                $this->perform_silent_update($repo_plugin['download_url'], $slug);
            }
        }
        
        // Sync back to Hub
        $this->send_handshake();
        $this->log("Auto-Update Check Complete.");
    }

    private function log($msg) {
        $file = WP_CONTENT_DIR . '/olu-agent-debug.log';
        $entry = date('Y-m-d H:i:s') . " [OLU AGENT] " . $msg . PHP_EOL;
        @file_put_contents($file, $entry, FILE_APPEND);
    }
    
    private function log($msg) {
        $file = WP_CONTENT_DIR . '/olu-agent-debug.log';
        $entry = date('Y-m-d H:i:s') . " [OLU AGENT] " . $msg . PHP_EOL;
        @file_put_contents($file, $entry, FILE_APPEND);
    }
    
    private function perform_silent_update($url, $slug) {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        
        if (!function_exists('request_filesystem_credentials')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if (false === ($credentials = request_filesystem_credentials(''))) return;
        if (!WP_Filesystem($credentials)) return;

        $skin = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader($skin);
        
        ob_start(); // Silence
        $temp_file = download_url($url);
        if (!is_wp_error($temp_file)) {
            $upgrader->install($temp_file, ['overwrite_package' => true]);
            @unlink($temp_file);
        }
        ob_end_clean();
    }
    public function handle_repo_install() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('olu_agent_install', 'nonce');
        
        $url = $_POST['download_url'] ?? '';
        $slug = $_POST['slug'] ?? '';
        
        if (empty($url)) {
            wp_redirect(admin_url('admin.php?page=olu-agent-repo&error=Missing URL'));
            exit;
        }

        // Reuse Logic: Create a Mock Request to feed into handle_update
        // But handle_update expects a JSON request object. 
        // Let's copy the logic or refactor. Copying for safety now.
        
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        
        if (!function_exists('request_filesystem_credentials')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
        global $wp_filesystem;
        
        $temp_file = download_url($url);
        if (is_wp_error($temp_file)) {
            wp_redirect(admin_url('admin.php?page=olu-agent-repo&error=' . urlencode($temp_file->get_error_message())));
            exit;
        }
        
        $skin = new WP_Ajax_Upgrader_Skin(); // Or WP_Upgrader_Skin for visible output but manual redirect
        // Ideally we want to see output. But let's keep it simple: silent install & redirect.
        
        $upgrader = new Plugin_Upgrader($skin);
        $result = $upgrader->install($temp_file, ['overwrite_package' => true]);
        @unlink($temp_file);

        if (is_wp_error($result)) {
            wp_redirect(admin_url('admin.php?page=olu-agent-repo&error=' . urlencode($result->get_error_message())));
            exit;
        }

        // Activate
        $plugin_file = $slug . '/' . $slug . '.php'; 
        // Scan for real file
        $installed = get_plugins('/' . $slug);
        if (!empty($installed)) {
             $plugin_file = $slug . '/' . key($installed);
        }
        activate_plugin($plugin_file);
        
        // Refresh Hub
        $this->send_handshake();

        exit;
    }

    public function handle_force_update() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('olu_agent_force_update', 'nonce');
        
        $this->run_auto_updates_gpl();
        
        wp_redirect(admin_url('admin.php?page=olu-agent&status=check_complete'));
        exit;
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
        );
        
        add_submenu_page(
            'olu-agent',
            'Plugin Repository',
            'Repository',
            'manage_options',
            'olu-agent-repo',
            [$this, 'render_repo_page']
        );
    }

    public function admin_notices() {
        if (isset($_GET['page']) && ($_GET['page'] === 'olu-agent' || $_GET['page'] === 'olu-agent-repo')) {
            if (isset($_GET['status']) && $_GET['status'] === 'success') {
                 echo '<div class="notice notice-success is-dismissible"><p><strong>Success!</strong> Operation completed successfully.</p></div>';
            }
            if (isset($_GET['error'])) {
                 echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> ' . esc_html($_GET['error']) . '</p></div>';
            }
        }
    }

    public function render_repo_page() {
        // Fetch Plugins from Hub
        $hub_url = 'https://masterhub.olutek.com/api/v1/repo';
        $response = wp_remote_get($hub_url, ['timeout' => 10]);
        
        $plugins = [];
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $plugins = json_decode(wp_remote_retrieve_body($response), true);
        } else {
            echo '<div class="notice notice-error"><p>Failed to fetch repository data from Master Hub.</p></div>';
        }

        ?>
        <div class="wrap">
            <h1>OLU Plugin Repository</h1>
            <p>Install plugins directly from the Master Hub.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                <?php if (empty($plugins)): ?>
                    <p>No plugins available in the repository.</p>
                <?php else: ?>
                    <?php foreach ($plugins as $plugin): ?>
                        <div class="card" style="padding: 20px; border: 1px solid #ccd0d4; background: #fff;">
                            <h2 style="margin-top: 0;"><?php echo esc_html($plugin['name']); ?></h2>
                            <p style="color: #666;">Version: <?php echo esc_html($plugin['version']); ?></p>
                            <p><?php echo esc_html($plugin['description'] ?? 'No description available.'); ?></p>
                            
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                                <input type="hidden" name="action" value="olu_agent_install">
                                <input type="hidden" name="slug" value="<?php echo esc_attr($plugin['slug']); ?>">
                                <input type="hidden" name="download_url" value="<?php echo esc_attr($plugin['download_url']); ?>">
                                <?php wp_nonce_field('olu_agent_install', 'nonce'); ?>
                                
                                <button type="submit" class="button button-primary">Install Now</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
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
        include_once ABSPATH . 'wp-admin/includes/misc.php'; // For save_mod_rewrite_rules if needed

        // Initialize Filesystem
        if (false === ($credentials = request_filesystem_credentials(''))) {
            return new WP_REST_Response(['status' => 'error', 'message' => 'Filesystem credentials required'], 500);
        }

        if (!WP_Filesystem($credentials)) {
            return new WP_REST_Response(['status' => 'error', 'message' => 'Filesystem initialization failed'], 500);
        }

        $skin = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader($skin);

        // Capture output to prevent breaking JSON response
        ob_start();

        // Case 1: Custom/GPL Update (with URL)
        if (!empty($params['download_url'])) {
            $url = $params['download_url'];
            $temp_file = download_url($url);
            
            if (is_wp_error($temp_file)) {
                ob_end_clean();
                return new WP_REST_Response(['status' => 'error', 'message' => $temp_file->get_error_message()], 500);
            }

            $result = $upgrader->install($temp_file, ['overwrite_package' => true]);
            @unlink($temp_file);

        } else {
            // Case 2: Standard WP Update via Repository
            // ... (Search logic matches previous) ...
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $all_plugins = get_plugins();
            $plugin_file = '';

            // Exact match or folder match
            foreach ($all_plugins as $file => $data) {
                if ($file === $slug . '.php' || dirname($file) === $slug) {
                    $plugin_file = $file;
                    break;
                }
            }

            if (!$plugin_file) {
                // Try searching via Text Domain as fallback
                foreach ($all_plugins as $file => $data) {
                    if (isset($data['TextDomain']) && $data['TextDomain'] === $slug) {
                        $plugin_file = $file;
                        break;
                    }
                }
            }

            if (!$plugin_file) {
                 ob_end_clean();
                 return new WP_REST_Response(['status' => 'error', 'message' => "Plugin file not found for slug: $slug"], 404);
            }

            // 2. Force WP to check for updates
            delete_site_transient('update_plugins');
            wp_update_plugins();
            
            // 3. Perform Upgrade
            $result = $upgrader->upgrade($plugin_file);
        }

        $output = ob_get_clean(); // Discard output or log if needed
        // file_put_contents(WP_CONTENT_DIR . '/olu-update.log', $output); // Debugging

        if (is_wp_error($result)) {
            return new WP_REST_Response(['status' => 'error', 'message' => 'Update Logic Failed: ' . $result->get_error_message()], 500);
        }
        
        // If result is false/null...
        if (!$result) {
             return new WP_REST_Response([
                 'status' => 'warning', 
                 'message' => 'No update performed. System returned false. Log: ' . substr(strip_tags($output), 0, 200)
             ], 200);
        }
        
        // Activate if requested
        if (!empty($params['activate']) && $params['activate']) {
             if (empty($plugin_file)) {
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

        return new WP_REST_Response(['status' => 'success', 'message' => 'Plugin Updated Successfully'], 200);
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

            <!-- Debug / Status Card -->
            <div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
                <h2>⚙️ Auto-Update Status</h2>
                <?php
                    $interval = get_option('olu_agent_update_interval', 86400);
                    $last_run = get_option('olu_agent_last_auto_update', 'Never');
                    if ($last_run !== 'Never') $last_run = date('Y-m-d H:i:s', $last_run);
                    
                    $next_cron = wp_next_scheduled('olu_agent_heartbeat');
                    $next_cron = $next_cron ? date('Y-m-d H:i:s', $next_cron) : 'Not Scheduled';
                ?>
                <p><strong>Configured Interval:</strong> <?php echo $interval; ?> seconds</p>
                <p><strong>Last Auto-Update Run:</strong> <?php echo $last_run; ?></p>
                <p><strong>Next Heartbeat (Cron):</strong> <?php echo $next_cron; ?></p>
                <p><strong>Debug Log:</strong> <code>wp-content/olu-agent-debug.log</code></p>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="margin-top:15px;">
                    <input type="hidden" name="action" value="olu_agent_force_update">
                    <?php wp_nonce_field('olu_agent_force_update', 'nonce'); ?>
                    <button type="submit" class="button button-secondary">Force Check Check Now</button>
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
        // Add success notice
        wp_redirect(admin_url('admin.php?page=olu-agent&status=success'));
        exit;
    }

    public function handle_configure($request) {
        $params = $request->get_json_params();
        $interval = $params['update_interval'] ?? null;
        
        if ($interval) {
            update_option('olu_agent_update_interval', (int)$interval);
            return new WP_REST_Response(['status' => 'success', 'message' => "Interval updated to $interval"], 200);
        }
        return new WP_REST_Response(['status' => 'error', 'message' => 'Missing interval'], 400);
    }
}
