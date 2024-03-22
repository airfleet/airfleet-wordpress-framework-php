<?php

namespace Airfleet\Framework\Options\Fields;

class MediaField extends Field {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'hidden' ], $args ) );
	}

	public function enqueue(): void {
		wp_enqueue_media();

		$file_path = __DIR__ . '/../../../assets/scripts/MediaField.js';
		$url_path  = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $file_path );

		wp_enqueue_script( 'media-field-script', $url_path, array(), null, true );
	}

	protected function attributes(array $args, mixed $value) : array {
		return [
			'type'         => $args['type'],
			'name'         => $this->input_name(),
			'id'           => $this->id,
			'value'        => $value,
			'title' 	   => $args['title'] ?? '',
			'label' 	   => $args['label'] ?? '',
			'required'     => $args['required'] ?? \false,
			'disabled'     => $args['disabled'] ?? \false,
			'min_width'    => $args['min_width'] ?? \false,
			'max_width'    => $args['max_width'] ?? \false,
			'min_height'   => $args['min_height'] ?? \false,
			'max_height'   => $args['max_height'] ?? \false,
			'instructions' => $args['instructions'] ?? '',
			'media_types'  => $args['media_types'] ?? [],
		];
	}

	protected function render_input( array $args, mixed $value ): void {
		$label 			 = $args['label'] ?? '';
		$url             = ! empty( $value ) ? $value : sprintf( 'https://placehold.co/%sx%s', $args['min_width'], $args['min_height'] );
		$attrs           = $this->attributes( $args, $value );
		$data_attributes = \sprintf( 'data-min-height="%s" data-min-width="%s" data-max-height="%s" data-max-width="%s" data-media-types="%s" data-instructions="%s"', $args['min_height'], $args['min_width'], $args['max_height'], $args['max_width'], \implode( ',', $args['media_types'] ), $args['instructions'] );
		$img             = \sprintf( '<img class="js-image-upload" id="%s" style="width: %spx" alt="%s" src="%s" %s />', $this->id, $args['min_width'], $label, $url, $data_attributes );
		$hidden          = \sprintf( '<input type="hidden" name="%s" id="%s_url" value="%s" />', $attrs['name'], $this->id, $url );

		\printf( '%s%s', $img, $hidden );
	}

	protected function default_validate( mixed $value ): bool {
		$required = isset( $this->args['required'] ) && $this->args['required'];

		if ( $required && ! $value ) {
			$this->add_error( "{$this->title} is required." );

			return \false;
		}

		return parent::default_validate( $value );
	}
}
