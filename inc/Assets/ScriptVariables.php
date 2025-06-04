<?php

namespace Airfleet\Framework\Assets;

/**
 * Adds JS variables.
 */
class ScriptVariables extends InlineScript {
	/**
	 * The name of the window variable.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * The content for the window variable. Will be converted to JS object.
	 *
	 * @var array
	 */
	protected mixed $variables;

	/**
	 * Constructor.
	 *
	 * @param array $options Array with:
	 * 	action    => (string) The action where the script will be added
	 *  handle    => (string) The handle of the parent script the inline script will be added to
	 *  position  => (string) "before" or "after". Optional (default is "before")
	 *  priority  => (int) Priority for the action. Optional (default is 10)
	 *  name      => (string) The name of the window variable
	 *  variables => (array|callable) The content for the window variable. Will be converted to JS object
	 */
	public function __construct( array $options ) {
		parent::__construct(
			[
				'action' => $options['action'],
				'handle' => $options['handle'],
				'script' => '',
				'position' => 'before',
				'priority' => $options['priority'] ?? 10,
				'scripts_attributes' => $options['scripts_attributes'] ?? [],
			]
		);
		$this->name = $options['name'];
		$this->variables = $options['variables'];
	}

	protected function script(): string {
		return "window.{$this->name} = " . wp_json_encode( $this->js_content() ) . ';';
	}

	protected function js_content(): array {
		if ( is_callable( $this->variables ) ) {
			// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			return call_user_func( $this->variables );
		}

		return $this->variables;
	}
}
