<?php

namespace Airfleet\Framework\Acf;

use Airfleet\Framework\Cache\Cacheable;
use Airfleet\Framework\Features\Feature;

/**
 * Loads local ACF JSON files to and from object cache.
 */
class LocalJsonLoadFromCache implements Feature {
	use Cacheable;

	/**
	 * Path to JSON folder.
	 *
	 * @var string
	 */
	protected string $json_path;

	/**
	 * Priority for the filters.
	 *
	 * @var integer
	 */
	protected int $priority = 10;

	/**
	 * Cache key for storing/retrieving field groups.
	 * Automatically generated when needed.
	 *
	 * @var string
	 */
	protected string $cache_key = '';

	/**
	 * Cache key prefix.
	 *
	 * @var string
	 */
	protected string $cache_key_prefix = 'field_groups';

	/**
	 * Cache scope for CacheManager.
	 *
	 * @var string
	 */
	protected string $cache_scope = 'acf_local_json';

	/**
	 * Cache expiration time in seconds.
	 *
	 * @var int
	 */
	protected int $cache_expiration = DAY_IN_SECONDS;

	/**
	 * Constructor
	 *
	 * @param string $json_path Path to JSON folder.
	 * @param array $options Optional configuration options.
	 */
	public function __construct( string $json_path, array $options = [] ) {
		$this->json_path = $json_path;

		// Set priority if provided
		if ( isset( $options['priority'] ) && is_int( $options['priority'] ) ) {
			$this->priority = $options['priority'];
		}

		// Set cache key prefix if provided
		if ( isset( $options['cache_key_prefix'] ) && is_string( $options['cache_key_prefix'] ) ) {
			$this->cache_key_prefix = $options['cache_key_prefix'];
		}

		// Set cache scope if provided
		if ( isset( $options['cache_scope'] ) && is_string( $options['cache_scope'] ) ) {
			$this->cache_scope = $options['cache_scope'];
		}

		// Set cache expiration if provided
		if ( isset( $options['cache_expiration'] ) && is_int( $options['cache_expiration'] ) ) {
			$this->cache_expiration = $options['cache_expiration'];
		}
	}

	/**
	 * Setup local ACF JSON.
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->setup_loading();
		$this->setup_cache_invalidation();
	}

	/**
	 * Invalidate cache for this specific ACF Local JSON path.
	 */
	public function invalidate_cache(): void {
		$this->cache()->delete( $this->cache_scope, $this->cache_key );
	}

	/**
	 * Invalidate all cached ACF Local JSON paths.
	 *
	 * @return void
	 */
	public function invalidate_all_cache(): void {
		$this->cache()->flush_scope( $this->cache_scope );
	}

	protected function setup_loading(): void {
		\add_action(
			'acf/init',
			function() {
				$index = $this->get_field_group_index();

				foreach ( $index as $field_groups ) {
					foreach ( $field_groups as $field_group ) {
						\acf_add_local_field_group( $field_group );
					}
				}
			},
			$this->priority
		);
	}

	protected function setup_cache_invalidation(): void {
		// Invalidate cache on field group save
		add_action(
			'save_post',
			function( $post_id, $post ) {
				if ( $post->post_type === 'acf-field-group' ) {
					$key = $post->post_name;

					if ( $this->is_local_group( $key ) ) {
						// Selectively invalidate the current path
						$this->invalidate_cache();
					}
				}
			},
			$this->priority,
			2
		);

		// Invalidate cache on field group deletion
		\add_action(
			'acf/trash_field_group',
			function ( array $field_group ): void {
				$key = str_replace( '__trashed', '', $field_group['key'] );

				if ( $this->is_local_group( $key ) ) {
					// Selectively invalidate the current path
					$this->invalidate_cache();
				}
			},
			$this->priority
		);

		// Explicitly invalidate cache based on custom action
		add_action(
			'airfleet/framework/acf/local_json_cache/invalidate',
			function() {
				$this->invalidate_cache();

				// Delete all entries in this group
				$this->invalidate_all_cache();
			},
			$this->priority
		);
	}

	protected function get_field_group_index(): array {
		return $this->cache()->remember_directory(
			$this->cache_scope,
			$this->cache_key(),
			fn () => $this->build_field_group_index(),
			$this->json_path,
			$this->cache_expiration
		);
	}

    protected function build_field_group_index(): array {
		$index = [];
		$field_groups = glob( $this->json_path . '/*.json' );

		if ( $field_groups === false ) {
			return [];
		}

		foreach ( $field_groups as $json_file ) {
			try {
				$content = file_get_contents( $json_file );

				if ( $content === false ) {
					continue;
				}
				$field_group = json_decode( $content, true );

				if ( json_last_error() !== JSON_ERROR_NONE ) {
					error_log( '[LocalJsonLoadFromCache] Invalid JSON in file: ' . $json_file );
					continue;
				}

				if ( ! is_array( $field_group ) || ! isset( $field_group['key'] ) ) {
					continue;
				}

				$key = $field_group['key'];
				$index[ $key ][] = $field_group;
			} catch ( \Throwable $e ) {
				error_log( '[LocalJsonLoadFromCache] Error reading ACF JSON file ' . $json_file . ': ' . $e->getMessage() );
				continue;
			}
		}

		return $index;
	}

	protected function is_local_group( $key ): bool {
		$index = $this->get_field_group_index();
		$is_local_group = ! empty( $index[ $key ] );

		return $is_local_group;
	}

	protected function cache_key(): string {
		if ( empty( $this->cache_key ) ) {
			$this->cache_key = $this->cache()->generate_key( $this->cache_key_prefix, $this->json_path );
		}

		return $this->cache_key;
	}
}
