<?php

namespace Airfleet\Framework\Helpers;

class AssetsImplementation {
	/**
	 * The default path to the manifest JSON file from within the plugin/theme.
	 *
	 * @var string
	 */
	protected string $manifest_path;

	/**
	 * The default path to the compiled assets folder from within the plugin/theme.
	 *
	 * @var string
	 */
	protected string $assets_path;

	/**
	 * Cached manifests. An array of arrays. The first key is the short slug
	 * for the plugin/theme (e.g. "analytics", "security", "theme").
	 *
	 * @var array
	 */
	protected array $manifests;

	/**
	 * Constructor
	 *
	 * @param array $options Options
	 * 	manifest_path => (string) Optional. The default path to the manifest JSON file from within the plugin/theme (defaults to "dist/parcel-manifest.json")
	 * 	assets_path   => (string) Optional. The default path to the compiled assets folder from within the plugin/theme (defaults to "dist")
	 */
	public function __construct( array $options = [] ) {
		$this->manifests = [];
		$this->manifest_path = $options['manifest_path'] ?? 'dist/parcel-manifest.json';
		$this->assets_path = $options['assets_path'] ?? 'dist';
	}

	/**
	 * Get the URL to an asset.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics") or "theme" to get an asset from the theme.
	 * @param string $asset_key The path to the asset (e.g. "assets/frontend/fonts/Roboto.woff2").
	 * @return string The URL to the compiled asset or an empty URL if not found.
	 */
	public function asset_url( string $slug, string $asset_key ): string {
		$manifest = $this->manifest( $slug );

		return $manifest[ $asset_key ] ?? $this->fallback_asset_url( $slug, $asset_key );
	}

	/**
	 * A manifest with all the assets for a plugin/theme.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics") or "theme" to get an asset from the theme.
	 * @return array The manifest if found or an empty array.
	 */
	public function manifest( string $slug ): array {
		if ( ! isset( $this->manifests[ $slug ] ) ) {
			$this->manifests[ $slug ] = $this->load_manifest( $slug );
		}

		return $this->manifests[ $slug ];
	}

	/**
	 * Gets the fallback URL for an asset.
	 * If the file exists, return the direct URL to the asset, otherwise return an empty string.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics") or "theme" to get an asset from the theme.
	 * @param string $asset_key The path to the asset (e.g. "assets/frontend/fonts/Roboto.woff2").
	 * @return string The fallback URL for the asset.
	 */
	protected function fallback_asset_urL( string $slug, string $asset_key ): string {
		$path = $this->assets_path( $slug, $asset_key );

		if ( file_exists( $path ) ) {
			return $this->assets_url( $slug, $asset_key );
		}

		return '';
	}

	/**
	 * Load a manifest for a plugin/theme.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics") or "theme" to get an asset from the theme.
	 * @return array The manifest if found or an empty array.
	 */
	protected function load_manifest( string $slug ): array {
		$raw = $this->manifest_file_content( $slug );

		if ( ! $raw ) {
			return [];
		}

		// Add the full URL to each manifest entry.
		$base = $this->assets_url( $slug, $this->assets_path );

		foreach ( $raw as $key => $value ) {
			$raw[ $key ] = $base . $value;
		}

		return $raw;
	}

	/**
	 * Get the parsed manifest file contents for a plugin/theme.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics") or "theme" to get an asset from the theme.
	 * @return array The manifest if found or an empty array.
	 */
	protected function manifest_file_content( string $slug ): array {
		$path = $this->manifest_path( $slug );

		if ( ! $path || ! file_exists( $path ) ) {
			return [];
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json = file_get_contents( $path );

		if ( ! $json ) {
			return [];
		}
		$manifest = json_decode( $json, true );

		return is_array( $manifest ) ? $manifest : [];
	}

	/**
	 * Get the path to a plugin/theme manifest.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics") or "theme" to get an asset from the theme.
	 * @return string|false The path to the manifest file or false if not found.
	 */
	protected function manifest_path( string $slug ): mixed {
		return $this->assets_path( $slug, $this->manifest_path );
	}

	/**
	 * Get the path to the assets folder of a plugin/theme.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics") or "theme" to get an asset from the theme.
	 * @param string $base Base path to the assets folder (e.g. "dist").
	 * @return mixed The path to the assets.
	 */
	protected function assets_path( string $slug, string $base ): mixed {
		if ( $slug === 'theme' ) {
			return $this->theme_path( $base );
		}

		return $this->plugin_path( $slug, $base );
	}

	/**
	 * Get the path to a theme file/folder.
	 *
	 * @param string $sub_path The file folder.
	 * @return string
	 */
	protected function theme_path( string $sub_path ): string {
		return get_template_directory() . DIRECTORY_SEPARATOR . $sub_path;
	}

	/**
	 * Get the path to a plugin file/folder.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics").
	 * @param string $sub_path The file folder.
	 * @return mixed
	 */
	protected function plugin_path( string $slug, string $sub_path ): mixed {
		$url = plugins_url( "/airfleet-{$slug}/{$sub_path}" );
		$path = wp_parse_url( $url );

		if ( ! $path ) {
			return false;
		}

		return untrailingslashit( ABSPATH ) . $path['path'];
	}

	/**
	 * Get the URL to the assets folder of a plugin/theme.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics") or "theme" to get an asset from the theme.
	 * @param string $base Base path to the assets folder (e.g. "dist").
	 * @return string|false The path to the assets folder or false if not found.
	 */
	protected function assets_url( string $slug, string $base ): mixed {
		if ( $slug === 'theme' ) {
			return $this->theme_url( $base );
		}

		return $this->plugin_url( $slug, $base );
	}

	/**
	 * Get the URL to a theme file/folder.
	 *
	 * @param string $sub_path The file folder.
	 * @return string
	 */
	protected function theme_url( string $sub_path ): string {
		return get_template_directory_uri() . '/' . $sub_path;
	}

	/**
	 * Get the URL to a plugin file/folder.
	 *
	 * @param string $slug The plugin short slug (e.g. "analytics").
	 * @param string $sub_path The file folder.
	 * @return mixed
	 */
	protected function plugin_url( string $slug, string $sub_path ): mixed {
		return plugins_url( "/airfleet-{$slug}/{$sub_path}" );
	}
}
