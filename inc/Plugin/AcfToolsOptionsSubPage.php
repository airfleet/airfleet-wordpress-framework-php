<?php

namespace Airfleet\Framework\Plugin;

use Airfleet\Framework\Acf\ToolsOptionsSubPage;

/**
 * Create an ACF options sub-page under the core "Tools" menu.
 */
class AcfToolsOptionsSubPage extends ToolsOptionsSubPage {
	protected array $options;

	/**
	 * Constructor
	 *
	 * @param array $config The plugin config.
	 * @param array $options Options sub-page properties.
	 */
	public function __construct( array $config, array $options = [] ) {
		parent::__construct(
			array_merge(
				[
					'page_title' => $config['title'],
					'menu_title' => $config['short_title'],
					'menu_slug' => $config['slug'],
				],
				$options
			)
		);
	}
}
