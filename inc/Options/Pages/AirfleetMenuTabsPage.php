<?php

namespace Airfleet\Plugins\Admin\Options\Pages;

class AirfleetMenuTabsPage extends MenuTabsPage {
	public function __construct( array $args ) {
		parent::__construct( $args, 'airfleet' );
	}
}
