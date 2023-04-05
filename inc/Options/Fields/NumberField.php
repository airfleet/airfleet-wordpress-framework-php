<?php

namespace Airfleet\Plugins\Admin\Options\Fields;

class NumberField extends InputField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'number' ], $args ) );
	}

	protected function attributes( array $args, mixed $value ): array {
		return array_merge(
			parent::attributes( $args, $value ),
			[
				'readonly' => $args['readonly'] ?? false,
				'placeholder' => $args['placeholder'] ?? false,
				'min' => $args['min'] ?? false,
				'max' => $args['max'] ?? false,
				'step' => $args['step'] ?? false,
			]
		);
	}

	protected function default_sanitize( mixed $value ): mixed {
		return (float) $value;
	}

	protected function default_validate( mixed $value ): bool {
		if ( isset( $this->args['min'] ) && $value < $this->args['min'] ) {
			$this->add_error( "{$this->title} must be at least {$this->args['min']}." );

			return false;
		}

		if ( isset( $this->args['max'] ) && $value > $this->args['max'] ) {
			$this->add_error( "{$this->title} must be at most {$this->args['max']}." );

			return false;
		}

		return parent::default_validate( $value );
	}
}
