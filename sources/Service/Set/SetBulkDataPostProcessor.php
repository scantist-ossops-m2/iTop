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

namespace Combodo\iTop\Service\Set;

use Combodo\iTop\Service\Base\iDataPostProcessor;
use DBObjectSet;
use DBSearch;
use Exception;
use ExceptionLog;

/**
 * Class SetBulkDataPostProcessor
 *
 * @api
 *
 * @since 3.1.0
 * @package Combodo\iTop\Service\Set
 */
class SetBulkDataPostProcessor implements iDataPostProcessor
{
	/** @inheritDoc */
	public static function Execute(array $aData, array $aSettings): array
	{
		return self::ComputeScopeData($aData, $aSettings['bulk_oql'], $aSettings['tag_field_code']);
	}

	/**
	 * ComputeScopeData.
	 *
	 * @param array $aResult
	 * @param string $sScope
	 * @param string $sTagFieldCode
	 *
	 * @return array
	 */
	public static function ComputeScopeData(array $aResult, string $sScope, string $sTagFieldCode): array
	{
		if (!empty($sScope)) {

			try {
				// OQL to select bulk object selection
				$oDbObjectSearchBulkObjects = DBSearch::FromOQL($sScope);
				$oDbObjectSetBulkObjects = new DBObjectSet($oDbObjectSearchBulkObjects);

				// Iterate throw tags...
				foreach ($aResult as &$aItem) {

					$iCount = 0;

					// count occurrence in bulk context
					$oDbObjectSetBulkObjects->Rewind();
					while ($oObject = $oDbObjectSetBulkObjects->Fetch()) {
						$oTagField = $oObject->Get($sTagFieldCode);
						if (in_array($aItem['code'], $oTagField->GetValues())) {
							$iCount++;
						}
					}

					// compute bulk information
					$aItem['occurrence'] = $iCount;
					if ($iCount === $oDbObjectSetBulkObjects->Count()) {
						$aItem['full'] = ($iCount == $oDbObjectSetBulkObjects->Count());
					}
				}

			}
			catch (Exception $e) {

				ExceptionLog::LogException($e);
			}

		}

		return $aResult;
	}

}