<?php

namespace Combodo\iTop\DI\Controller;

use Combodo\iTop\DI\Form\Manager\ObjectFormManager;
use Combodo\iTop\DI\Form\Type\Compound\PartialObjectType;
use Combodo\iTop\DI\Form\Type\Compound\ObjectType;
use Combodo\iTop\DI\Services\ObjectService;
use Exception;
use MetaModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Object controller.
 *
 */
class ObjectController extends AbstractController
{


	/**
	 * Return object view page with object data printed with key value representation.
	 *
	 * @Route ("/{class<\w+>}/{id<\d+>}/view", name="object_view")
	 */
	public function objectView(string $class, int $id) : Response
	{
		// retrieve object
		try{
			$oObject = MetaModel::GetObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist", $e);
		}

		// return object view
		return $this->render('DI/object/view.html.twig', [
			'id' => $id,
			'class' => $class,
			'object' => $oObject
		]);
	}

	/**
	 * Return object view as JSon response.
	 *
	 * @Route ("/{class<\w+>}/{id<\d+>}/json", name="object_json")
	 */
	public function objectJSon(string $class, int $id) : JsonResponse
	{
		// retrieve object
		try{
			$oObject = MetaModel::GetObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist", $e);
		}

		// return object as json response
		$oResponse = new JsonResponse($oObject->GetValues());
		$oResponse->setEncodingOptions($oResponse->getEncodingOptions() | JSON_PRETTY_PRINT);
		return $oResponse;
	}

	/**
	 * Return object edition view page.
	 * The form is constructed with the ObjectType form type @see ObjectType
	 *
	 * @todo perform database DbInsert, DbDelete, DbUpdate on links
	 *
	 * @Route("/{class<\w+>}/{id<\d+>}/edit", name="object_edit")
	 */
	public function objectEdit(Request $request, string $class, int $id, ObjectService $oObjectService, Stopwatch $oStopWatch) : Response
	{
		// retrieve object
		try{
			$oObject = $oObjectService->getObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist");
		}

		// create object form
		$oStopWatch->start('creating form');
		$oForm = $this->createForm(ObjectType::class, $oObject, [
			'object_class' => $class
		]);
		$oStopWatch->stop('creating form');

		// handle HTTP request
		$oForm->handleRequest($request);

		// submitted and valid
		if ($oForm->isSubmitted() && $oForm->isValid()) {

			try {

				// handle link set (apply DbInsert, DbDelete, DbUpdate) should be automatic ? handle by host object ?
				$oObjectService->handleLinkSetDB($oObject);

				// save object
				if($id === 0){
					$id = $oObject->DBInsert();
				}
				else{
					$oObject->DBUpdate();
				}

			}
			catch(Exception $e){
				throw new HttpException(500, 'Error while trying to save object', $e);
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
	 * Return an object form view.
	 *
	 * @Route("/{class<\w+>}/{id<\d+>}/{name<\w+>}/form", name="object_form", methods={"POST"})
	 */
	public function objectForm(Request $request, string $name, string $class, int $id, ObjectService $oObjectService, FormFactoryInterface $oFormFactory) : Response
	{
		// retrieve object
		try{
			$oObject = $oObjectService->getObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist", $e);
		}

		// decode data
		$aData = json_decode($request->getContent(), true);

		// locked attributes
		foreach($aData['locked_attributes'] as $sKey => $sValue){
			$oObject->Set($sKey, $sValue);
		}

		// create object form
		$oForm = $oFormFactory->createNamed($name, ObjectType::class, $oObject, [
			'object_class' => $class,
			'locked_attributes' => $aData['locked_attributes'],
			'attr' => [
				'data-reload-url' => $this->generateUrl('object_reload', [
					'class' => $class,
					'id' => $id
				]),
				'data-object-class' => $class,
				'data-att-code' => $aData['att_code']
			]
		]);

		// locked attributes
		$oForm->add('locked_attributes', HiddenType::class, [
			'mapped' => false,
			'data' => json_encode($aData['locked_attributes'])
		]);

		// return object form
		return new JsonResponse([
			'template' => $this->renderView('DI/form/form.html.twig', [
				'id' => $id,
				'class' => $class,
				'form' => $oForm->createView(),
			])
		]);
	}

	/**
	 * Save object into database and return its list representation.
	 *
	 * @Route("/{class<\w+>}/{id<\d+>}/{name<\w+>}/save", name="object_save", methods={"POST"})
	 */
	public function objectSave(Request $request, string $name, string $class, int $id, ObjectService $oObjectService, FormFactoryInterface $oFormFactory, ObjectFormManager $oObjectFormManager) : Response
	{
		// retrieve object
		try{
			$oObject = $oObjectService->getObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist", $e);
		}

		// create object form
		$oForm = $oFormFactory->createNamed($name, ObjectType::class, $oObject, [
			'object_class' => $class,
		]);

		// handle HTTP request
		$oForm->handleRequest($request);

		// apply locked attributes to object
		$oObjectFormManager->applyRequestLockedAttributesToObject($request, $oObject, 'new');

		$sExtKeyToMe = $request->get('ext_key_to_me');
		$sObjectClass = $request->get('object_class');

		// submitted and valid
		if ($oForm->isSubmitted() && $oForm->isValid()) {

			try {
				// save object
				if($id === 0){
					$id = $oObject->DBInsert();
				}
				else{
					$oObject->DBUpdate();
				}
			}
			catch(Exception $e){
				throw new HttpException(500, 'Error while trying to save object', $e);
			}

			// create object form
			$oForm = $oFormFactory->createNamed($name, ObjectType::class, $oObject, [
				'object_class' => $sObjectClass,
				'z_list' => 'list',
				'is_link_set' => true,
				'ext_key_to_me' => $sExtKeyToMe
			]);

			// return object form
			return new JsonResponse([
				'succeeded' => true,
				'template' => $this->renderView('DI/form/form.html.twig', [
					'id' => $id,
					'class' => $sObjectClass,
					'form' => $oForm->createView(),
				])
			]);
		}

		// return object form
		return new JsonResponse([
			'succeeded' => false,
		]);
	}

	/**
	 * Actualize a piece of the form.
	 * The first form, used to apply modifications, contains all dependencies attributes and dependent attributes.
	 * The second form, used for new fields templates, contains only the dependent attributes.
	 *
	 * @Route("/{class<\w+>}/{id<\d+>}/reload", name="object_reload")
	 */
	public function objectReload(Request $request, string $class, int $id, ObjectService $oObjectService) : Response
	{
		// retrieve object
		try{
			$oObject = $oObjectService->getObject($class, $id);
		}
		catch(Exception $e){
			throw $this->createNotFoundException("The $class $id does not exist", $e);
		}

		$aDependencyAttCodes = explode(',', $request->get('dependency_att_codes'));
		$aAttCodes = explode(',', $request->get('att_codes'));

		// create form with request data (dependent field)
		$oForm = $this->createForm(PartialObjectType::class, $oObject, [
			'object_class' => $class,
			'att_codes' => array_merge($aDependencyAttCodes, $aAttCodes)
		]);

		// handle form data
		$oForm->handleRequest($request);

		// compute values
		$oObject->ComputeValues();

		// create a new form for affected field with updated (but not persist) data
		$oForm = $this->createForm(PartialObjectType::class, $oObject, [
			'object_class' => $class,
			'att_codes' => $aAttCodes
		]);

		// return object form
		return $this->renderForm('DI/form/form.html.twig', [
			'form' => $oForm,
		]);
	}

}
