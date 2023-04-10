<?php

namespace Airfleet\Framework\Plugin;

use Airfleet\Framework\Features\Feature;

/**
 * Add action links in the plugin row in the admin plugins list.
 */
class ActionLinks implements Feature {
	/**
	 * The plugin/theme slug.
	 *
	 * @var string
	 */
	protected string $slug;

	/**
	 * Path to the plugin/theme folder.
	 *
	 * @var string
	 */
	protected string $path;

	/**
	 * The action links.
	 *
	 * @var array|callback
	 */
	protected mixed $links;

	/**
	 * Constructor.
	 *
	 * @param array $options Enqueue properties.
	 *   $options = [
	 *     'slug'  => (string) The slug
	 *     'path'  => (string) Path to the base folder (plugin/theme)
	 *     'links' => (array|callback) The action links
	 *   ]
	 */
	public function __construct( array $options ) {
		$this->slug = $options['slug'];
		$this->path = $options['path'];
		$this->links = $options['links'];
	}

	public function initialize(): void {
		add_filter(
			'plugin_action_links',
			function ( array $plugin_actions, string $plugin_file ): array {
				if ( ! $this->is_plugin( $plugin_file ) ) {
					return $plugin_actions;
				}

				return array_merge( $this->action_links(), $plugin_actions );
			},
			10,
			2
		);
	}

	protected function is_plugin( string $plugin_file ): bool {
		$this_plugin = basename( $this->path ) . "/{$this->slug}.php";

		return $plugin_file === $this_plugin;
	}

	protected function action_links(): array {
		if ( is_callable( $this->links ) ) {
			// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			return call_user_func( $this->links );
		}

		return $this->links;
	}
}
