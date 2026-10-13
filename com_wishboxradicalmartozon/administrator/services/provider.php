<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\WishboxRadicalMartOzon\Administrator\Extension\WishboxRadicalMartOzonComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use WishboxRadicalMartLibrary\Service\Product\ProductService;
use WishboxRadicalMartLibrary\Service\Provider\ProductServiceProvider;
use WishboxRadicalMartOzonLibrary\Service\Provider\RadicalMartToOzonProductSyncServiceProvider;
use WishboxRadicalMartOzonLibrary\Service\RadicalMartToOzonProductSyncService;
use WishboxOzonLibrary\Service\Provider\OzonApiClientProvider;
use WishboxOzonLibrary\Service\Provider\OzonProductServiceProvider;

defined('_JEXEC') or die;

/**
 * Registers the Wishbox RadicalMart Ozon component services.
 *
 * @since 1.0.0
 */
return new class implements ServiceProviderInterface {
	/**
	 * Register the component in the Joomla dependency injection container.
	 *
	 * @param   Container  $container  Joomla dependency injection container.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function register(Container $container): void
	{
		if (!class_exists(OzonApiClientProvider::class)
			|| !class_exists(OzonProductServiceProvider::class))
		{
			throw new RuntimeException('The Wishbox Ozon library must be installed before using this component.');
		}

		if (!class_exists(ProductService::class)
			|| !class_exists(ProductServiceProvider::class))
		{
			throw new RuntimeException('The Wishbox RadicalMart library must be installed before using this component.');
		}

		if (!ComponentHelper::isEnabled('com_wishboxozon'))
		{
			throw new RuntimeException('The Wishbox Ozon component must be installed before using this component.');
		}

		$container->registerServiceProvider(new OzonApiClientProvider());
		$container->registerServiceProvider(new OzonProductServiceProvider());
		$container->registerServiceProvider(new ProductServiceProvider());
		$container->registerServiceProvider(new RadicalMartToOzonProductSyncServiceProvider());

		$container->registerServiceProvider(
			new MVCFactory('\\Joomla\\Component\\WishboxRadicalMartOzon')
		);
		$container->registerServiceProvider(
			new ComponentDispatcherFactory('\\Joomla\\Component\\WishboxRadicalMartOzon')
		);

		$container->set(
			ComponentInterface::class,
			static function (Container $container): ComponentInterface {
				$wishboxRadicalMartOzonComponent = new WishboxRadicalMartOzonComponent(
					$container->get(ComponentDispatcherFactoryInterface::class)
				);
				$wishboxRadicalMartOzonComponent->setMVCFactory(
					$container->get(MVCFactoryInterface::class)
				);
				$wishboxRadicalMartOzonComponent->setRadicalMartToOzonProductSyncService(
					$container->get(RadicalMartToOzonProductSyncService::class)
				);

				return $wishboxRadicalMartOzonComponent;
			}
		);
	}
};
