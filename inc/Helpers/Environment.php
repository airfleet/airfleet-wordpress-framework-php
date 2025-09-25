<?php

namespace Airfleet\Framework\Helpers;

class Environment extends Helper {
	protected static function instance_name(): string {
		return 'environment';
	}

	protected static function create_instance(): mixed {
		return new EnvironmentImplementation();
	}
}
