<?php

namespace Airfleet\Framework\Assets;

/**
 * Registry for inline scripts.
 * Handles loading and rendering of inline scripts from files.
 */
class InlineScriptRegistry {
    private static $instance = null;
    private $scripts = [];
    private $rendered = [];
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
        add_filter('script_loader_tag', [$this, 'maybeAttachToEnqueuedScript'], 10, 2);

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
            if ($key && $value !== null) {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Check if a script handle has a matching enqueued WordPress script.
     *
     * @param string $handle The script handle to check.
     * @return bool
     */
    private function hasMatchingEnqueuedScript($handle) {
        $wp_scripts = wp_scripts();
        return isset($wp_scripts->registered[$handle]);
    }

    /**
     * Filter callback for script_loader_tag.
     * Attaches inline scripts to matching enqueued script tags.
     *
     * @param string $tag The script tag HTML.
     * @param string $handle The script handle.
     * @return string
     */
    public function maybeAttachToEnqueuedScript($tag, $handle) {
        if (!isset($this->scripts[$handle]) || isset($this->rendered[$handle])) {
            return $tag;
        }

        // Render any unrendered dependency inline scripts first.
        $depsOutput = $this->renderDependencies($handle);
        $script = $this->scripts[$handle];
        $inlineTag = $this->buildScriptTag($handle, $script);
        $this->rendered[$handle] = true;

        return $depsOutput . $inlineTag . $tag;
    }

    /**
     * Recursively render unrendered dependency inline scripts.
     *
     * @param string $handle The script handle whose deps should be rendered.
     * @return string The concatenated HTML of rendered dependency scripts.
     */
    private function renderDependencies($handle) {
        $output = '';
        if (!isset($this->scripts[$handle])) {
            return $output;
        }

        foreach ($this->scripts[$handle]['deps'] as $dep) {
            if (isset($this->rendered[$dep]) || !isset($this->scripts[$dep])) {
                continue;
            }

            // Recurse into the dependency's own deps first.
            $output .= $this->renderDependencies($dep);
            if (!isset($this->rendered[$dep])) {
                $output .= $this->buildScriptTag($dep, $this->scripts[$dep]);
                $this->rendered[$dep] = true;
            }
        }

        return $output;
    }

    /**
     * Render scripts that don't have a matching enqueued WordPress script.
     * Scripts with a matching enqueued handle are skipped here and will be
     * attached to their enqueued script tag via the script_loader_tag filter.
     */
    public function render() {
        $sorted = $this->resolveDependencies($this->scripts);
        foreach ($sorted as $handle => $script) {
            if (isset($this->rendered[$handle])) {
                continue;
            }

            if ($this->hasMatchingEnqueuedScript($handle)) {
                continue;
            }

            $this->outputScript($handle, $script);
            $this->rendered[$handle] = true;
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

    /**
     * Build the inline script tag HTML string.
     *
     * @param string $handle The script handle.
     * @param array  $script The script data.
     * @return string
     */
    private function buildScriptTag($handle, $script) {
        $props = '';

        foreach ($script['dataAttributes'] as $key => $value) {
            $props .= sprintf(' data-%s="%s"',
                esc_attr($key),
                esc_attr($value)
            );
        }

        return sprintf(
            '<script class="airfleet-script-registry" id="%s"%s>%s</script>' . "\n",
            esc_attr($handle),
            $props,
            $script['content']
        );
    }

    private function outputScript($handle, $script) {
        echo $this->buildScriptTag($handle, $script);
    }
}
