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

			// retrieve orm attribute
			$aAttributes = $argument->getAttributesOfType(Orm::class);

			// default mapping name
			$sMapping = 'id';

			// attribute defined mapping name
			if(count($aAttributes) > 0){
				$sMapping = $aAttributes[0]->mapping;
			}

			// retrieve request parameter
			$sRef = $request->get($sMapping);

			// load orm object
			$oObject = \MetaModel::GetObject($argument->getType(), $sRef);

			return [$oObject];
		}

        return [];
	}
}