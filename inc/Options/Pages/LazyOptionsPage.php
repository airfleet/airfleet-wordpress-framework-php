<?php

namespace Airfleet\Framework\Options\Pages;

use Airfleet\Framework\Options\Tabs\LazyOptionsTab;

class LazyOptionsPage extends OptionsPage {
	public function __construct( string $id, callable $get_groups, string $page_id, array $args = [] ) {
		$this->id = $id;
		$this->options = new LazyOptionsTab( '', '', $get_groups, $page_id );
		$this->args = $args;
	}
}
