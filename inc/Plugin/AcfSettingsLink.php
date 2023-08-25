<?php

namespace Airfleet\Framework\Plugin;

/**
 * Adds a plugin Settings link for the ACF options sub-page in the plugin row in the admin plugins list.
 */
class AcfSettingsLink extends SettingsLink {
	/**
	 * Constructor.
	 *
	 * @param array $options Plugin properties.
	 *   $options = [
	 *     'slug'       => (string) The slug
	 *     'short_slug' => (string) The short slug
	 *     'path'       => (string) Path to the base folder (plugin/theme)
	 *   ]
	 */
	public function __construct( array $options ) {
		parent::__construct( $options );
	}

	protected function settings_url( array $options ): string {
		$base = $this->base( $options );

		return admin_url( "{$base}?page={$options['slug']}" );
	}
}
