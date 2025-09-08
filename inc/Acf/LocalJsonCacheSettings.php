<?php

namespace Airfleet\Framework\Acf;

use Airfleet\Framework\Features\Feature;

class LocalJsonCacheSettings implements Feature {
	protected static ?LocalJsonCacheSettings $instance = null;

	protected bool $enabled;
	protected int $expiration;

	public function initialize(): void {
		$this->enabled = apply_filters( 'airfleet/framework/acf/local_json_cache/enabled', true );
		$this->expiration = apply_filters( 'airfleet/framework/acf/local_json_cache/expiration', DAY_IN_SECONDS );
	}

	protected static function instance(): LocalJsonCacheSettings {
		if ( self::$instance === null ) {
			self::$instance = new self();
			self::$instance->initialize();
		}

		return self::$instance;
	}

	public static function is_enabled(): bool {
		return self::instance()->enabled;
	}

	public static function expiration(): int {
		return self::instance()->expiration;
	}
}
