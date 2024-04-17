<?php

namespace Airfleet\Framework\Options\Fields;

class ImageField extends Field {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'hidden' ], $args ) );
	}

	public function enqueue(): void {
		wp_enqueue_media();

		$image_field_script_url = sprintf( '%svendor-scoped/airfleet/wordpress-framework/assets/scripts/ImageField.js', AIRFLEET_SIGNATURE_URL );

		if ( $this->url_exists( $image_field_script_url ) ) {
			wp_enqueue_script( 'image-field-script', $image_field_script_url, array(), null, true );
		}
	}

	protected function get_img_attributes( array $args, mixed $value ): array {
		$media_types  = $args['media_types'] ?? [];
		$media_types  = ! empty( $media_types ) ? implode( ",", $media_types ) : $media_types;
		$min_width    = $args['min_width'] ?? 600;
		$min_height   = $args['min_height'] ?? 400;
		$instructions = $args['instructions'] ?? '';
		$disabled     = $args['disabled'] ?? false;

		return [
			'src'               => ! empty( $value ) ? $value : sprintf( 'https://placehold.co/%sx%s', $min_width, $min_height ),
			'name'              => $this->input_name(),
			'id'                => $this->id,
			'class'             => ! $disabled ? 'js-image-upload' : '',
			'style' 			=> ! $disabled ? 'cursor: pointer;' : '',
			'width'             => $args['min_width'] ?? 100,
			'alt'               => $args['label'] ?? '',
			'title'             => $args['title'] ?? '',
			'label'             => $args['label'] ?? '',
			'data-min-width'    => $args['min_width'] ?? false,
			'data-max-width'    => $args['max_width'] ?? false,
			'data-min-height'   => $args['min_height'] ?? false,
			'data-max-height'   => $args['max_height'] ?? false,
			'data-instructions' => strip_tags( $instructions ),
			'data-media-types'  => $media_types
		];
	}

	protected function get_hidden_attributes( mixed $value ): array {
		return [
			'type'  => 'hidden',
			'name'  => $this->input_name(),
			'id'    => $this->id . '_url',
			'value' => ! empty( $value ) ? $value : '',
		];
	}

	protected function render_input( array $args, mixed $value ): void {
		$disabled     = $args['disabled'] ?? false;
		$img_attrs    = $this->get_img_attributes( $args, $value );
		$hidden_attrs = $this->get_hidden_attributes( $value );

		$img    = sprintf( '<img %s />', Field::render_attributes( $img_attrs ) );
		$hidden = '';

		if ( ! $disabled ) {
			$hidden = sprintf( '<input %s />', Field::render_attributes( $hidden_attrs ) );
		}

		printf( '%s%s', $img, $hidden );
	}

	protected function default_validate( mixed $value ): bool {
		$required = isset( $this->args['required'] ) && $this->args['required'];
		if ( $required && ! $value ) {
			$this->add_error( "{$this->title} is required." );

			return false;
		}

		return parent::default_validate( $value );
	}

	private function url_exists( $url ): bool {
		$headers = @get_headers( $url );

		return $headers && str_contains( $headers[0], '200' );
	}
}
