<?php

namespace Combodo\iTop\DI\Form\Builder;

use Combodo\iTop\DI\Form\Type\Layout\ColumnType;
use Combodo\iTop\DI\Form\Type\Layout\FieldSetType;
use Combodo\iTop\DI\Form\Type\Layout\RowType;
use Dict;

class LayoutBuilder
{
	/**
	 * createRow.
	 *
	 * @param $key
	 * @param $columns
	 *
	 * @return array
	 */
	public function createRow($key, $columns) : array
	{
		return [
			'type' => RowType::class,
			'options' => [
				'items' => $columns,
				'label' => false,
				'row_attr' => [
					'data-block' => 'row_container'
				]
			]
		];
	}

	/**
	 * createColumn.
	 *
	 * @param $key
	 * @param $item
	 * @param $dataClass
	 *
	 * @return array
	 */
	public function createColumn($key, $items) : array
	{
		return [
			'type' => ColumnType::class,
			'options' => [
				'items' => $items,
				'label' => false,
				'row_attr' => [
					'data-block' => 'column_container'
				]
			]
		];
	}

	/**
	 * createFieldSet.
	 *
	 * @param $key
	 * @param $item
	 * @param $dataClass
	 *
	 * @return array
	 */
	public function createFieldSet($key, $items) : array
	{
		return [
			'type' => FieldSetType::class,
			'options' => [
				'items' => $items,
				'label' => Dict::S(substr($key, 9)),
				'row_attr' => [
					'data-block' => 'fieldset_container'
				]
			]
		];
	}
}