<?php

namespace Airfleet\Framework\Cache;

/**
 * Centralized cache manager for handling caching with WordPress object cache.
 */
class CacheManager {

	/**
	 * Whether caching is enabled.
	 *
	 * @var bool
	 */
	protected bool $cache_enabled;

	/**
	 * Cache key prefix.
	 *
	 * @var string
	 */
	protected string $cache_prefix;

	/**
	 * Default cache expiration time in seconds.
	 *
	 * @var int
	 */
	protected int $cache_expiration;

	/**
	 * Cache group name.
	 *
	 * @var string
	 */
	protected string $cache_group;

	/**
	 * Cache expiration time for directory modification time checks in seconds.
	 *
	 * @var int
	 */
	protected int $cache_expiration_dir_check;

	/**
	 * Whether to cache directory modification time checks.
	 *
	 * @var bool
	 */
	protected bool $cache_dir_checks;

	/**
	 * Constructor.
	 *
	 * @param array $config Configuration array with keys: enabled, prefix, expiration, group.
	 */
	public function __construct( array $config = [] ) {
		$defaults = [
			'enabled'              => true,
			'prefix'               => 'airfleet_',
			'expiration'           => DAY_IN_SECONDS,
			'expiration_dir_check' => HOUR_IN_SECONDS,
			'group'                => 'airfleet',
			'cache_dir_checks'     => true,
		];

		$config = array_merge( $defaults, $config );

		$this->cache_enabled               = $config['enabled'];
		$this->cache_prefix                = $config['prefix'];
		$this->cache_expiration            = $config['expiration'];
		$this->cache_group                 = $config['group'];
		$this->cache_expiration_dir_check  = $config['expiration_dir_check'];
		$this->cache_dir_checks            = $config['cache_dir_checks'];
	}

	/**
	 * Check if caching is enabled and available.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return $this->cache_enabled && wp_using_ext_object_cache();
	}

	/**
	 * Get cached data.
	 *
	 * @param string $scope Cache scope.
	 * @param string $key Cache key.
	 * @param mixed  $default_value Default value if not found.
	 * @return mixed
	 */
	public function get( string $scope, string $key, $default_value = null ) {
		if ( ! $this->is_enabled() ) {
			return $default_value;
		}
		$cache_key = $this->get_cache_key( $scope, $key );
		$cached = wp_cache_get( $cache_key, $this->cache_group );

		if ( $cached !== false ) {
			$this->track_cache_hit( $scope, $key );

			return $cached;
		}
		$this->track_cache_miss( $scope, $key );

		return $default_value;
	}

	/**
	 * Set cached data.
	 *
	 * @param string   $scope Cache scope.
	 * @param string   $key Cache key.
	 * @param mixed    $data Data to cache.
	 * @param int|null $expiration Cache expiration time in seconds.
	 * @return bool
	 */
	public function set( string $scope, string $key, $data, int $expiration = -1 ): bool {
		if ( ! $this->is_enabled() ) {
			return false;
		}
		$cache_key = $this->get_cache_key( $scope, $key );
		$expiration = $expiration === -1 ? $this->cache_expiration : $expiration;

		$result = wp_cache_set( $cache_key, $data, $this->cache_group, $expiration );

		if ( $result ) {
			$this->track_scope_key( $scope, $cache_key );
		}

		return $result;
	}

	/**
	 * Delete cached data.
	 *
	 * @param string $scope Cache scope.
	 * @param string $key Cache key.
	 * @return bool
	 */
	public function delete( string $scope, string $key ): bool {
		$cache_key = $this->get_cache_key( $scope, $key );
		$result = wp_cache_delete( $cache_key, $this->cache_group );

		if ( $result ) {
			$this->untrack_scope_key( $scope, $cache_key );
		}

		return $result;
	}

	/**
	 * Apply the standard caching pattern: check cache, generate if needed, store and return.
	 *
	 * @param string   $scope The cache scope.
	 * @param string   $key The cache key.
	 * @param callable $generator Callback to generate data if not cached.
	 * @param int $expiration Cache expiration time in seconds (-1 = default).
	 * @return mixed
	 */
	public function remember( string $scope, string $key, callable $generator, int $expiration = -1 ) {
		// 1. Get data from cache
		$cached_data = $this->get( $scope, $key );

		// 2. If there is cached data, return cached data
		if ( $cached_data !== null ) {
			return $cached_data;
		}

		// 3. Otherwise, generate data
		$data = $generator();

		// 4. Save data to cache
		$this->set( $scope, $key, $data, $expiration );

		// 5. Return data
		return $data;
	}

