<?php

namespace Airfleet\Framework\Assets;

use Airfleet\Framework\Features\Feature;

/**
 * Enqueue assets.
 */
class Enqueue implements Feature {
	/**
	 * The plugin/theme slug.
	 *
	 * @var string
	 */
	protected string $slug;

	/**
	 * URL to the plugin/theme folder.
	 *
	 * @var string
	 */
	protected string $url;

	/**
	 * Path to the plugin/theme folder.
	 *
	 * @var string
	 */
	protected string $path;

	/**
	 * Plugin/theme version.
	 *
	 * @var string
	 */
	protected string $version;

	/**
	 * Determine which assets will be enqueued.
	 *
	 * @var array Array of booleans or callbacks.
	 */
	protected array $enqueue;

	/**
	 * Dependencies for each asset.
	 *
	 * @var array Array of [ 'styles' => [], 'scripts' => [] ]
	 */
	protected array $dependencies;

	/**
	 * Constructor.
	 *
	 * @param array $options Enqueue properties.
	 *   $options = [
	 *     'slug'         => (string) The slug.
	 *     'url'          => (string) URL to the base folder (plugin/theme).
	 *     'path'         => (string) Path to the base folder (plugin/theme).
	 *     'version'      => (string) The version.
	 *     'enqueue'      => (array) Determine which assets will be enqueued (array of booleans or callbacks)
	 *     'dependencies' => (array) Dependencies for each asset (array of [ 'styles' => [], 'scripts' => [] ])
	 *   ]
	 */
	public function __construct( array $options ) {
		$this->slug = $options['slug'];
		$this->url = $options['url'];
		$this->path = $options['path'];
		$this->version = $options['version'];
		$this->enqueue = array_merge(
			[
				'admin' => true,
				'editor' => true,
				'frontend' => true,
				'critical' => true,
				'login' => true,
			],
			$options['enqueue'] ?? []
		);
		$this->dependencies = array_merge(
			[
				'admin' => [],
				'editor' => [],
				'frontend' => [],
				'critical' => [],
				'login' => [],
			],
			$options['dependencies'] ?? []
		);
	}

	public function initialize(): void {
		$this->enqueue_admin();
		$this->enqueue_editor();
		$this->enqueue_frontend();
		$this->enqueue_critical();
		$this->enqueue_login();
	}

	protected function enqueue_admin(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				if ( ! $this->is_enqueue_enabled( 'admin' ) ) {
					return false;
				}
				$this->enqueue_style( 'admin' );
				$this->enqueue_script( 'admin' );
			}
		);
	}

	protected function enqueue_editor(): void {
		add_action(
			'enqueue_block_editor_assets',
			function () {
				if ( ! $this->is_enqueue_enabled( 'editor' ) ) {
					return false;
				}
				$this->enqueue_style( 'editor' );
				$this->enqueue_script( 'editor' );
			}
		);
	}

	protected function enqueue_frontend(): void {
		add_action(
			'wp_enqueue_scripts',
			function () {
				if ( ! $this->is_enqueue_enabled( 'frontend' ) ) {
					return false;
				}
				$this->enqueue_style( 'frontend', 'index' );
				$this->enqueue_script( 'frontend', 'index' );
			}
		);
	}

	protected function enqueue_critical(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_critical_handle' ], 1 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_critical_handle' ], 1 );
		add_action(
			'wp_enqueue_scripts',
			function () {
				if ( ! $this->is_enqueue_enabled( 'critical' ) ) {
					return false;
				}
				$this->enqueue_critical_style( 'frontend', 'critical' );
				$this->enqueue_critical_script( 'frontend', 'critical' );
			}
		);
	}

	protected function enqueue_login(): void {
		add_action(
			'login_enqueue_scripts',
			function () {
				if ( ! $this->is_enqueue_enabled( 'login' ) ) {
					return false;
				}
				$this->enqueue_style( 'login' );
				$this->enqueue_script( 'login' );
			}
		);
	}

	protected function enqueue_style( string $key, string $filename = '' ): void {
		$file_name = $filename ?: $key;
		$file_path = "/dist/assets/{$key}/styles/{$file_name}.entry.css";

		if ( ! file_exists( $this->path . $file_path ) ) {
			return;
		}
		wp_enqueue_style(
			"{$this->slug}-{$key}-styles",
			$this->url . $file_path,
			$this->dependencies( $key, 'styles' ),
			$this->version
		);
	}

	protected function enqueue_script( string $key, string $filename = '' ): void {
		$file_name = $filename ?: $key;
		$file_path = "/dist/assets/{$key}/scripts/{$file_name}.entry.js";

		if ( ! file_exists( $this->path . $file_path ) ) {
			return;
		}
		wp_enqueue_script(
			"{$this->slug}-{$key}-scripts",
			$this->url . $file_path,
			$this->dependencies( $key, 'scripts' ),
			$this->version,
			true
		);
	}

	/**
	 * Add the handle for critical styles/scripts.
	 * Later on, one can use wp_add_inline_style and wp_add_inline_script to add
	 * inline styles and scripts to the header.
	 *
	 * @return void
	 */
	public function enqueue_critical_handle(): void {
		if ( ! $this->is_enqueue_enabled( 'critical' ) ) {
			return;
		}
		wp_register_style(
			"{$this->slug}-critical-styles",
			false,
			$this->dependencies( 'critical', 'styles' ),
			$this->version
		);
		wp_enqueue_style( "{$this->slug}-critical-styles" );

		wp_register_script(
			"{$this->slug}-critical-scripts",
			'',
			$this->dependencies( 'critical', 'styles' ),
			$this->version,
			false
		);
		wp_enqueue_script( "{$this->slug}-critical-scripts" );
	}

	protected function enqueue_critical_style( string $key = 'frontend', string $filename = 'critical' ): void {
		$file_name = $filename ?: $key;
		$file_path = "/dist/assets/{$key}/styles/{$file_name}.entry.css";

		if ( ! file_exists( $this->path . $file_path ) ) {
			return;
		}
		$css = file_get_contents( $this->path . $file_path );

		if ( ! $css ) {
			return;
		}
		wp_add_inline_style( "{$this->slug}-critical-styles", $css );
	}

	protected function enqueue_critical_script( string $key = 'frontend', string $filename = 'critical' ): void {
		$file_name = $filename ?: $key;
		$file_path = "/dist/assets/{$key}/scripts/{$file_name}.entry.js";

		if ( ! file_exists( $this->path . $file_path ) ) {
			return;
		}
		$js = file_get_contents( $this->path . $file_path );

		if ( ! $js ) {
			return;
		}
		wp_add_inline_script( "{$this->slug}-critical-scripts", $js );
	}

	protected function is_enqueue_enabled( string $key ): bool {
		if ( ! isset( $this->enqueue[ $key ] ) ) {
			return false;
		}

		if ( is_bool( $this->enqueue[ $key ] ) ) {
			return $this->enqueue[ $key ];
		}

		if ( is_callable( $this->enqueue[ $key ] ) ) {
			// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			return call_user_func( $this->enqueue[ $key ] );
		}

		return false;
	}

	protected function dependencies( string $key, string $type ): array {
		if ( ! isset( $this->dependencies[ $key ] ) || empty( $this->dependencies[ $key ] ) ) {
			return [];
		}

		if ( ! isset( $this->dependencies[ $key ][ $type ] ) ) {
			return [];
		}

		return $this->dependencies[ $key ][ $type ];
	}
}
