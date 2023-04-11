<?php

namespace Airfleet\Framework\Options\Fields;

use Airfleet\Framework\Options\Group;

abstract class Field {
	protected string $id;
	protected string $title;
	protected array $args;
	protected Group|null $group;
	protected bool $is_array;

	public function __construct( string $id, string $title, array $args = [] ) {
		$this->id = $id;
		$this->title = $title;
		$this->args = array_merge( [ 'label_for' => $this->id ], $args );
		$this->group = null;
		$this->is_array = $this->args['multiple'] ?? false;
	}

	abstract protected function render_input( array $args, mixed $value ): void;

	public static function render_attributes( array $attrs ): string {
		return join(
			' ',
			array_map(
				function( $key ) use ( $attrs ): string {
					$value = $attrs[ $key ];

					if ( is_bool( $value ) ) {
						return $value ? $key : '';
					}

					return sprintf( '%s="%s"', $key, esc_attr( $value ) );
				},
				array_keys( $attrs )
			)
		);
	}

	public static function options( array $args ): array {
		if ( ! isset( $args['options'] ) || empty( $args ) ) {
			return [];
		}

		if ( is_callable( $args['options'] ) ) {
			// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			return call_user_func( $args['options'], $args );
		}

		return $args['options'];
	}

	public static function render_options( array $options, mixed $selected, callable $render ): string {
		return join(
			'',
			array_map(
				function ( string $option_value, string $option_text ) use ( $render, $selected ): string {
					// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
					return call_user_func( $render, $option_value, $option_text, $selected );
				},
				array_keys( $options ),
				array_values( $options )
			)
		);
	}

	public function register( string $page, Group $group, string $section ): void {
		$this->group = $group;

		if ( $this->is_visible() ) {
			add_settings_field(
				$this->id,
				$this->title,
				function ( array $args ): void {
					$this->render( $args );
				},
				$page,
				$section,
				$this->args,
			);
		}
	}

	public function is_visible(): bool {
		if ( ! isset( $this->args['visible'] ) ) {
			return true;
		}

		if ( is_callable( $this->args['visible'] ) ) {
			// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			return (bool) call_user_func( $this->args['visible'], $this );
		}

		return (bool) $this->args['visible'];
	}

	public function id(): string {
		return $this->id;
	}

	public function title(): string {
		return $this->title;
	}

	public function default_value(): mixed {
		return $this->args['default'] ?? false;
	}

	public function sanitize( mixed $value ): mixed {
		return $this->call_args_callback( 'sanitize', $value, [ $this, 'default_sanitize' ] );
	}

	public function before_save( mixed $value ): mixed {
		return $value;
	}

	public function validate( mixed $value ): bool {
		return $this->call_args_callback( 'validate', $value, [ $this, 'default_validate' ] );
	}

	/**
	 * Filter the value when being read from the options.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function format( mixed $value ): mixed {
		return $value;
	}

	protected function default_sanitize( mixed $value ): mixed {
		return $this->is_array ? (array) $value : $value;
	}

	// phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	protected function default_validate( mixed $value ): bool {
		return true;
	}

	protected function input_name(): string {
		return sprintf(
			'%s[%s]%s',
			$this->group->name(),
			$this->id,
			$this->is_array ? '[]' : '', // for checkboxes input name must end with "[]"
		);
	}

	protected function render( array $args ): void {
		$value = $this->group->value( $this->id );
		$this->render_input( $args, $value );
		$this->render_instructions( $args );
		$this->render_images( $args );
		$this->render_notice( $args );
	}

	protected function render_notice( array $args ): void {
		$notice = $args['notice'] ?? '';

		if ( ! $notice ) {
			return;
		}
		$type = $args['notice_type'] ?? 'warning';

		printf( '<section class="notice notice-%s"><p>%s</p></section>', $type, $notice );
	}

	protected function render_instructions( array $args ): void {
		$instructions = $args['instructions'] ?? '';

		if ( ! $instructions ) {
			return;
		}

		printf( '<p class="description">%s</p>', $instructions );
	}

	protected function render_images( array $args ): void {
		$images = $args['images'] ?? [];

		if ( ! $images ) {
			return;
		}
		$path = $args['images_path'] ?? '';
		$html = '';

		foreach ( $images as $key => $image ) {
			$image_path = rtrim( $path, '/' ) . '/' . $image;
			$alt = is_string( $key ) ? $key : '';
			$html .= sprintf(
				'<a href="%s" target="_blank" class="afa-options-image-anchor">'
				. '<img src="%s" alt="%s" class="afa-options-image" />'
				. '</a>',
				$image_path,
				$image_path,
				$alt
			);
		}

		printf( '<div class="afa-options-images">%s</div>', $html );
	}

	protected function add_error( string $message, string $error_id = '' ): void {
		add_settings_error(
			"{$this->group->name()}_errors",
			$error_id ?: "{$this->id}_error",
			$message,
			'error'
		);
	}

	protected function call_args_callback( string $key, mixed $value, callable $default_callback ): mixed {
		if ( isset( $this->args[ $key ] ) ) {
			if ( ! $this->args[ $key ] ) {
				return $value;
			}

			if ( is_callable( $this->args[ $key ] ) ) {
				// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
				return call_user_func( $this->args[ $key ], $value );
			}

			return $value;
		}

		// phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
		return call_user_func( $default_callback, $value );
	}
}
