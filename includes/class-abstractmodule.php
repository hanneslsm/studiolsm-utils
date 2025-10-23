<?php
/**
 * Abstract base module class for StudiolsmUtils plugin
 *
 * @package StudiolsmUtils
 */

declare(strict_types=1);

namespace StudiolsmUtils\Includes;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract base class for all plugin modules
 */
abstract class AbstractModule implements ModuleInterface
{
    /**
     * Module name
     *
     * @var string
     */
    protected string $name = '';

    /**
     * Module version
     *
     * @var string
     */
    protected string $version = '1.0.0';

    /**
     * Whether the module is active
     *
     * @var bool
     */
    protected bool $is_active = false;

    /**
     * Module constructor
     */
    public function __construct()
    {
        $this->init();
        $this->is_active = true;
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * Get module version
     *
     * @return string
     */
    public function get_version(): string
    {
        return $this->version;
    }

    /**
     * Check if module is active
     *
     * @return bool
     */
    public function is_active(): bool
    {
        return $this->is_active;
    }

    /**
     * Initialize the module - must be implemented by child classes
     */
    abstract public function init(): void;

    /**
     * Add WordPress hooks - can be overridden by child classes
     */
    protected function add_hooks(): void
    {
        // Default implementation - child classes can override
    }

    /**
     * Load module assets - can be overridden by child classes
     */
    protected function load_assets(): void
    {
        // Default implementation - child classes can override
    }

    /**
     * Get module directory path
     *
     * @return string
     */
    protected function get_module_dir(): string
    {
        $reflection = new \ReflectionClass($this);
        return dirname($reflection->getFileName()) . '/';
    }

    /**
     * Get module URL
     *
     * @return string
     */
    protected function get_module_url(): string
    {
        $module_dir = $this->get_module_dir();
        return str_replace(STUDIOLSM_UTILS_PLUGIN_DIR, STUDIOLSM_UTILS_PLUGIN_URL, $module_dir);
    }
}