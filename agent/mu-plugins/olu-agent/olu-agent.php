/**
 * Plugin Name: OLU Satellite Agent
 * Description: Connects this site to the OLU Master Hub for remote management.
 * Version: 2.0.0
 * Author: Olutek Digital
 * Text Domain: olu-agent
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OLU_AGENT_VERSION', '2.0.0');
define('OLU_AGENT_PATH', plugin_dir_path(__FILE__));
define('OLU_AGENT_URL', plugin_dir_url(__FILE__));

// Initialize Agent
require_once OLU_AGENT_PATH . 'includes/class-olu-agent-core.php';

function olu_agent_init() {
    Olu_Agent_Core::instance();
}
add_action('plugins_loaded', 'olu_agent_init');

// Admin UI for Connection Management
add_action('admin_menu', function() {
    add_menu_page(
        'OLU Agent', 
        'OLU Agent', 
        'manage_options', 
        'olu-agent', 
        ['Olu_Agent_Core', 'render_admin_page'], 
        'dashicons-shield', 
        99
    );
});

// Handle Manual Connection Action
add_action('admin_post_olu_agent_connect', ['Olu_Agent_Core', 'handle_manual_connect']);
