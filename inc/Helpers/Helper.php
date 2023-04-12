<?php

namespace Airfleet\Framework\Helpers;

abstract class Helper {
	protected static array $instances = [];

	abstract protected static function instance_name(): string;
	abstract protected static function create_instance(): mixed;

	public static function __callStatic( string $method, array $args ) {
		$instance = static::instance();

		return call_user_func_array( [ $instance, $method ], $args );
	}

	protected static function instance(): mixed {
		$name = static::instance_name();

		if ( ! isset( static::$instances[ $name ] ) ) {
			static::$instances[ $name ] = static::create_instance();
		}

		return static::$instances[ $name ];
	}
}
