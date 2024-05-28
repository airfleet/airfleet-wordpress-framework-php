<?php

namespace Airfleet\Framework\Options\Tabs;

class LazyOptionsTab extends Tab {
	protected string $id;
	protected string $title;
	protected array $groups;
	protected string $page_id;
	protected $get_groups;

	public function __construct( string $id, string $title, callable $get_groups, string $page_id ) {
		parent::__construct( $id, $title );
		$this->groups = [];
		$this->get_groups = $get_groups;
		$this->page_id = $page_id;
	}

	public function register(): void {
		add_action(
			'admin_init',
			function () {
				foreach ( $this->groups() as $group ) {
					$group->register( $this->page_id );
				}
			}
		);

		$this->enqueue();
	}

	public function render(): void {
		settings_errors();
		echo '<form method="post" action="options.php">';

		foreach ( $this->groups() as $group ) {
			$group->render();
		}
		do_settings_sections( $this->page_id );
		submit_button();
		echo '</form>';
	}

	public function enqueue(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				foreach ( $this->groups() as $group ) {
					$group->enqueue();
				}
			}
		);
	}

	protected function groups(): array {
		if ( empty( $this->groups ) ) {
			$this->groups = call_user_func( $this->get_groups );
		}

		return $this->groups;
	}
}
