<?php

namespace Airfleet\Framework\Acf;

use Airfleet\Framework\Features\Feature;

/**
 * Create an ACF options sub-page.
 */
class OptionsSubPage implements Feature {
	protected array $options;

	public function __construct( array $options ) {
		$this->options = $options;
	}

	public function initialize(): void {
		add_action(
			'acf/init',
			function () {
				\acf_add_options_sub_page( $this->options );
			}
		);
	}
}
