<?php

namespace Airfleet\Framework;

class Features implements Feature {
	protected array $features;

	public function __construct( array $features ) {
		$this->features = $features;
	}

	public function initialize(): void {
		foreach ( $this->features as $feature ) {
			$feature->initialize();
		}
	}
}
