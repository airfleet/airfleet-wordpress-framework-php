<?php

namespace Airfleet\Framework\Benchmark;

use Airfleet\Framework\Features\BasePluginFeature;

/**
 * Benchmark feature for performance testing.
 */
class Benchmark extends BasePluginFeature {
	protected static bool $is_initialized = false;

	protected NestedTimerRegistry $stopwatch;

	public function initialize(): void {
		if ( self::$is_initialized ) {
			return;
		}
		self::$is_initialized = true;
		$this->setup_benchmark();
	}

	/**
	 * Sets up the benchmark actions and hooks.
	 */
	public function setup_benchmark(): void {
		$enabled = (bool) \apply_filters( 'airfleet/benchmark/enabled', true );

		if ( ! $enabled ) {
			return;
		}
		$this->stopwatch = new NestedTimerRegistry();
		$this->stopwatch->start( '__total' );

		// Start and Stop actions for external instrumentation
		add_action( 'airfleet/benchmark/start', [ $this, 'on_start' ], 10, 1 );
		add_action( 'airfleet/benchmark/stop', [ $this, 'on_stop' ], 10, 1 );

		// Append a simple HTML comment at the very end of the response with timings.
		add_action(
			'shutdown',
			function (): void {
				if ( ! $this->is_appropriate_request() ) {
					return;
				}
				$this->stopwatch->stop_all();
				$totals = $this->stopwatch->get_all_totals_ms();

				if ( ! empty( $totals ) ) {
					$json = $this->get_json_result( $totals );
					$comment = 'airfleet-benchmark: ' . $json;
					$this->write_comment( $comment );

					// Optionally write to error_log
					$should_log = (bool) \apply_filters( 'airfleet/benchmark/log_to_error_log', defined( '\\WP_DEBUG' ) && \WP_DEBUG );

					if ( $should_log ) {
						error_log( $comment );
					}
				} else {
					$this->write_comment( 'airfleet-benchmark: no timings recorded' );
				}
			}
		);
	}

	/**
	 * Action callback to start timing an id.
	 */
	public function on_start( string $id ): void {
		// Ignore external attempts to restart total
		if ( $id === '__total' ) {
			return;
		}

		$this->stopwatch->start( (string) $id );
	}

	/**
	 * Action callback to stop timing an id.
	 */
	public function on_stop( string $id ): void {
		// Ignore external attempts to stop total timer
		if ( $id === '__total' ) {
			return;
		}

		$this->stopwatch->stop( (string) $id );
	}

	protected function get_formatted_result( array $totals ): array {
		$decimals = 2;
		$formatted = [];

		foreach ( $totals as $id => $ms ) {
			$formatted[ $id ] = round( $ms, $decimals );
		}

		// Sort totals by key alphabetically
		ksort( $formatted );

		return $formatted;
	}

	protected function get_json_result( array $totals ): string {
		$formatted = $this->get_formatted_result( $totals );

		// Prevent PHP float serialization from emitting long binary artifacts
		$old_serialize_precision = ini_get( 'serialize_precision' );

		// phpcs:ignore WordPress.PHP.IniSet.Risky
		ini_set( 'serialize_precision', '-1' );

		// Encode to JSON
		$json = wp_json_encode( $formatted, JSON_PRETTY_PRINT );

		// restore previous setting
		if ( $old_serialize_precision !== false ) {
			// phpcs:ignore WordPress.PHP.IniSet.Risky
			ini_set( 'serialize_precision', $old_serialize_precision );
		}

		return $json;
	}

	protected function write_comment( string $comment ): void {
		echo "\n<!-- " . trim( $comment ) . " -->\n";
	}

	protected function is_appropriate_request(): bool {
		// 1. Only for logged-in users.
		if ( ! \is_user_logged_in() ) {
			return false;
		}

		// 2. Do not output on non-HTML requests (AJAX, REST, XML-RPC, feeds, etc.).
		if (
			( defined( '\\REST_REQUEST' ) && \REST_REQUEST ) ||
			( defined( '\\XMLRPC_REQUEST' ) && \XMLRPC_REQUEST ) ||
			( defined( '\\WP_CLI' ) && \WP_CLI ) ||
			( defined( '\\DOING_CRON' ) && \DOING_CRON ) ||
			( defined( '\\DOING_AJAX' ) && \DOING_AJAX ) ||
			( defined( '\\JSON_REQUEST' ) && \JSON_REQUEST ) ||
			( defined( '\\IFRAME_REQUEST' ) && \IFRAME_REQUEST ) ||
			( defined( '\\WC_API_REQUEST' ) && \WC_API_REQUEST ) ||
			\wp_doing_ajax() ||
			\is_feed() ||
			\is_robots()
		) {
			return false;
		}

		if ( function_exists( '\\wp_is_json_request' ) && \wp_is_json_request() ) {
			return false;
		}
	}
}
