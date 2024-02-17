<?php

declare ( strict_types = 1 );

namespace Northrook\Support\Type;

use ZxcvbnPhp\Zxcvbn;

class Password extends Type {

	public const TYPE = 'string';

	public readonly bool $isValid;
	public readonly array $score;

	public function __toString(): string {
		return (string) $this->string;
	}

	public function __construct(
		public ?string $string = null,
		public int $strength = 3,
		public array $context = [],
		public bool $validate = true
	) {

		$this->strength = max( 0, min( 4, $this->strength ) );

		if ( $this->validate ) {
			$this->validate();
		}

	}

	public function validate(): bool {

		$validator = new Zxcvbn();

		$this->score = $validator->passwordStrength( $this->string ?? '', $this->context );

		if ( $this->score['score'] < $this->strength ) {
			return false;
		}

		return true;
	}
}
