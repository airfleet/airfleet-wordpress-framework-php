<?php

namespace Airfleet\Framework\Options\Fields;

class SelectField extends Field {
	protected function render_input( array $args, mixed $value ): void {
		$attrs = $this->attributes( $args );
		$options = Field::options( $args );

		printf(
			'<select %s>%s</select>',
			Field::render_attributes( $attrs ),
			Field::render_options(
				$options,
				$value,
				function ( string $option_value, string $option_text, mixed $selected_value ): string {
					return $this->render_option( $option_value, $option_text, $selected_value );
				}
			)
		);
	}

	protected function render_option( string $value, string $text, mixed $selected_value ): string {
		return sprintf(
			'<option %s>%s</option>',
			Field::render_attributes(
				[
					'value' => esc_attr( $value ),
					'selected' => $this->is_selected( $value, $selected_value ),
				]
			),
			esc_html( $text ),
		);
	}

	protected function attributes( array $args ): array {
		return [
			'name' => $this->input_name(),
			'id' => $this->id,
			'class' => $args['class'] ?? '',
			'required' => $this->is_required(),
			'disabled' => $args['disabled'] ?? false,
			'multiple' => $args['multiple'] ?? false,
		];
	}

	protected function is_selected( string $value, mixed $selected_value ): bool {
		if ( ! $this->is_array ) {
			return $value === $selected_value;
		}

		return in_array( $value, (array) $selected_value, true );
	}
}
