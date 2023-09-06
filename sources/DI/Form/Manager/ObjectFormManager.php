<?php

namespace Combodo\iTop\DI\Form\Manager;

use DBObject;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class ObjectFormManager
{
	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \DBObject $oDbObject
	 * @param string $sFormName
	 *
	 * @return void
	 */
	public function applyRequestLockedAttributesToObject(Request $request, DBObject $oDbObject, string $sFormName){

		try{
			$aValue = $request->get($sFormName);
			$sLockedAttributes = $aValue['locked_attributes'];
			$aLockedAttributes = json_decode($sLockedAttributes);
			foreach($aLockedAttributes as $sKey => $sValue){
				$oDbObject->Set($sKey, $sValue);
			}
		}
		catch(Exception $e){

		}

	}
}