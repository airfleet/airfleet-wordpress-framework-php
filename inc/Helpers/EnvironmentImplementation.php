<?php

namespace Airfleet\Framework\Helpers;

class EnvironmentImplementation {
	protected string $environment = '';

	/**
	 * Get the current environment type.
	 */
	public function get(): string {
		if ( $this->environment === '' ) {
			$this->environment = $this->determine_environment();
		}

		return $this->environment;
	}

	/**
	 * Check if the current environment is local.
	 */
	public function is_local(): bool {
		return $this->get() === 'local';
	}

	/**
	 * Determine the current environment.
	 */
	protected function determine_environment(): string {
		if ( empty( $_SERVER['HTTP_HOST'] ) ) {
			return 'unknown';
		}

		// ? Check by domain keyword
		$domain = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
		$domains = [
			'local' => [
				'.local',
				'.loc',
				'localhost',
				'.test',
				'.internal',
				'.home',
			],
			'staging' => [
				'cloudways',
				'wpengine',
				'kinsta',
			],
		];

		foreach ( $domains as $env => $keywords ) {
			foreach ( $keywords as $keyword ) {
				if ( str_contains( $domain, $keyword ) ) {
					return $env;
				}
			}
		}

		// ? Check by subdomain keyword
		$parts = explode( '.', $domain );

		if ( ! empty( $parts ) ) {
			$subdomain = $parts[0];
			$subdomains = [
				'staging' => [
					'stg',
					'staging',
				],
			];

			foreach ( $subdomains as $env => $keywords ) {
				foreach ( $keywords as $keyword ) {
					if ( str_contains( $subdomain, $keyword ) ) {
						return $env;
					}
				}
			}
		}

		// ? Fallback to production
		return 'production';
	}
}
