<?php
/**
 * Module interface for StudiolsmUtils plugin
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
 * Interface for all plugin modules
 */
interface ModuleInterface
{
    /**
     * Initialize the module
     */
    public function init(): void;

    /**
     * Get module name
     *
     * @return string
     */
    public function get_name(): string;

    /**
     * Get module version
     *
     * @return string
     */
    public function get_version(): string;

    /**
     * Check if module is active
     *
     * @return bool
     */
    public function is_active(): bool;
}