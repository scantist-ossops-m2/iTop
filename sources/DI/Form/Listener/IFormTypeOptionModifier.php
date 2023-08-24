<?php

namespace Combodo\iTop\DI\Form\Listener;

use DBObject;

/**
 * Used when FormType options depends on underlying data.
 * Ex: ExternalKeyType needs the object data to provide options
 */
interface IFormTypeOptionModifier
{
	/**
	 * Return new form type options for the provided DB object.
	 *
	 * @param array $aInitialOptions initial form type options
	 * @param DBObject $oObject iTop DB object
	 *
	 * @return array new form type options
	 */
	public function getNewOptions(array $aInitialOptions, DBObject $oObject) : array;
}