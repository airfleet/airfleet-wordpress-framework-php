<?php

namespace Airfleet\Framework\Options\Tabs;

use Airfleet\Framework\Options\Group;

class OptionsTab extends LazyOptionsTab {
	protected string $id;
	protected string $title;
	protected Group $group;
	protected string $page_id;

	public function __construct( string $id, string $title, Group $group, string $page_id ) {
		parent::__construct(
			$id,
			$title,
			function () use ( $group ) {
				return [ $group ];
			},
			$page_id
		);
	}
}
