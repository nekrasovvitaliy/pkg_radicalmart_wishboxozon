<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartOzonLibrary\Service;

use UnexpectedValueException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides RadicalMart to Ozon product synchronization service injection.
 *
 * @since 1.0.0
 */
trait RadicalMartToOzonProductSyncServiceAwareTrait
{
	private ?RadicalMartToOzonProductSyncService $radicalMartToOzonProductSyncService = null;

	/**
	 * Get the configured RadicalMart Ozon product service.
	 *
	 * @return RadicalMartToOzonProductSyncService Product synchronization service.
	 *
	 * @throws UnexpectedValueException When the service has not been injected.
	 *
	 * @since 1.0.0
	 */
	public function getRadicalMartToOzonProductSyncService(): RadicalMartToOzonProductSyncService
	{
		if ($this->radicalMartToOzonProductSyncService instanceof RadicalMartToOzonProductSyncService)
		{
			return $this->radicalMartToOzonProductSyncService;
		}

		throw new UnexpectedValueException(
			'RadicalMartToOzonProductSyncService not set in ' . static::class . '.'
		);
	}

	/**
	 * Set the RadicalMart Ozon product service.
	 *
	 * @param   RadicalMartToOzonProductSyncService  $radicalMartToOzonProductSyncService  Product synchronization service.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setRadicalMartToOzonProductSyncService(
		RadicalMartToOzonProductSyncService $radicalMartToOzonProductSyncService
	): void
	{
		$this->radicalMartToOzonProductSyncService = $radicalMartToOzonProductSyncService;
	}
}
