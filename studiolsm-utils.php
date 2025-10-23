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

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('STUDIOLSM_UTILS_VERSION', '3.0.0');
define('STUDIOLSM_UTILS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('STUDIOLSM_UTILS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('STUDIOLSM_UTILS_PLUGIN_FILE', __FILE__);

// Autoloader
require_once STUDIOLSM_UTILS_PLUGIN_DIR . 'includes/class-autoloader.php';

/**
 * Main plugin class
 */
class StudiolsmUtils
{
    /**
     * Single instance of the plugin
     *
     * @var StudiolsmUtils|null
     */
    private static ?StudiolsmUtils $instance = null;

    /**
     * Array of enabled modules
     *
     * @var array
     */
    private array $modules = [];

    /**
     * Array of available modules
     *
     * @var array
     */
    private array $available_modules = [
        'utility-classes' => [
            'name' => 'CSS Classes Panel',
            'description' => 'Gutenberg Inspector panel for utility/helper CSS classes with SCSS parsing and responsive breakpoints.',
            'class' => 'StudiolsmUtils\\Modules\\CssClassesPanel\\CssClassesPanelModule',
            'enabled' => true, // Default enabled
        ],
        // Future modules can be added here
        // 'block-extensions' => [
        //     'name' => 'Block Extensions',
        //     'description' => 'Additional block editor enhancements and custom blocks.',
        //     'class' => 'StudiolsmUtils\\Modules\\BlockExtensions\\BlockExtensionsModule',
        //     'enabled' => false,
        // ],
    ];

    /**
     * Get single instance of the plugin
     *
     * @return StudiolsmUtils
     */
    public static function get_instance(): StudiolsmUtils
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks(): void
    {
        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'admin_init']);
        
        // Activation and deactivation hooks
        register_activation_hook(STUDIOLSM_UTILS_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(STUDIOLSM_UTILS_PLUGIN_FILE, [$this, 'deactivate']);
    }

    /**
     * Initialize plugin
     */
    public function init(): void
    {
        $this->load_modules();
    }

    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain(): void
    {
        load_plugin_textdomain(
            'studiolsm-utils',
            false,
            dirname(plugin_basename(STUDIOLSM_UTILS_PLUGIN_FILE)) . '/languages'
        );
    }

    /**
     * Load enabled modules
     */
    private function load_modules(): void
    {
        $enabled_modules = get_option('studiolsm_utils_enabled_modules', array_keys(
            array_filter($this->available_modules, static fn($module) => $module['enabled'])
        ));

        foreach ($enabled_modules as $module_id) {
            if (!isset($this->available_modules[$module_id])) {
                continue;
            }

            $module_config = $this->available_modules[$module_id];
            
            if (class_exists($module_config['class'])) {
                try {
                    $this->modules[$module_id] = new $module_config['class']();
                } catch (Exception $e) {
                    error_log("StudiolsmUtils: Failed to load module {$module_id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu(): void
    {
        add_options_page(
            __('Studio Leismann Utils', 'studiolsm-utils'),
            __('Studio Leismann Utils', 'studiolsm-utils'),
            'manage_options',
            'studiolsm-utils',
            [$this, 'admin_page']
        );
    }

    /**
     * Initialize admin settings
     */
    public function admin_init(): void
    {
        register_setting(
            'studiolsm_utils_settings',
            'studiolsm_utils_enabled_modules',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_enabled_modules'],
                'default' => array_keys(
                    array_filter($this->available_modules, static fn($module) => $module['enabled'])
                ),
            ]
        );

        add_settings_section(
            'studiolsm_utils_modules',
            __('Available Modules', 'studiolsm-utils'),
            [$this, 'modules_section_callback'],
            'studiolsm-utils'
        );

        add_settings_field(
            'enabled_modules',
            __('Enabled Modules', 'studiolsm-utils'),
            [$this, 'enabled_modules_callback'],
            'studiolsm-utils',
            'studiolsm_utils_modules'
        );
    }

    /**
     * Sanitize enabled modules setting
     *
     * @param mixed $input
     * @return array
     */
    public function sanitize_enabled_modules($input): array
    {
        if (!is_array($input)) {
            return [];
        }

        return array_intersect($input, array_keys($this->available_modules));
    }

    /**
     * Modules section callback
     */
    public function modules_section_callback(): void
    {
        echo '<p>' . esc_html__('Choose which modules to enable for your site.', 'studiolsm-utils') . '</p>';
    }

    /**
     * Enabled modules field callback
     */
    public function enabled_modules_callback(): void
    {
        $enabled_modules = get_option('studiolsm_utils_enabled_modules', array_keys(
            array_filter($this->available_modules, static fn($module) => $module['enabled'])
        ));

        foreach ($this->available_modules as $module_id => $module_config) {
            $checked = in_array($module_id, $enabled_modules, true);
            
            echo '<div style="margin-bottom: 10px;">';
            echo '<label>';
            printf(
                '<input type="checkbox" name="studiolsm_utils_enabled_modules[]" value="%s" %s /> ',
                esc_attr($module_id),
                checked($checked, true, false)
            );
            echo '<strong>' . esc_html($module_config['name']) . '</strong>';
            echo '</label>';
            
            if (!empty($module_config['description'])) {
                echo '<br><span style="color: #666; font-size: 13px; margin-left: 20px;">';
                echo esc_html($module_config['description']);
                echo '</span>';
            }
            echo '</div>';
        }
    }

    /**
     * Display admin page
     */
    public function admin_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle form submission
        if (isset($_POST['submit'])) {
            check_admin_referer('studiolsm_utils_settings-options');
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div style="background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #007cba;">
                <h2><?php esc_html_e('Studio Leismann Utils', 'studiolsm-utils'); ?></h2>
                <p><?php esc_html_e('A modular collection of utility tools and features for WordPress development.', 'studiolsm-utils'); ?></p>
                <p><strong><?php esc_html_e('Version:', 'studiolsm-utils'); ?></strong> <?php echo esc_html(STUDIOLSM_UTILS_VERSION); ?></p>
            </div>

            <form action="options.php" method="post">
                <?php
                settings_fields('studiolsm_utils_settings');
                do_settings_sections('studiolsm-utils');
                submit_button();
                ?>
            </form>

            <div style="margin-top: 30px;">
                <h2><?php esc_html_e('Active Modules', 'studiolsm-utils'); ?></h2>
                <?php if (empty($this->modules)): ?>
                    <p><?php esc_html_e('No modules are currently active.', 'studiolsm-utils'); ?></p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($this->modules as $module_id => $module): ?>
                            <li>
                                <strong><?php echo esc_html($this->available_modules[$module_id]['name']); ?></strong>
                                - <?php echo esc_html($this->available_modules[$module_id]['description']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Plugin activation
     */
    public function activate(): void
    {
        // Set default options
        if (!get_option('studiolsm_utils_enabled_modules')) {
            $default_modules = array_keys(
                array_filter($this->available_modules, static fn($module) => $module['enabled'])
            );
            update_option('studiolsm_utils_enabled_modules', $default_modules);
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate(): void
    {
        // Clean up any necessary data
        flush_rewrite_rules();
    }

    /**
     * Get available modules
     *
     * @return array
     */
    public function get_available_modules(): array
    {
        return $this->available_modules;
    }

    /**
     * Get loaded modules
     *
     * @return array
     */
    public function get_loaded_modules(): array
    {
        return $this->modules;
    }

}

// Initialize the plugin
StudiolsmUtils::get_instance();
