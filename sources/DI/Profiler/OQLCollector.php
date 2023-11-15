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
			 'execution' => ExecutionKPI::getExecData()
		 ];
	}

	public function getRequests()
	{
		return $this->data['execution'];
	}

	public static function getTemplate(): ?string
	{
		return 'DI/profiler/oql.html.twig';
	}
}