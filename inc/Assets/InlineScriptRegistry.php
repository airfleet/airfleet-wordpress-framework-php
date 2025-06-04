<?php

namespace Airfleet\Framework\Assets;

/**
 * Registry for inline scripts.
 * Handles loading and rendering of inline scripts from files.
 */
class InlineScriptRegistry {
    private static $instance = null;
    private $scripts = [];

    private function __construct() {
        add_action('wp_head', [$this, 'render'], 1);
        add_action('admin_head', [$this, 'render'], 1);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add an inline script.
     *
     * @param string $handle Unique identifier for the script.
     * @param string $content The script content.
     * @param array $props Additional properties to add as data attributes.
     * @param array $deps Dependencies for this script.
     */
    public function addScript($handle, $content, $props = [], $deps = []) {
        if (!$content) {
            return;
        }

        $script = [
            'content' => $content,
            'props' => $this->sanitizeProps($props),
            'deps' => (array) $deps
        ];

        // Handle duplicate handles by appending a number
        $base_handle = $handle;
        $counter = 1;
        while (isset($this->scripts[$handle])) {
            $handle = $base_handle . '-' . $counter;
            $counter++;
        }

        $this->scripts[$handle] = $script;
    }

    private function sanitizeProps($props) {
        $sanitized = [];
        foreach ($props as $key => $value) {
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
        foreach ($script['props'] as $key => $value) {
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
