<?php
/**
 * Uninstall handler — removes all data stored by the plugin.
 *
 * Runs only when the plugin is deleted from the Plugins screen.
 *
 * @package Mudrava\Kit
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Options are per-site (autoloaded). On multisite, uninstall runs once for
 * the network, so walk every site or rows survive forever on the others.
 */
if ( is_multisite() ) {
	foreach ( get_sites( [ 'number' => 0 ] ) as $site ) {
		switch_to_blog( (int) $site->blog_id );
		delete_option( 'mudrava_mt_admin-tweaks_settings' );
		delete_option( 'mudrava_mt_standalone_menus' );
		restore_current_blog();
	}
} else {
	delete_option( 'mudrava_mt_admin-tweaks_settings' );
	delete_option( 'mudrava_mt_standalone_menus' );
}

delete_site_option( 'mudrava_mt_admin-tweaks_settings' );
