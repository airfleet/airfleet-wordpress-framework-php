<?php

namespace Airfleet\Framework\Features;

interface PluginFeature extends Feature {
	/**
	 * Runs when the plugin is activated.
	 *
	 * @return void
	 */
	public function on_plugin_activated(): void;
}
