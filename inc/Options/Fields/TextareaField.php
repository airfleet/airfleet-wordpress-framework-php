<?php

namespace Airfleet\Plugins\Admin\Options\Fields;

class TextareaField extends Field {
	protected function render_input( array $args, mixed $value ): void {
		$attrs = $this->attributes( $args );

		printf(
			'<textarea %s>%s</textarea>',
			Field::render_attributes( $attrs ),
			$value
		);
	}

	protected function attributes( array $args ): array {
		return [
			'name' => $this->input_name(),
			'id' => $this->id,
			'class' => ( $args['class'] ?? '' ) . ' large-text code',
			'required' => $args['required'] ?? false,
			'disabled' => $args['disabled'] ?? false,
			'readonly' => $args['readonly'] ?? false,
			'placeholder' => $args['placeholder'] ?? false,
			'minlength' => $args['minlength'] ?? false,
			'maxlength' => $args['maxlength'] ?? false,
			'rows' => $args['rows'] ?? false,
			'cols' => $args['cols'] ?? false,
		];
	}

	protected function default_sanitize( mixed $value ): mixed {
		return sanitize_textarea_field( $value );
	}

	protected function default_validate( mixed $value ): bool {
		$length = strlen( $value );

		if ( isset( $this->args['required'] ) && $this->args['required'] && $length === 0 ) {
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
