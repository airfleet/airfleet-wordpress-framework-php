<?php

namespace Airfleet\Framework\Acf;

use Airfleet\Framework\Features\Feature;

/**
 * Syncs ACF JSON from a custom path.
 */
class LocalJson implements Feature {
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
	protected int $priority;


	/**
	 * Constructor
	 *
	 * @param string $json_path Path to JSON folder.
	 * @param int $priority Priority for the filters.
	 */
	public function __construct( string $json_path, $priority = 10 ) {
		$this->json_path = $json_path;
		$this->priority = $priority;
	}

	/**
	 * Gets all the location values with equal operator for a specific param.
	 * Example: you want to check if an ACF group matches the location for a specific block - you use this function to get all the "block" location values and see if it matches what you want.
	 *
	 * @param array $data $_POST data from save JSON call (filter acf/settings/save_json).
	 * @param string $param Param from which we want to get values (e.g. "block", "page_template")
	 * @return array
	 */
	public static function get_location_values( array $data, string $param, array $operators = [ '==' ] ): array {
		if ( ! isset( $data['acf_field_group'] ) || ! isset( $data['acf_field_group']['location'] ) ) {
			return [];
		}
		$templates = [];

		foreach ( $data['acf_field_group']['location'] as $group ) {
			foreach ( $group as $rule ) {
				if ( ! isset( $rule['param'] ) || ! isset( $rule['operator'] ) || ! isset( $rule['value'] ) ) {
					continue;
				}

				if ( $param === $rule['param'] && ( in_array( $rule['operator'], $operators ) || empty( $operators ) ) ) {
					$templates[] = $rule['value'];
				}
			}
		}

		return $templates;
	}

	/**
	 * Setup local ACF JSON.
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->setup_loading();
		$this->setup_saving();
		$this->setup_deleting();
	}

	/**
	 * Setup the loading of the local JSON files.
	 *
	 * @return void
	 */
	protected function setup_loading(): void {
		// ? Use init action to give the chance for all plugins/theme to load,
		// ? because LocalJsonCacheSettings relies on filters to check if caching
		// ? is enabled, but need to make sure this runs before ACF initializes
		add_action(
			'init',
			function () {
				if ( LocalJsonCacheSettings::is_enabled() ) {
					$this->setup_loading_from_cache();
				} else {
					$this->setup_loading_uncached();
				}
			},
			1
		);
	}

	protected function setup_loading_from_cache(): void {
		$cache = new LocalJsonLoadFromCache( $this->json_path, [
			'priority' => $this->priority,
			'expiration' => LocalJsonCacheSettings::expiration(),
		] );
		$cache->initialize();
	}

	protected function setup_loading_uncached(): void {
		add_filter(
			'acf/settings/load_json',
			function ( array $paths ): array {
				$paths[] = $this->json_path;

				return $paths;
			},
			$this->priority
		);
	}

	/**
	 * Setup the saving of the local JSON files.
	 *
	 * @return void
	 */
	protected function setup_saving(): void {
		add_filter(
			'acf/settings/save_json',
			function ( string $path ): string {
				if ( $this->is_local_group( $_POST ) ) {
					return $this->json_path;
				}

				return $path;
			},
			$this->priority
		);
	}

	protected function is_local_group( array $data ): bool {
		if ( ! isset( $data['acf_field_group']['key'] ) ) {
			return false;
		}
		$groups = $this->get_local_groups( $data['acf_field_group']['key'] );

		return ! empty( $groups );
	}

	protected function setup_deleting(): void {
		\add_action(
			'acf/trash_field_group',
			function ( array $field_group ): void {
				$this->delete_json( $field_group );
			},
			$this->priority
		);
	}

	protected function delete_json( array $field_group ): bool {
		$key = str_replace( '__trashed', '', $field_group['key'] );
		$groups = $this->get_local_groups( $key );
		$deleted = false;

		foreach ( $groups as $file ) {
			if ( is_readable( $file ) ) {
				unlink( $file );
				$deleted = true;
			}
		}

		return $deleted;
	}

	protected function get_local_groups( string $key ): array {
		$result = [];
		$field_groups = glob( $this->json_path . '/*.json' );

		foreach ( $field_groups as $json_file ) {
			$field_group = json_decode( file_get_contents( $json_file ), true );
			$is_valid_field_group = is_array( $field_group ) && isset( $field_group['key'] );

			if ( $is_valid_field_group && $field_group['key'] === $key ) {
				$result[] = $json_file;
			}
		}

		return $result;
	}
}
