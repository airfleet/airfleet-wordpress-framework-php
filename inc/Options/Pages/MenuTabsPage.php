<?php

namespace Airfleet\Framework\Options\Pages;

use Airfleet\Framework\Features\Feature;
use Airfleet\Framework\Options\Pages\Page;
use Airfleet\Framework\Options\Pages\TabsPage;
use Airfleet\Framework\Options\Menu\AirfleetMenuEntry;
use Airfleet\Framework\Options\Menu\SettingsMenuEntry;
use Airfleet\Framework\Options\Menu\MainMenuEntry;
use Airfleet\Framework\Options\Menu\ToolsMenuEntry;

class MenuTabsPage implements Feature {
	protected Page $page;
	protected Feature $menu;

	public function __construct( array $args, string $menu_type ) {
		$this->page = new TabsPage(
			$args['slug'],
			$args['tabs'],
			[
				'class' => $args['class'] ?? '',
				'base' => $this->page_base( $menu_type ),
			]
		);
		$this->menu = $this->create_menu( $args, $menu_type );
	}

	public function initialize(): void {
		$this->menu->initialize();
		$this->page->register();
	}

	protected function page_base( string $menu_type ): string {
		if ( $menu_type === 'settings' ) {
			return 'options-general.php';
		}

		if ( $menu_type === 'tools' ) {
			return 'tools.php';
		}

		return 'admin.php';
	}

	protected function create_menu( array $args, string $menu_type ): Feature {
		$menu_args = [
			'page_title' => $args['title'] ?? $args['page_title'] ?? $args['menu_title'] ?? '',
			'menu_title' => $args['title'] ?? $args['menu_title'] ?? $args['page_title'] ?? '',
			'menu_slug' => $args['slug'],
			'callback' => function () {
				$this->page->render();
			},
			'position' => $args['position'] ?? null,
			'menu_icon' => $args['menu_icon'] ?? '',
		];

		switch ( $menu_type ) {
			case 'main':
				return new MainMenuEntry( $menu_args );

			case 'settings':
				return new SettingsMenuEntry( $menu_args );

			case 'tools':
				return new ToolsMenuEntry( $menu_args );

			case 'airfleet':
			default:
				return new AirfleetMenuEntry( $menu_args );
		}
	}
}
