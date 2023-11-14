<?php

namespace Combodo\iTop\DI\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TestDto
{
	public function __construct(
		#[Assert\Length(
			min: 2,
			max: 50,
			minMessage: 'Your first name must be at least {{ limit }} characters long',
			maxMessage: 'Your first name cannot be longer than {{ limit }} characters',
		)]
		public readonly string $color,

	) {
	}
}