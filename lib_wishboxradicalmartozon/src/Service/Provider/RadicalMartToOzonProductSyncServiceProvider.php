<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartOzonLibrary\Service\Provider;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use WishboxOzonLibrary\Service\OzonProductService;
use WishboxRadicalMartLibrary\Service\Product\ProductService;
use WishboxRadicalMartOzonLibrary\Service\RadicalMartToOzonProductSyncService;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Registers the RadicalMart-to-Ozon product synchronization service.
 *
 * @since 1.0.0
 */
final class RadicalMartToOzonProductSyncServiceProvider implements ServiceProviderInterface
{
	/**
	 * Register the configured product synchronization service.
	 *
	 * @param   Container  $container  Dependency injection container.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function register(Container $container): void
	{
		$container->share(
			RadicalMartToOzonProductSyncService::class,
			static function (Container $container): RadicalMartToOzonProductSyncService {
				$wishboxOzonComponentParams = ComponentHelper::getParams('com_wishboxozon');
				$wishboxRadicalMartOzonComponentParams = ComponentHelper::getParams(
					'com_wishboxradicalmartozon'
				);

				return new RadicalMartToOzonProductSyncService(
					$container->get(OzonProductService::class),
					$container->get(ProductService::class),
					(int) $wishboxOzonComponentParams->get('ozon_warehouse_id', 0),
					(string) $wishboxOzonComponentParams->get('ozon_currency_code', 'RUB'),
					(float) $wishboxRadicalMartOzonComponentParams->get('product_price_ratio', 1)
				);
			}
		);
	}
}
