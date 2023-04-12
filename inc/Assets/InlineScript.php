<?php

namespace Airfleet\Framework\Assets;

use Airfleet\Framework\Features\Feature;

/**
 * Add an inline script.
 */
class InlineScript implements Feature {
	/**
	 * The action where the script will be added.
	 *
	 * @var string
	 */
	protected string $action;

	/**
	 * The handle of the parent script the inline script will be added to.
	 *
	 * @var string
	 */
	protected string $handle;

	/**
	 * The script (excluding <script> tag).
	 *
	 * @var string
	 */
	protected string $script;

	/**
	 * "before" or "after".
	 *
	 * @var string
	 */
	protected string $position;

	/**
	 * Priority for the action.
	 *
	 * @var int
	 */
	protected int $priority;

	/**
	 * Constructor.
	 *
	 * @param array $options Array with:
	 * 	action   => (string) The action where the script will be added
	 *  handle   => (string) The handle of the parent script the inline script will be added to
	 *  script   => (string) The script (excluding <script> tag)
	 *  position => (string) "before" or "after". Optional (default is "after")
	 *  priority => (int) Priority for the action. Optional (default is 10)
	 */
	public function __construct( array $options ) {
		$this->action = $options['action'];
		$this->handle = $options['handle'];
		$this->script = $options['script'];
		$this->position = $options['position'] ?? 'after';
		$this->priority = $options['priority'] ?? 10;
	}

	public function initialize(): void {
		add_action(
			$this->action,
			function () {
				$script = $this->script();

				if ( ! $script ) {
					return;
				}
				wp_add_inline_script( $this->handle, $script, $this->position );
			},
			$this->priority
		);
	}

	protected function script(): string {
		return $this->script;
	}
}
