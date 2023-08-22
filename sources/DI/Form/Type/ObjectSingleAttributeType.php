<?php

namespace Combodo\iTop\DI\Form\Type;

use Combodo\iTop\DI\Form\Listener\ObjectFormListener;
use Combodo\iTop\DI\Services\ObjectPresentationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Used to handle depends on fields.
 *
 */
class ObjectSingleAttributeType extends AbstractType
{
	/** @var ObjectFormListener object form modifier */
	private ObjectFormListener $oObjectFormModifier;

	/** @var ObjectPresentationService object presentation service */
	private ObjectPresentationService $objectPresentationService;

	/**
	 * Constructor.
	 *
	 * @param ObjectFormListener $oObjectFormModifier
	 * @param ObjectPresentationService $objectPresentationService
	 */
	public function __construct(ObjectFormListener $oObjectFormModifier, ObjectPresentationService $objectPresentationService)
	{
		$this->oObjectFormModifier = $oObjectFormModifier;
		$this->objectPresentationService = $objectPresentationService;
	}

	/** @inheritdoc  */
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults( [
			'object_class' => null,
			'att_code' => null
		]);
	}

	/** @inheritdoc  */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		// build form from options
		$sFormType = $this->objectPresentationService->GetAttributeFormType($options['object_class'], $options['att_code']);

		// add form field
		$builder->add($options['att_code'], $sFormType['type'], $sFormType['options']);

		// dynamic form handling
		$builder->addEventSubscriber($this->oObjectFormModifier);
	}

}
