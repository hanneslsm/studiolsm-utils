<?php
/**
 * Autoloader for StudiolsmUtils plugin
 *
 * @package StudiolsmUtils
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * StudiolsmUtils Autoloader class
 */
class StudiolsmUtils_Autoloader
{
    /**
     * Register the autoloader
     */
    public static function register(): void
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Autoload classes
     *
     * @param string $class_name
     */
    public static function autoload(string $class_name): void
    {
        // Only handle our namespace
        if (!str_starts_with($class_name, 'StudiolsmUtils\\')) {
            return;
        }

        // Remove namespace prefix
        $class_name = substr($class_name, strlen('StudiolsmUtils\\'));
        
        // Convert to file path
        $file_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
        $file_path = strtolower(str_replace('_', '-', $file_path));
        
        // Try different file naming conventions
        $possible_files = [
            STUDIOLSM_UTILS_PLUGIN_DIR . 'includes/class-' . $file_path . '.php',
            STUDIOLSM_UTILS_PLUGIN_DIR . 'modules/' . $file_path . '.php',
        ];

        // Special handling for modules
        if (str_starts_with($class_name, 'Modules\\')) {
            $module_parts = explode('\\', $class_name);
            if (count($module_parts) >= 3) {
                // Convert CamelCase to kebab-case for module directory names
                $module_name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $module_parts[1]));
                $class_file = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', end($module_parts)));
                $possible_files[] = STUDIOLSM_UTILS_PLUGIN_DIR . 'modules/' . $module_name . '/class-' . $class_file . '.php';
            }
        }

        foreach ($possible_files as $file) {
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}

// Register the autoloader
StudiolsmUtils_Autoloader::register();