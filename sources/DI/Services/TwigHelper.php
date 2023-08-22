<?php

namespace Combodo\iTop\DI\Services;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Helper to construct assets paths.
 *
 */
class TwigHelper extends AbstractExtension
{
	private string $sAppRoot = '';

	/**
	 * Constructor.
	 *
	 */
	public function __construct()
	{
		$this->sAppRoot = \MetaModel::GetConfig()->Get('app_root_url');
	}

	/** @inheritdoc  */
	public function getFunctions() : array
	{
		return [
			new TwigFunction('asset_js', [$this, 'asset_js']),
			new TwigFunction('asset_css', [$this, 'asset_css']),
			new TwigFunction('asset_image', [$this, 'asset_image']),
		];
	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	public function asset_js($name)
	{
		return $this->sAppRoot . 'js/' . $name;
	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	public function asset_css($name)
	{
		return $this->sAppRoot . 'css/' . $name;
	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	public function asset_image($name)
	{
		return $this->sAppRoot . 'images/' . $name;
	}
}