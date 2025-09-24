<?php

namespace Airfleet\Framework\Cache;

use Airfleet\Framework\Features\Feature;

/**
 * Sets up and manages a caching system.
 */
class CacheSetup implements Feature {

	/**
	 * The cache manager instance.
	 *
	 * @var CacheManager
	 */
	protected ?CacheManager $cache_manager = null;

	public function initialize(): void {
		$this->register_cache_actions();
	}

	public function cache_manager(): CacheManager {
		if ( $this->cache_manager === null ) {
			// Delay cache manager instantiation until needed
			$this->cache_manager = \apply_filters(
				'airfleet/framework/cache/manager',
				new CacheManager( [
					'enabled'              => apply_filters( 'airfleet/framework/cache/enabled', true ),
					'prefix'               => 'airfleet_',
					'expiration'           => apply_filters( 'airfleet/framework/cache/expiration', DAY_IN_SECONDS ),
					'cache_dir_checks'     => apply_filters( 'airfleet/framework/cache/cache_dir_checks', true ),
					'expiration_dir_check' => apply_filters( 'airfleet/framework/cache/expiration_dir_check', HOUR_IN_SECONDS ),
					'group'                => 'airfleet_framework',
				] )
			);
		}

		return $this->cache_manager;
	}

	/**
	 * Flush all caches.
	 *
	 * @return void
	 */
	public function flush_all_caches(): void {
		$this->cache_manager()->flush_all();
	}

	/**
	 * Register cache-related actions and hooks.
	 *
	 * @return void
	 */
	protected function register_cache_actions(): void {
		// Add action to flush all caches
		add_action( 'airfleet/framework/cache/flush', [ $this, 'flush_all_caches' ] );

		// Flush cache when switching themes
		add_action( 'switch_theme', [ $this, 'flush_all_caches' ] );

		// Flush cache when plugins are activated/deactivated
		add_action( 'activated_plugin', [ $this, 'flush_all_caches' ] );
		add_action( 'deactivated_plugin', [ $this, 'flush_all_caches' ] );
	}
}
