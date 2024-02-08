<?php

namespace Airfleet\Framework\Acf;

/**
 * Syncs ACF local JSON for custom page templates.
 */
class LocalJsonCustomTemplate extends LocalJson {
	protected string $template_path;

	public function __construct( string $json_path, string $template_path, $priority = 10 ) {
		parent::__construct( $json_path, $priority );
		$this->template_path = $template_path;
	}

	public static function is_local_group_for_custom_template( array $data, string $template_path ): bool {
		$templates = LocalJson::get_location_values( $data, 'page_template' );

		if ( ! $templates ) {
			return false;
		}
		$get_normalized_path = function ( string $path ): string {
			return str_replace( '\\', '/', $path );
		};
		$normalized_path = $get_normalized_path( $template_path );

		foreach ( $templates as $template ) {
			if ( $get_normalized_path( $template ) === $normalized_path ) {
				return true;
			}
		}

		return false;
	}

	protected function is_local_group( array $data ): bool {
		return self::is_local_group_for_custom_template( $data, $this->template_path );
	}
}
