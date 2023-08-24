<?php

namespace Combodo\iTop\DI\Form\Listener;

use Combodo\iTop\Application\Helper\FormHelper;
use Combodo\iTop\DI\Form\Type\Layout\AbstractContainerType;
use DBObject;
use Exception;
use IssueLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Listener to modify fields alongside the form lifecycle.
 *
 * https://symfony.com/doc/current/form/events.html
 *
 */
class ObjectFormListener implements EventSubscriberInterface
{

	/** @inheritdoc */
	public static function getSubscribedEvents(): array
	{
		return [
			FormEvents::PRE_SET_DATA => 'preSetData',
			FormEvents::PRE_SUBMIT   => 'preSubmit',
		];
	}

	/**
	 * When form is first initialized, it hasn't data set yet.
	 * So we listen for form PRE_SET_DATA event to modify form with data.
	 *
	 * @param FormEvent $oEvent form event
	 *
	 * @suppress-unused-warning
	 */
	public function preSetData(FormEvent $oEvent)
	{
		// handle form with the event data is our object (from database)
		$this->handleForm($oEvent->getForm(), $oEvent, $oEvent->getData());
	}

	/**
	 * Previous preSetData handling doesn't know about form submission data.
	 * So, to allow us to use our DBObject for filters we need to update it before.
	 *
	 * @param FormEvent $oEvent form event
	 *
	 * @suppress-unused-warning
	 */
	public function preSubmit(FormEvent $oEvent)
	{
		// modify object with posted data
		if($oEvent->getForm()->getData() !== null){
			// the event data is an array of request data
			$this->handlePostedData($oEvent->getForm(), $oEvent->getForm()->getData(), $oEvent->getData());
		}

		// handle form with the updated object data
		$this->handleForm($oEvent->getForm(), $oEvent, $oEvent->getForm()->getData());
	}

	/**
	 * Handle form.
	 *
	 * We need to reset fields to take into account object values.
	 * The form fields options can't be changed at this time.
	 * All fields are reset to keep fields order.
	 *
	 * @param FormInterface $oForm
	 * @param FormEvent $oEvent
	 * @param DBObject|null $oDBObject
	 *
	 * @return void
	 *
	 */
	private function handleForm(FormInterface $oForm, FormEvent $oEvent, ?DBObject $oDBObject)
	{
		// retrieve all children
		$aChildren = $oForm->all();

		// remove all non container child... (we create new on with new options)
		foreach ($aChildren as $sName => $oChild) {

			// retrieve inner type
			$oInnerType = $oChild->getConfig()->getType()->getInnerType();

			// do not process container types
			if ($oDBObject != null && !($oInnerType instanceof AbstractContainerType)) {

				// remove previous field
				$oForm->remove($sName);

				// get options provided by consumer
				$aOptions = $this->HandleField($sName, $oInnerType, $oChild->getConfig()->getOptions(), $oDBObject);
				if ($aOptions == null) {
					continue;
				}

				// create new field
				$oForm->add($sName,	get_class($oInnerType),	$aOptions);
			}

			// deep processing
			$this->handleForm($oChild, $oEvent, $oDBObject);
		}
	}

	/**
	 * Handle posted data.
	 * The data are the view data. We skipped data transformation !!
	 * @todo handle data transformation
	 *
	 * @param FormInterface $form
	 * @param DBObject $oDBObject
	 * @param array $aPostedData
	 *
	 * @return void
	 */
	private function handlePostedData(FormInterface $form, DBObject $oDBObject, array $aPostedData)
	{

		// iterate throw posted data...
		foreach ($aPostedData as $key => $value) {

			if(!$form->has($key)){
				continue;
			}

			// retrieve form type
			$oFormField = $form->get($key);

			if (is_array($value)) {

				// deep processing ^
				$this->handlePostedData($oFormField, $oDBObject, $value);

			} else {

				// retrieve form type
				$oFormType = $oFormField->getConfig()->getType()->getInnerType();

				// do not process container type
				if ($oFormType instanceof AbstractContainerType || $oFormType instanceof SubmitType) {
					continue;
				}

				// update DBObject
				try{
					$oDBObject->Set((string)$oFormField->getPropertyPath(), $value);
				}
				catch(Exception $e){

					IssueLog::Error('ObjectFormListener::handlePostedData error');
				}
			}
		}
	}

	/**
	 * Handle field.
	 *
	 * @param string $sName
	 * @param FormTypeInterface $oFormType
	 * @param array $aOptions
	 * @param DBObject|null $oDBObject
	 *
	 * @return array|null
	 */
	private function handleField(string $sName, FormTypeInterface $oFormType, array $aOptions, ?DBObject $oDBObject) : ?array
	{
		// return default options
		if($oDBObject === null){
			return $aOptions;
		}

		// if form type implements iDataObserver, we request new options
		if ($oFormType instanceof IFormTypeOptionModifier) {
			$aOptions = $oFormType->getNewOptions($aOptions, $oDBObject);
		}

		// attribute flag
		$iFlags = FormHelper::GetAttributeFlagsForObject($oDBObject, $sName);
		if ($iFlags & (OPT_ATT_READONLY | OPT_ATT_SLAVE)) {
			$aOptions['disabled'] = true;
		}

		// ignore fields hidden
		if ($iFlags & (OPT_ATT_HIDDEN)) {
			return null;
		}

		return $aOptions;
	}

}