<?php

namespace Airfleet\Framework\Features;

class BasePluginFeature implements PluginFeature {
	public function initialize(): void {
		// Do nothing
	}

	public function on_activation(): void {
		// Do nothing
	}

	public function on_deactivation(): void{
		// Do nothing
	}

	public function on_uninstall(): void {
		// Do nothing
	}
}
