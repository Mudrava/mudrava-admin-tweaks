<?php
/**
 * Module auto-discovery.
 *
 * Discovers available modules by scanning the Modules directory
 * at runtime. Each module subdirectory must follow the convention:
 *
 *   src/Modules/{DirName}/{DirName}.php
 *
 * where the main class implements ModuleInterface. Module availability
 * is determined solely by folder presence - NO hardcoded references.
 *
 * Discovery reads static manifest() metadata from each module class
 * WITHOUT instantiation. No hooks are registered until boot() is
 * called on enabled modules.
 *
 * @package Mudrava\AdminTweaks\Core
 */

declare(strict_types=1);

namespace Mudrava\AdminTweaks\Core;

final class ModuleManifest {

	private const NAMESPACE_PREFIX = 'Mudrava\\AdminTweaks\\Modules\\';

	/**
	 * Per-request cache of discovered modules.
	 *
	 * @var array<string, array{class: class-string<ModuleInterface>, name: string, description: string, icon: string, category: string, requires: list<string>}>|null
	 */
	private static ?array $cache = null;

	/**
	 * Return metadata for all available modules.
	 *
	 * Scans src/Modules/ for subdirectories matching the convention.
	 * Results are cached for the duration of the request.
	 *
	 * @return array<string, array{class: class-string<ModuleInterface>, name: string, description: string, icon: string, category: string, requires: list<string>}>
	 */
	public static function all(): array {
		if ( self::$cache !== null ) {
			return self::$cache;
		}

		self::$cache = [];

		$modulesDir = self::getModulesDir();

		if ( ! is_dir( $modulesDir ) ) {
			return self::$cache;
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Graceful if unreadable.
		$entries = @scandir( $modulesDir );

		if ( $entries === false ) {
			return self::$cache;
		}

		foreach ( $entries as $entry ) {
			if ( $entry === '.' || $entry === '..' ) {
				continue;
			}

			$dirPath = $modulesDir . '/' . $entry;

			if ( ! is_dir( $dirPath ) ) {
				continue;
			}

			// Convention: entry-point class file matches directory name.
			$classFile = $dirPath . '/' . $entry . '.php';

			if ( ! file_exists( $classFile ) ) {
				continue;
			}

			// Derive PSR-4 fully-qualified class name.
			$className = self::NAMESPACE_PREFIX . $entry . '\\' . $entry;

			if ( ! class_exists( $className ) ) {
				continue;
			}

			if ( ! is_subclass_of( $className, ModuleInterface::class ) ) {
				continue;
			}

			try {
				/** @var class-string<ModuleInterface> $className */
				$meta = $className::manifest();

				self::$cache[ $meta['id'] ] = [
					'class'       => $className,
					'name'        => $meta['name'],
					'description' => $meta['description'],
					'icon'        => $meta['icon'],
					'menu_icon'   => $meta['menu_icon'] ?? 'dashicons-admin-generic',
					'category'    => $meta['category'],
					'requires'    => $meta['requires'],
				];
			} catch ( \Throwable $e ) {
				// Skip broken modules - best effort discovery.
			}
		}

		return self::$cache;
	}

	/**
	 * Reset the discovery cache.
	 *
	 * Useful after enabling/disabling modules or during tests.
	 */
	public static function reset(): void {
		self::$cache = null;
	}

	/**
	 * Absolute path to the Modules directory.
	 *
	 * Derived from this file's location so it works independently
	 * of plugin constants (e.g. when called from uninstall.php).
	 */
	private static function getModulesDir(): string {
		// This file: src/Core/ModuleManifest.php
		// Target:    src/Modules/
		return dirname( __DIR__ ) . '/Modules';
	}
}
