<?php

namespace Airfleet\Framework\Acf;

use Airfleet\Framework\Features\Feature;
use Airfleet\Framework\Helpers\Strings;

/**
 * Syncs ACF JSON from a custom path, using natural filenames and different sub-folders based on ACF type.
 * The location should have the following folders created, otherwise the files are not saved:
 *   field-groups
 *   option-pages
 *   post-types
 *   taxonomies
 */
class LocalJsonCategorized implements Feature {
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
	 * ACF JSON group types and folder names.
	 *
	 * @var array
	 */
	protected array $json_types = [
		'acf-post-type' => 'post-types',
		'acf-field-group' => 'field-groups',
		'acf-taxonomy' => 'taxonomies',
		'acf-ui-options-page' => 'option-pages',
	];


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
	 * Setup local ACF JSON.
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->setup_loading();
		$this->setup_saving();
	}

	/**
	 * Setup the loading of the local JSON files.
	 *
	 * @return void
	 */
	protected function setup_loading(): void {
		if ( LocalJsonCacheSettings::is_enabled() ) {
			$this->setup_loading_from_cache();
		} else {
			$this->setup_loading_uncached();
		}
	}

	protected function setup_loading_from_cache(): void {
		$paths = $this->loading_paths();

		foreach ( $paths as $path ) {
			$cache = new LocalJsonLoadFromCache( $path, [
				'priority' => $this->priority,
				'expiration' => LocalJsonCacheSettings::expiration(),
			] );
			$cache->initialize();
		}
	}

	protected function setup_loading_uncached(): void {
		add_filter(
			'acf/json/load_paths',
			function ( array $paths ): array {
				return array_merge( $paths, $this->loading_paths() );
			},
			$this->priority
		);
	}

	protected function loading_paths(): array {
		$paths = [];

		foreach ( $this->json_types as $folder_name ) {
			$paths[] = $this->json_path . '/' . $folder_name;
		}

		return $paths;
	}

	/**
	 * Setup the saving of the local JSON files.
	 *
	 * @return void
	 */
	protected function setup_saving(): void {
		$this->setup_save_paths();
		$this->setup_save_file_names();
		$this->prioritize_views_paths();
	}

	protected function setup_save_paths(): void {
		foreach ( $this->json_types as $type => $folder_name ) {
			add_filter(
				'acf/settings/save_json/type=' . $type,
				function () use ( $folder_name ): string {
					return $this->json_path . '/' . $folder_name;
				},
				$this->priority
			);
		}
	}

	protected function prioritize_views_paths(): void {
		add_filter(
			'acf/json/save_paths',
			function ( $paths, $post ) {
				// Check if it's a path for a view
				$is_views_path = function ( string $path ): bool {
					$normalized = str_replace( '\\', '/', $path );

					return str_contains( $normalized, '/views' );
				};

				// Get all views paths
				$views_paths = array_filter(
					$paths,
					function ( $path ) use ( $is_views_path ) {
						return $is_views_path( $path );
					}
				);

				// If we don't have any views paths, return the original save paths
				if ( empty( $views_paths ) ) {
					return $paths;
				}

				return $views_paths;
			},
			$this->priority,
			2
		);
	}

	protected function setup_save_file_names(): void {
		add_filter(
			'acf/json/save_file_name',
			function( $filename, $post, $load_path ) {
				if ( ! empty( $load_path ) ) {
					// Use the same name it was loaded from
					return basename( $load_path );
				}

				if ( $this->is_group_for_airfleet_view( $post ) ) {
					// Ignore views
					return $filename;
				}
				$title = $post['title'] ?? '';

				if ( empty( $title ) ) {
					// No title, use default filename
					return $filename;
				}

				// Create name based on title
				return Strings::convert( $title )->fromTitle()->toKebab() . '.json';
			},
			$this->priority,
			3
		);
	}

	protected function is_group_for_airfleet_view( array $data ): bool {
		if ( ! isset( $data['location'] ) ) {
			return false;
		}
		$view_params = [
			'block',
			'airfleet_block',
			'airfleet_component',
			'page_template',
		];

		foreach ( $data['location'] as $group ) {
			foreach ( $group as $rule ) {
				if ( ! isset( $rule['param'] ) || ! isset( $rule['operator'] ) || ! isset( $rule['value'] ) ) {
					continue;
				}

				if ( in_array( $rule['param'], $view_params, true ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
