<?php

namespace Combodo\iTop\DI\Services;

use AttributeLinkedSet;
use Combodo\iTop\Core\MetaModel\FriendlyNameType;
use Combodo\iTop\Service\Base\ObjectRepository;
use DBObject;
use DBObjectSet;
use MetaModel;
use ormLinkSet;
use Symfony\Component\Stopwatch\Stopwatch;

class ObjectService
{
	/** @var string database host */
	private string $sDbHost;

	/** @var string database name */
	private string $sDbName;

	/** @var \Symfony\Component\Stopwatch\Stopwatch  */
	private Stopwatch $oStopWatch;

	/**
	 * Constructor.
	 *
	 * @param $sDbHost
	 * @param $sDbName
	 */
	public function __construct($sDbHost, $sDbName, Stopwatch $oStopWatch)
	{
		$this->sDbHost = $sDbHost;
		$this->sDbName = $sDbName;
		$this->oStopWatch = $oStopWatch;
	}

	/**
	 * @param string $sClass
	 * @param $sRef
	 *
	 * @return DBObject
	 * @throws \ArchivedObjectException
	 * @throws \CoreException
	 */
	public function getObject(string $sClass, $sRef) : DBObject
	{
		if($sRef !== 0){
			return MetaModel::GetObject($sClass, $sRef);
		}
		else{
			return MetaModel::NewObject($sClass);
		}
	}

	/**
	 * Convert object set to array of choices.
	 *
	 * @param DBObjectSet $oObjectsSet
	 *
	 * @return array
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 * @throws \DictExceptionMissingString
	 * @throws \MySQLException
	 */
	public function ToChoices(DBObjectSet $oObjectsSet) : array
	{
		$this->oStopWatch->start('ToChoices');

		$aChoices = [];

		// Retrieve friendly name complementary specification
		$aComplementAttributeSpec = MetaModel::GetNameSpec($oObjectsSet->GetClass(), FriendlyNameType::COMPLEMENTARY);

		// Retrieve image attribute code
		$sObjectImageAttCode = MetaModel::GetImageAttributeCode($oObjectsSet->GetClass());

		// Prepare fields to load
		$aDefaultFieldsToLoad = ObjectRepository::GetDefaultFieldsToLoad($aComplementAttributeSpec, $sObjectImageAttCode);

		$oObjectsSet->OptimizeColumnLoad([$oObjectsSet->GetClassAlias() => $aDefaultFieldsToLoad]);

		$i = 0;
		while ($i < 10 && $oObj = $oObjectsSet->Fetch()) {
			$aChoices[$oObj->GetName()] = $oObj->GetKey();
			$i++;
		}

		$this->oStopWatch->stop('ToChoices');

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

	/**
	 * @param \DBObject $object
	 *
	 * @return void
	 * @throws \ArchivedObjectException
	 * @throws \CoreCannotSaveObjectException
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 * @throws \CoreWarning
	 * @throws \DeleteException
	 * @throws \MySQLException
	 * @throws \MySQLHasGoneAwayException
	 * @throws \OQLException
	 * @throws \Exception
	 */
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


	public function listConcreteChildClasses(string $sObjectClass)
	{
		$aChildClasses = MetaModel::EnumChildClasses($sObjectClass);
		return array_filter($aChildClasses, function ($sChildClass){
			return !MetaModel::IsAbstract($sChildClass);
		});
	}
}