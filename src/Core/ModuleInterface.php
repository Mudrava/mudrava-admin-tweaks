<?php
/**
 * Module contract.
 *
 * Every module MUST implement this interface.
 *
 * @package Mudrava\AdminTweaks\Core
 */

declare(strict_types=1);

namespace Mudrava\AdminTweaks\Core;

interface ModuleInterface {

	/**
	 * Static module metadata for discovery.
	 *
	 * Called by ModuleManifest during auto-discovery WITHOUT instantiation.
	 * Must return an array with keys: id, name, description, icon, category, requires.
	 *
	 * @return array{id: string, name: string, description: string, icon: string, category: string, requires: list<string>}
	 */
	public static function manifest(): array;

	/**
	 * Unique module identifier (slug).
	 *
	 * Example: 'login-as', 'email-log'.
	 */
	public function id(): string;

	/**
	 * Human-readable module name.
	 */
	public function name(): string;

	/**
	 * Short description of what the module does.
	 */
	public function description(): string;

	/**
	 * Lucide icon name used in the admin hub.
	 */
	public function icon(): string;

	/**
	 * Module category for grouping in the hub.
	 *
	 * @return string One of: 'security', 'tools', 'logging', or a custom slug.
	 */
	public function category(): string;

	/**
	 * IDs of other modules this module depends on.
	 *
	 * The module cannot be enabled unless all listed dependencies
	 * are already enabled. A dependency cannot be disabled while
	 * this module remains enabled.
	 *
	 * @return list<string> Module IDs, e.g. ['email-log'].
	 */
	public function requires(): array;

	/**
	 * Register WordPress hooks.
	 *
	 * Called ONLY for enabled modules during `init` (priority 0).
	 * Disabled modules never reach this method - their files are not
	 * even autoloaded.
	 */
	public function boot(): void;

	/**
	 * Run once when the module is first enabled.
	 *
	 * Use for creating database tables, registering cron events,
	 * setting default options, etc.
	 */
	public function activate(): void;

	/**
	 * Run when the module is disabled.
	 *
	 * Use for clearing cron events, transient data, etc.
	 * Should NOT delete persistent data (tables, options).
	 */
	public function deactivate(): void;

	/**
	 * Run when the entire plugin is uninstalled.
	 *
	 * Drop tables, delete options, remove all traces.
	 * Called from uninstall.php for EVERY module regardless of
	 * enabled/disabled state.
	 */
	public function uninstall(): void;

	/**
	 * URL to the module settings page, if any.
	 *
	 * @return string|null Admin URL or null if the module has no settings page.
	 */
	public function getSettingsUrl(): ?string;
}
