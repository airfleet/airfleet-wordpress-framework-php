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

	protected function is_local_group( array $data ): bool {
		$templates = LocalJson::get_location_values( $data, 'page_template' );

		if ( ! $templates ) {
			return false;
		}
		$normalized_path = $this->normalized_path( $this->template_path );

		foreach ( $templates as $template ) {
			if ( $this->normalized_path( $template ) === $normalized_path ) {
				return true;
			}
		}

		return false;
	}

	protected function normalized_path( string $path ): string {
		return str_replace( '\\', '/', $path );
	}
}
