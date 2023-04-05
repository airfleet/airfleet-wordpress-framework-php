<?php

namespace Airfleet\Framework\Options\Fields;

class UrlField extends TextField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'url' ], $args ) );
	}
}
