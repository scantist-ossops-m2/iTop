<?php

namespace Combodo\iTop\DI\Form\Type\Compound;

use Combodo\iTop\DI\Form\Builder\AttributeBuilder;
use Combodo\iTop\DI\Form\Listener\ObjectFormListener;
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

	/** @var AttributeBuilder attribute builder */
	private AttributeBuilder $oAttributeBuilder;

	/**
	 * Constructor.
	 *
	 * @param ObjectFormListener $oObjectFormModifier
	 * @param AttributeBuilder $oAttributeBuilder
	 */
	public function __construct(ObjectFormListener $oObjectFormModifier, AttributeBuilder $oAttributeBuilder)
	{
		$this->oObjectFormModifier = $oObjectFormModifier;
		$this->oAttributeBuilder = $oAttributeBuilder;
	}

	/** @inheritdoc  */
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults( [
			'object_class' => null,
			'att_code' => null
		]);
	}

	/** @inheritdoc
	 * @throws \CoreException
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		// build form from options
		$sFormType = $this->oAttributeBuilder->createAttribute($options['object_class'], $options['att_code']);

		// add form field
		$builder->add($options['att_code'], $sFormType['type'], $sFormType['options']);

		// dynamic form handling
		$builder->addEventSubscriber($this->oObjectFormModifier);
	}

}
