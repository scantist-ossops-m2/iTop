<?php

namespace Combodo\iTop\DI\Services;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Orm
{
	public string $mapping;

	public function __construct(string $mapping)
	{
		$this->mapping = $mapping;
	}
}