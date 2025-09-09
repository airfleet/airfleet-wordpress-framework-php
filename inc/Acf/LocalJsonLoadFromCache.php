<?php

namespace Airfleet\Framework\Acf;

use Airfleet\Framework\Features\Feature;

/**
 * Loads local ACF JSON files to and from object cache.
 */
class LocalJsonLoadFromCache implements Feature {
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
	 *
	 * @var string
	 */
	protected string $cache_key;

	/**
	 * Cache group for object cache.
	 *
	 * @var string
	 */
	protected string $cache_group = 'airfleet_framework_acf_local_json';

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

		// Set cache key if provided, otherwise generate from path
		if ( isset( $options['cache_key'] ) && is_string( $options['cache_key'] ) ) {
			$this->cache_key = $options['cache_key'];
		} else {
			$this->cache_key = 'field_groups_' . md5( $json_path );
		}

		// Set cache group if provided
		if ( isset( $options['cache_group'] ) && is_string( $options['cache_group'] ) ) {
			$this->cache_group = $options['cache_group'];
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

	public function invalidate_cache(): void {
		wp_cache_delete( $this->cache_key, $this->cache_group );
	}

	public function invalidate_all_cache(): void {
		wp_cache_flush_group( $this->cache_group );
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
			'acf/save_post',
			function( $post_id ) {
				if ( get_post_type( $post_id ) === 'acf-field-group' ) {
					// Always invalidate on save because we may be adding a new file to the current path
					$this->invalidate_cache();
				}
			},
			$this->priority
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
		// Try object cache first
		$index = wp_cache_get( $this->cache_key, $this->cache_group );

		if ( $index !== false && $this->is_cache_valid( $index ) ) {
			return $index['data'];
		}

		// Build fresh index
		$field_groups_data = $this->build_field_group_index();

		// Cache with metadata
		$cache_data = [
			'data' => $field_groups_data,
			'timestamp' => time(),
			'directory_mtime' => filemtime( $this->json_path ),
		];

		wp_cache_set( $this->cache_key, $cache_data, $this->cache_group, $this->cache_expiration );

		return $field_groups_data;
	}

	protected function is_cache_valid( array $cache_data ): bool {
		if ( ! isset( $cache_data['directory_mtime'] ) ) {
			return false;
		}
		$current_mtime = filemtime( $this->json_path );

		// Cache is valid if directory hasn't been modified since we cached
		return $current_mtime !== false && $current_mtime === $cache_data['directory_mtime'];
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
}
