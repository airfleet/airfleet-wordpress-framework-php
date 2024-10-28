<?php

namespace Airfleet\Framework\Acf;

/**
 * Syncs ACF local JSON for groups with a specific param value in the location rules.
 */
class LocalJsonByLocationParam extends LocalJson {
	protected string $param;
	protected string $value;

	public function __construct( string $json_path, string $param, string $value, $priority = 10 ) {
		parent::__construct( $json_path, $priority );
		$this->param = $param;
		$this->value = $value;
	}

	public static function is_local_group_by_location_param( array $data, string $param, string $value, array $operators = [ '==' ] ): bool {
		$results = LocalJson::get_location_values( $data, $param, $operators );

		if ( ! $results ) {
			return false;
		}

		foreach ( $results as $result ) {
			if ( $value === $result ) {
				return true;
			}
		}

		return false;
	}

	protected function is_local_group( array $data ): bool {
		return self::is_local_group_by_location_param( $data, $this->param, $this->value );
	}
}
