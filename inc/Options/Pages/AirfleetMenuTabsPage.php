<?php

namespace Airfleet\Framework\Options\Pages;

class AirfleetMenuTabsPage extends MenuTabsPage {
	public function __construct( array $args ) {
		parent::__construct( $args, 'airfleet' );
	}
}
