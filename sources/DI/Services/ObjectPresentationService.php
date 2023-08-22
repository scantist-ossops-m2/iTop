<?php

namespace Combodo\iTop\DI\Services;

use AttributeBoolean;
use AttributeDate;
use AttributeDateTime;
use AttributeEmailAddress;
use AttributeEnum;
use AttributeExternalKey;
use AttributeImage;
use AttributeLinkedSet;
use AttributePassword;
use AttributePhoneNumber;
use AttributeText;
use Combodo\iTop\DI\Form\Type\Layout\ColumnType;
use Combodo\iTop\DI\Form\Type\Simple\ExternalKeyType;
use Combodo\iTop\DI\Form\Type\Layout\FieldSetType;
use Combodo\iTop\DI\Form\Type\Layout\RowType;
use Combodo\iTop\DI\Form\Type\Simple\DocumentType;
use Combodo\iTop\DI\Form\Type\Simple\LinkSetType;
use Dict;
use Exception;
use MetaModel;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Data model presentation conversion.
 *
 */
class ObjectPresentationService
{


	/**
	 * buildFormFromPresentation.
	 *
	 * @param $class
	 * @param $zList
	 * @param $isLinkSet
	 * @param $sExtKeyToMe
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 *
	 * @return void
	 */
	public function buildFormFromPresentation($class, $zList, $isLinkSet, $sExtKeyToMe, FormBuilderInterface $builder)
	{
		// retrieve presentation
		$aPresentation = MetaModel::GetZListItems($class, $zList);

		if($isLinkSet){
			$aPresentation = $this->filterLinkSetpresentation($aPresentation, $class, $sExtKeyToMe);
		}

		$level = $this->handleLevel($aPresentation, $class);

		foreach ($level as $key => $value) {
			$value['options']['attr']['data-att-code'] = $key;
			$builder->add($key, $value['type'], $value['options']);
		}
	}

