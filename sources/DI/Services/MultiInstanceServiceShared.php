<?php

namespace Combodo\iTop\DI\Services;

class MultiInstanceServiceShared
{
	public $sInstanceName;

	public $iInstanceNumber;

	public $sExternalInfo;

	public static $iActiveInstancesCount = 0;

	public function __construct(string $sInstanceName)
	{
		self::$iActiveInstancesCount++;
		$this->iInstanceNumber = self::$iActiveInstancesCount;
		$this->sInstanceName = $sInstanceName;
	}
}