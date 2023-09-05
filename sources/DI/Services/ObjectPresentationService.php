<?php

namespace Combodo\iTop\DI\Services;

use Combodo\iTop\DI\Form\Builder\AttributeBuilder;
use Combodo\iTop\DI\Form\Builder\LayoutBuilder;
use Dict;
use MetaModel;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Data model presentation conversion.
 *
 */
class ObjectPresentationService
{
	/** @var \Combodo\iTop\DI\Form\Builder\LayoutBuilder $oLayoutBuilder */
	private LayoutBuilder $oLayoutBuilder;

	/** @var \Combodo\iTop\DI\Form\Builder\AttributeBuilder $oAttributeBuilder */
	private AttributeBuilder $oAttributeBuilder;

	/**
	 * @param \Combodo\iTop\DI\Form\Builder\LayoutBuilder $oLayoutBuilder
	 * @param \Combodo\iTop\DI\Form\Builder\AttributeBuilder $oAttributeBuilder
	 */
	public function __construct(LayoutBuilder $oLayoutBuilder, AttributeBuilder $oAttributeBuilder)
	{
		$this->oLayoutBuilder = $oLayoutBuilder;
		$this->oAttributeBuilder = $oAttributeBuilder;
	}

	/**
	 * buildFormFromPresentation.
	 *
	 * @param string $class
	 * @param string $sZList
	 * @param bool $isLinkSet
	 * @param string|null $sExtKeyToMe
	 * @param array|null $lockedAttributes
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function buildFormFromPresentation(string $class, string $sZList, bool $isLinkSet, ?string $sExtKeyToMe, ?array $lockedAttributes, FormBuilderInterface $builder)
	{
		// retrieve presentation
		$aPresentation = MetaModel::GetZListItems($class, $sZList);

		// filter zList for links set
		if($isLinkSet){
			$aPresentation = $this->filterLinkSetPresentation($aPresentation, $class, $sExtKeyToMe);
		}

		// handle level
		$level = $this->handleLevel($aPresentation, $class, $lockedAttributes);
		foreach ($level as $key => $value) {
			$builder->add($key, $value['type'], $value['options']);
		}
	}

	/**
	 * filterLinkSetPresentation.
	 *
	 * @param array $aPresentation
	 * @param string $sClass
	 * @param string|null $sExtKeyToMe
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function filterLinkSetPresentation(array $aPresentation, string $sClass, ?string $sExtKeyToMe) : array
	{

		$aNewPresentation = [];
		foreach($aPresentation as $sLinkedAttCode)
		{
			if ($sLinkedAttCode != $sExtKeyToMe)
			{
				$oAttDef = MetaModel::GetAttributeDef($sClass, $sLinkedAttCode);

				if ((!$oAttDef->IsExternalField() || ($oAttDef->GetKeyAttCode() != $sExtKeyToMe)) &&
					(!$oAttDef->IsLinkSet()) )
				{
					$aNewPresentation[] = $sLinkedAttCode;
				}
			}
		}

		array_unshift($aNewPresentation, 'friendlyname');

		return $aNewPresentation;
	}

	/**
	 * @param $class
	 * @param $zList
	 * @param $sExtKeyToMe
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getFormPresentationLabels($class, $zList, $sExtKeyToMe) : array
	{
		// retrieve presentation
		$aPresentation = MetaModel::GetZListItems($class, $zList);
		$aPresentation = $this->filterLinkSetpresentation($aPresentation, $class, $sExtKeyToMe);

		$level = $this->handleLevel($aPresentation, $class, null);
		$aLabels = [];
		foreach ($level as $key => $value) {
			$value['options']['attr']['data-att-code'] = $key;

			$sKey = "Class:$class/Attribute:$key";
			if(Dict::Exists($sKey)){
				$aLabels[] = Dict::S($sKey);
			}
			else{
				$aLabels[] = $key;
			}
		}
		return $aLabels;
	}


	/**
	 * handleLevel.
	 *
	 * @param array $aElement
	 * @param string $sDataClass
	 * @param array|null $lockedAttributes
	 *
	 * @return array
	 * @throws \CoreException
	 */
	private function handleLevel(array $aElement, string $sDataClass, ?array $lockedAttributes) : array
	{
		$aChildren = [];
		$aRowCols = [];

		// iterate trow level entries...
		foreach ($aElement as $key => $item){

			// column
			if(str_starts_with($key, 'col')){
				$aItems = $this->handleLevel($item, $sDataClass, $lockedAttributes);
				$aRowCols[$key] = $this->oLayoutBuilder->createColumn($key, $aItems);
				$this->handleDynamics($key, $aRowCols[$key]);
			}
			// field set
			else if(str_starts_with($key, 'fieldset')){
				$aItems = $this->handleLevel($item, $sDataClass, $lockedAttributes);
				$aChildren[$key] = $this->oLayoutBuilder->createFieldSet($key, $aItems);
				$this->handleDynamics($key, $aChildren[$key]);
			}
			// logs
			else if(in_array($item, ['log', 'public_log', 'private_log'])){
				continue;
			}
			// others
			else{
				$sFormType = $this->oAttributeBuilder->createAttribute($sDataClass, $item, $lockedAttributes);
				if($sFormType !== null){
					$sFormType['options']['attr']['data-att-code'] = $item;
					$aChildren[$item] = $sFormType;
					$this->handleDynamics($item, $aChildren[$item]);
				}
			}
		}

		if(count($aRowCols)){
			$aChildren['row'] = $this->oLayoutBuilder->createRow('row', $aRowCols);
		}

		return $aChildren;
	}

	/**
	 * Handle dynamics.
	 *
	 * @param $key
	 * @param $item
	 *
	 * @return void
	 */
	private function handleDynamics($key, &$item)
	{
		if($key === 'employee_number'){
			$hideWhen = [
				'att_code' => "status",
				'value' => "inactive"
			];
			$item['options']['attr']['data-hide-when'] = json_encode($hideWhen);
		}

		if($key === 'fieldset:Person:notifiy'){
			$hideWhen = [
				'att_code' => "status",
				'value' => "inactive"
			];
			$item['options']['attr']['data-disable-when'] = json_encode($hideWhen);
		}

		if($key === 'fieldset:Ticket:contact'){
			$hideWhen = [
				'att_code' => "urgency",
				'value' => "1"
			];
			$item['options']['attr']['data-disable-when'] = json_encode($hideWhen);
		}
	}

}