<?php

namespace Airfleet\Framework\Cache;

/**
 * Trait to provide caching capabilities to classes.
 */
trait Cacheable {

	/**
	 * The cache manager instance.
	 *
	 * @var CacheManager|null
	 */
	protected static ?CacheManager $cache_manager = null;

	public static function load_cache_manager(): void {
		// Ensure the cache manager is initialized
		if ( ! self::$cache_manager ) {
			$setup = new CacheSetup();
			$setup->initialize();
			self::$cache_manager = $setup->cache_manager();
		}
	}

	/**
	 * Get the cache manager instance.
	 *
	 * @return CacheManager
	 */
	protected function cache(): CacheManager {
		self::load_cache_manager();

		return self::$cache_manager;
	}
}
