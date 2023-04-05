<?php

namespace Airfleet\Framework\Options\Tabs;

class Tab {
	protected string $id;
	protected string $title;

	public function __construct( string $id, string $title ) {
		$this->id = $id;
		$this->title = $title;
	}

	public function id(): string {
		return $this->id;
	}

	public function title(): string {
		return $this->title;
	}

	public function register(): void {
		// Do nothing (override if needed)
	}

	public function render(): void {
		// Do nothing (override if needed)
	}
}
