<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Task\WishboxRadicalMartOzon\Extension\WishboxRadicalMartOzon;
use WishboxOzonLibrary\Service\Provider\OzonApiClientProvider;
use WishboxOzonLibrary\Service\Provider\OzonProductServiceProvider;
use WishboxRadicalMartLibrary\Service\Product\ProductService;
use WishboxRadicalMartLibrary\Service\Provider\ProductServiceProvider;
use WishboxRadicalMartOzonLibrary\Service\Provider\RadicalMartToOzonProductSyncServiceProvider;
use WishboxRadicalMartOzonLibrary\Service\RadicalMartToOzonProductSyncService;

defined('_JEXEC') or die;

/**
 * Registers the scheduled RadicalMart-to-Ozon synchronization task.
 *
 * @since 1.0.0
 */
return new class implements ServiceProviderInterface {
	/**
	 * Register the task plugin and its services.
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
			throw new RuntimeException('The Wishbox Ozon library must be installed before enabling this plugin.');
		}

		if (!class_exists(ProductService::class)
			|| !class_exists(ProductServiceProvider::class))
		{
			throw new RuntimeException('The Wishbox RadicalMart library must be installed before enabling this plugin.');
		}

		if (!ComponentHelper::isEnabled('com_wishboxozon'))
		{
			throw new RuntimeException('The Wishbox Ozon component must be installed before enabling this plugin.');
		}

		$container->registerServiceProvider(new OzonApiClientProvider());
		$container->registerServiceProvider(new OzonProductServiceProvider());
		$container->registerServiceProvider(new ProductServiceProvider());
		$container->registerServiceProvider(new RadicalMartToOzonProductSyncServiceProvider());

		$container->set(
			PluginInterface::class,
			static function (Container $container): PluginInterface {
				$plugin = new WishboxRadicalMartOzon(
					$container->get(DispatcherInterface::class),
					(array) PluginHelper::getPlugin('task', 'wishboxradicalmartozon')
				);

				$plugin->setApplication(Factory::getApplication());
				$plugin->setRadicalMartToOzonProductSyncService(
					$container->get(RadicalMartToOzonProductSyncService::class)
				);

				return $plugin;
			}
		);
	}
};
