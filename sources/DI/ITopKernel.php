<?php

namespace Combodo\iTop\DI;

use Symfony;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * ITopKernel.
 *
 */
class ITopKernel extends BaseKernel
{
	use MicroKernelTrait;

	/**
	 * @return array|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
	 */
	public function registerBundles(): array
	{
		$bundles = [
			new FrameworkBundle(),
			new TwigBundle(),
		];

		if ($this->getEnvironment() == 'dev') {
			$bundles[] = new WebProfilerBundle();
		}

		return $bundles;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container
	 *
	 * @return void
	 */
	protected function configureContainer(ContainerConfigurator $container): void
	{
		$container->import(__DIR__.'/../../conf/production/framework.yml');
		$container->import(__DIR__.'/../../conf/production/services.yml');
		$container->import(__DIR__.'/../../conf/production/configuration.php');

		// configure WebProfilerBundle only if the bundle is enabled
		if (isset($this->bundles['WebProfilerBundle'])) {
			$container->extension('web_profiler', [
				'toolbar' => true,
				'intercept_redirects' => false,
			]);
		}
	}

	/**
	 * @param \Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator $routes
	 *
	 * @return void
	 */
	protected function configureRoutes(RoutingConfigurator  $routes)
	{
		// import the WebProfilerRoutes, only if the bundle is enabled
		if (isset($this->bundles['WebProfilerBundle'])) {
			$routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml')->prefix('/_wdt');
			$routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml')->prefix('/_profiler');
		}

		// load the annotation routes
		$routes->import(__DIR__.'/Controller/', 'annotation');
	}

	// optional, to use the standard Symfony cache directory
	public function getCacheDir(): string
	{
		return __DIR__.'/../../data/app/cache/'.$this->getEnvironment();
	}

	// optional, to use the standard Symfony logs directory
	public function getLogDir(): string
	{
		return __DIR__.'/../../data/app/log';
	}
}
