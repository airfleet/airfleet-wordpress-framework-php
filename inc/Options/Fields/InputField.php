<?php

namespace Airfleet\Plugins\Admin\Options\Fields;

class InputField extends Field {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'text' ], $args ) );
	}

	protected function render_input( array $args, mixed $value ): void {
		$attrs = $this->attributes( $args, $value );

		printf( '<input %s />', Field::render_attributes( $attrs ) );
	}

	protected function attributes( array $args, mixed $value ): array {
		return [
			'type' => $args['type'],
			'name' => $this->input_name(),
			'id' => $this->id,
			'class' => ( $args['class'] ?? '' ) . ' regular-text',
			'value' => $value,
			'required' => $args['required'] ?? false,
			'disabled' => $args['disabled'] ?? false,
		];
	}
}
