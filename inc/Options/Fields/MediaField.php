<?php

namespace Airfleet\Framework\Options\Fields;

class MediaField extends HiddenField {
	public function __construct( string $id, string $title, array $args = [] ) {
		parent::__construct( $id, $title, array_merge( [ 'type' => 'hidden' ], $args ) );
	}

	public function enqueue(): void {
		wp_enqueue_media();
	}

	protected function render_input( array $args, mixed $value ): void {
		$label = $args['label'] ?? '';

		if ( ! $label ) {
			parent::render_input( $args, $value );

			return;
		}

		$url             = ! empty( $value ) ? $value : sprintf( 'https://placehold.co/%sx%s', $args['min_width'], $args['min_height'] );
		$attrs           = $this->attributes( $args, $value );
		$data_attributes = \sprintf( 'data-min-height="%s" data-min-width="%s" data-max-height="%s" data-max-width="%s"', $args['min_width'], $args['min_height'], $args['max_height'], $args['max_width'] );
		$img             = \sprintf( '<img class="js-image-upload" id="%s" style="width: %spx" alt="%s" src="%s" %s />', $this->id, $args['min_width'], $label, $url, $data_attributes );
		$hidden 		 = \sprintf( '<input type="hidden" name="%s" id="%s_url" value="%s" />', $attrs['name'], $this->id, $url );

		\printf( '%s%s', $img, $hidden );
	}
}
