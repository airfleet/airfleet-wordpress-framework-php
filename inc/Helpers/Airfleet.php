<?php

namespace Airfleet\Framework\Helpers;

class Airfleet extends Helper {
	protected static function instance_name(): string {
		return 'airfleet';
	}

	protected static function create_instance(): mixed {
		return new AirfleetImplementation();
	}
}
