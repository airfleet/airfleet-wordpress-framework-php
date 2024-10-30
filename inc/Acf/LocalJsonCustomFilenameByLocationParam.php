<?php

namespace Airfleet\Framework\Acf;

/**
 * Syncs ACF local JSON for groups with a specific param value in the location rules and saves to specific filename.
 */
class LocalJsonCustomFilenameByLocationParam extends LocalJsonCustomFilename {
	protected string $param;
	protected string $value;
	protected array $operators;

	public function __construct( string $json_path, string $param, string $value, string $filename, array $operators = [ '==' ], $priority = 10 ) {
		parent::__construct( $json_path, $filename, $priority );
		$this->param = $param;
		$this->value = $value;
		$this->operators = $operators;
	}

	protected function is_local_group( array $data ): bool {
		return LocalJsonByLocationParam::is_local_group_by_location_param( $data, $this->param, $this->value, $this->operators );
	}
}
