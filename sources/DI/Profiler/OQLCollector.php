<?php

namespace Combodo\iTop\DI\Profiler;

use ExecutionKPI;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OQLCollector extends AbstractDataCollector
{
	private array $aOQL;

	public function __construct()
	{
		$this->aOQL = [
			'SELECT PERSON AS p WHERE p.name = "Pascal"',
			'SELECT PERSON AS p WHERE p.name = "Daniel"',
		];
	}

	public function addOQL(string $sOQL)
	{
		$this->aOQL[] = $sOQL;
	}

	public function collect(Request $request, Response $response, \Throwable $exception = null)
	{
		 $this->data = [
			 'exec_data' => ExecutionKPI::getExecData(),
			 'stats' => ExecutionKPI::getStats()
		 ];
	}

	public function getExecData()
	{
		return $this->data['exec_data'];
	}

	public function getStats()
	{
		return $this->data['stats'];
	}

	public static function getTemplate(): ?string
	{
		return 'DI/profiler/oql.html.twig';
	}
}