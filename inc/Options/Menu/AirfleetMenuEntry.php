<?php

namespace Airfleet\Framework\Options\Menu;

class AirfleetMenuEntry implements \Airfleet\Framework\Features\Feature {
	/**
	 * Menu data. See methods add_menu_item and add_submenu_item for expected data.
	 *
	 * @var array
	 */
	protected array $menu;

	/**
	 * Fallback title if "Airfleet" menu item does not exist and we have to create a parent menu item.
	 *
	 * @var string
	 */
	protected string $airfleet_title;

	/**
	 * Position for the fallback parent menu item.
	 *
	 * @var int|float
	 */
	protected int|float $airfleet_position;

	public function __construct( array $menu ) {
		$this->menu = $menu;
		$this->airfleet_title = __( 'Airfleet', 'airfleet' );
		$this->airfleet_position = 2;
	}

	public function initialize(): void {
		$this->add_menu();
	}

	protected function add_menu(): void {
		// Set high priority so this runs after ACF has created the parent page from the Starter Theme
		$priority = 200;
		add_action(
			'admin_menu',
			function () {
				$parent = $this->existing_airfleet_menu();

				if ( $parent ) {
					$this->add_submenu_item( $parent );
				} else {
					$this->add_menu_item();
				}
			},
			$priority
		);
	}

	/**
	 * Adds a first-level "Airfleet" menu item that render the configured page.
	 *
	 * @return void
	 */
	protected function add_menu_item(): void {
		add_menu_page(
			$this->menu['page_title'],
			$this->airfleet_title,
			$this->menu['capability'] ?? 'manage_options',
			$this->menu['menu_slug'],
			$this->menu['callback'],
			$this->menu['menu_icon'] ?? 'dashicons-admin-generic',
			$this->airfleet_position
		);
	}

	/**
	 * Adds the menu item as a child of the parent "Airfleet" menu.
	 *
	 * @param string $parent
	 * @return void
	 */
	protected function add_submenu_item( string $parent ): void {
		add_submenu_page(
			$parent,
			$this->menu['page_title'],
			$this->menu['menu_title'],
			$this->menu['capability'] ?? 'manage_options',
			$this->menu['menu_slug'],
			$this->menu['callback'],
			$this->menu['position']
		);
	}

	/**
	 * Find the menu item named "Airfleet" and return its slug.
	 *
	 * @return string
	 */
	protected function existing_airfleet_menu(): string {
		global $menu;

		foreach ( $menu as $item ) {
			$title = $item[0] ?? '';
			$slug = $item[2] ?? '';

			if ( strtolower( $title ) === 'airfleet' ) {
				return $slug;
			}
		}

		return '';
	}
}
