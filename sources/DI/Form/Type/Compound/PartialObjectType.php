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
class PartialObjectType extends AbstractType
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
			'att_codes' => null
		]);
	}

	/** @inheritdoc
	 * @throws \CoreException
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		// add requested attributes form types...
		foreach ($options['att_codes'] as $sAttCode){

			// create attribute type
			$sFormType = $this->oAttributeBuilder->createAttribute($options['object_class'], $sAttCode, null);

			// build form type
			$builder->add($sAttCode, $sFormType['type'], $sFormType['options']);
		}

		// dynamic form handling
		$builder->addEventSubscriber($this->oObjectFormModifier);
	}

}
