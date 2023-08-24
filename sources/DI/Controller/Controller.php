<?php

namespace Combodo\iTop\DI\Controller;

use Combodo\iTop\DI\Form\Type\Compound\ConfigurationType;
use Combodo\iTop\DI\Form\Type\Compound\ObjectSingleAttributeType;
use Combodo\iTop\DI\Form\Type\Compound\ObjectType;
use Combodo\iTop\DI\Services\ObjectService;
use Exception;
use MetaModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class Controller extends AbstractController
{
	/**
	 * @Route ("/", name="root")
	 */
	public function root(): Response
	{
		return $this->redirectToRoute('home');
	}

	/**
	 * @Route ("/home", name="home")
	 */
	public function home(): Response
	{
		return $this->render('DI/home.html.twig');
	}

	/**
	 * @Route ("/{class<\w+>}/{id<\d+>}/view", name="object_view")
	 *
	 */
	public function objectView(string $class, int $id) : Response
	{
		// retrieve object
		try{
			$oObject = MetaModel::GetObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist");
		}

		// return object view
		return $this->render('DI/object/view.html.twig', [
			'id' => $id,
			'class' => $class,
			'object' => $oObject
		]);
	}

	/**
	 * @Route ("/{class<\w+>}/{id<\d+>}/json", name="object_json")
	 */
	public function objectJSon(string $class, int $id) : Response
	{
		// retrieve object
		try{
			$oObject = MetaModel::GetObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist");
		}

		// return object as json response
		$oResponse = new JsonResponse($oObject->GetValues());
		$oResponse->setEncodingOptions($oResponse->getEncodingOptions() | JSON_PRETTY_PRINT);
		return $oResponse;
	}

	/**
	 * @Route("/{class<\w+>}/{id<\d+>}/edit", name="object_edit")
	 */
	public function objectEdit(Request $request, string $class, int $id, ObjectService $oObjectService) : Response
	{
		// retrieve object
		try{
			$oObject= $oObjectService->getObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist");
		}

		// create object form
		$oForm = $this->createForm(ObjectType::class, $oObject, [
			'object_class' => $class,
			'attr' => [
				'data-reload-url' => $this->generateUrl('object_reload', ['class' => $class, "id" => $id])
			]
		]);

		// handle HTTP request
		$oForm->handleRequest($request);

		// submitted and valid
		if ($oForm->isSubmitted() && $oForm->isValid()) {

			// retrieve object
			$oObject = $oForm->getData();

			try {
				// handle link set (apply DbInsert, DbDelete, DbUpdate) could be automatic ?
				$oObjectService->handleLinkSetDB($oObject);

				// save object
				$oObject->DBUpdate();
			}
			catch(Exception $e){
				throw new HttpException(500, 'Error while trying to save object');
			}

			// redirect to view object
			return $this->redirectToRoute('object_view', [
				'id' => $id,
				'class' => $class
			]);
		}

		// return object edition form
		return $this->renderForm('DI/object/edit.html.twig', [
			'id' => $id,
			'class' => $class,
			'form' => $oForm,
			'db_host' => $oObjectService->getDbHost(),
			'db_name' => $oObjectService->getDbName()
		]);
	}

	/**
	 * @Route("/{class<\w+>}/{id<\d+>}/{name<\w+>}/form", name="object_form", methods={"POST"})
	 */
	public function objectForm(Request $request, string $name, string $class, int $id, ObjectService $oObjectService, FormFactoryInterface $oFormFactory) : Response
	{
		// retrieve object
		try{
			$oObject= $oObjectService->getObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist");
		}

		// decode data
		$aData = json_decode($request->getContent(), true);

		// create object form
		$oForm = $oFormFactory->createNamed($name, ObjectType::class, $oObject, [
			'object_class' => $class,
			'locked_attributes' => $aData['locked_attributes'],
			'attr' => [
				'data-reload-url' => $this->generateUrl('object_reload', [
					'class' => $class,
					'id' => $id
				])
			]
		]);

		// handle HTTP request
		$oForm->handleRequest($request);

		// submitted and valid
		if ($oForm->isSubmitted() && $oForm->isValid()) {

			// retrieve object
			$oObject = $oForm->getData();

			try {
				// handle link set (apply DbInsert, DbDelete, DbUpdate) could be automatic ?
				$oObjectService->handleLinkSetDB($oObject);

				// save object
				$oObject->DBUpdate();
			}
			catch(Exception $e){
				throw new HttpException(500, 'Error while trying to save object');
			}

			// redirect to view object
			return new JsonResponse();
		}

		// return object form
		return new JsonResponse([
			'template' => $this->renderView('DI/form.html.twig', [
				'id' => $id,
				'class' => $class,
				'form' => $oForm->createView(),
			])
		]);
	}

	/**
	 * @Route("/{class<\w+>}/{id<\d+>}/reload", name="object_reload")
	 */
	public function objectReload(Request $request, string $class, int $id, ObjectService $oObjectService) : Response
	{
		// retrieve object
		try{
			$oObject= $oObjectService->getObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist");
		}

		// create form with request data (dependent field)
		$oForm = $this->createForm(ObjectType::class, $oObject, [
			'object_class' => $class,
		]);

		// handle form data
		$oForm->handleRequest($request);

		// compute values
		$oObject->ComputeValues();

		// create a new form for affected field with updated (but not persist) data
		$oForm = $this->createForm(ObjectSingleAttributeType::class, $oObject, [
			'object_class' => $class,
			'att_code' => $request->get('att_code')
		]);

		// return object form
		return $this->renderForm('DI/form.html.twig', [
			'form' => $oForm,
		]);
	}

	/**
	 * @Route("/configuration/edit", name="configuration_edit")
	 */
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
