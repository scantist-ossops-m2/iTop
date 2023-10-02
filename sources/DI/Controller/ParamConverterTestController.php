<?php

namespace Combodo\iTop\DI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Param converter test controller.
 *
 * public const ENUM_SANITIZATION_FILTER_INTEGER = 'integer';
 * public const ENUM_SANITIZATION_FILTER_CLASS = 'class';
 * public const ENUM_SANITIZATION_FILTER_STRING = 'string';
 * public const ENUM_SANITIZATION_FILTER_CONTEXT_PARAM = 'context_param';
 * public const ENUM_SANITIZATION_FILTER_ROUTE = 'route';
 * public const ENUM_SANITIZATION_FILTER_OPERATION = 'operation';
 * public const ENUM_SANITIZATION_FILTER_PARAMETER = 'parameter';
 * public const ENUM_SANITIZATION_FILTER_FIELD_NAME = 'field_name';
 * public const ENUM_SANITIZATION_FILTER_TRANSACTION_ID = 'transaction_id';
 * public const ENUM_SANITIZATION_FILTER_ELEMENT_IDENTIFIER = 'element_identifier';
 * public const ENUM_SANITIZATION_FILTER_VARIABLE_NAME = 'variable_name';
 * public const ENUM_SANITIZATION_FILTER_RAW_DATA = 'raw_data';
 * public const ENUM_SANITIZATION_FILTER_URL = 'url';
 */
class ParamConverterTestController extends AbstractController
{

	/**
	 *
	 * @Route("/param_converter_test/{integer<\d+>}/{class<\w+>}", name="param_converter_test", methods={"GET"})
	 * @ParamConverter("integer", options={"sanitization" : "integer"})
	 * @ParamConverter("class", options={"sanitization" : "class"})
	 */
	public function convert(Request $request, string $integer, string $class) : Response
	{
		return $this->json([
			'sanitization' => [
				'integer' => $integer,
				'class' => $class,
			]
		]);

	}

}
