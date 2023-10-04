<?php

namespace Combodo\iTop\DI\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ClassParameterValueResolver implements ValueResolverInterface
{

	public function resolve(Request $request, ArgumentMetadata $argument): iterable
	{
		if(\MetaModel::IsValidClass($argument->getType())){

			$aAttributes = $argument->getAttributesOfType(Orm::class);

			$sParameterName = 'id';

			if(count($aAttributes) > 0){
				$sParameterName = $aAttributes[0]->mapping;
			}

			$sRef = $request->get($sParameterName);

			$oObject = \MetaModel::GetObject($argument->getType(), $sRef);
			return [$oObject];
		}

        return [];
	}
}