<?php
/**
 * Base module class.
 *
 * Provides shared helpers so concrete modules don't repeat boilerplate.
 * Extend this instead of implementing ModuleInterface directly.
 *
 * @package Mudrava\Kit\Core
 */

declare(strict_types=1);

namespace Mudrava\Kit\Core;

abstract class AbstractModule implements ModuleInterface {

	/** @var ?array<string,mixed> Instance-level cache for module options. */
	private ?array $optionsCache = null;

	/* ------------------------------------------------------------------
	 * Default metadata — delegates to manifest() to avoid duplication.
	 * Subclasses MUST implement manifest().
	 * ----------------------------------------------------------------*/

	public function id(): string {
		return static::manifest()['id'];
	}

	public function name(): string {
		return static::manifest()['name'];
	}

	public function description(): string {
		return static::manifest()['description'];
	}

	public function icon(): string {
		return static::manifest()['icon'];
	}

	public function category(): string {
		return static::manifest()['category'];
	}

	/* ------------------------------------------------------------------
	 * Default implementations (override when needed)
	 * ----------------------------------------------------------------*/

	public function activate(): void {}

	public function deactivate(): void {}

	public function uninstall(): void {}

	/**
	 * No dependencies by default.
	 *
	 * @return list<string>
	 */
	public function requires(): array {
		return [];
	}

	public function getSettingsUrl(): ?string {
		return null;
	}

	/* ------------------------------------------------------------------
	 * Helpers available to every module
	 * ----------------------------------------------------------------*/

	/**
	 * Return the filesystem path to the module directory.
	 */
	protected function dir(): string {
		$ref = new \ReflectionClass( static::class );

		return trailingslashit( dirname( (string) $ref->getFileName() ) );
	}

	/**
	 * Render a PHP view template and return the HTML.
	 *
	 * Templates live in `<ModuleDir>/views/<name>.php`.
	 *
	 * @param string               $template Template filename without extension.
	 * @param array<string, mixed> $data     Variables to extract into the template scope.
	 */
	protected function renderView( string $template, array $data = [] ): string {
		$file = $this->dir() . 'views/' . $template . '.php';

		if ( ! file_exists( $file ) ) {
			return '';
		}

		ob_start();
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Controlled template rendering.
		extract( $data, EXTR_SKIP );
		include $file;

		return (string) ob_get_clean();
	}

	/**
	 * Echo a PHP view template directly.
	 *
	 * @param string               $template Template filename without extension.
	 * @param array<string, mixed> $data     Variables to extract into the template scope.
	 */
	protected function displayView( string $template, array $data = [] ): void {
		$file = $this->dir() . 'views/' . $template . '.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Controlled template rendering.
		extract( $data, EXTR_SKIP );
		include $file;
	}

