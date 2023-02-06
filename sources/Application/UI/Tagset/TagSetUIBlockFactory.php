<?php
/**
 * Copyright (C) 2013-2022 Combodo SARL
 *
 * This file is part of iTop.
 *
 * iTop is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 */

namespace Combodo\iTop\Application\UI\Tagset;

use AttributeSet;
use Combodo\iTop\Application\UI\Base\Component\Input\Set\Set;
use Combodo\iTop\Application\UI\Base\Component\Input\Set\SetUIBlockFactory;
use Combodo\iTop\Service\Set\SetBulkDataPostProcessor;
use ormSet;

/**
 * Class TagSetUIBlockFactory
 *
 * @api
 *
 * @since 3.1.0
 * @package Combodo\iTop\Application\UI\Links\Set
 */
class TagSetUIBlockFactory extends SetUIBlockFactory
{
	/**
	 * Make a tag set block.
	 *
	 * @param string $sId
	 * @param \AttributeSet $oAttributeSet
	 * @param \ormSet $oValue
	 * @param array $aArgs
	 *
	 * @return \Combodo\iTop\Application\UI\Base\Component\Input\Set\Set
	 * @throws \CoreException
	 * @throws \OQLException
	 */
	public static function MakeForTagSet(string $sId, AttributeSet $oAttributeSet, ormSet $oValue, array $aArgs): Set
	{
		$aAllowedValues = $oAttributeSet->GetPossibleValues($aArgs);

		$aOptions = [];
		foreach ($aAllowedValues as $sCode => $sLabel) {
			$aOptions[] = [
				'code'  => $sCode,
				'label' => $sLabel,
			];
		}

		// Set UI block for OQL
		$oSetUIBlock = SetUIBlockFactory::MakeForSimple($sId, $aOptions, 'label', 'code', ['label']);
		$oSetUIBlock->SetValue(json_encode($oValue->GetValues()));
		$oSetUIBlock->SetMaxItems($oAttributeSet->GetMaxItems());

		return $oSetUIBlock;
	}

	/**
	 * Make a tag set block for bulk modify.
	 *
	 * @param string $sId
	 * @param \AttributeSet $oAttributeSet
	 * @param \ormSet $oValue
	 * @param array $aArgs
	 *
	 * @return \Combodo\iTop\Application\UI\Base\Component\Input\Set\Set
	 * @throws \CoreException
	 * @throws \OQLException
	 */
	public static function MakeForBulkTagSet(string $sId, AttributeSet $oAttributeSet, ormSet $oValue, array $aArgs): Set
	{
		$oSetUIBlock = self::MakeForTagSet($sId, $oAttributeSet, $oValue, $aArgs);

		// Bulk modify specific
		$oSetUIBlock->SetIsMultiValuesSynthesis(true);
		$oSetUIBlock->SetMaxItems(null); // can't handle max items in bulk, need to be performed on field

		$aCurrentValues = $oAttributeSet->GetPossibleValues($aArgs);

		// retrieve options
		$aOptions = array_values($aArgs['bulk_context']['options'][$oAttributeSet->GetCode()]);
		$oSetUIBlock->GetDataProvider()->SetOptions($aOptions);

		return $oSetUIBlock;
	}
}