<?php
/**
 * Plugin Name: OLU Satellite Agent
 * Description: Connects this site to the OLU Master Hub for remote management.
 * Version: 2.2.0
 * Author: Olutek Digital
 * Text Domain: olu-agent
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OLU_AGENT_VERSION', '2.2.0');
define('OLU_AGENT_PATH', plugin_dir_path(__FILE__));
define('OLU_AGENT_URL', plugin_dir_url(__FILE__));

// Initialize Agent
require_once OLU_AGENT_PATH . 'includes/class-olu-agent-core.php';

function olu_agent_init() {
    Olu_Agent_Core::instance();
}
add_action('plugins_loaded', 'olu_agent_init');

// Handle Manual Connection Action
add_action('admin_post_olu_agent_connect', ['Olu_Agent_Core', 'handle_manual_connect']);

// Activation Redirect
register_activation_hook(__FILE__, 'olu_agent_activate_flag');
register_deactivation_hook(__FILE__, ['Olu_Agent_Core', 'deactivate_agent']);

function olu_agent_activate_flag() {
    add_option('olu_agent_do_activation_redirect', true);
}

add_action('admin_init', 'olu_agent_redirect');
function olu_agent_redirect() {
    if (get_option('olu_agent_do_activation_redirect', false)) {
        delete_option('olu_agent_do_activation_redirect');
        if(!isset($_GET['activate-multi'])) {
            wp_redirect(admin_url('admin.php?page=olu-agent'));
            exit;
        }
    }
}
