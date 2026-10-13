<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace Joomla\Component\WishboxRadicalMartOzon\Administrator\Controller;

use Exception;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\WishboxRadicalMartOzon\Administrator\Extension\WishboxRadicalMartOzonComponent;
use WishboxRadicalMartOzonLibrary\Dto\ProductSyncResult;
use WishboxRadicalMartOzonLibrary\Service\RadicalMartToOzonProductSyncService;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handles product synchronization requests from the RadicalMart toolbar.
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
final class ProductsController extends BaseController
{
	private const string DEFAULT_REDIRECT_URL = 'index.php?option=com_radicalmart&view=products';

	/**
	 * Match Ozon offers to RadicalMart products.
	 *
	 * @return void
	 *
	 * @throws Exception When synchronization cannot be started.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getIds(): void
	{
		$this->validateRequest();

		$result = $this->getProductService()
			->updateOzonProductIds();

		$this->enqueueResult('COM_WISHBOXRADICALMARTOZON_MESSAGE_IDS_UPDATED', $result);
		$this->redirectToProducts();
	}

	/**
	 * Synchronize selected RadicalMart products with Ozon.
	 *
	 * @return void
	 *
	 * @throws Exception When synchronization cannot be started.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function update(): void
	{
		$this->validateRequest();

		$productIds = array_map('intval', (array) $this->input->post->get('cid', [], 'array'))
				|> (fn($x) => array_filter($x, static fn(int $productId): bool => $productId > 0))
				|> array_unique(...)
				|> array_values(...);

		if ($productIds === [])
		{
			$this->app->enqueueMessage(
				Text::_('COM_WISHBOXRADICALMARTOZON_MESSAGE_SELECT_PRODUCTS'),
				'warning'
			);
			$this->redirectToProducts();

			return;
		}

		$result = $this->getProductService()
			->updateProducts($productIds);

		$this->enqueueResult('COM_WISHBOXRADICALMARTOZON_MESSAGE_PRODUCTS_UPDATED', $result);
		$this->redirectToProducts();
	}

	/**
	 * Synchronize all linked RadicalMart products with Ozon.
	 *
	 * @return void
	 *
	 * @throws Exception When synchronization cannot be started.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function updateAll(): void
	{
		$this->validateRequest();

		$result = $this->getProductService()
			->updateProducts();

		$this->enqueueResult('COM_WISHBOXRADICALMARTOZON_MESSAGE_PRODUCTS_UPDATED', $result);
		$this->redirectToProducts();
	}

	/**
	 * Get the product synchronization service from the component.
	 *
	 * @return RadicalMartToOzonProductSyncService Product synchronization service.
	 *
	 * @throws Exception When the component cannot be booted.
	 *
	 * @since 1.0.0
	 */
	private function getProductService(): RadicalMartToOzonProductSyncService
	{
		/** @var WishboxRadicalMartOzonComponent $wishboxRadicalMartOzonComponent */
		$wishboxRadicalMartOzonComponent = $this->app->bootComponent('com_wishboxradicalmartozon');

		return $wishboxRadicalMartOzonComponent->getRadicalMartToOzonProductSyncService();
	}

	/**
	 * Add synchronization results to the Joomla message queue.
	 *
	 * @param   string             $languageKey  Result language key.
	 * @param   ProductSyncResult  $result       Synchronization result.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function enqueueResult(string $languageKey, ProductSyncResult $result): void
	{
		$this->app->enqueueMessage(
			Text::sprintf(
				$languageKey,
				$result->updated,
				$result->skipped,
				$result->failed
			),
			$result->failed > 0 ? 'warning' : 'success'
		);

		foreach ($result->errors as $message)
		{
			$this->app->enqueueMessage($message, 'error');
		}
	}

	/**
	 * Validate the request token and component permission.
	 *
	 * @return void
	 *
	 * @throws Exception When the request is not authorized.
	 *
	 * @since 1.0.0
	 */
	private function validateRequest(): void
	{
		$this->checkToken();

		$identity = $this->app->getIdentity();

		if (!$identity->authorise('core.manage', 'com_wishboxradicalmartozon'))
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}

	/**
	 * Redirect to the RadicalMart products list.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function redirectToProducts(): void
	{
		$redirectUrl = $this->input->post->getString('redirect_url', self::DEFAULT_REDIRECT_URL);

		if ($redirectUrl === '' || !Uri::isInternal($redirectUrl))
		{
			$redirectUrl = self::DEFAULT_REDIRECT_URL;
		}

		$this->setRedirect(Route::_($redirectUrl, false));
	}
}
