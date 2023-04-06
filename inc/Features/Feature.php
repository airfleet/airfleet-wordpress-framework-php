<?php

namespace Airfleet\Framework\Features;

interface Feature {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function initialize(): void;
}
