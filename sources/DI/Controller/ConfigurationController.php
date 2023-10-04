<?php

namespace Combodo\iTop\DI\Controller;

use Combodo\iTop\DI\Form\Type\Compound\ConfigurationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Configuration controller.
 *
 */
class ConfigurationController extends AbstractController
{

	#[Route('/configuration/edit', name: 'configuration_edit')]
	public function configurationEdit(Request $request) : Response
	{
		// create object form
		$oForm = $this->createForm(ConfigurationType::class, []);

		// handle HTTP request
		$oForm->handleRequest($request);

		// return object form
		return $this->renderForm('DI/configuration/edit.html.twig', [
			'form' => $oForm
		]);
	}
}
