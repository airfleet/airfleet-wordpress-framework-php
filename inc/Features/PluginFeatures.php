<?php

namespace Airfleet\Framework\Features;

class PluginFeatures extends Features implements PluginFeature {
	protected static $instance = null;

	public function __construct( array $features ) {
		$this->features = $features;
		static::$instance = $this;
	}

	public function on_activation(): void {
		foreach ( $this->features as $feature ) {
			if ( method_exists( $feature, 'on_activation' ) ) {
				$feature->on_activation();
			}
		}
	}

	public function on_deactivation(): void {
		foreach ( $this->features as $feature ) {
			if ( method_exists( $feature, 'on_deactivation' ) ) {
				$feature->on_deactivation();
			}
		}
	}

	public function on_uninstall(): void {
		foreach ( $this->features as $feature ) {
			if ( method_exists( $feature, 'on_uninstall' ) ) {
				$feature->on_uninstall();
			}
		}
	}

	public static function uninstall(): void {
		static::$instance->on_uninstall();
	}
}
