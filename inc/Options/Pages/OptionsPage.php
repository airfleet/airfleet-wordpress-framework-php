<?php

namespace Airfleet\Framework\Options\Pages;

use Airfleet\Framework\Options\Group;
use Airfleet\Framework\Options\Tabs\Tab;
use Airfleet\Framework\Options\Tabs\OptionsTab;

/**
 * A single page of options.
 */
class OptionsPage extends Page {
	protected string $id;
	protected Tab $options;
	protected array $args;

	public function __construct( string $id, Group $group, string $page_id, array $args = [] ) {
		$this->id = $id;
		$this->options = new OptionsTab( '', '', $group, $page_id );
		$this->args = $args;
	}

	public function register(): void {
		$this->options->register();
	}

	public function render(): void {
		?>
		<div class="<?php echo $this->css_classes(); ?>">
				<h1><?php echo get_admin_page_title(); ?></h1>
				<?php $this->options->render(); ?>
			</div>
		<?php
	}

	protected function css_classes(): string {
		if ( ! isset( $this->args['class'] ) ) {
			return 'wrap';
		}

		return 'wrap ' . esc_attr( $this->args['class'] );
	}
}
