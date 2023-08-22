<?php

namespace Combodo\iTop\DI\Form\Type\Simple;

use Combodo\iTop\DI\Form\Listener\IFormTypeOptionModifier;
use Combodo\iTop\DI\Services\ObjectService;
use Exception;
use DBObject;
use MetaModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExternalKeyType extends AbstractType implements IFormTypeOptionModifier
{
	/** @var \Combodo\iTop\DI\Services\ObjectService  */
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

	/** @inheritdoc  */
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'display_style' => 'list',
			'allow_target_creation' => false,
			'object_class' => null,
			'att_code' => null,
		]);

		$resolver->setAllowedTypes('display_style', 'string');
		$resolver->setAllowedValues('display_style', ['radio', 'radio_horizontal', 'radio_vertical', 'select', 'list']);
	}

	/** @inheritdoc  */
	public function buildView(FormView $view, FormInterface $form, array $options): void
	{
		$view->vars['allow_target_creation'] = $options['allow_target_creation'];
	}

	/** @inheritdoc  */
	public function getParent(): string
	{
		return ChoiceType::class;
	}

	/** @inheritdoc  */
	public function getNewOptions(array $aInitialOptions, DBObject $oObject) : array
	{
		try{
			$oObjectsSet = MetaModel::GetAllowedValuesAsObjectSet(get_class($oObject), $aInitialOptions['att_code'], ['this' => $oObject]);
			$aInitialOptions['choices'] = $this->oObjectService->ToChoices($oObjectsSet);
		}
		catch(Exception $e){

		}

		return $aInitialOptions;
	}
}
