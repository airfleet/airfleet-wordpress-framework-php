<?php

namespace Airfleet\Framework\Acf;

/**
 * Create an ACF options sub-page under the "Settings" core menu.
 */
class SettingsOptionsSubPage extends OptionsSubPage {
	protected array $options;

	public function __construct( array $options ) {
		$this->options = $options + [ 'parent_slug' => 'options-general.php' ];
	}
}
