<?php
/**
 * Plugin Name: BC Security
 * Plugin URI: https://github.com/chesio/bc-security
 * Description: Helps keeping WordPress websites secure. Plugin requires PHP 5.6 or newer to run.
 * Version: 0.1.0-beta
 * Author: Česlav Przywara <ceslav@przywara.cz>
 * Author URI: https://www.chesio.com
 * Requires at least: 4.7
 * Tested up to: 4.8
 * Text Domain: bc-security
 * Domain Path: /languages
 */

// Throw in some constants
define('BC_SECURITY_PLUGIN_DIR', __DIR__);
define('BC_SECURITY_PLUGIN_FILE', __FILE__);


if (version_compare(PHP_VERSION, '5.6', '<')) {
    // Warn user that his/her PHP version is too low for this plugin to function.
    add_action('admin_notices', function () {
        echo '<div class="error"><p>';
        echo esc_html('BC Security requires PHP 5.6 to function properly. Please upgrade your PHP version. The plugin has been auto-deactivated.', 'bc-security');
        echo '</p></div>';
        // https://make.wordpress.org/plugins/2015/06/05/policy-on-php-versions/
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }, 10, 0);

    // Self deactivate.
    add_action('admin_init', function () {
        deactivate_plugins(plugin_basename(BC_SECURITY_PLUGIN_FILE));
    }, 10, 0);

    // Bail.
    return;
}


// Get autoloader
require_once __DIR__ . '/includes/autoload.php';

// Load the plugin
$bc_security = new \BlueChip\Security\Plugin($GLOBALS['wpdb']);
$bc_security->load();