	/**
	 * Convenience method for caching filesystem-based data with automatic invalidation.
	 *
	 * @param string   $scope The cache scope.
	 * @param string   $key The cache key.
	 * @param callable $generator Callback to generate data if not cached.
	 * @param string   $file_path File path to check for modifications.
	 * @param int $expiration Cache expiration time in seconds (-1 = default).
	 * @return mixed
	 */
	public function remember_file( string $scope, string $key, callable $generator, string $file_path, int $expiration = -1 ) {
		// Include file modification time in cache key for automatic invalidation
		$file_mtime = file_exists( $file_path ) ? filemtime( $file_path ) : 0;
		$cache_key = $this->generate_key( $key, $file_mtime );

		return $this->remember( $scope, $cache_key, $generator, $expiration );
	}

	/**
	 * Convenience method for caching directory-based data with automatic invalidation.
	 *
	 * @param string   $scope The cache scope.
	 * @param string   $key The cache key.
	 * @param callable $generator Callback to generate data if not cached.
	 * @param string   $dir_path Directory path to check for modifications.
	 * @param int $expiration Cache expiration time in seconds (-1 = default).
	 * @return mixed
	 */
	public function remember_directory( string $scope, string $key, callable $generator, string $dir_path, int $expiration = -1 ) {
		// Include directory modification key in cache key for automatic invalidation
		$dir_mtime = $this->get_directory_modification_key( $dir_path );
		$cache_key = $this->generate_key( $key, $dir_mtime );

		return $this->remember( $scope, $cache_key, $generator, $expiration );
	}

	/**
	 * Generate a cache key from multiple components.
	 *
	 * @param mixed ...$components Components to include in the cache key.
	 * @return string
	 */
	public function generate_key( ...$components ): string {
		return md5( wp_json_encode( $components ) );
	}

	/**
	 * Get the modification key of a directory and its contents.
	 * The key changes whenever any file in the directory (or subdirectories) is added, removed, or modified.
	 *
	 * @param string $dir Directory path.
	 * @return int
	 */
	public function get_directory_modification_key( string $dir ): int {
		// Normalize the directory path to prevent issues with mixed slashes.
		$dir = str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $dir );

		// If the directory doesn't exist, there's no modification time. Return 0.
		if ( ! is_dir( $dir ) ) {
			return 0;
		}

		$generator = function () use ( $dir ) {
			return $this->generate_directory_modification_key_from_path( $dir );
		};

		if ( ! $this->cache_dir_checks ) {
			return $generator();
		}

		// Cache the directory modification key lookup itself for performance
		$modification_key_cache = $this->generate_key( 'dir_modification_key', $dir );

