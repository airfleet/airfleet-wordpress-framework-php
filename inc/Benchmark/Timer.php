<?php

namespace Airfleet\Framework\Benchmark;

/**
 * Simple high-resolution timer.
 */
class Timer {
	/**
	 * Indicates if the timer is currently running.
	 *
	 * @var bool
	 */
	protected bool $is_running = false;

	/**
	 * Start time of the current run (nanoseconds).
	 *
	 * @var int
	 */
	protected int $start_time_ns = 0;

	/**
	 * Accumulated finished time in nanoseconds.
	 *
	 * @var float
	 */
	protected float $accumulated_ns = 0.0;

	public function start(): void {
		if ( $this->is_running ) {
			return;
		}
		$this->is_running = true;
		$this->start_time_ns = $this->now();
	}

	public function stop(): void {
		if ( ! $this->is_running ) {
			return;
		}
		$this->is_running = false;
		$this->accumulated_ns += $this->delta();
		$this->start_time_ns = 0;
	}

	/**
	 * Get the total elapsed time in milliseconds.
	 *
	 * @return float Elapsed time in milliseconds.
	 */
	public function get_elapsed_time_ms(): float {
		$total_ns = $this->accumulated_ns;

		if ( $this->is_running ) {
			$total_ns += $this->delta();
		}

		return $total_ns / 1_000_000.0; // Convert nanoseconds to milliseconds
	}

	/**
	 * Return nanoseconds elapsed since the current start.
	 *
	 * @return int Nanoseconds elapsed for the current run.
	 */
	protected function delta(): int {
		if ( $this->start_time_ns === 0 ) {
			return 0;
		}
		$elapsed = $this->now() - $this->start_time_ns;

		return $elapsed > 0 ? $elapsed : 0;
	}

	/**
	 * Get current high-resolution time in nanoseconds.
	 *
	 * @return int Current time in nanoseconds.
	 */
	protected function now(): int {
		return hrtime( true );
	}
}
