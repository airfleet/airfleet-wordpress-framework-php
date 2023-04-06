<?php

namespace Airfleet\Framework\Options\Pages;

use Airfleet\Framework\Options\Tabs\Tab;

/**
 * A page with several tabs.
 */
class TabsPage extends Page {
	protected string $id;
	protected array $tabs;
	protected array $args;

	public function __construct( string $id, array $tabs, array $args = [] ) {
		$this->id = $id;
		$this->tabs = $tabs;
		$this->args = $args;
	}

	public function register(): void {
		foreach ( $this->tabs as $tab ) {
			$tab->register();
		}
	}

	public function render(): void {
		?>
			<div class="<?php echo $this->css_classes(); ?>">
				<h1><?php echo get_admin_page_title(); ?></h1>
				<?php $this->render_tab_navigation(); ?>
				<?php $this->render_tab_content(); ?>
			</div>
		<?php
	}

	protected function css_classes(): string {
		$classes = [
			'wrap',
		];

		if ( isset( $this->args['class'] ) ) {
			$classes[] = esc_attr( $this->args['class'] );
		}

		$tab = $this->active_tab();

		if ( $tab ) {
			$classes[] = "tab--{$tab->id()}";
		}

		return implode( ' ', $classes );
	}

	protected function render_tab_navigation(): void {
		echo '<nav class="nav-tab-wrapper">';

		foreach ( $this->tabs as $tab ) {
			printf(
				'<a href="%s" class="nav-tab %s">%s</a>',
				$this->tab_url( $tab->id() ),
				$this->active_class( $tab->id() ),
				esc_html( $tab->title() )
			);
		}
		echo '</nav>';
	}

	protected function render_tab_content(): void {
		$active = $this->active_tab();

		if ( ! $active ) {
			return;
		}
		$active->render();
	}

	protected function active_class( string $tab ): string {
		if ( $this->is_active_tab( $tab ) ) {
			return 'nav-tab-active';
		}

		return '';
	}

	protected function is_active_tab( string $tab ): bool {
		$active = $this->active_tab();

		return $active && $active->id() === $tab;
	}

	protected function active_tab(): Tab | null {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_id = sanitize_text_field( wp_unslash( $_GET['tab'] ?? '' ) );
		$active_tab = $this->tab( $active_id );

		return $active_tab ?: $this->default_tab();
	}

	protected function tab( string $tab_id ): Tab | null {
		if ( ! $tab_id ) {
			return null;
		}

		foreach ( $this->tabs as $tab ) {
			if ( $tab->id() === $tab_id ) {
				return $tab;
			}
		}

		return null;
	}

	protected function default_tab(): Tab | null {
		return $this->tabs[0] ?? null;
	}

	protected function tab_url( string $tab ): string {
		$base = $this->args['base'] ?? 'admin.php';

		return esc_url(
			add_query_arg(
				[ 'tab' => $tab ],
				admin_url( "{$base}?page={$this->id}" )
			)
		);
	}
}
