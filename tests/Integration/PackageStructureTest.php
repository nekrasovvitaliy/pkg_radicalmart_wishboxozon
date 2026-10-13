<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Verifies the installable package structure and migration cleanup.
 *
 * @since 1.0.0
 */
final class PackageStructureTest extends TestCase
{
	private string $projectRoot;

	/**
	 * Resolve the package project root.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected function setUp(): void
	{
		$this->projectRoot = dirname(__DIR__, 2);
	}

	/**
	 * Ensure all required extensions are included in the package manifest.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function testPackageContainsAllExtensions(): void
	{
		$manifest = $this->read('pkg_radicalmart_wishboxozon.xml');

		self::assertStringContainsString('lib_wishboxradicalmartozon.zip', $manifest);
		self::assertStringContainsString('com_wishboxradicalmartozon.zip', $manifest);
		self::assertStringContainsString('plg_radicalmart_wishboxozon.zip', $manifest);
		self::assertStringContainsString('plg_task_wishboxradicalmartozon.zip', $manifest);
	}

	/**
	 * Ensure the RadicalMart component contains only its product price setting.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function testComponentContainsOnlyProductPriceSetting(): void
	{
		$config = simplexml_load_string(
			$this->read('com_wishboxradicalmartozon/administrator/config.xml')
		);

		self::assertNotFalse($config);

		$fieldNames = [];

		foreach ($config->xpath('//field') ?: [] as $field)
		{
			if ((string) $field['type'] !== 'rules')
			{
				$fieldNames[] = (string) $field['name'];
			}
		}

		self::assertSame(['product_price_ratio'], $fieldNames);
	}

	/**
	 * Ensure the component exposes only its dashboard view.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function testComponentContainsOnlyDashboardView(): void
	{
		$viewDirectories = glob(
			$this->projectRoot . '/com_wishboxradicalmartozon/administrator/src/View/*',
			GLOB_ONLYDIR
		);

		self::assertIsArray($viewDirectories);
		self::assertSame(['Dashboard'], array_map('basename', $viewDirectories));
	}

	/**
	 * Ensure all required RadicalMart toolbar actions are registered.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function testToolbarContainsThreeOzonActions(): void
	{
		$plugin = $this->read('plg_radicalmart_wishboxozon/src/Extension/WishboxOzon.php');

		self::assertStringContainsString("'products.getids'", $plugin);
		self::assertStringContainsString("'products.update'", $plugin);
		self::assertStringContainsString("'products.updateall'", $plugin);
	}

	/**
	 * Ensure all installer scripts require Joomla 6.1.4.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function testInstallersRequireJoomla614(): void
	{
		self::assertStringContainsString("MINIMUM_JOOMLA = '6.1.4'", $this->read('script.php'));
		self::assertStringContainsString(
			"MINIMUM_JOOMLA = '6.1.4'",
			$this->read('plg_radicalmart_wishboxozon/script.php')
		);
		self::assertStringContainsString(
			"MINIMUM_JOOMLA = '6.1.4'",
			$this->read('plg_task_wishboxradicalmartozon/script.php')
		);
	}

	/**
	 * Read a project file.
	 *
	 * @param   string  $relativePath  Project-relative file path.
	 *
	 * @return string File contents.
	 *
	 * @since 1.0.0
	 */
	private function read(string $relativePath): string
	{
		$contents = file_get_contents($this->projectRoot . '/' . $relativePath);

		self::assertNotFalse($contents);

		return $contents;
	}
}
