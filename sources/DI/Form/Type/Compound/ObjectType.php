<?php

namespace Combodo\iTop\DI\Form\Type\Compound;

use cmdbAbstractObject;
use Combodo\iTop\DI\Form\Builder\AttributeBuilder;
use Combodo\iTop\DI\Form\Builder\LayoutBuilder;
use Combodo\iTop\DI\Form\Listener\ObjectFormListener;
use Combodo\iTop\DI\Services\ObjectPresentationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Convert iTop data model presentation into a symfony form.
 *
 * @see AbstractContainerType to compose colemn and fieldsets, organization
 *
 */
class ObjectType extends AbstractType
{
	/** @var ObjectFormListener object service */
	private ObjectFormListener $oObjectFormModifier;

	/** @var ObjectPresentationService object presentation service */
	private ObjectPresentationService $objectPresentationService;

	/** @var \Combodo\iTop\DI\Form\Builder\AttributeBuilder attribute builder */
	private AttributeBuilder $oAttributeBuilder;

	/** @var \Combodo\iTop\DI\Form\Builder\LayoutBuilder layout builder */
	private LayoutBuilder $oLayoutBuilder;

	/**
	 * Constructor.
	 *
	 * @param ObjectFormListener $oObjectFormModifier
	 * @param ObjectPresentationService $objectPresentationService
	 * @param \Combodo\iTop\DI\Form\Builder\AttributeBuilder $oAttributeBuilder
	 * @param \Combodo\iTop\DI\Form\Builder\LayoutBuilder $oLayoutBuilder
	 */
	public function __construct(ObjectFormListener $oObjectFormModifier, ObjectPresentationService $objectPresentationService, AttributeBuilder $oAttributeBuilder, LayoutBuilder $oLayoutBuilder)
	{
		$this->oObjectFormModifier = $oObjectFormModifier;
		$this->objectPresentationService = $objectPresentationService;
		$this->oAttributeBuilder = $oAttributeBuilder;
		$this->oLayoutBuilder = $oLayoutBuilder;
	}

	/** @inheritdoc  */
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'z_list' => 'details',
			'is_link_set' => false,
			'ext_key_to_me' => null,
			'attr' => [
				'class' =>  'z_list_details'
			],
			'object_class' => null,
			'locked_attributes' => null,
			'data_class' => cmdbAbstractObject::class,
		]);

	}

	/** @inheritdoc  */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		// build form from presentation
		$this->objectPresentationService->buildFormFromPresentation(
			$options['object_class'],
			$options['z_list'],
			$options['is_link_set'],
			$options['ext_key_to_me'],
			$options['locked_attributes'],
			$builder);

		// dynamic form handling
		$builder->addEventSubscriber($this->oObjectFormModifier);
	}

	/** @inheritdoc  */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		parent::buildView($view, $form, $options);

		$view->vars['z_list'] = $options['z_list'];
	}

}
