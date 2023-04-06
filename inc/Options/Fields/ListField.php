<?php

namespace Airfleet\Framework\Options\Fields;

class ListField extends Field {
	protected function render_input( array $args, mixed $value ): void {
		$options = Field::options( $args );

		printf(
			'<fieldset id="%s">%s%s</select>',
			esc_attr( $this->id ),
			$this->render_legend( $args ),
			Field::render_options(
				$options,
				$value,
				function ( string $option_value, string $option_text, mixed $selected_value ) use ( $args ): string {
					return $this->render_option( $option_value, $option_text, $selected_value, $args );
				}
			)
		);
	}

	protected function render_legend( array $args ): string {
		if ( ! isset( $args['legend'] ) || empty( $args['legend'] ) ) {
			return '';
		}

		return sprintf(
			'<legend class="screen-reader-text"><span>%s</span></legend>',
			$args['legend'],
		);
	}

	protected function render_option( string $value, string $text, mixed $selected_value, array $args ): string {
		$type = $args['type'] ?? 'radio';

		return sprintf(
			'<label><input %s>%s</label><br>',
			Field::render_attributes(
				[
					'type' => $type,
					'name' => $this->input_name(),
					'value' => esc_attr( $value ),
					'checked' => $this->is_selected( $value, $selected_value ),
					'required' => $type === 'radio' ? ( $args['required'] ?? false ) : false,
				]
			),
			$text,
		);
	}

	protected function is_selected( string $value, mixed $selected_value ): bool {
		if ( ! $this->is_array ) {
			return $value === $selected_value;
		}

		return in_array( $value, (array) $selected_value, true );
	}
}
