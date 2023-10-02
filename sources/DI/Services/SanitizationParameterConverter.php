<?php

namespace Combodo\iTop\DI\Services;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use utils;

class SanitizationParameterConverter implements ParamConverterInterface
{

	public function apply(Request $request, ParamConverter $configuration)
	{
		$sInitial = $request->get($configuration->getName());
		$sSanitization = $configuration->getOptions()['sanitization'];
		$oSanitized = utils::Sanitize($sInitial, null, $sSanitization);
		$request->attributes->set($configuration->getName(), $oSanitized);
	}

	public function supports(ParamConverter $configuration)
	{
		return array_key_exists('sanitization', $configuration->getOptions());
	}
}