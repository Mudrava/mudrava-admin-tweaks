<?php
/**
 * Sanitization utilities.
 *
 * Thin wrappers around WordPress sanitize functions with strict types.
 *
 * @package Mudrava\AdminTweaks\Core
 */

declare(strict_types=1);

namespace Mudrava\AdminTweaks\Core;

final class Sanitize {

	public static function text( mixed $value ): string {
		return sanitize_text_field( (string) $value );
	}

	public static function textarea( mixed $value ): string {
		return sanitize_textarea_field( (string) $value );
	}

	public static function email( mixed $value ): string {
		return sanitize_email( (string) $value );
	}

	public static function int( mixed $value ): int {
		return absint( $value );
	}

	public static function bool( mixed $value ): bool {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	public static function slug( mixed $value ): string {
		return sanitize_title( (string) $value );
	}

	public static function url( mixed $value ): string {
		return esc_url_raw( (string) $value );
	}

	public static function html( mixed $value ): string {
		return wp_kses_post( (string) $value );
	}

	public static function key( mixed $value ): string {
		return sanitize_key( (string) $value );
	}

	public static function fileName( mixed $value ): string {
		return sanitize_file_name( (string) $value );
	}

	/**
	 * Sanitize an array of strings.
	 *
	 * @param mixed $value The input value.
	 * @return list<string>
	 */
	public static function stringArray( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}

		return array_values(
			array_map( [ self::class, 'text' ], $value ),
		);
	}
}
