<?php

namespace Airfleet\Plugins\Admin\Options\Fields;

class CheckboxField extends InputField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'checkbox' ], $args ) );
	}

	protected function render_input( array $args, mixed $value ): void {
		$label = $args['label'] ?? '';

		if ( ! $label ) {
			parent::render_input( $args, $value );
			return;
		}
		$attrs = $this->attributes( $args, $value );

		printf(
			'<label for="%s"><input %s />%s</label>',
			$this->id,
			Field::render_attributes( $attrs ),
			$label
		);
	}

	protected function attributes( array $args, mixed $value ): array {
		return array_merge(
			parent::attributes( $args, $value ),
			[
				'value' => $args['value'] ?? $this->id,
				'checked' => $value ? true : false,
			]
		);
	}

	protected function default_sanitize( mixed $value ): mixed {
		return (bool) $value;
	}

	protected function default_validate( mixed $value ): bool {
		$required = isset( $this->args['required'] ) && $this->args['required'];

		if ( $required && ! $value ) {
			$this->add_error( "{$this->title} is required." );

			return false;
		}

		return parent::default_validate( $value );
	}
}
