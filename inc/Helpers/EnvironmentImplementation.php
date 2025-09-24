<?php

namespace Airfleet\Framework\Helpers;

class EnvironmentImplementation {
	protected string $environment = '';

	public function get(): string {
		if ( $this->environment === '' ) {
			$this->environment = $this->determine_environment();
		}

		return $this->environment;
	}

	public function is_local(): bool {
		return $this->get() === 'local';
	}

	protected function determine_environment(): string {
		if ( empty( $_SERVER['HTTP_HOST'] ) ) {
			return '';
		}

		// ? Check by domain keyword
		$domain = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ), PHP_URL_HOST );
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
