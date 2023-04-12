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