	/**
	 * filterLinkSetpresentation.
	 *
	 * @param $aPresentation
	 * @param $class
	 * @param $sExtKeyToMe
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function filterLinkSetpresentation($aPresentation, $class, $sExtKeyToMe){

		$aNewPresentation = [];
		foreach($aPresentation as $sLinkedAttCode)
		{
			if ($sLinkedAttCode != $sExtKeyToMe)
			{
				$oAttDef = MetaModel::GetAttributeDef($class, $sLinkedAttCode);

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


	public function getFormPresentationLabels($class, $zList, $sExtKeyToMe)
	{
		// retrieve presentation
		$aPresentation = MetaModel::GetZListItems($class, $zList);
		$aPresentation = $this->filterLinkSetpresentation($aPresentation, $class, $sExtKeyToMe);

		$level = $this->handleLevel($aPresentation, $class);
		$labels = [];
		foreach ($level as $key => $value) {
			$value['options']['attr']['data-att-code'] = $key;
			$labels[] = $key;
		}
		return $labels;
	}


	/**
	 * handleLevel.
	 *
	 * @param array $aElement
	 * @param $dataClass
	 *
	 * @return array
	 */
	private function handleLevel(array $aElement, $dataClass) : array
	{
		$aChildren = [];
		$aRowCol = [];

		// iterate trow level entries...
		foreach ($aElement as $key => $item){

			// column
			if(str_starts_with($key, 'col')){
				$aRowCol[$key] = $this->createColumn($key, $item, $dataClass);
				$this->handleDynamics($key, $aRowCol[$key]);
			}
			// field set
			else if(str_starts_with($key, 'fieldset')){
				$aChildren[$key] =$this->createFieldSet($key, $item, $dataClass);
				$this->handleDynamics($key, $aChildren[$key]);
			}
			// logs
			else if(in_array($item, ['log', 'public_log', 'private_log'])){
				continue;
			}
			// others
			else{
				$sFormType = $this->GetAttributeFormType($dataClass, $item);
				if($sFormType !== null){
					$sFormType['options']['attr']['data-att-code'] = $item;
					$aChildren[$item] = $sFormType;
					$this->handleDynamics($item, $aChildren[$item]);
				}
			}
		}

		if(count($aRowCol)){
			$aChildren['row'] = $this->createRow('row', $aRowCol, $dataClass);
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

	/**
	 * createRow.
	 *
	 * @param $key
	 * @param $columns
	 * @param $dataClass
	 *
	 * @return array
	 */
	private function createRow($key, $columns, $dataClass) : array
	{
		return [
			'type' => RowType::class,
			'options' => [
				'items' => $columns,
				'label' => false,
				'row_attr' => [
					'data-block' => 'container'
				]
			]
		];
	}

	/**
	 * createColumn.
	 *
	 * @param $key
	 * @param $item
	 * @param $dataClass
	 *
	 * @return array
	 */
	private function createColumn($key, $item, $dataClass) : array
	{
		return [
			'type' => ColumnType::class,
			'options' => [
				'items' => $this->handleLevel($item, $dataClass),
				'label' => false,
				'row_attr' => [
					'data-block' => 'container'
				]
			]
		];
	}

	/**
	 * createFieldSet.
	 *
	 * @param $key
	 * @param $item
	 * @param $dataClass
	 *
	 * @return array
	 */
	private function createFieldSet($key, $item, $dataClass) : array
	{
		return [
			'type' => FieldSetType::class,
			'options' => [
				'items' => $this->handleLevel($item, $dataClass),
				'label' => Dict::S(substr($key, 9)),
				'row_attr' => [
					'data-block' => 'container'
				]
			]
		];
	}

	/**
	 * Map AttributeDefinition >> FormType
	 *
	 * @param string $sObjectClass
	 * @param string $sCode
	 *
	 * @return array|null
	 */
	public function GetAttributeFormType(string $sObjectClass, string $sCode) : ?array
	{
		// load attribute definition
		try {
			$oAttributeDefinition = MetaModel::GetAttributeDef($sObjectClass, $sCode);
			$sLabel = $oAttributeDefinition->GetLabel();
		}
		catch(Exception $e){
			return null;
		}

		// create global form type
		$aFormType = [
			'type' => TextType::class,
			'label' => $sLabel,
			'options' => [
				'required' => !$oAttributeDefinition->IsNullAllowed(),
				'disabled' => !$oAttributeDefinition->IsWritable(),
			]
		];

		// inject corresponding configuration
		if($oAttributeDefinition instanceof AttributeExternalKey){
			$aFormType['type'] = ExternalKeyType::class;
			$aFormType['options']['display_style'] =  'list';
			$aFormType['options']['allow_target_creation'] =  true;
			$aFormType['options']['object_class'] =  $oAttributeDefinition->GetTargetClass();
			$aFormType['options']['att_code'] =  $oAttributeDefinition->GetCode();

			try{
				$oObjectsSet = MetaModel::GetAllowedValuesAsObjectSet($oAttributeDefinition->GetHostClass(), $oAttributeDefinition->GetCode(), []);
				$aFormType['options']['choices'] = $this->ToChoices($oObjectsSet);
			}
			catch(Exception $e){

			}

		}
		else if($oAttributeDefinition instanceof AttributeEmailAddress){
			$aFormType['type'] = EmailType::class;
		}
		else if($oAttributeDefinition instanceof AttributePassword){
			$aFormType['type'] = PasswordType::class;
		}
		else if($oAttributeDefinition instanceof AttributePhoneNumber){
			$aFormType['type'] = TelType::class;
			$aFormType['options']['attr'] =  [
				'pattern' => '\+[0-9]{2}\s[0-9]\s[0-9]{2}\s[0-9]{2}\s[0-9]{2}\s[0-9]{2}',
				'placeholder' => '+-- - -- -- --'
			];
		}
		else if($oAttributeDefinition instanceof AttributeBoolean){
			$aFormType['type'] = CheckboxType::class;
		}
		else if($oAttributeDefinition instanceof AttributeEnum){
			$aFormType['type'] = ChoiceType::class;
			try{
				$aOptions = array_flip($oAttributeDefinition->GetAllowedValues());
			}
			catch(Exception $e){
				$aOptions = [];
			}
			$aFormType['options']['choices'] = $aOptions;
		}
		else if($oAttributeDefinition instanceof AttributeImage){
			$aFormType['type'] = DocumentType::class;
		}
		else if($oAttributeDefinition instanceof AttributeLinkedSet){
			$aFormType['type'] = LinkSetType::class;
			$aFormType['options']['entry_options'] = [
				'object_class' => $oAttributeDefinition->GetLinkedClass(),
				'data_class' => $oAttributeDefinition->GetLinkedClass(),
				'is_link_set' => true,
				'ext_key_to_me' => $oAttributeDefinition->GetExtKeyToMe(),
				'z_list' => 'list',
				'attr' => [
					'class' => 'z_list_list'
				]
			];
			$aFormType['options']['attr'] = [
				'class' => 'link_set'
			];
			$aFormType['options']['label_attr'] = [
				'class' => 'combodo-field-set-label'
			];
		}
		else if($oAttributeDefinition instanceof AttributeText){
			$aFormType['type'] = TextareaType::class;
			$aFormType['options']['attr']['rows'] =  10;
			$aFormType['options']['attr']['data-widget'] = 'text_widget';
		}
		else if($oAttributeDefinition instanceof AttributeDate){
			$aFormType['type'] = DateType::class;
			$aFormType['options']['input'] = 'string';
		}
		else if($oAttributeDefinition instanceof AttributeDateTime){
			$aFormType['type'] = DateTimeType::class;
			$aFormType['options']['input'] = 'string';
			$aFormType['options']['widget'] = 'single_text';
			$aFormType['options']['with_seconds'] = true;
		}

		if(count($oAttributeDefinition->GetPrerequisiteAttributes()) > 0){
			$dependencies = implode(' ', $oAttributeDefinition->GetPrerequisiteAttributes());
			$aFormType['options']['attr']['data-depends-on'] = $dependencies;
			$aFormType['depends_on'] = $dependencies;
		}

		$aFormType['options']['row_attr']['data-block'] = 'container';

		return $aFormType;
	}

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
}