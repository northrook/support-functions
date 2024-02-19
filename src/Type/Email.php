<?php

declare ( strict_types = 1 );

namespace Northrook\Support\Type;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\Extra\SpoofCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;

class Email extends Type {

	public const TYPE = 'string';

	private static EmailValidator $validator;

	public readonly bool $isValid;

	public function __toString(): string {
		return (string) $this->string;
	}

	public function __construct( public ?string $string = null, bool $validate = true ) {

		if ( $validate ) {
			$this->validate();
		}

	}

	public function validate(): bool {

		if ( ! class_exists( EmailValidator::class ) ) {
			$this->isValid = false;

			return false;
		}

		if ( false === isset( self::$validator ) ) {
			self::$validator = new EmailValidator();
		}

		$validate = new MultipleValidationWithAnd( [
			new RFCValidation(),
			new SpoofCheckValidation(),
		] );

		$this->isValid = self::$validator->isValid(
			$this->string ?? '',
			$validate
		);

		return $this->isValid;
	}

}