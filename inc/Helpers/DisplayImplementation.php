<?php

namespace Airfleet\Framework\Helpers;

class DisplayImplementation {
	/**
	 * Dump pretty variables.
	 *
	 * @param mixed ...$vars
	 * @return void
	 */
	public function dump( ...$vars ) {
		echo $this->print( $vars );
	}

	/**
	 * Return variables pretty printed as a string.
	 *
	 * @param mixed ...$vars Variables to print.
	 * @return string
	 */
	public function print( ...$vars ): string {
		$result = [];

		foreach ( $vars as $var ) {
			$result[] = '<pre style="max-height: 20rem; overflow: auto; margin: 0;border: solid 1px #00f">';
			ob_start();
			highlight_string( '<?php ' . var_export( $var, true ) . '?>' );
			$highlighted_output = ob_get_clean();
			$result[] = $highlighted_output;
			$result[] = '</pre>';
		}

		return join( '', $result );
	}

	/**
	 * Renders the attributes for an HTML tag.
	 * The function accepts attributes in associative array,
	 * filters out falsy attributes and escapes the attribute values.
	 *
	 * @param array $attributes List of attributes to render.
	 * @return string
	 */
	public function attributes( array $attributes = [] ): string {
		$attributes = array_map(
			function ( $name, $value ): string {
				// All the falsy attributes should be filtered out except the alt attribute for the images.
				if ( ! boolval( $value ) && ! in_array( $name, [ 'alt' ], true ) ) {
					return false;
				}

				if ( true === $value ) {
					return $name;
				}

				if ( is_callable( $value ) ) {
					// phpcs:ignore NeutronStandard.Functions.VariableFunctions.VariableFunction
					$value = $value();
				}

				if ( is_array( $value ) || is_object( $value ) ) {
					$value = wp_json_encode( $value );
				}

				return $name . '="' . esc_attr( $value ) . '"';
			},
			array_keys( $attributes ),
			array_values( $attributes )
		);

		return join( ' ', array_filter( $attributes ) );
	}

	/**
	 * Renders data attributes for an HTML tag.
	 *
	 * @param array $attributes List of data attributes to render.
	 * @return string
	 */
	public function data_attributes( array $attributes = [] ): string {
		$attributes = array_map(
			function ( $name, $value ): string {
				if ( true === $value ) {
					$value = 'true';
				} elseif ( false === $value ) {
					$value = 'false';
				} elseif ( is_array( $value ) || is_object( $value ) ) {
					$value = wp_json_encode( $value );
				}

				return 'data-' . $name . '="' . esc_attr( $value ) . '"';
			},
			array_keys( $attributes ),
			array_values( $attributes )
		);

		return join( ' ', array_filter( $attributes ) );
	}

	/**
	 * Render a reusable block post.
	 *
	 * @param int|WP_Post|null $post The reusable block post.
	 * @return string Rendered HTML.
	 */
	public function render_reusable_block( mixed $post ): string {
		if ( ! $post ) {
			return '';
		}

		$post = get_post( $post );

		if ( ! $post ) {
			return '';
		}

		return apply_filters( 'the_content', $post->post_content );
	}
}
