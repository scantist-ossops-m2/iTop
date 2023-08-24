<?php

namespace Combodo\iTop\DI\Form\Transformer;

use ormDocument;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Perform transformation between ormDocument and UploadedFile.
 */
class DocumentTransformer implements DataTransformerInterface
{

	/** @inheritdoc  */
	public function transform($value)
	{

		return null;
	}

	/** @inheritdoc  */
	public function reverseTransform($value)
	{
		if($value === null){
			return null;
		}

		$doc_content = file_get_contents($value->getRealPath());
		return new ormDocument($doc_content, $value->getClientMimeType(), $value->getRealPath());
	}
}
