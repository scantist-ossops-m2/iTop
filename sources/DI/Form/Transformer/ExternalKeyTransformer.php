<?php

namespace Combodo\iTop\DI\Form\Transformer;

use Exception;
use MetaModel;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform external key ref to external key name representation.
 */
class ExternalKeyTransformer implements DataTransformerInterface
{

	/** @var string $sObjectClass */
	private string $sObjectClass;

	/**
	 * @param string $sObjectClass
	 */
	public function __construct(string $sObjectClass)
	{
		$this->sObjectClass = $sObjectClass;
	}

	/** @inheritdoc  */
	public function transform($value)
	{
		try{
			return MetaModel::GetObject($this->sObjectClass, $value)->GetName();
		}
		catch(Exception $e){
			return null;
		}
	}

	/** @inheritdoc  */
	public function reverseTransform($value)
	{
		return $value;
	}
}
