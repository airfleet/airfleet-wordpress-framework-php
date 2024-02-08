<?php

namespace Airfleet\Framework\Acf;

/**
 * Syncs ACF JSON from a custom path and saves to a specific filename.
 */
class LocalJsonCustomFilename extends LocalJson {
	protected string $filename;

	public function __construct( string $json_path, string $filename, $priority = 10 ) {
		parent::__construct( $json_path, $priority );
		$this->filename = $filename;
	}

	public function initialize(): void {
		parent::initialize();
		$this->setup_save_file_names();
	}

	protected function setup_save_file_names(): void {
		add_filter(
			'acf/json/save_file_name',
			function( $filename, $post, $load_path ) {
				if ( ! empty( $load_path ) ) {
					// Use the same name it was loaded from
					return basename( $load_path );
				}

				if ( ! $this->is_local_group( [ 'acf_field_group' => $post ] ) ) {
					// Use default filename if it's not a match for this group
					return $filename;
				}

				return $this->filename;
			},
			$this->priority,
			3
		);
	}
}
