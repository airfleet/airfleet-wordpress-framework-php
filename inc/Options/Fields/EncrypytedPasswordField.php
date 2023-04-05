<?php

namespace Airfleet\Framework\Options\Fields;

class EncrypytedPasswordField extends PasswordField {
	protected string $encryption_key;
	protected string $cipher_method = 'AES-256-CBC';

	public function __construct( string $id, string $title, array $args = [], $encryption_key = null ) {
		parent::__construct(
			$id,
			$title,
			array_merge(
				[
					'notice' => __( 'Value is not shown in the input after loading. Re-insert value before saving page.', 'airfleet' ),
					'notice_type' => 'warning',
				],
				$args
			)
		);
		$fallback_encryption_key = '|EZi7!^(oRQ^?r|/W-X^S5jS]M,zaDw+G%zYb$9!8gN{u(i}4llyWK-9afD|Y|3W';
		$this->encryption_key = $encryption_key ? $encryption_key : ( defined( 'SECURE_AUTH_KEY' ) ? \SECURE_AUTH_KEY : $fallback_encryption_key );
	}

	public function encrypt( string $value ): string {
		if ( ! extension_loaded( 'openssl' ) ) {
			throw new \Exception( 'openssl PHP extension must be enabled' );
		}
		$iv_length = openssl_cipher_iv_length( $this->cipher_method );
		$iv = openssl_random_pseudo_bytes( $iv_length );
		$encrypted = openssl_encrypt( $value, $this->cipher_method, $this->encryption_key, 0, $iv );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $iv . $encrypted );
	}

	public function decrypt( string $value ): string {
		if ( ! extension_loaded( 'openssl' ) ) {
			throw new \Exception( 'openssl PHP extension must be enabled' );
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$decoded = base64_decode( $value, true );
		$iv_length = openssl_cipher_iv_length( $this->cipher_method );
		$iv = substr( $decoded, 0, $iv_length );
		$data = substr( $decoded, $iv_length );
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$decrypted = @openssl_decrypt( $data, $this->cipher_method, $this->encryption_key, 0, $iv );

		return $decrypted ?: '';
	}

	public function before_save( mixed $value ): mixed {
		return $this->encrypt( $value );
	}

	public function format( mixed $value ): mixed {
		return $this->decrypt( $value );
	}

	// phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	protected function render_input( array $args, mixed $value ): void {
		// ! Do not show value when editing field
		parent::render_input( $args, '' );
	}
}
