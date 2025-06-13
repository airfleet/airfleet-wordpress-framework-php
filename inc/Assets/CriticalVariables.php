<?php

namespace Airfleet\Framework\Assets;

/**
 * Adds JS variables to the critical script.
 */
class CriticalVariables extends ScriptVariables {
	/**
	 * Constructor.
	 *
	 * @param array $options Array with:
	 *  priority  => (int) Priority for the action. Optional (default is 1)
	 *  slug      => (string) The slug of the plugin/theme
	 *  name      => (string) The name of the window variable
	 *  variables => (array) The content for the window variable. Will be converted to JS object
	 */
	public function __construct( array $options ) {
		parent::__construct(
			[
				'action' => 'wp_enqueue_scripts',
				'handle' => "{$options['slug']}-critical-scripts",
				'priority' => $options['priority'] ?? 1,
				'name' => $options['name'],
				'variables' => $options['variables'],
				'data_attributes' => $options['data_attributes'] ?? [],
			]
		);
	}
}
