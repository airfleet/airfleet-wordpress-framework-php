<?php

namespace Airfleet\Framework\Options;

use Airfleet\Framework\Options\Fields\Field;
use Airfleet\Framework\Options\Fields;

/**
 * Easy way to generate the options.
 */
class Wizard {
	public static function create_sections( array $sections ): array {
		$result = [];

		foreach ( $sections as $id => $section ) {
			$result[ $id ] = self::create_section(
				array_merge(
					[
						'id' => $section['id'] ?? ( is_string( $id ) ? $id : false ),
					],
					$section
				)
			);
		}

		return $result;
	}

	public static function create_section( array $args ): Section {
		if ( ! isset( $args['id'] ) || empty( $args['id'] ) ) {
			throw new \Exception( 'Missing section ID' );
		}

		return new Section(
			$args,
			self::create_fields( $args['fields'] ?? [] )
		);
	}

	public static function create_fields( array $fields ): array {
		$result = [];

		foreach ( $fields as $id => $field ) {
			$result[ $id ] = $field instanceof Field ? $field : self::create_field(
				array_merge(
					[
						'id' => $field['id'] ?? ( is_string( $id ) ? $id : self::field_id( $field ) ),
					],
					$field
				)
			);
		}

		return $result;
	}

	public static function create_field( array $args ): Field {
		if ( ! isset( $args['type'] ) ) {
			throw new \Exception( 'Missing type of field' );
		}

		$id = self::field_id( $args );
		$title = self::field_title( $args );

		// Remove the type otherwise it would override when creating the class instances
		$args_without_type = $args;
		unset( $args_without_type['type'] );

		switch ( $args['type'] ) {
			case 'checkbox':
				return new Fields\CheckboxField( $id, $title, $args_without_type );
			case 'checkbox_list':
				return new Fields\CheckboxListField( $id, $title, $args_without_type );
			case 'number':
				return new Fields\NumberField( $id, $title, $args_without_type );
			case 'password':
				return new Fields\PasswordField( $id, $title, $args_without_type );
			case 'radio_list':
				return new Fields\RadioListField( $id, $title, $args_without_type );
			case 'select':
				return new Fields\SelectField( $id, $title, $args_without_type );
			case 'textarea':
				return new Fields\TextareaField( $id, $title, $args_without_type );
			case 'text':
				return new Fields\TextField( $id, $title, $args_without_type );
			case 'url':
				return new Fields\UrlField( $id, $title, $args_without_type );
			case 'encrypted_password':
				return new Fields\EncrypytedPasswordField( $id, $title, $args_without_type );
			case 'hidden':
				return new Fields\HiddenField( $id, $title, $args_without_type );
			case 'image':
				return new Fields\ImageField( $id, $title, $args_without_type );
			case 'wysiwyg':
				return new Fields\WysiwygField($id, $title, $args_without_type);
			default:
				$class_type = $args['type'] ?? '';

				if ( $class_type && class_exists( $class_type ) && is_subclass_of( $class_type, Field::class ) ) {
					return new $class_type($id, $title, $args_without_type);
				}

				throw new \Exception( "Unrecognized type '{$args['type']}'" );
		}
	}

	protected static function field_id( array $args ): string {
		if ( isset( $args['id'] ) ) {
			return $args['id'];
		}

		if ( isset( $args['title'] ) ) {
			return sanitize_title( $args['title'] );
		}

		throw new \Exception( 'Missing ID field' );
	}

	protected static function field_title( array $args ): string {
		if ( isset( $args['title'] ) ) {
			return $args['title'];
		}

		if ( isset( $args['id'] ) ) {
			return $args['id'];
		}

		return '';
	}
}
