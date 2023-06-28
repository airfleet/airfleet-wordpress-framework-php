<?php

namespace Airfleet\Framework\Helpers;

use Jawira\CaseConverter\Convert;

class StringsImplementation {
	public function convert( string $source ): Convert {
		return new Convert( $source );
	}
}
