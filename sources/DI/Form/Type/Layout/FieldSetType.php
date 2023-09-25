<?php

namespace Combodo\iTop\DI\Form\Type\Layout;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldSetType extends AbstractContainerType
{

	/** @inheritdoc  */
	public function configureOptions(OptionsResolver $resolver): void
	{
		parent::configureOptions($resolver);
		$resolver->setDefault('icon', null);
	}

	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		parent::buildView($view, $form, $options);

		$view->vars['icon'] = $options['icon'];
	}
}
