<?php

namespace Combodo\iTop\DI\Form\Type\Simple;

use Combodo\iTop\DI\Form\Type\ObjectType;
use Combodo\iTop\DI\Services\ObjectPresentationService;
use Combodo\iTop\DI\Services\ObjectService;
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
			'entry_options' => [
				'object_class' => null
			],
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
		$view->vars['labels'] = $this->oObjectService->getFormPresentationLabels($options['entry_options']['object_class'], 'list', $options['entry_options']['ext_key_to_me']);;
	}
}
