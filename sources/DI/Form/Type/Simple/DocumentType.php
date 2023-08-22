<?php

namespace Combodo\iTop\DI\Form\Type\Simple;

use ormDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Example of type with data transformer.
 *
 */
class DocumentType extends AbstractType
{

	/** @inheritdoc  */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);

		$builder->addModelTransformer(new CallbackTransformer(

			// transform
			function (?ormDocument $oDocument){
				return null;
			},

			// reverse
			function (?UploadedFile $oFileData){
				if($oFileData === null){
					return null;
				}

				$doc_content = file_get_contents($oFileData->getRealPath());
				return new ormDocument($doc_content, $oFileData->getClientMimeType(), $oFileData->getRealPath());
			},
		));
	}

	/** @inheritdoc  */
	public function buildView(FormView $view, FormInterface $form, array $options): void
	{
		/** @var ormDocument $oOrmDocument */
		$oOrmDocument = $form->getData();
		$view->vars['data'] = 'data:image/' . $oOrmDocument->GetMimeType() . ';base64,' . base64_encode($oOrmDocument->GetData());;
		$view->vars['mime_type'] = $oOrmDocument->GetMimeType();
		$view->vars['filename'] = $oOrmDocument->GetFileName();
	}

	/** @inheritdoc  */
	public function getParent(): string
	{
		return FileType::class;
	}
}
