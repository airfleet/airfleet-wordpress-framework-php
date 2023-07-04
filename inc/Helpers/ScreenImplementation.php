<?php

namespace Airfleet\Framework\Helpers;

class ScreenImplementation {
	/**
	 * The type of screen for the current page (e.g. "frontend", "editor", etc.).
	 *
	 * @return array
	 */
	public function screen(): array {
		$is_admin = is_admin();

		if ( ! $is_admin ) {
			return [ 'frontend' ];
		}

		if ( $this->in_block_editor() ) {
			return [ 'editor', 'admin' ];
		}

		return [ 'admin' ];
	}

	/**
	 * Determines if we are in the block editor.
	 *
	 * @return boolean
	 */
	public function in_block_editor(): bool {
		return self::in_block_editor_admin() || self::in_block_editor_ajax();
	}

	/**
	 * Check if we are in the block editor through a standard admin page request (@see in_block_editor_ajax)
	 *
	 * @return boolean
	 */
	public function in_block_editor_admin(): bool {
		if ( function_exists( '\get_current_screen' ) ) {
			$screen = \get_current_screen();

			if ( $screen instanceof \WP_Screen ) {
				return $screen->is_block_editor();
			}
		}

		return false;
	}

	/**
	 * Check if we are in the block editor through an AJAX request (e.g. ACF request to render)
	 *
	 * @return boolean
	 */
	public function in_block_editor_ajax(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$block = wp_unslash( $_REQUEST['block'] ?? false );

		if ( ! $block ) {
			return false;
		}

		$block_array = json_decode( $block, true );

		if ( strpos( $block_array['id'], 'block_' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine current post type in the context.
	 *
	 * @return string
	 */
	public function post_type(): string {
		global $post, $typenow, $current_screen, $pagenow;

		$post_type = '';

		if ( $post && ( property_exists( $post, 'post_type' ) || method_exists( $post, 'post_type' ) ) ) {
			$post_type = $post->post_type;
		}

		if ( empty( $post_type ) && ! empty( $current_screen ) && ( property_exists( $current_screen, 'post_type' ) || method_exists( $current_screen, 'post_type' ) ) && ! empty( $current_screen->post_type ) ) {
			$post_type = $current_screen->post_type;
		}

		if ( empty( $post_type ) && ! empty( $typenow ) ) {
			$post_type = $typenow;
		}

		if ( empty( $post_type ) && function_exists( '\get_current_screen' ) ) {
			$screen = \get_current_screen();

			if ( $screen ) {
				$post_type = $screen->post_type;
			}
		}

		// phpcs:ignore: WordPress.Security.NonceVerification.Recommended
		if ( empty( $post_type ) && isset( $_REQUEST['post'] ) && ! empty( $_REQUEST['post'] ) && function_exists( '\get_post_type' ) ) {
			// phpcs:ignore: WordPress.Security.NonceVerification.Recommended
			$get_post_type = \get_post_type( (int) $_REQUEST['post'] );
			$post_type = $get_post_type;
		}

		// phpcs:ignore: WordPress.Security.NonceVerification.Recommended
		if ( empty( $post_type ) && isset( $_REQUEST['post_type'] ) && ! empty( $_REQUEST['post_type'] ) ) {
			// phpcs:ignore: WordPress.Security.NonceVerification.Recommended
			$post_type = sanitize_key( $_REQUEST['post_type'] );
		}

		if ( empty( $post_type ) && 'edit.php' === $pagenow ) {
			$post_type = 'post';
		}

		return $post_type;
	}

	/**
	 * Determines if we are editing ACF fields.
	 *
	 * @return boolean
	 */
	public function is_editing_acf(): bool {
		return self::post_type() === 'acf-field-group';
	}
}
