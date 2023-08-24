<?php

namespace Combodo\iTop\DI\Form\Type\Attribute;

use Combodo\iTop\DI\Form\Transformer\ExternalKeyTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExternalFieldType extends AbstractType
{

	/** @inheritdoc  */
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'object_class' => null,
			'is_external_key' => false,
		]);

	}
	/** @inheritdoc  */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		if($options['is_external_key']){
			$builder->addViewTransformer(new ExternalKeyTransformer($options['object_class']));
		}

	}

	/** @inheritdoc  */
	public function getParent(): string
	{
		return TextType::class;
	}

}
