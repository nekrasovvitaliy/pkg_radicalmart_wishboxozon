<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartOzonLibrary\Service;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the contract for classes that depend on RadicalMart to Ozon product synchronization.
 *
 * @since 1.0.0
 */
interface RadicalMartToOzonProductSyncServiceAwareInterface
{
	/**
	 * Get the configured RadicalMart Ozon product service.
	 *
	 * @return RadicalMartToOzonProductSyncService Product synchronization service.
	 *
	 * @since 1.0.0
	 */
	public function getRadicalMartToOzonProductSyncService(): RadicalMartToOzonProductSyncService;

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
	): void;
}
