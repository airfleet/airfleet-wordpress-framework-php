<?php

namespace Airfleet\Framework\Helpers;

class Display extends Helper {
	protected static function instance_name(): string {
		return 'display';
	}

	protected static function create_instance(): mixed {
		return new DisplayImplementation();
	}
}
