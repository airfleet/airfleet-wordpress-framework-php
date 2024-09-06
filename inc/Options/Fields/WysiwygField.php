<?php

namespace Airfleet\Framework\Options\Fields;

class WysiwygField extends Field {
	protected function render_input( array $args, mixed $value ): void {
		$attrs = $this->attributes( $args );

		wp_editor( $value, $attrs['id'], $attrs['settings'] );
	}

	protected function attributes( array $args ): array {
		$name     = $this->input_name();
		$settings = $args['settings'] ?? [];

		if ( ! empty( $settings ) ) {
			$settings['textarea_name'] = $name;
		}

		return array(
			'name'     => $name,
			'id'       => $this->id ?? '',
			'required' => $args['required'] ?? false,
			'settings' => $settings,
		);
	}

	protected function default_validate( mixed $value ): bool {
		$length = strlen( $value );

		if ( isset( $this->args['required'] ) && $this->args['required'] && $length === 0 ) {
			$this->add_error( "{$this->title} is required." );

			return false;
		}

		return parent::default_validate( $value );
	}
}
