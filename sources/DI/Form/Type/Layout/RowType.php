<?php

namespace Combodo\iTop\DI\Form\Type\Layout;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RowType extends AbstractContainerType
{

	/** @inheritdoc  */
	public function configureOptions(OptionsResolver $resolver): void
	{
		parent::configureOptions($resolver);
		$resolver->setDefault('label', false);
	}

}
