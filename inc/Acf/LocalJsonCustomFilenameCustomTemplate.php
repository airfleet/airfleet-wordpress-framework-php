<?php

namespace Airfleet\Framework\Acf;

/**
 * Syncs ACF local JSON for custom page templates and saves to custom filename.
 */
class LocalJsonCustomFilenameCustomTemplate extends LocalJsonCustomFilename {
	protected string $template_path;

	public function __construct( string $json_path, string $template_path, string $filename, $priority = 10 ) {
		parent::__construct( $json_path, $filename, $priority );
		$this->template_path = $template_path;
	}

	protected function is_local_group( array $data ): bool {
		return LocalJsonCustomTemplate::is_local_group_for_custom_template( $data, $this->template_path );
	}
}
