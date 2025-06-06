<?php

namespace Airfleet\Framework\Options\Fields;

class EncrypytedPasswordField extends PasswordField {
	protected string $encryption_key;
	protected string $cipher_method = 'AES-256-CBC';
	protected string $field_id = '';

	public function __construct( string $id, string $title, array $args = [], $encryption_key = null ) {
		parent::__construct( $id, $title, $args );
		$this->field_id = $id;
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

	public function before_save(mixed $new_value, mixed $old_value): mixed {

        // if old value is empty or user wanted to change value.
		// phpcs:ignore: WordPress.Security.NonceVerification.Recommended
        if ( empty( $old_value ) || ( isset( $_REQUEST[ $this->field_id . '_change' ] ) && '1' === $_REQUEST[ $this->field_id . '_change' ] ) ) {
            return $this->encrypt($new_value);
        }

        return $this->encrypt($old_value);
    }

	public function format( mixed $value ): mixed {
		return $this->decrypt( $value );
	}

	// phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
    protected function render_input(array $args, mixed $value): void {
        // puts some asterisk in placeholder showcase old value.
        if ( ! empty($value) ) {
            $args['placeholder'] = '******************';
            $args['disabled'] = 'disabled';
        }

        // ! Do not show value when editing field
        parent::render_input($args, '');

        // Show checkbox if field have old value.
        if ( ! empty( $value ) ) {
            printf('<input type="hidden" name="%1$s_change" id="%1$s_change" value="0">', $this->field_id);
            printf('<input type="button" class="button button-secondry" style="margin-left: 5px;" value="Remove Value" onclick="let fieldInput = document.getElementById(\'%1$s\'); fieldInput.removeAttribute(\'disabled\'); fieldInput.removeAttribute(\'placeholder\'); document.getElementById(\'%1$s_change\').value=\'1\';">', $this->field_id);
        }
    }
}
