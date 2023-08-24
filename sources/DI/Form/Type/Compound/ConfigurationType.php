<?php

namespace Combodo\iTop\DI\Form\Type\Compound;

use Combodo\iTop\DI\Form\Type\Layout\FieldSetType;
use MetaModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Example of a non object form.
 *
 */
class ConfigurationType extends AbstractType
{

	private static array $CATEGORIES = [
		'csv_import_' => [ 'name' => 'csv_import', 'icon' => 'fa-solid fa-file-import'],
		'app_' => [ 'name' => 'application', 'icon' => 'fa-solid fa-mobile-screen-button'],
		'db_' => [ 'name' => 'database', 'icon' => 'fa-solid fa-database'],
		'apc_cache' => [ 'name' => 'cache', 'icon' => 'fa-solid fa-magnifying-glass-chart'],
		'log' => [ 'name' => 'logs', 'icon' => 'fa-solid fa-file-code'],
		'cas_' => [ 'name' => 'CAS', 'icon' => 'fa-solid fa-wand-magic-sparkles'],
		'temporary' => [ 'name' => 'Temporary_Objects', 'icon' => 'fa-solid fa-layer-group'],
		'email' => [ 'name' => 'email', 'icon' => 'fa-solid fa-envelope'],
	];

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$aManualCategories = [];
		$aCategories = [];
		$aSafeEntries = [];

		// retrieve configuration
		$oConfig = MetaModel::GetConfig();

		// get settings
		$aSettings = $oConfig->GetSettings();

		// configuration entries
		$aConfigurationEntries = $oConfig->ToArray();

		// iterate throw configuration entries and create categories...
		foreach ($aConfigurationEntries as $key => $value){

			// create element
			$aConfigurationElement = [
				'value' => $value,
				'safe_key' => str_replace(".", "__", $key)
			];

			// search manual category
			$sCategoryKey = null;
			foreach (static::$CATEGORIES as $sKey => $aValue){
				if(str_starts_with($key, $sKey)){
					$sCategoryKey = $sKey;
				}
			}

			// search automatic category
			$i = strpos($key, ".");

			if($sCategoryKey != null){
				// manual category
				$aManualCategories[$sCategoryKey][$key] = $aConfigurationElement;
			}
			else if($i){
				// automatic category
				$s = substr($key, 0, $i);
				$aCategories[$s][$key] = $aConfigurationElement;
			}
			else{
				// others
				$aSafeEntries[$key] = $aConfigurationElement;
			}
		}

		// start with manual categories
		$this->handleCategories($aManualCategories, $aSettings, $builder);

		// then, automatic categories
		$this->handleCategories($aCategories, $aSettings, $builder);

		// then, other entries
		$this->handleEntries($aSafeEntries, $aSettings, $builder);
	}

	/**
	 * Handle category.
	 *
	 * @param array $aCategories
	 * @param array $aSettings
	 * @param FormBuilderInterface $builder
	 *
	 * @return void
	 */
	private function handleCategories(array $aCategories, array $aSettings, FormBuilderInterface $builder)
	{
		// iterate throw categories...
		foreach ($aCategories as $sKey => $sValue) {

			// prepare category items
			$items = [];

			// add all items
			foreach ($sValue as $key => $value) {
				$type = $this->getFormType($aSettings, $key);
				$items[$value['safe_key']] = [
					'type' => $type,
					'options' => [
						'required' => array_key_exists($key, $aSettings) && array_key_exists('show_in_conf_sample', $aSettings[$key]) ? $aSettings[$key]['show_in_conf_sample'] : false
					]
				];

				$sTopic = $this->getTopic($aSettings, $key, 'description');
				if($sTopic != null){
					$items[$value['safe_key']]['options']['help'] = $sTopic;
				}
			}

			$sIcon = null;
			$sLabel = $sKey;
			if(array_key_exists($sKey, self::$CATEGORIES)){
				$sIcon = self::$CATEGORIES[$sKey]['icon'];
				$sLabel = self::$CATEGORIES[$sKey]['name'];
			}

			// create field set
			$builder->add($sKey, FieldSetType::class, [
				'label' => $sLabel,
				'items' => $items,
				'icon' => $sIcon,
			]);
		}
	}

	/**
	 * Handle array of entries.
	 *
	 * @param array $aEntries
	 * @param array $aSettings
	 * @param FormBuilderInterface $builder
	 *
	 * @return void
	 */
	private function handleEntries(array $aEntries, array $aSettings, FormBuilderInterface $builder)
	{
		// iterate throw entries...
		foreach ($aEntries as $sKey => $aValue){

			// retrieve form type
			$type = $this->getFormType($aSettings, $sKey);

			// create type
			$builder->add($aValue['safe_key'], $type, [
				'label' => $sKey
			]);
		}
	}

	/**
	 * Return form type depending on settings definition.
	 *
	 * @param array $aSettings
	 * @param string $sKey
	 *
	 * @return string
	 */
	private function getFormType(array $aSettings, string $sKey) : string{

		if(!array_key_exists($sKey, $aSettings)){
			return TextType::class;
		}

		switch($aSettings[$sKey]['type']){
			case 'bool':
				return CheckboxType::class;
			case 'integer':
				return IntegerType::class;
			default:
				return TextType::class;
		}
	}

	/**
	 * Get parameter setting topic.
	 *
	 * @param array $aSettings
	 * @param string $sKey
	 * @param string $sTopic
	 *
	 * @return string|null
	 */
	private function getTopic(array $aSettings, string $sKey, string $sTopic) : ?string
	{

		if(!array_key_exists($sKey, $aSettings)){
			return null;
		}

		if(!array_key_exists($sTopic, $aSettings[$sKey])){
			return null;
		}

		return($aSettings[$sKey][$sTopic]);
	}
}
