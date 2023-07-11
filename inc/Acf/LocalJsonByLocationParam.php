<?php

namespace Airfleet\Framework\Acf;

/**
 * Syncs ACF local JSON for groups with a specific param value in the location rules.
 */
class LocalJsonByLocationParam extends LocalJson {
	protected string $param;
	protected string $value;

	public function __construct( string $json_path, $priority, string $param, string $value ) {
		parent::__construct( $json_path, $priority );
		$this->param = $param;
		$this->value = $value;
	}

	protected function is_local_group( array $data ): bool {
		$results = LocalJson::get_location_values( $data, $this->param );

		if ( ! $results ) {
			return false;
		}

		foreach ( $results as $result ) {
			if ( $this->value === $result ) {
				return true;
			}
		}

		return false;
	}
}