	/**
	 * Build an admin page URL for this module.
	 *
	 * @param string               $page  Page slug.
	 * @param array<string, mixed> $args  Additional query args.
	 */
	protected function adminUrl( string $page, array $args = [] ): string {
		$args['page'] = $page;

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Get a module-specific option.
	 *
	 * Stored in `mudrava_mt_{module_id}_settings`.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Default value.
	 */
	protected function getOption( string $key, mixed $default = null ): mixed {
		$options = $this->getOptions();

		return $options[ $key ] ?? $default;
	}

	/**
	 * Set a module-specific option.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Value.
	 */
	protected function setOption( string $key, mixed $value ): void {
		$options         = $this->getOptions();
		$options[ $key ] = $value;
		$this->saveOptions( $options );
	}

	/**
	 * Get all module options.
	 *
	 * Uses instance-level cache to avoid repeated get_option() calls.
	 *
	 * @return array<string, mixed>
	 */
	protected function getOptions(): array {
		if ( $this->optionsCache !== null ) {
			return $this->optionsCache;
		}

		$options = get_option( 'mudrava_mt_' . $this->id() . '_settings', [] );

		$this->optionsCache = is_array( $options ) ? $options : [];

		return $this->optionsCache;
	}

	/**
	 * Save all module options at once.
	 *
	 * Invalidates the instance cache after writing.
	 *
	 * @param array<string, mixed> $options Options array.
	 */
	protected function saveOptions( array $options ): void {
		update_option( 'mudrava_mt_' . $this->id() . '_settings', $options, true );
		$this->optionsCache = $options;
	}

	/**
	 * Create a nonce for a module action.
	 *
	 * @param string $action Full action identifier (NOT auto-prefixed).
	 */
	protected function createNonce( string $action ): string {
		return wp_create_nonce( $action );
	}

	/**
	 * Verify a nonce for a module action.
	 *
	 * Reads the nonce value from `$_REQUEST['_wpnonce']` automatically.
	 *
	 * @param string $action Full action identifier (NOT auto-prefixed).
	 */
	protected function verifyNonce( string $action ): bool {
		$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return (bool) wp_verify_nonce( $nonce, $action );
	}

	/* ------------------------------------------------------------------
	 * Asset helpers
	 * ----------------------------------------------------------------*/

	/**
	 * Get the URL to the module directory.
	 */
	protected function moduleUrl(): string {
		$moduleDir = $this->dir();
		$pluginDir = MUDRAVA_MT_DIR;
		$relative  = str_replace( wp_normalize_path( $pluginDir ), '', wp_normalize_path( $moduleDir ) );

		return MUDRAVA_MT_URL . $relative;
	}

	/**
	 * Enqueue a module-specific stylesheet.
	 *
	 * Files are expected in `<ModuleDir>/assets/<filename>`.
	 *
	 * @param string        $handle   Style handle (auto-prefixed with `mudrava-mt-`).
	 * @param string        $filename CSS filename inside assets/.
	 * @param array<string> $deps     Dependency handles.
	 */
	protected function enqueueStyle( string $handle, string $filename, array $deps = [] ): void {
		wp_enqueue_style(
			'mudrava-mt-' . $handle,
			$this->moduleUrl() . 'assets/' . $filename,
			$deps,
			MUDRAVA_MT_VERSION,
		);
	}

	/**
	 * Enqueue a module-specific script.
	 *
	 * Files are expected in `<ModuleDir>/assets/<filename>`.
	 *
	 * @param string        $handle   Script handle (auto-prefixed with `mudrava-mt-`).
	 * @param string        $filename JS filename inside assets/.
	 * @param array<string> $deps     Dependency handles.
	 * @param bool          $inFooter Whether to load in footer.
	 */
	protected function enqueueScript( string $handle, string $filename, array $deps = [], bool $inFooter = true ): void {
		wp_enqueue_script(
			'mudrava-mt-' . $handle,
			$this->moduleUrl() . 'assets/' . $filename,
			$deps,
			MUDRAVA_MT_VERSION,
			$inFooter,
		);
	}

	/**
	 * Register a submenu page under the MUDRAVA Kit menu.
	 *
	 * If the module is marked as "standalone" (via `mudrava_mt_standalone_menus`
	 * option), it will be registered as a top-level admin menu item instead.
	 *
	 * @param string   $title    Page title.
	 * @param string   $slug     Unique page slug (auto-prefixed with `mudrava-mt-`).
	 * @param callable $renderer Render callback.
	 * @param string   $capability Required capability. Defaults to `manage_options`.
	 */
	protected function addSubmenuPage(
		string $title,
		string $slug,
		callable $renderer,
		string $capability = 'manage_options',
	): void {
		$fullSlug = 'mudrava-mt-' . $slug;

		/*
		 * Standalone build (no Kit hub present): register a dedicated
		 * top-level menu and stop — there is no hub parent to attach to.
		 */
		if ( ! class_exists( ModuleRegistry::class ) ) {
			$manifest = $this->manifest();

			add_menu_page(
				$title,
				$title,
				$capability,
				$fullSlug,
				$renderer,
				$manifest['menu_icon'] ?? 'dashicons-admin-generic',
				66,
			);

			return;
		}

		if ( self::isStandalone( $this->id() ) ) {
			$manifest = $this->manifest();
			$menuIcon = $manifest['menu_icon'] ?? 'dashicons-admin-generic';

			add_menu_page(
				$title,
				$title,
				$capability,
				$fullSlug,
				$renderer,
				$menuIcon,
				66,
			);
		}

		// Always register as submenu too — WordPress needs it for the parent link.
		add_submenu_page(
			'mudrava-admin-tweaks',
			$title,
			$title,
			$capability,
			$fullSlug,
			$renderer,
		);
	}

	/**
	 * Check whether a module is registered as a standalone admin menu item.
	 *
	 * @param string $moduleId Module ID.
	 */
	public static function isStandalone( string $moduleId ): bool {
		$standaloneIds = get_option( 'mudrava_mt_standalone_menus', [] );

		return ( is_array( $standaloneIds ) && in_array( $moduleId, $standaloneIds, true ) )
			|| ( class_exists( ModuleRegistry::class )
				&& ModuleRegistry::hasActiveStandaloneCompanion( $moduleId ) );
	}

	/**
	 * Register a page under any WordPress admin menu parent.
	 *
	 * @param string   $title      Page title.
	 * @param string   $slug       Unique page slug (auto-prefixed with `mudrava-mt-`).
	 * @param callable $renderer   Render callback.
	 * @param string   $parent     Parent menu slug (e.g. 'options-general.php', 'tools.php').
	 * @param string   $capability Required capability.
	 */
	protected function addSettingsPage(
		string $title,
		string $slug,
		callable $renderer,
		string $parent = 'options-general.php',
		string $capability = 'manage_options',
	): void {
		add_submenu_page(
			$parent,
			$title,
			$title,
			$capability,
			'mudrava-mt-' . $slug,
			$renderer,
		);
	}
}
