<?php

namespace Airfleet\Framework\Options\Tabs;

use Airfleet\Framework\Options\Group;

class OptionsTab extends Tab {
	protected string $id;
	protected string $title;
	protected Group $group;
	protected string $page_id;

	public function __construct( string $id, string $title, Group $group, string $page_id ) {
		parent::__construct( $id, $title );
		$this->group = $group;
		$this->page_id = $page_id;
	}

	public function register(): void {
		add_action(
			'admin_init',
			function () {
				$this->group->register( $this->page_id );
			}
		);
	}

	public function render(): void {
		settings_errors();
		echo '<form method="post" action="options.php">';
		$this->group->render();
		do_settings_sections( $this->page_id );
		submit_button();
		echo '</form>';
	}
}
