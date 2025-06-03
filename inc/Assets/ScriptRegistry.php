class ScriptRegistry {
    private static $instance = null;
    private $scripts = [];
    private $critical_scripts = [];

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

    public function addScript($handle, $content, $props = [], $deps = [], $is_critical = false) {
        $script = [
            'content' => $content,
            'props' => $this->sanitizeProps($props),
            'deps' => (array) $deps,
            'is_critical' => $is_critical
        ];

        if ($is_critical) {
            $this->critical_scripts[$handle] = $script;
        } else {
            $this->scripts[$handle] = $script;
        }
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
        // First render critical scripts
        $this->renderScripts($this->critical_scripts);
        // Then render regular scripts
        $this->renderScripts($this->scripts);
    }

    private function renderScripts($scripts) {
        $sorted = $this->resolveDependencies($scripts);
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
            '<script id="%s"%s>%s</script>',
            esc_attr($handle),
            $props,
            $script['content']
        );
    }
}