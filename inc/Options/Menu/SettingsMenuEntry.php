<?php

namespace Airfleet\Framework\Options\Menu;

/**
 * Add a menu item under the standard "Settings" menu.
 */
class SettingsMenuEntry implements \Airfleet\Framework\Features\Feature {
	/**
	 * Menu data. See methods add_menu_item and add_submenu_item for expected data.
	 *
	 * @var array
	 */
	protected array $menu;

	public function __construct( array $menu ) {
		$this->menu = $menu;
	}

	public function initialize(): void {
		add_action(
			'admin_menu',
			function () {
				add_submenu_page(
					$this->settings_page_slug(),
					$this->menu['page_title'],
					$this->menu['menu_title'],
					$this->menu['capability'] ?? 'manage_options',
					$this->menu['menu_slug'],
					$this->menu['callback'],
					$this->menu['position']
				);
			}
		);
	}

	protected function settings_page_slug(): string {
		return is_multisite() ? 'settings.php' : 'options-general.php';
	}
}
