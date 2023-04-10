<?php

namespace Airfleet\Framework\Assets;

/**
 * Adds a plugin Settings link in the plugin row in the admin plugins list.
 */
class SettingsLink extends ActionLinks {
	/**
	 * Constructor.
	 *
	 * @param array $options Enqueue properties.
	 *   $options = [
	 *     'slug' => (string) The slug
	 *     'path' => (string) Path to the base folder (plugin/theme)
	 * 	   'base' => (string) Base page name that contains the settings. Optional (defaults to "admin.php")
	 *   ]
	 */
	public function __construct( array $options ) {
		parent::__construct(
			array_merge(
				$options,
				[
					'links' => function() use ( $options ): array {
						return $this->get_links( $options );
					},
				]
			)
		);
	}

	protected function get_links( array $options ): array {
		return [
			'settings' => sprintf(
				'<a href="%s">%s</a>',
				$this->settings_url( $options ),
				__( 'Settings', 'airfleet' )
			),
		];
	}

	protected function settings_url( array $options ): string {
		$base = $this->base( $options );

		return admin_url( "{$base}?page={$options['slug']}" );
	}

	protected function base( array $options ): string {
		if ( ! isset( $options['base'] ) || empty( $options['base'] ) ) {
			return 'admin.php';
		}

		if ( is_callable( $options['base'] ) ) {
			// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			return call_user_func( $options['base'] );
		}

		return $options['base'];
	}
}
