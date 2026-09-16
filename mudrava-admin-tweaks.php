<?php
/**
 * Plugin Name:       MUDRAVA Admin Tweaks
 * Plugin URI:        https://mudrava.com/en/
 * Description:       A collection of handy admin-area tweaks: branding, columns, media, security, notifications and cleanup.
 * Version:           1.1.1
 * Requires at least: 6.6
 * Requires PHP:      8.2
 * Author:            MUDRAVA
 * Author URI:        https://mudrava.com/en/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mudrava-admin-tweaks
 * Domain Path:       /languages
 *
 * Standalone module extracted from MUDRAVA Kit.
 *
 * @package Mudrava\Kit
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mudravaMtIsPluginActive = static function ( string $pluginFile ): bool {
	$active = get_option( 'active_plugins', [] );

	if ( is_array( $active ) && in_array( $pluginFile, $active, true ) ) {
		return true;
	}

	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		$networkActive = get_site_option( 'active_sitewide_plugins', [] );

		return is_array( $networkActive ) && isset( $networkActive[ $pluginFile ] );
	}

	return false;
};

/*
 * Guard: if the full MUDRAVA Kit is active, skip this standalone loader.
 * The hub already registers and boots this module.
 */
if ( defined( 'MUDRAVA_MT_VERSION' ) || $mudravaMtIsPluginActive( 'mudrava-kit/mudrava-kit.php' ) ) {
	add_action( 'admin_notices', static function (): void {
		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'MUDRAVA Admin Tweaks is already included in MUDRAVA Kit. You can deactivate this standalone plugin.', 'mudrava-admin-tweaks' )
		);
	} );
	return;
}

unset( $mudravaMtIsPluginActive );

/*
 * Define hub-compatible constants so Core classes work unchanged.
 */
define( 'MUDRAVA_MT_VERSION',  '1.1.1' );
define( 'MUDRAVA_MT_FILE',     __FILE__ );
define( 'MUDRAVA_MT_DIR',      plugin_dir_path( __FILE__ ) );
define( 'MUDRAVA_MT_URL',      plugin_dir_url( __FILE__ ) );
define( 'MUDRAVA_MT_BASENAME', plugin_basename( __FILE__ ) );

/*
 * Minimum PHP check.
 */
if ( PHP_VERSION_ID < 80200 ) {
	add_action( 'admin_notices', static function (): void {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'MUDRAVA Admin Tweaks requires PHP 8.2 or higher.', 'mudrava-admin-tweaks' )
		);
	} );
	return;
}

/*
 * PSR-4 autoloader for the bundled Core + Module classes.
 */
spl_autoload_register( static function ( string $class ): void {
	$prefix = 'Mudrava\\Kit\\';

	if ( ! str_starts_with( $class, $prefix ) ) {
		return;
	}

	$relative = str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) );
	$file     = MUDRAVA_MT_DIR . 'src/' . $relative . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/*
 * Boot the module on init so translated strings are first resolved at init.
 * The menu page is registered by the module itself (standalone mode).
 */
add_action( 'init', static function (): void {
	load_plugin_textdomain(
		'mudrava-admin-tweaks',
		false,
		dirname( MUDRAVA_MT_BASENAME ) . '/languages'
	);

	$module = new \Mudrava\Kit\Modules\AdminTweaks\AdminTweaks();
	$module->boot();
}, 1 );

/*
 * Activation / deactivation hooks.
 */
register_activation_hook( __FILE__, static function (): void {
	$module = new \Mudrava\Kit\Modules\AdminTweaks\AdminTweaks();
	$module->activate();
} );

register_deactivation_hook( __FILE__, static function (): void {
	$module = new \Mudrava\Kit\Modules\AdminTweaks\AdminTweaks();
	$module->deactivate();
} );
