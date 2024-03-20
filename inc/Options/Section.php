<?php

namespace Airfleet\Framework\Options;

/**
 * A section that separates fields.
 */
class Section {
	protected string $id;
	protected string $title;
	protected string $description;
	protected mixed $visible;
	protected array $fields;

	public function __construct( array $args, array $fields = [] ) {
		$this->id = $args['id'];
		$this->title = $args['title'] ?? '';
		$this->description = $args['description'] ?? '';
		$this->visible = $args['visible'] ?? true;
		$this->fields = $fields;
	}

	public function register( string $page, Group $group ): void {
		if ( ! $this->is_visible() ) {
			return;
		}
		add_settings_section(
			$this->id,
			$this->title,
			[ $this, 'render' ],
			$page
		);

		foreach ( $this->fields as $field ) {
			$field->register( $page, $group, $this->id );
		}
	}

	public function is_visible(): bool {
		if ( is_callable( $this->visible ) ) {
			// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			return (bool) call_user_func( $this->visible, $this );
		}

		return (bool) $this->visible;
	}

	public function id(): string {
		return $this->id;
	}

	public function render(): void {
		if ( ! $this->description ) {
			return;
		}
		$allowed = [
			'br' => [],
			'strong' => [],
			'i' => [],
		];

		printf( '<p class="description">%s</p>', wp_kses( $this->description, $allowed ) );
	}

	public function fields(): array {
		return $this->fields;
	}

	public function enqueue(): void {
		foreach ( $this->fields as $field ) {
			$field->enqueue();
		}
	}
}
