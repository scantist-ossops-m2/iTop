<?php

namespace Combodo\iTop\DI\Form\Builder;

use AttributeBoolean;
use AttributeDate;
use AttributeDateTime;
use AttributeEmailAddress;
use AttributeEnum;
use AttributeExternalField;
use AttributeExternalKey;
use AttributeImage;
use AttributeLinkedSet;
use AttributePassword;
use AttributePhoneNumber;
use AttributeText;
use Combodo\iTop\DI\Form\Type\Attribute\DocumentType;
use Combodo\iTop\DI\Form\Type\Attribute\ExternalFieldType;
use Combodo\iTop\DI\Form\Type\Attribute\ExternalKeyType;
use Combodo\iTop\DI\Form\Type\Attribute\LinkSetType;
use Combodo\iTop\DI\Services\ObjectService;
use Combodo\iTop\Service\Links\LinkSetModel;
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

class AttributeBuilder
{
	/** @var \Combodo\iTop\DI\Services\ObjectService object service */
	private ObjectService $oObjectService;

	/**
	 * Constructor.
	 *
	 * @param \Combodo\iTop\DI\Services\ObjectService $oObjectService
	 */
	public function __construct(ObjectService $oObjectService)
	{
		$this->oObjectService = $oObjectService;
	}

	/**
	 * Map AttributeDefinition >> FormType
	 *
	 * @param string $sObjectClass
	 * @param string $sCode
	 * @param array|null $lockedAttributes
	 *
	 * @return array|null
	 * @throws \CoreException
	 */
	public function createAttribute(string $sObjectClass, string $sCode, ?array $lockedAttributes) : ?array
	{
		// load attribute definition
		try {
			$oAttributeDefinition = MetaModel::GetAttributeDef($sObjectClass, $sCode);
			$sLabel = $oAttributeDefinition->GetLabel();
		}
		catch(Exception $e){
			return null;
		}

		// locked state
		$bIsLocked = $lockedAttributes !== null && array_key_exists($sCode, $lockedAttributes);

		// create global form type (default as text)
		$aFormType = [
			'type' => TextType::class,
			'label' => $sLabel,
			'options' => [
				'required' => !$oAttributeDefinition->IsNullAllowed(),
				'disabled' => !$oAttributeDefinition->IsWritable() || $bIsLocked,
				'attr' => [
					'data-att-code' => $sCode,
				],
				'row_attr' => [
					'data-block' => 'attribute_container',
				],
				'label_attr' => [
					'class' => $bIsLocked ? 'locked' : ''
				]
			],
		];

		// register dependencies
		if(count($oAttributeDefinition->GetPrerequisiteAttributes()) > 0){
			$dependencies = implode(' ', $oAttributeDefinition->GetPrerequisiteAttributes());
			$aFormType['options']['attr']['data-depends-on'] = $dependencies;
			$aFormType['options']['label_attr']['data-bs-toggle'] = 'tooltip';
			$aFormType['options']['label_attr']['data-bs-title'] = '<b>Depends on</b> ' . $dependencies;
			$aFormType['options']['label_attr']['data-bs-html'] = 'true';
			$aFormType['options']['label_attr']['class'] .= ' dependent';
			$aFormType['depends_on'] = $dependencies;
		}

		// inject corresponding configuration
		if($oAttributeDefinition instanceof AttributeExternalKey){
			$aFormType['type'] = ExternalKeyType::class;
			$aFormType['options']['allow_target_creation'] = $oAttributeDefinition->AllowTargetCreation();
			$aFormType['options']['object_class'] =  $oAttributeDefinition->GetTargetClass();
			$aFormType['options']['att_code'] =  $oAttributeDefinition->GetCode();
			$aFormType['options']['is_locked'] = $bIsLocked;
			try{
				$oObjectsSet = MetaModel::GetAllowedValuesAsObjectSet($oAttributeDefinition->GetHostClass(), $oAttributeDefinition->GetCode(), []);
				$aFormType['options']['choices'] = $this->oObjectService->ToChoices($oObjectsSet);
			}
			catch(Exception $e){

			}
		}
		else if($oAttributeDefinition instanceof AttributeExternalField){
			$aFormType['type'] = ExternalFieldType::class;
			$bIsExternalKey = $oAttributeDefinition->IsExternalKey(EXTKEY_ABSOLUTE);
			if($bIsExternalKey){
				$aFormType['options']['is_external_key'] = true;
				$aFormType['options']['object_class'] = $oAttributeDefinition->GetExtAttDef()->GetTargetClass();
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
			$aFormType['options']['is_indirect'] = $oAttributeDefinition->IsIndirect();
			$aFormType['options']['is_abstract'] = MetaModel::IsAbstract(LinkSetModel::GetTargetClass($oAttributeDefinition));
			$aFormType['options']['target_class'] = LinkSetModel::GetTargetClass($oAttributeDefinition);
			$aFormType['options']['row_attr']['data-object-class'] = $oAttributeDefinition->GetLinkedClass();
			if($aFormType['options']['is_abstract']){
				$aFormType['options']['object_classes'] = $this->oObjectService->listConcreteChildClasses(LinkSetModel::GetTargetClass($oAttributeDefinition));
			}
			$aFormType['options']['entry_options'] = [
				'object_class' => $oAttributeDefinition->GetLinkedClass(),
				'data_class' => $oAttributeDefinition->GetLinkedClass(),
				'is_link_set' => true,
				'ext_key_to_me' => $oAttributeDefinition->GetExtKeyToMe(),
				'z_list' => 'list',
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

		return $aFormType;
	}
}