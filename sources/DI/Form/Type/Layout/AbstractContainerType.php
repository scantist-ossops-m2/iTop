<?php

namespace Combodo\iTop\DI\Form\Type\Layout;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractContainerType extends AbstractType
{

	/** @inheritdoc  */
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'items' => null,
			'inherit_data' => true,
		]);

		$resolver->setAllowedTypes('items', 'array');
	}

	/** @inheritdoc  */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		foreach ($options['items'] as $key => $value) {
			$value['options']['attr']['data-att-code'] = $key;
			$builder->add($key, $value['type'], $value['options']);
		}
	}

}
