<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMart\WishboxOzon\Extension\WishboxOzon;

defined('_JEXEC') or die;

/**
 * Provides the Wishbox Ozon plugin dependencies.
 *
 * @since 1.0.0
 */
return new class implements ServiceProviderInterface
{
	/**
	 * Register the plugin dependencies.
	 *
	 * @param Container $container Dependency injection container.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function register(Container $container): void
	{
		$container->set(
			PluginInterface::class,
			static function (Container $container): PluginInterface {
				$subject = $container->get(DispatcherInterface::class);
				$config  = (array) PluginHelper::getPlugin('radicalmart', 'wishboxozon');
				$plugin  = new WishboxOzon($subject, $config);

				$app = Factory::getApplication();
				$plugin->setApplication($app);

				return $plugin;
			}
		);
	}
};
