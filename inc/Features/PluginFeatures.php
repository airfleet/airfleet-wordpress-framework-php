<?php

namespace Airfleet\Framework\Features;

class PluginFeatures extends Features implements PluginFeature {
	public function on_plugin_activated(): void {
		foreach ( $this->features as $feature ) {
			if ( method_exists( $feature, 'on_plugin_activated' ) ) {
				$feature->on_plugin_activated();
			}
		}
	}
}
