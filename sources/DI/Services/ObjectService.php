<?php

namespace Combodo\iTop\DI\Services;

use AttributeLinkedSet;
use DBObject;
use MetaModel;
use ormLinkSet;

class ObjectService
{
	/** @var string database host */
	private string $sDbHost;

	/** @var string database name */
	private string $sDbName;

	/**
	 * Constructor.
	 *
	 * @param $sDbHost
	 * @param $sDbName
	 */
	public function __construct($sDbHost, $sDbName)
	{
		$this->sDbHost = $sDbHost;
		$this->sDbName = $sDbName;
	}

	/**
	 * Convert object set to array of choices.
	 *
	 * @param $oObjectsSet
	 *
	 * @return array
	 */
	public function ToChoices($oObjectsSet) : array
	{
		$aChoices = [];

		$i = 0;

		while ($i < 100 && $oObj = $oObjectsSet->Fetch()) {
			$aChoices[$oObj->GetName()] = $oObj->GetKey();
			$i++;
		}

		return $aChoices;
	}

	/**
	 * @return string
	 */
	public function getDbHost(): string
	{
		return $this->sDbHost;
	}

	/**
	 * @return string
	 */
	public function getDbName(): string
	{
		return $this->sDbName;
	}

	public function handleLinkSetDB(DBObject $object){
		foreach($object->GetValues() as $key => $value){
			if($value instanceof ormLinkSet){

					/** @var AttributeLinkedSet $a */
				 $a = MetaModel::GetAttributeDef(get_class($object), $key);

				/** @var DBObject $link */
				foreach($value->ListModifiedLinks() as $link){
					if($value->IsDeleted($link)){
						$link->DBDelete();
					}
				}


				/** @var DBObject $link */
				foreach($value->ListModifiedLinks() as $link){
					if($link->IsNew()){
						$link->Set($a->GetExtKeyToMe(), $object);
						$link->DBInsert();
					}
					else{
						$link->DBUpdate();
					}


				}
			}
		}
	}
}