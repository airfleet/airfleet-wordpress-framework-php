<?php

namespace Airfleet\Plugins\Admin\Options\Menu;

/**
 * Add a main menu item.
 */
class MainMenuEntry implements \Airfleet\Plugins\Admin\Feature {
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
				add_menu_page(
					$this->menu['page_title'],
					$this->menu['menu_title'],
					$this->menu['capability'] ?? 'manage_options',
					$this->menu['menu_slug'],
					$this->menu['callback'],
					$this->menu['menu_icon'] ?? 'dashicons-admin-generic',
					$this->menu['position']
				);
			}
		);
	}
}
