<?php

namespace Airfleet\Framework\Helpers;

class AirfleetImplementation {
	/**
	 * Check if the current theme is the Airfleet Starter Theme.
	 *
	 * @return boolean
	 */
	public function is_starter_theme(): bool {
		return function_exists( 'af_field' );
	}

	/**
	 * Check if the current logged-in user (if any) is an Airfleet member.
	 *
	 * @return boolean
	 */
	public function is_airfleet_user(): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$email = wp_get_current_user()->data->user_email;

		if ( ! $email ) {
			return false;
		}

		return $this->is_airfleet_email( $email );
	}

	/**
	 * Check if the given email is from an Airfleet member.
	 *
	 * @param string $email
	 * @return boolean
	 */
	public function is_airfleet_email( string $email ): bool {
		if ( ! $email ) {
			return false;
		}

		$allowed_emails = [
			'@airfleet.co',
			'@airfleet.dev',
			'@codersclan.net',
			'@flywheel.local',
			'@wpengine.local',
		];

		return ! empty(
			array_filter(
				$allowed_emails, function( $item ) use ( $email ): bool {
					return str_ends_with( $email, $item );
				}
			)
		);
	}

	/**
	 * Check if the post type is an Airfleet post type.
	 *
	 * @param \WP_Post_Type $post_type Post type to check.
	 * @return boolean
	 */
	public function is_airfleet_post_type( \WP_Post_Type $post_type ): bool {
		$airfleet_registered = isset( $post_type->_airfleet ) && $post_type->_airfleet;

		return $airfleet_registered || $this->contains_airfleet_strings( $post_type->name );
	}

	/**
	 * Check if the arguments are for an Airfleet post type.
	 *
	 * @param array $args Post type args.
	 * @param string $post_type Post type slug.
	 * @return boolean
	 */
	public function is_airfleet_post_type_args( array $args, string $post_type ): bool {
		if ( isset( $args['_airfleet'] ) && $args['_airfleet'] ) {
			return true;
		}

		return $this->contains_airfleet_strings( $post_type );
	}

	protected function contains_airfleet_strings( string $value ): bool {
		if ( str_contains( $value, 'airfleet' ) ) {
			return true;
		}
		$starts = [
			'af-',
			'af_',
			'afe_',
		];

		foreach ( $starts as $start ) {
			if ( str_starts_with( $value, $start ) ) {
				return true;
			}
		}

		if ( $value === 'cta' ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if arguments are for an Airfleet taxonomy.
	 *
	 * @param array $args
	 * @param string $taxonomy
	 * @param array|string $object_type
	 * @return boolean
	 */
	public function is_airfleet_taxonomy_args( array $args, string $taxonomy, array|string $object_type ): bool {
		if ( isset( $args['_airfleet'] ) && $args['_airfleet'] ) {
			return true;
		}

		if ( $this->contains_airfleet_strings( $taxonomy ) ) {
			return true;
		}

		// For here on out, if it belongs to an Airfleet post type,
		// then consider it an Airfleet taxonomy
		if ( is_string( $object_type ) ) {
			return $this->is_airfleet_post_type_args( $args, $object_type );
		}

		foreach ( $object_type as $post_type ) {
			if ( $this->is_airfleet_post_type_args( $args, $post_type ) ) {
				return true;
			}
		}

		return false;
	}
}
