<?php

namespace Combodo\iTop\DI\Controller;

use Combodo\iTop\DI\Services\MultiInstanceServiceNotShared;
use Combodo\iTop\DI\Services\MultiInstanceServiceShared;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ServiceTestController extends AbstractController
{

	/**
	 * @Route("/service_test_multi_instance_shared/", name="service_test_multi_instance_shared", methods={"GET"})
	 */
	public function testMultiInstance(Request $request, MultiInstanceServiceNotShared $oService1, MultiInstanceServiceNotShared $oService2): JsonResponse
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

	/**
	 * @Route("/service_test_multi_instance_by_method/", name="service_test_multi_instance_by_method", methods={"GET"})
	 */
	public function testMultiInstanceByMethod(Request $request, MultiInstanceServiceShared $oService): JsonResponse
	{
		$this->changeServiceExtInfo($oService);
		$this->changeServiceInstanceNumber($oService);

		$oResponse = new JsonResponse([
			'service.name' => $oService->sInstanceName,
			'service.ext_info' => $oService->sExternalInfo,
			'service.number' => $oService->iInstanceNumber,
		]);

		return $oResponse;
	}

	public function changeServiceExtInfo(MultiInstanceServiceShared $oService)
	{
		$oService->sExternalInfo = 'from method1';
	}

	public function changeServiceInstanceNumber(MultiInstanceServiceShared $oService)
	{
		$oService->iInstanceNumber = 1;
	}
}
