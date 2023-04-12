<?php

namespace Airfleet\Framework\Helpers;

class Screen extends Helper {
	protected static function instance_name(): string {
		return 'screen';
	}

	protected static function create_instance(): mixed {
		return new ScreenImplementation();
	}
}
