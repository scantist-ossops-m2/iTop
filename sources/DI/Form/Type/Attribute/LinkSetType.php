<?php

namespace Combodo\iTop\DI\Form\Type\Attribute;

use Combodo\iTop\DI\Form\Type\Compound\ObjectType;
use Combodo\iTop\DI\Services\ObjectPresentationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Example of collection.
 *
 */
class LinkSetType extends AbstractType
{
	/** @var \Combodo\iTop\DI\Services\ObjectPresentationService  */
	private ObjectPresentationService $oObjectService;

	/**
	 * Constructor.
	 *
	 * @param \Combodo\iTop\DI\Services\ObjectPresentationService $oObjectService
	 */
	public function __construct(ObjectPresentationService $oObjectService)
	{
		$this->oObjectService = $oObjectService;
	}

	/** @inheritdoc  */
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'entry_type' => ObjectType::class,
			'is_indirect' => false,
			'is_abstract' => false,
			'object_classes' => null,
			'target_class' => null,
			'entry_options' => null,
			'allow_add' => true,
			'allow_delete' => true,
		]);

	}

	/** @inheritdoc  */
	public function getParent(): string
	{
		return CollectionType::class;
	}

	/** @inheritdoc  */
	public function buildView(FormView $view, FormInterface $form, array $options): void
	{
		$view->vars['labels'] = $this->oObjectService->getFormPresentationLabels($options['entry_options']['object_class'], 'list', $options['entry_options']['ext_key_to_me']);
		$view->vars['object_class'] = $options['entry_options']['object_class'];
		$view->vars['ext_key_to_me'] = $options['entry_options']['ext_key_to_me'];
		$view->vars['target_class'] = $options['target_class'];
		$view->vars['is_indirect'] = $options['is_indirect'];
		$view->vars['is_abstract'] = $options['is_abstract'];
		$view->vars['object_classes'] = $options['object_classes'];
	}
}
