<?php

namespace Airfleet\Framework\Benchmark;

/**
 * Simple high-resolution stopwatch that accumulates total time per ID.
 * Handles nested timers with the same ID by pausing parent timers.
 */
class NestedTimerRegistry {
	/**
	 * Running stopwatches.
	 *
	 * @var array<string, Timer[]>
	 */
	protected array $running = [];

	/**
	 * Finished stopwatches.
	 *
	 * @var array<string, Timer[]>
	 */
	protected array $finished = [];

	/**
	 * Paused stopwatches.
	 *
	 * @var array<string, Timer[]>
	 */
	protected array $paused = [];

	/**
	 * All stopwatches.
	 *
	 * @var array<string, Timer[]>
	 */
	protected array $all = [];

	/**
	 * Start timing for an id and pause any running timer with the same ID.
	 */
	public function start( string $id ): void {
		// If there's an active timer for this ID, pause only the top-most (LIFO)
		if ( ! empty( $this->running[ $id ] ) ) {
			$watch = array_pop( $this->running[ $id ] );
			$watch->stop();
			$this->paused[ $id ][] = $watch;
		}

		// Start a new stopwatch for this ID
		$new = new Timer();
		$new->start();
		$this->running[ $id ][] = $new;
		$this->all[ $id ][] = $new;
	}

	/**
	 * Stop timing for an id and resume any paused timers with the same ID.
	 */
	public function stop( string $id ): void {
		$this->stop_current_running( $id );
		$this->resume_current_paused( $id );
	}

	/**
	 * Stop all timers.
	 */
	public function stop_all(): void {
		foreach ( $this->running as $id => $watches ) {
			while ( ! empty( $this->running[ $id ] ) ) {
				$this->stop_current_running( $id );
			}
			unset( $this->running[ $id ] );
		}

		// Paused timers should also be finalized (decide policy: here we stop & mark them finished)
		foreach ( $this->paused as $id => $watches ) {
			while ( ! empty( $this->paused[ $id ] ) ) {
				$this->stop_current_paused( $id );
			}
			unset( $this->paused[ $id ] );
		}
	}

	/**
	 * Get all totals as id => ms (float).
	 *
	 * @return array<string, float>
	 */
	public function get_all_totals_ms(): array {
		$result = [];
		foreach ( $this->all as $id => $watches ) {
			$result[ $id ] = array_reduce(
				$watches,
				fn( $total, $watch ) => $total + $watch->get_elapsed_time_ms(),
				0
			);
		}
		return $result;
	}

	protected function stop_current_running( string $id ): void {
		if ( empty( $this->running[ $id ] ) ) {
			return;
		}
		$watch = array_pop( $this->running[ $id ] );
		$watch->stop();
		$this->finished[ $id ][] = $watch;
	}

	protected function resume_current_paused( string $id ): void {
		if ( empty( $this->paused[ $id ] ) ) {
			return;
		}
		$watch = array_pop( $this->paused[ $id ] );
		$watch->start();
		$this->running[ $id ][] = $watch;
	}

	protected function stop_current_paused( string $id ): void {
		if ( empty( $this->paused[ $id ] ) ) {
			return;
		}
		$watch = array_pop( $this->paused[ $id ] );
		// They were previously stopped already, but ensure they are not running
		$watch->stop();
		$this->finished[ $id ][] = $watch;
	}
}
