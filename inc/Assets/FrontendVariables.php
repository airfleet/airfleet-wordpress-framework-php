<?php

namespace Airfleet\Framework\Assets;

/**
 * Adds JS variables to the frontend script.
 */
class FrontendVariables extends ScriptVariables {
	/**
	 * Constructor.
	 *
	 * @param array $options Array with:
	 *  slug      => (string) The slug of the plugin/theme
	 *  name      => (string) The name of the window variable
	 *  variables => (array) The content for the window variable. Will be converted to JS object
	 */
	public function __construct( array $options ) {
		parent::__construct(
			[
				'action' => 'wp_enqueue_scripts',
				'handle' => "{$options['slug']}-frontend-scripts",
				'name' => $options['name'],
				'variables' => $options['variables'],
				'scripts_attributes' => $options['scripts_attributes'] ?? [],
			]
		);
	}
}
