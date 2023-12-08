<?php

namespace Combodo\iTop\DI\Controller;

use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Timezone controller.
 *
 */
class TimezoneController extends AbstractController
{

	#[Route('/timezone/', name: 'timezone', methods: ['GET', 'POST'])]
	public function showForm(Request $request) : Response
	{
		$oTimezone = "Europe/Paris";
		$sDateTimeValue = '23-12-08 0:0:0';
		$oTime =  new \DateTime($sDateTimeValue, new DateTimeZone($oTimezone));

		if($request->request->has('form')){
			$oForm = $request->request->all()['form'];
			$oTimezone = $oForm['timezone'];
			$sDateTimeValue = $oForm['date'];
			$oTime = new \DateTime($sDateTimeValue, new DateTimeZone($oTimezone));
		}

		$oParis = clone $oTime;
		$oParis->setTimezone(new DateTimeZone('Europe/Paris'));
		$sParisValue = $oParis->format('Y-m-d\\ H:i:s');

		$aData = [
			'date' => $oTime,
			'paris' => $sParisValue,
			'timestamp' => $oTime->getTimestamp(),
			'timezone' => $oTimezone
		];

		$oForm = $this->createFormBuilder($aData)
			->add('date', DateTimeType::class, [
				'label' => 'Time',
				'model_timezone' => 'Europe/Paris',
				'view_timezone' => $oTimezone,
				'html5' => true,
				'widget' => 'single_text',
			])
			->add('timezone', TimezoneType::class, [
				'label' => 'Timezone',
			])
			->add('paris', TextType::class, [
				'label' => 'Time in Paris',
				'disabled' => true
			])
			->add('timestamp', TextType::class, [
				'label' => 'Timestamp',
				'disabled' => true
			])
			->add('save', SubmitType::class, ['label' => 'Create'])
			->getForm();

		$oForm->handleRequest($request);

		// submitted and valid
		if ($oForm->isSubmitted() && $oForm->isValid()) {


		}

		// return object edition form
		return $this->render('DI/form/form_page.html.twig', [
			'form' => $oForm,
		]);
	}



}
