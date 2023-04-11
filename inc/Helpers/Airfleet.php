<?php

namespace Airfleet\Framework\Helpers;

class Airfleet extends Helper {
	protected static function create_instance(): static {
		return new Airfleet();
	}

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
		if ( ! \is_user_logged_in() ) {
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
		];

		return ! empty(
			array_filter(
				$allowed_emails, function( $item ) use ( $email ): bool {
					return str_ends_with( $email, $item );
				}
			)
		);
	}
}
