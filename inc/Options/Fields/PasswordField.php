<?php

namespace Airfleet\Framework\Options\Fields;

class PasswordField extends TextField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'password' ], $args ) );
	}
}
