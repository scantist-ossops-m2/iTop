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

use Exception;
use ExceptionLog;
use ormSet;

/**
 * Class SetDataTransformer
 *
 * @api
 *
 * @since 3.1.0
 * @package Combodo\iTop\Service\Set
 */
class SetDataTransformer
{

	/**
	 * ExecuteOperations.
	 *
	 * @param array $aOperations
	 * @param \ormSet $oOrmSet
	 *
	 * @return void
	 * @throws \CoreException
	 */
	static public function ExecuteOperations(array $aOperations, ormSet $oOrmSet)
	{
		// remove (first remove to avoid max item wrong limit on add)
		$aAddOperations = array_filter($aOperations, function ($aOperation) {
			return $aOperation['operation'] === 'remove';
		}, ARRAY_FILTER_USE_BOTH);
		foreach ($aAddOperations as $sElement => $aOperation) {
			$oOrmSet->Remove($sElement);
		}

		// add
		$aAddOperations = array_filter($aOperations, function ($aOperation) {
			return $aOperation['operation'] === 'add';
		}, ARRAY_FILTER_USE_BOTH);
		foreach ($aAddOperations as $sElement => $aOperation) {
			$oOrmSet->Add($sElement);
		}

	}

	/**
	 * Append values to orm set object.
	 * Values are separated by spaces.
	 *
	 * @param string $sValue
	 * @param ormSet $oOrmSet
	 * @param bool $bIgnoreLimit
	 *
	 */
	static public function AppendValuesToOrmSet(string $sValue, ormSet $oOrmSet, bool $bIgnoreLimit)
	{
		try {
			$aItems = explode(" ", $sValue);
			foreach ($aItems as $sItem) {
				if (!empty($sItem)) {
					$oOrmSet->Add($sItem, $bIgnoreLimit);
				}
			}
		}
		catch (Exception $e) {
			ExceptionLog::LogException($e);
		}
	}
}