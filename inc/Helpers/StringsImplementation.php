<?php

namespace Airfleet\Framework\Helpers;

use Jawira\CaseConverter\Convert;

class StringsImplementation {
	public function convert( string $source ): Convert {
		return new Convert( $source );
	}

	public function kebabToPascal( string $source ): string {
		$pieces = explode( '-', $source );
		$pieces = array_map( 'ucfirst', $pieces );

		return implode('', $pieces);
	}

	public function kebabToTitle( string $source ): string {
		$pieces = explode( '-', $source );
		$pieces = array_map( 'ucfirst', $pieces );

		return implode(' ', $pieces);
	}
}
