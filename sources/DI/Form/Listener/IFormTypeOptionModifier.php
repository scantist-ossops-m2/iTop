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
	 * @param array $aInitialOptions form type option
	 * @param DBObject $oObject iTop DB object
	 *
	 * @return array
	 */
	public function getNewOptions(array $aInitialOptions, DBObject $oObject) : array;
}