<?php

namespace Airfleet\Framework\Helpers;

class Assets extends Helper {
	protected static function instance_name(): string {
		return 'assets';
	}

	protected static function create_instance(): mixed {
		return new AssetsImplementation();
	}
}
