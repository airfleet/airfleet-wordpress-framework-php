<?php

namespace Airfleet\Plugins\Admin\Options\Fields;

class UrlField extends TextField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'url' ], $args ) );
	}
}
