<?php

namespace Airfleet\Framework\Options;

use Airfleet\Framework\Options\Fields\Field;

/**
 * A group of fields.
 */
class Group {
	protected string $group;
	protected string $name;
	protected array $sections;

	public function __construct( string $group, string $name, array $sections ) {
		$this->group = $group;
		$this->name = $name;
		$this->sections = $sections;
	}

	public function register( string $page ): void {
		register_setting(
			$this->group,
			$this->name,
			[
				'default' => $this->default_values(),
				'sanitize_callback' => function ( $values ): array {
					return $this->sanitize( $values ?: [] );
				},
			]
		);

		foreach ( $this->sections as $section ) {
			$section->register( $page, $this );
		}
	}

	public function group(): string {
		return $this->group;
	}

	public function name(): string {
		return $this->name;
	}

	public function enqueue(): array {
		return $this->sections;
	}

	public function render(): void {
		settings_fields( $this->group );
	}

	public function value( string $field_id, bool $format = true ): mixed {
		$option = get_option( $this->name );
		$field = $this->field( $field_id );

		if ( isset( $option[ $field_id ] ) ) {
			return $format ? $field->format( $option[ $field_id ] ) : $option[ $field_id ];
		}

		return $field->default_value();
	}

	protected function sanitize( array $values ): array {
		$result = [];

		foreach ( $values as $id => $value ) {
			$field = $this->field( $id );
			$old_value = $this->value( $id );
			$new_value = $field->sanitize( $value );
			$save_value = $field->validate( $new_value ) ? $field->before_save( $new_value ) : $old_value;
			$result[ $id ] = $save_value;
		}

		foreach ( $this->sections as $section ) {
			foreach ( $section->fields() as $field ) {
				$id = $field->id();
				$old_value = $this->value( $id );
				$new_value = $field->sanitize( $values[ $id ] ?? null );
				$save_value = $field->validate( $new_value ) ? $field->before_save( $new_value ) : $old_value;
				$result[ $id ] = $save_value;
			}
		}

		return $result;
	}

	protected function default_values(): array {
		$default = [];

		foreach ( $this->sections as $section ) {
			foreach ( $section->fields() as $field ) {
				$default[ $field->id() ] = $field->default_value();
			}
		}

		return $default;
	}

	protected function field( string $id ): Field {
		foreach ( $this->sections as $section ) {
			foreach ( $section->fields() as $field ) {
				if ( $field->id() === $id ) {
					return $field;
				}
			}
		}

		throw new \Exception( "Field '{$id}' not found" );
	}
}
