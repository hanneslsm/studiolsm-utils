<?php
/**
 * Plugin Name: ❦ Studio Leismann · Utils
 * Plugin URI: https://Studioleismann.com/
 * Description: A collection of utility tools and features for WordPress development by Studio Leismann. Includes utility classes panel, and more.
 * Version: 1.0.0
 * Author: Hanneslsm, Studio Leismann
 * Requires at least: 6.3
 * Requires PHP: 8.1
 * Text Domain: studiolsm-utils
 * Domain Path: /languages
 *
 * @package StudiolsmUtils
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('STUDIOLSM_UTILS_PLUGIN_FILE', __FILE__);
define('STUDIOLSM_UTILS_BUILD_PATH', plugin_dir_path(__FILE__) . 'build/');
define('STUDIOLSM_UTILS_BUILD_URL', plugin_dir_url(__FILE__) . 'build/');

$bootstrap_candidates = [
    STUDIOLSM_UTILS_BUILD_PATH . 'plugin.php',
    plugin_dir_path(__FILE__) . 'src/plugin.php',
];

foreach ($bootstrap_candidates as $bootstrap) {
    if (is_readable($bootstrap)) {
        require_once $bootstrap;
        return;
    }
}

if (function_exists('wp_die')) {
    wp_die(
        esc_html__('Studiolsm Utils could not locate its bootstrap file. Please run npm run build.', 'studiolsm-utils')
    );
}

throw new RuntimeException('Studiolsm Utils could not locate its bootstrap file.');
