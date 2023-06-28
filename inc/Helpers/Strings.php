<?php

namespace Airfleet\Framework\Helpers;

class Strings extends Helper {
	protected static function instance_name(): string {
		return 'strings';
	}

	protected static function create_instance(): mixed {
		return new StringsImplementation();
	}
}
