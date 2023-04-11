<?php

namespace Airfleet\Framework\Helpers;

abstract class Helper {
	protected static $instance;

	abstract protected static function create_instance(): static;

	public static function __callStatic( string $method, array $args ): mixed {
		$instance = static::instance();

		return call_user_func_array( [ $instance, $method ], $args );
	}

	protected static function instance(): static {
		if ( ! isset( static::$instance ) ) {
			static::$instance = static::create_instance();
		}

		return static::$instance;
	}
}
