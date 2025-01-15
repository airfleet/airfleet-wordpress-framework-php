<?php

namespace Airfleet\Framework\Options\Fields;

class TextField extends InputField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'text' ], $args ) );
	}

	protected function attributes( array $args, mixed $value ): array {
		return array_merge(
			parent::attributes( $args, $value ),
			[
				'readonly' => $args['readonly'] ?? false,
				'placeholder' => $args['placeholder'] ?? false,
				'minlength' => $args['minlength'] ?? false,
				'maxlength' => $args['maxlength'] ?? false,
			]
		);
	}

	protected function default_sanitize( mixed $value ): mixed {
		return sanitize_text_field( $value );
	}

	protected function default_validate( mixed $value ): bool {
		$length = strlen( $value );

		if ( $this->is_required() && $length === 0 ) {
			$this->add_error( "{$this->title} is required." );

			return false;
		}

		if ( isset( $this->args['minlength'] ) && $length < $this->args['minlength'] ) {
			$this->add_error( "{$this->title} must be at least {$this->args['minlength']} characters." );

			return false;
		}

		if ( isset( $this->args['maxlength'] ) && $length > $this->args['maxlength'] ) {
			$this->add_error( "{$this->title} must be at most {$this->args['maxlength']} characters." );

			return false;
		}

		return parent::default_validate( $value );
	}
}
