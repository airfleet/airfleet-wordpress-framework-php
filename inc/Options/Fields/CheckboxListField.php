<?php

namespace Airfleet\Framework\Options\Fields;

class CheckboxListField extends ListField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'checkbox' ], $args ) );
		$this->is_array = true;
	}
}
