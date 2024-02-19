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
		public readonly int $strength = 3,
		public readonly array $context = [],
		bool $validate = true
	) {

		if ( $validate ) {
			$this->validate();
		}

	}

	private function validate( ?int $strength = null ): void {

		$strength ??= $this->strength;

		$strength = max( 0, min( 4, $strength ) );

		$validator = new Zxcvbn();

		$this->score = $validator->passwordStrength( $this->string ?? '', $this->context );

		if ( $this->score['score'] < $strength ) {
			$this->isValid = false;
		} else {
			$this->isValid = true;
		}
	}
}
