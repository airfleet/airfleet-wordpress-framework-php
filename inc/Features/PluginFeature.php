<?php

namespace Airfleet\Framework\Features;

interface PluginFeature extends Feature {
	/**
	 * Runs when the plugin is activated.
	 *
	 * @return void
	 */
	public function on_activation(): void;

	/**
	 * Runs when the plugin is deactivated.
	 *
	 * @return void
	 */
	public function on_deactivation(): void;

	/**
	 * Runs when the plugin is uninstalled.
	 *
	 * @return void
	 */
	public function on_uninstall(): void;
}
