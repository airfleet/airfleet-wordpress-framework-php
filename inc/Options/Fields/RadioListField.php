<?php

namespace Airfleet\Plugins\Admin\Options\Fields;

class RadioListField extends ListField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'radio' ], $args ) );
	}
}