		return $this->remember(
			'filesystem',
			$modification_key_cache,
			$generator,
			$this->cache_expiration_dir_check
		);
	}

	/**
	 * Flush all cache entries for a specific scope.
	 *
	 * @param string $scope Cache scope to flush.
	 * @return void
	 */
	public function flush_scope( string $scope ): void {
		$scope_keys = $this->get_scope_keys( $scope );

		foreach ( $scope_keys as $cache_key ) {
			wp_cache_delete( $cache_key, $this->cache_group );
		}

		$this->clear_scope_keys( $scope );
	}

	/**
	 * Flush all cache entries.
	 *
	 * @return void
	 */
	public function flush_all(): void {
		wp_cache_flush_group( $this->cache_group );
	}

	/**
	 * Get cache statistics.
	 *
	 * @return array
	 */
	public function get_stats(): array {
		if ( ! $this->is_enabled() ) {
			return [
				'enabled' => false,
				'message' => 'Cache is disabled or object cache not available',
			];
		}

		$stats = [
			'enabled' => true,
			'total_keys' => 0,
			'scopes' => [],
		];

		foreach ( $this->get_active_scopes() as $scope ) {
			$scope_keys = $this->get_scope_keys( $scope );
			$scope_count = count( $scope_keys );
			$stats['scopes'][ $scope ] = $scope_count;
			$stats['total_keys'] += $scope_count;
		}

		return $stats;
	}

	/**
	 * Generates a modification key for a directory from its path.
	 *
	 * @param string $dir Directory path.
	 * @return int
	 */
	protected function generate_directory_modification_key_from_path( string $dir ): int {
		$file_manifest = [];
		$iterator      = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS )
		);

		/** @var \SplFileInfo $file */
		foreach ( $iterator as $file ) {
			// Create a manifest of file paths and their modification times.
			$file_manifest[ $file->getPathname() ] = $file->getMTime();
		}

		// Sort the manifest by file path to ensure a consistent order.
		ksort( $file_manifest );

		// Create a unique signature hash from the manifest.
		// crc32 is fast and sufficient for detecting changes.
		return crc32( wp_json_encode( $file_manifest ) );
	}

	/**
	 * Generate cache key for scope and key combination.
	 *
	 * @param string $scope Cache scope.
	 * @param string $key Cache key.
	 * @return string
	 */
	protected function get_cache_key( string $scope, string $key ): string {
		return $this->cache_prefix . $scope . '_' . $key;
	}

	/**
	 * Track cache key for a scope (for later bulk deletion).
	 *
	 * @param string $scope Cache scope.
	 * @param string $cache_key Full cache key.
	 * @return void
	 */
	protected function track_scope_key( string $scope, string $cache_key ): void {
		// Track the scope as active
		$this->add_active_scope( $scope );

		$scope_keys = $this->get_scope_keys( $scope );
		$scope_keys[] = $cache_key;
		$scope_keys = array_unique( $scope_keys );

		wp_cache_set(
			$this->get_scope_keys_cache_key( $scope ),
			$scope_keys,
			$this->cache_group,
			$this->cache_expiration
		);
	}

	/**
	 * Get all active scopes that have been used.
	 *
	 * @return array
	 */
	protected function get_active_scopes(): array {
		$active_scopes_key = $this->get_active_scopes_cache_key();
		$active_scopes = wp_cache_get( $active_scopes_key, $this->cache_group );

		return is_array( $active_scopes ) ? $active_scopes : [];
	}

	/**
	 * Add a scope to the active scopes list.
	 *
	 * @param string $scope Cache scope.
	 * @return void
	 */
	protected function add_active_scope( string $scope ): void {
		$active_scopes = $this->get_active_scopes();

		if ( ! in_array( $scope, $active_scopes, true ) ) {
			$active_scopes[] = $scope;

			wp_cache_set(
				$this->get_active_scopes_cache_key(),
				$active_scopes,
				$this->cache_group,
				$this->cache_expiration
			);
		}
	}

	/**
	 * Remove cache key from scope tracking.
	 *
	 * @param string $scope Cache scope.
	 * @param string $cache_key Full cache key.
	 * @return void
	 */
	protected function untrack_scope_key( string $scope, string $cache_key ): void {
		$scope_keys = $this->get_scope_keys( $scope );
		$scope_keys = array_diff( $scope_keys, [ $cache_key ] );

		wp_cache_set(
			$this->get_scope_keys_cache_key( $scope ),
			$scope_keys,
			$this->cache_group,
			$this->cache_expiration
		);
	}

	/**
	 * Get all cache keys for a scope.
	 *
	 * @param string $scope Cache scope.
	 * @return array
	 */
	protected function get_scope_keys( string $scope ): array {
		$scope_keys_key = $this->get_scope_keys_cache_key( $scope );
		$scope_keys = wp_cache_get( $scope_keys_key, $this->cache_group );

		return is_array( $scope_keys ) ? $scope_keys : [];
	}

	/**
	 * Clear all tracked keys for a scope.
	 *
	 * @param string $scope Cache scope.
	 * @return void
	 */
	protected function clear_scope_keys( string $scope ): void {
		$scope_keys_key = $this->get_scope_keys_cache_key( $scope );
		wp_cache_delete( $scope_keys_key, $this->cache_group );
	}

	/**
	 * Get cache key for scope keys tracking.
	 *
	 * @param string $scope Cache scope.
	 * @return string
	 */
	protected function get_scope_keys_cache_key( string $scope ): string {
		return $this->cache_prefix . 'scope_keys_' . $scope;
	}

	/**
	 * Get cache key for active scopes tracking.
	 *
	 * @return string
	 */
	protected function get_active_scopes_cache_key(): string {
		return $this->cache_prefix . 'active_scopes';
	}

	/**
	 * Track cache hit for debugging/statistics.
	 *
	 * @param string $scope Cache scope.
	 * @param string $key Cache key.
	 * @return void
	 */
	protected function track_cache_hit( string $scope, string $key ): void {
		if ( WP_DEBUG ) {
			do_action( 'airfleet/framework/cache/hit', $scope, $key );
		}
	}

	/**
	 * Track cache miss for debugging/statistics.
	 *
	 * @param string $scope Cache scope.
	 * @param string $key Cache key.
	 * @return void
	 */
	protected function track_cache_miss( string $scope, string $key ): void {
		if ( WP_DEBUG ) {
			do_action( 'airfleet/framework/cache/miss', $scope, $key );
		}
	}
}
