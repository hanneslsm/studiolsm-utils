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

        // Remove namespace prefix and normalise
        $relative = substr($class_name, strlen('StudiolsmUtils\\'));
        $relative = ltrim($relative, '\\');

        if ($relative === '') {
            return;
        }

        $parts = explode('\\', $relative);

        $normalise = static function (string $segment): string {
            $segment = str_replace('_', '-', $segment);
            $segment = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $segment);
            return strtolower($segment);
        };

        $normalised_parts = array_map($normalise, $parts);
        $last = array_pop($normalised_parts);
        $directory = $normalised_parts ? implode(DIRECTORY_SEPARATOR, $normalised_parts) . DIRECTORY_SEPARATOR : '';

        // Try with and without the 'class-' prefix
        $path_variants = [
            STUDIOLSM_UTILS_PLUGIN_DIR . $directory . 'class-' . $last . '.php',
            STUDIOLSM_UTILS_PLUGIN_DIR . $directory . $last . '.php',
        ];

        // Legacy fallback without camel-case splitting
        $legacy_parts = array_map(static fn($segment) => strtolower(str_replace('_', '-', $segment)), $parts);
        $legacy_last = array_pop($legacy_parts);
        $legacy_file = 'class-' . $legacy_last . '.php';
        $legacy_directory = $legacy_parts ? implode(DIRECTORY_SEPARATOR, $legacy_parts) . DIRECTORY_SEPARATOR : '';
        $path_variants[] = STUDIOLSM_UTILS_PLUGIN_DIR . $legacy_directory . $legacy_file;
        $path_variants[] = STUDIOLSM_UTILS_PLUGIN_DIR . $legacy_directory . $legacy_last . '.php';

        foreach (array_unique($path_variants) as $file_path) {
            if (is_readable($file_path)) {
                require_once $file_path;
                return;
            }
        }
    }
}

// Register the autoloader
StudiolsmUtils_Autoloader::register();
