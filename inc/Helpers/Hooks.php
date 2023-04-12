<?php

namespace Airfleet\Framework\Helpers;

class Hooks extends Helper {
	protected static function instance_name(): string {
		return 'hooks';
	}

	protected static function create_instance(): mixed {
		return new HooksImplementation();
	}
}
