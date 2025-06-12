<?php

namespace Airfleet\Framework\Assets;

/**
 * Registry for inline scripts.
 * Handles loading and rendering of inline scripts from files.
 */
class InlineScriptRegistry {
    private static $instance = null;
    private $scripts = [];
    private $initialized = false;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Get the singleton instance.
     *
     * @return self
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the registry by setting up WordPress hooks.
     */
    public function initialize() {
        if ($this->initialized) {
            return;
        }

        add_action('wp_head', [$this, 'render'], 1);
        add_action('admin_head', [$this, 'render'], 1);

        $this->initialized = true;
    }

    /**
     * Add an inline script.
     *
     * @param string $handle Unique identifier for the script.
     * @param string $content The script content.
     * @param array $dataAttributes Additional properties to add as data attributes.
     * @param array $deps Dependencies for this script.
     */
    public function addScript($handle, $content, $dataAttributes = [], $deps = []) {
        if (!$content) {
            return;
        }

        $content = apply_filters('airfleet/framework/inline-script-registry/content', $content, $handle);
        $dataAttributes = apply_filters('airfleet/framework/inline-script-registry/data-attributes', $this->sanitizeDataAttributes($dataAttributes), $handle);
        $deps = apply_filters('airfleet/framework/inline-script-registry/deps', (array) $deps, $handle);

        $script = [
            'content' => $content,
            'dataAttributes' => $dataAttributes,
            'deps' => $deps
        ];

        // Handle duplicate handles by appending a number
        $base_handle = $handle;
        $counter = 1;
        while (isset($this->scripts[$handle])) {
            $handle = $base_handle . '-' . $counter;
            $counter++;
        }

        $script = apply_filters('airfleet/framework/inline-script-registry/add-script', $script, $handle);
        $this->scripts[$handle] = $script;
    }

    private function sanitizeDataAttributes($dataAttributes) {
        $sanitized = [];
        foreach ($dataAttributes as $key => $value) {
            $key = sanitize_key($key);
            $value = (string) $value;
            if ($key && $value) {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    public function render() {
        $sorted = $this->resolveDependencies($this->scripts);
        foreach ($sorted as $handle => $script) {
            $this->outputScript($handle, $script);
        }
    }

    private function resolveDependencies($scripts) {
        $sorted = [];
        $visited = [];

        foreach ($scripts as $handle => $script) {
            if (!isset($visited[$handle])) {
                $this->visitScript($handle, $scripts, $visited, $sorted);
            }
        }

        return $sorted;
    }

    private function visitScript($handle, $scripts, &$visited, &$sorted) {
        $visited[$handle] = true;

        if (!isset($scripts[$handle])) {
            return;
        }

        foreach ($scripts[$handle]['deps'] as $dep) {
            if (!isset($visited[$dep])) {
                $this->visitScript($dep, $scripts, $visited, $sorted);
            }
        }

        $sorted[$handle] = $scripts[$handle];
    }

    private function outputScript($handle, $script) {
        $props = '';
        foreach ($script['dataAttributes'] as $key => $value) {
            $props .= sprintf(' data-%s="%s"',
                esc_attr($key),
                esc_attr($value)
            );
        }

        printf(
            '<script class="airfleet-script-registry" id="%s"%s>%s</script>' . "\n",
            esc_attr($handle),
            $props,
            $script['content']
        );
    }
}
