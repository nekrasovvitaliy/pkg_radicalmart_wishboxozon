<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartOzonLibrary\Service;

use Exception;
use RuntimeException;
use Throwable;
use UnexpectedValueException;
use WishboxOzonLibrary\Service\OzonProductService;
use WishboxRadicalMartLibrary\Service\Product\ProductService;
use WishboxRadicalMartOzonLibrary\Dto\ProductSyncResult;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Synchronizes RadicalMart product prices and stocks with Ozon.
 *
 * @since 1.0.0
 */
final readonly class RadicalMartToOzonProductSyncService
{
	private const int PRODUCT_LIST_PAGE_SIZE = 1000;

	private const string OZON_PRODUCT_ID_FIELD = 'ozon_market_id';

	/**
	 * Create the RadicalMart Ozon product service.
	 *
	 * @param   OzonProductService  $ozonProductService  Ozon product service.
	 * @param   ProductService      $productService      RadicalMart product service.
	 * @param   int                 $warehouseId         Ozon warehouse identifier.
	 * @param   string              $currencyCode        Ozon price currency code.
	 * @param   float               $productPriceRatio   Product price multiplier.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function __construct(
		private OzonProductService $ozonProductService,
		private ProductService     $productService,
		private int                $warehouseId,
		private string             $currencyCode = 'RUB',
		private float              $productPriceRatio = 1.0
	)
	{
	}

	/**
	 * Update Ozon prices and stocks from linked RadicalMart products.
	 *
	 * @param   list<int>  $productIds  RadicalMart product identifiers, or an empty list for all linked products.
	 *
	 * @return ProductSyncResult Synchronization result.
	 *
	 * @throws Exception When RadicalMart products cannot be loaded.
	 *
	 * @since 1.0.0
	 */
	public function updateProducts(array $productIds = []): ProductSyncResult
	{
		$currencyCode = strtoupper(trim($this->currencyCode));

		$products = $this->productService->getProducts(
			self::OZON_PRODUCT_ID_FIELD,
			$currencyCode,
			$this->productPriceRatio,
			$productIds
		);

		$result = $this->ozonProductService->updateProducts(
			$products,
			$this->warehouseId,
			$currencyCode
		);

		return new ProductSyncResult(...$result);
	}

	/**
	 * Match Ozon offers to RadicalMart products and store Ozon product identifiers.
	 *
	 * @return ProductSyncResult Synchronization result.
	 *
	 * @since 1.0.0
	 */
	public function updateOzonProductIds(): ProductSyncResult
	{
		$result = new ProductSyncResult();
		$lastId = '';

		do
		{
			try
			{
				$response = $this->ozonProductService->getProductList(
					[],
					$lastId,
					self::PRODUCT_LIST_PAGE_SIZE
				);
				$items = $response->result->items ?? null;

				if (!is_array($items))
				{
					throw new RuntimeException('Ozon returned an invalid product list.');
				}
			}
			catch (Throwable $throwable)
			{
				$result->failed++;
				$result->errors[] = $throwable->getMessage();

				break;
			}

			foreach ($items as $item)
			{
				$offerId      = trim($item->offer_id ?? '');
				$ozonProductId = $item->product_id ?? 0;

				if ($offerId === '' || $ozonProductId < 1)
				{
					$result->skipped++;

					continue;
				}

				try
				{
					$productId = $this->productService->getIdByAlias($offerId);
					$this->productService->setProductFieldValue(
						$productId,
						self::OZON_PRODUCT_ID_FIELD,
						$ozonProductId
					);
					$result->updated++;
				}
				catch (UnexpectedValueException)
				{
					$result->skipped++;
				}
				catch (Throwable $throwable)
				{
					$result->failed++;
					$result->errors[] = 'Ozon offer ' . $offerId . ': ' . $throwable->getMessage();
				}
			}

			$nextLastId = trim((string) ($response->result->last_id ?? ''));

			if ($items === [] || $nextLastId === '' || $nextLastId === $lastId)
			{
				break;
			}

			$lastId = $nextLastId;
		}
		while (true);

		return $result;
	}
}
