<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace Joomla\Plugin\Task\WishboxRadicalMartOzon\Extension;

use Exception;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use Throwable;
use WishboxRadicalMartOzonLibrary\Service\RadicalMartToOzonProductSyncServiceAwareInterface;
use WishboxRadicalMartOzonLibrary\Service\RadicalMartToOzonProductSyncServiceAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides scheduled product synchronization between RadicalMart and Ozon.
 *
 * @since 1.0.0
 */
final class WishboxRadicalMartOzon extends CMSPlugin implements
	SubscriberInterface,
	RadicalMartToOzonProductSyncServiceAwareInterface
{
	use TaskPluginTrait;
	use RadicalMartToOzonProductSyncServiceAwareTrait;

	protected const array TASKS_MAP = [
		'plg_task_wishboxradicalmartozon_update_products' => [
			'langConstPrefix' => 'PLG_TASK_WISHBOXRADICALMARTOZON_UPDATE_PRODUCTS',
			'method'          => 'updateProducts',
		],
	];

	/**
	 * Load the language file on instantiation.
	 *
	 * @var bool
	 *
	 * @since 1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Return the Joomla events handled by this plugin.
	 *
	 * @return array<string, string> Event handler map.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList' => 'advertiseRoutines',
			'onExecuteTask'     => 'standardRoutineHandler',
		];
	}

	/**
	 * Synchronize all linked RadicalMart products with Ozon.
	 *
	 * @param   ExecuteTaskEvent  $event  Scheduler execution event.
	 *
	 * @return int Scheduler status code.
	 *
	 * @throws Exception When task logging fails.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	private function updateProducts(ExecuteTaskEvent $event): int
	{
		try
		{
			$result = $this->getRadicalMartToOzonProductSyncService()
				->updateProducts();

			$this->logTask(
				Text::sprintf(
					'PLG_TASK_WISHBOXRADICALMARTOZON_UPDATE_PRODUCTS_RESULT',
					$result->updated,
					$result->skipped,
					$result->failed
				),
				$result->failed > 0 ? 'warning' : 'info'
			);

			foreach ($result->errors as $message)
			{
				$this->logTask($message, 'error');
			}

			return $result->failed > 0 ? Status::KNOCKOUT : Status::OK;
		}
		catch (Throwable $throwable)
		{
			$this->logTask((string) $throwable, 'error');

			return Status::KNOCKOUT;
		}
	}
}
