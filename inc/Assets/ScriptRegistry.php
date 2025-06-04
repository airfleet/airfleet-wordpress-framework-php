<?php

namespace Airfleet\Framework\Assets;

/**
 * Registry for inline scripts.
 * Handles loading and rendering of inline scripts from files.
 */
class ScriptRegistry {
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
     * Add an inline script from a file.
     *
     * @param string $handle Unique identifier for the script.
     * @param string $file_path Absolute path to the script file.
     * @param array $props Additional properties to add as data attributes.
     * @param array $deps Dependencies for this script.
     */
    public function addScript($handle, $file_path, $props = [], $deps = []) {
        if (!file_exists($file_path)) {
            return;
        }

        $content = file_get_contents($file_path);
        if (!$content) {
            return;
        }

        $script = [
            'content' => $content,
            'props' => $this->sanitizeProps($props),
            'deps' => (array) $deps
        ];

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
            '<script class="airfleet-script-registry" id="%s"%s>%s</script>',
            esc_attr($handle),
            $props,
            $script['content']
        );
    }
}
