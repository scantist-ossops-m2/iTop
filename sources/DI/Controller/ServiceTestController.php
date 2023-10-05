<?php

namespace Combodo\iTop\DI\Controller;

use Combodo\iTop\DI\Services\MultiInstanceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ServiceTestController extends AbstractController
{

	/**
	 * @Route("/service_test_multi_instance/", name="service_test_multi_instance", methods={"GET"})
	 */
	public function testMultiInstance(Request $request, MultiInstanceService $oService1, MultiInstanceService $oService2) : JsonResponse
	{
		$oService1->sExternalInfo = 'service1';
		$oService2->sExternalInfo = 'service2';

		$oResponse = new JsonResponse([
			'service1.name' => $oService1->sInstanceName,
			'service2.name' => $oService2->sInstanceName,
			'service1.ext_info' => $oService1->sExternalInfo,
			'service2.ext_info' => $oService2->sExternalInfo,
			'service1.number' => $oService1->iInstanceNumber,
			'service2.number' => $oService2->iInstanceNumber,
		]);

		return $oResponse;
	}

}
