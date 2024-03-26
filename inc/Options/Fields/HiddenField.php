<?php

namespace Airfleet\Framework\Options\Fields;

class HiddenField extends TextField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'hidden' ], $args ) );
	}
}
