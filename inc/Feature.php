<?php

namespace Airfleet\Framework;

interface Feature {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function initialize(): void;
}
