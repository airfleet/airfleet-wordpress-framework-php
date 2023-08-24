<?php

namespace Airfleet\Framework\Acf;

/**
 * Create an ACF options sub-page under the "Tools" core menu.
 */
class ToolsOptionsSubPage extends OptionsSubPage {
	protected array $options;

	public function __construct( array $options ) {
		$this->options = $options + [ 'parent_slug' => 'tools.php' ];
	}
}
