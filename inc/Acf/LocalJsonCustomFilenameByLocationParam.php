<?php

namespace Airfleet\Framework\Acf;

/**
 * Syncs ACF local JSON for groups with a specific param value in the location rules and saves to specific filename.
 */
class LocalJsonCustomFilenameByLocationParam extends LocalJsonCustomFilename {
	protected string $param;
	protected string $value;

	public function __construct( string $json_path, string $param, string $value, string $filename, $priority = 10 ) {
		parent::__construct( $json_path, $filename, $priority );
		$this->param = $param;
		$this->value = $value;
	}

	protected function is_local_group( array $data ): bool {
		return LocalJsonByLocationParam::is_local_group_by_location_param( $data, $this->param, $this->value );
	}
}
