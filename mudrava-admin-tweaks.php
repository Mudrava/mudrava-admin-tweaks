<?php
/**
 * Plugin Name:       MUDRAVA Admin Tweaks
 * Plugin URI:        https://wordpress.org/plugins/mudrava-admin-tweaks/
 * Description:       A collection of handy admin-area tweaks: branding, columns, media, security, notifications and cleanup.
 * Version:           1.1.3
 * Requires at least: 6.6
 * Requires PHP:      8.2
 * Author:            MUDRAVA
 * Author URI:        https://mudrava.com/en/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mudrava-admin-tweaks
 * Domain Path:       /languages
 *
 * Standalone WordPress admin tweaks plugin.
 *
 * @package Mudrava\AdminTweaks
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Prevent double loading if another copy already defined these constants.
 */
if ( defined( 'MUDRAVA_MT_VERSION' ) ) {
	return;
}

/*
 * Define standalone constants used by Core classes and assets.
 */
define( 'MUDRAVA_MT_VERSION',  '1.1.2' );
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
	$prefix = 'Mudrava\\AdminTweaks\\';

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

	$module = new \Mudrava\AdminTweaks\Modules\AdminTweaks\AdminTweaks();
	$module->boot();
}, 1 );

/*
 * Activation / deactivation hooks.
 */
register_activation_hook( __FILE__, static function (): void {
	$module = new \Mudrava\AdminTweaks\Modules\AdminTweaks\AdminTweaks();
	$module->activate();
} );

register_deactivation_hook( __FILE__, static function (): void {
	$module = new \Mudrava\AdminTweaks\Modules\AdminTweaks\AdminTweaks();
	$module->deactivate();
} );
