<?php

namespace Combodo\iTop\DI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Default controller.
 *
 */
class DefaultController extends AbstractController
{

	#[Route('/', name: 'root', methods: ['GET'])]
	public function root(): Response
	{
		return $this->redirectToRoute('home');
	}

	#[Route('/home', name: 'home', methods: ['GET'])]
	public function home(): Response
	{
		return $this->render('DI/home.html.twig');
	}

}
