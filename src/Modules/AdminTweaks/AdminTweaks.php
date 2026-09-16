<?php
/**
 * Admin Tweaks module.
 *
 * A comprehensive collection of small but impactful admin-area
 * tweaks: branding, column helpers, media controls, security
 * hardening, notification management and general cleanup.
 *
 * @package Mudrava\Kit\Modules\AdminTweaks
 */

declare( strict_types=1 );

namespace Mudrava\Kit\Modules\AdminTweaks;

use Mudrava\Kit\Core\AbstractModule;
use Mudrava\Kit\Core\AdminUI;

final class AdminTweaks extends AbstractModule {

	/* ------------------------------------------------------------------
	 * Manifest
	 * ----------------------------------------------------------------*/

	public static function manifest(): array {
		return [
			'id'          => 'admin-tweaks',
			'name'        => __( 'Admin Tweaks', 'mudrava-admin-tweaks' ),
			'description' => __( 'A collection of handy admin-area tweaks: branding, columns, media, security, notifications and cleanup.', 'mudrava-admin-tweaks' ),
			'icon'        => 'settings',
			'menu_icon'   => 'dashicons-admin-settings',
			'category'    => 'tools',
			'tags' => [ 'admin', 'branding', 'security', 'cleanup' ],
			'features' => [
				__( 'Custom login/logout redirect URLs', 'mudrava-admin-tweaks' ),
				__( 'Custom footer text and admin bar logo', 'mudrava-admin-tweaks' ),
				__( 'Disable big image scaling and upload folders', 'mudrava-admin-tweaks' ),
				__( 'Hide WP version and disable file editing', 'mudrava-admin-tweaks' ),
				__( 'Disable update/health emails and notifications', 'mudrava-admin-tweaks' ),
				__( 'Limit revisions, empty trash, hide admin bar', 'mudrava-admin-tweaks' ),
			],
			'requires' => [],
		];
	}

	public function getSettingsUrl(): ?string {
		return admin_url( 'admin.php?page=mudrava-mt-admin-tweaks' );
	}

	/* ------------------------------------------------------------------
	 * Lifecycle
	 * ----------------------------------------------------------------*/

	public function boot(): void {
		$opts = array_merge( $this->getDefaults(), $this->getOptions() );

		/* ---- Admin menu & assets & AJAX ---- */
		add_action( 'admin_menu', [ $this, 'registerMenu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
		add_action( 'wp_ajax_mudrava_mt_admin_tweaks_save', [ $this, 'ajaxSave' ] );
		add_action( 'wp_ajax_mudrava_mt_admin_tweaks_force_logout', [ $this, 'ajaxForceLogout' ] );
		add_action( 'wp_ajax_mudrava_mt_admin_tweaks_send_test', [ $this, 'ajaxSendTestEmail' ] );

		/* ---- General ---- */
		if ( $opts['change_admin_email_without_confirm'] ) {
			$this->skipAdminEmailConfirm();
		}
		if ( $opts['change_user_email_without_confirm'] ) {
			$this->skipUserEmailConfirm();
		}
		if ( $opts['allow_username_change'] ) {
			$this->enableUsernameEdit();
		}
		if ( $opts['disable_fullscreen_editor'] ) {
			$this->disableFullscreenEditor();
		}
		if ( $opts['redirect_after_login'] !== '' ) {
			$this->setupLoginRedirect( $opts['redirect_after_login'] );
		}
		if ( $opts['redirect_after_logout'] !== '' ) {
			$this->setupLogoutRedirect( $opts['redirect_after_logout'] );
		}

		/* ---- Branding ---- */
		if ( $opts['footer_text'] !== '' || (int) $opts['footer_logo_id'] > 0 ) {
			$this->customFooterText( $opts );
		}
		if ( $opts['hide_wp_logo_admin_bar'] || (int) $opts['admin_bar_logo_id'] > 0 ) {
			$this->customizeAdminBarLogo( $opts );
		}
		if ( $opts['visit_site_new_tab'] ) {
			$this->visitSiteNewTab();
		}

		/* ---- Columns ---- */
		$needsAdminColumnStyles = false;
		if ( $opts['show_id_column'] ) {
			$this->addIdColumns();
			$needsAdminColumnStyles = true;
		}
		if ( ! empty( $opts['featured_image_post_types'] ) ) {
			$this->addFeaturedImageColumns( (array) $opts['featured_image_post_types'] );
			$needsAdminColumnStyles = true;
		}
		if ( $opts['show_modified_date_column'] ) {
			$this->addModifiedDateColumn();
			$needsAdminColumnStyles = true;
		}
		if ( $needsAdminColumnStyles ) {
			$this->enqueueAdminColumnStyles();
		}

		/* ---- Media ---- */
		if ( $opts['disable_big_image_scaling'] ) {
			add_filter( 'big_image_size_threshold', '__return_false' );
		}
		if ( $opts['disable_year_month_folders'] ) {
			add_filter( 'pre_option_uploads_use_yearmonth_folders', '__return_zero' );
		}
		if ( $opts['randomize_upload_filenames'] ) {
			$this->randomizeFilenames();
		} elseif ( $opts['rename_file_to_post_title'] ) {
			$this->renameToPostTitle();
		}
		if ( $opts['disable_avatars'] ) {
			$this->disableAvatars();
		}

		/* ---- Security ---- */
		if ( $opts['hide_wp_version'] ) {
			$this->hideWpVersion();
		}
		if ( $opts['disable_file_editing'] ) {
			/*
			 * Core passes contexts like 'capability_edit_themes' through
			 * wp_is_file_mod_allowed(), so context matching is fragile.
			 * Strip the primitive caps outright — same mechanism core
			 * uses when DISALLOW_FILE_EDIT is defined. Editor pages
			 * (plugin/theme/file) gate on these caps and die; menu items
			 * registered with them disappear.
			 */
			add_filter( 'user_has_cap', static function ( array $allcaps ): array {
				unset( $allcaps['edit_plugins'], $allcaps['edit_themes'], $allcaps['edit_files'] );
				return $allcaps;
			}, 999 );
		}
		if ( $opts['disable_rest_users_endpoint'] ) {
			$this->disableRestUsersEndpoint();
		}
		if ( $opts['remove_rest_api_link'] ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
		}
		if ( $opts['remove_x_pingback_header'] ) {
			$this->removeXPingbackHeader();
		}
		if ( $opts['disable_search_frontend'] ) {
			$this->disableFrontendSearch();
		}

		/* ---- Notifications ---- */
		if ( $opts['disable_site_health_emails'] ) {
			$this->disableSiteHealthEmails();
		}
		if ( $opts['disable_update_emails'] ) {
			$this->disableUpdateEmails();
		}
		if ( $opts['disable_email_verification_screen'] ) {
			add_filter( 'admin_email_check_interval', '__return_zero' );
		}
		if ( $opts['disable_new_user_admin_email'] ) {
			$this->disableNewUserAdminEmail();
		}
		if ( $opts['disable_password_change_admin_email'] ) {
			add_filter( 'send_password_change_email', '__return_false' );
		}

		/* ---- Cleanup ---- */
		$revisions = (int) $opts['limit_post_revisions'];
		if ( $revisions >= 0 ) {
			add_filter( 'wp_revisions_to_keep', static fn () => $revisions, 10, 0 );
		}

		$trashDays = (int) $opts['trash_auto_delete_days'];
		if ( $trashDays !== 30 ) {
			$this->setupTrashDays( $trashDays );
		}

		$barMode = (string) $opts['hide_admin_bar_frontend'];
		if ( $barMode !== 'none' ) {
			$this->setupHideAdminBar( $barMode );
		}
		if ( $opts['disable_rss_feeds'] ) {
			$this->disableRssFeeds();
		}
		if ( $opts['remove_category_prefix'] || $opts['remove_tag_prefix'] || $opts['remove_author_prefix'] ) {
			$this->removeArchivePrefixes( $opts );
		}
	}

	public function activate(): void {
		if ( get_option( 'mudrava_mt_admin-tweaks_settings' ) === false ) {
			update_option( 'mudrava_mt_admin-tweaks_settings', $this->getDefaults(), true );
		}
	}

	public function uninstall(): void {
		delete_option( 'mudrava_mt_admin-tweaks_settings' );
	}

	/* ------------------------------------------------------------------
	 * Admin menu & page
	 * ----------------------------------------------------------------*/

	public function registerMenu(): void {
		$this->addSubmenuPage(
			__( 'Admin Tweaks', 'mudrava-admin-tweaks' ),
			'admin-tweaks',
			[ $this, 'renderPage' ],
		);
	}

	/**
	 * Enqueue base + module assets on the settings page only.
	 *
	 * Must run on `admin_enqueue_scripts` (before `admin_head`) so the
	 * tags actually reach the document, and must localize the AJAX
	 * config (ajax URL + nonce) consumed by admin.js / admin-tweaks.js.
	 */
	public function enqueueAssets( string $hookSuffix ): void {
		if ( ! str_contains( $hookSuffix, 'mudrava-mt-admin-tweaks' ) ) {
			return;
		}

		wp_enqueue_style(
			'mudrava-mt-admin',
			MUDRAVA_MT_URL . 'assets/css/admin.css',
			[],
			MUDRAVA_MT_VERSION,
		);

		wp_enqueue_script(
			'mudrava-mt-admin',
			MUDRAVA_MT_URL . 'assets/js/admin.js',
			[],
			MUDRAVA_MT_VERSION,
			true,
		);

		wp_localize_script(
			'mudrava-mt-admin',
			'mudravaAdminTweaksAdmin',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mudrava_mt_admin' ),
				'i18n'    => [
					'saving'             => __( 'Saving…', 'mudrava-admin-tweaks' ),
					'sending'            => __( 'Sending…', 'mudrava-admin-tweaks' ),
					'processing'         => __( 'Processing…', 'mudrava-admin-tweaks' ),
					'error'              => __( 'Something went wrong. Please try again.', 'mudrava-admin-tweaks' ),
					'confirmForceLogout' => __( 'Force logout all other users? They will need to log in again.', 'mudrava-admin-tweaks' ),
					'networkError'       => __( 'Network error.', 'mudrava-admin-tweaks' ),
					'enterEmail'         => __( 'Please enter an email address.', 'mudrava-admin-tweaks' ),
				],
			]
		);

		wp_enqueue_media();

		$this->enqueueStyle( 'admin-tweaks', 'admin-tweaks.css', [ 'mudrava-mt-admin' ] );
		$this->enqueueScript( 'admin-tweaks', 'admin-tweaks.js', [ 'mudrava-mt-admin' ] );
	}

	public function renderPage(): void {
		$options = array_merge( $this->getDefaults(), $this->getOptions() );

		$tabs = [
			'general'       => __( 'General', 'mudrava-admin-tweaks' ),
			'branding'      => __( 'Branding', 'mudrava-admin-tweaks' ),
			'columns'       => __( 'Admin Columns', 'mudrava-admin-tweaks' ),
			'media'         => __( 'Media & Uploads', 'mudrava-admin-tweaks' ),
			'security'      => __( 'Security & Privacy', 'mudrava-admin-tweaks' ),
			'notifications' => __( 'Notifications', 'mudrava-admin-tweaks' ),
			'cleanup'       => __( 'Cleanup', 'mudrava-admin-tweaks' ),
		];

		echo wp_kses(
			AdminUI::pageHeader(
			__( 'Admin Tweaks', 'mudrava-admin-tweaks' ),
			__( 'A collection of handy tweaks for the WordPress admin area.', 'mudrava-admin-tweaks' ),
			$tabs,
			'general',
			'settings',
			),
			AdminUI::allowedHtml(),
		);

		/* Post types that support thumbnails (for columns tab) */
		$postTypes = [];
		foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $pt ) {
			if ( post_type_supports( $pt->name, 'thumbnail' ) ) {
				$postTypes[ $pt->name ] = $pt->labels->name;
			}
		}

		$this->displayView( 'settings', [
			'mudrava_admin_tweaks_options'    => $options,
			'mudrava_admin_tweaks_post_types' => $postTypes,
		] );

		echo wp_kses( AdminUI::pageFooter(), AdminUI::allowedHtml() );
	}

	/* ------------------------------------------------------------------
	 * AJAX: Save settings (tab-aware)
	 * ----------------------------------------------------------------*/

	public function ajaxSave(): void {
		check_ajax_referer( 'mudrava_mt_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'You are not allowed to do this.', 'mudrava-admin-tweaks' ) ], 403 );
		}

		$tab     = sanitize_key( wp_unslash( $_POST['tab'] ?? '' ) );
		$current = array_merge( $this->getDefaults(), $this->getOptions() );

		$fields = match ( $tab ) {
			'general' => [
				'change_admin_email_without_confirm' => ! empty( $_POST['change_admin_email_without_confirm'] ),
				'change_user_email_without_confirm'  => ! empty( $_POST['change_user_email_without_confirm'] ),
				'allow_username_change'              => ! empty( $_POST['allow_username_change'] ),
				'disable_fullscreen_editor'          => ! empty( $_POST['disable_fullscreen_editor'] ),
				'redirect_after_login'               => esc_url_raw( wp_unslash( $_POST['redirect_after_login'] ?? '' ) ),
				'redirect_after_logout'              => esc_url_raw( wp_unslash( $_POST['redirect_after_logout'] ?? '' ) ),
			],
			'branding' => [
				'footer_text'            => wp_kses_post( wp_unslash( $_POST['footer_text'] ?? '' ) ),
				'footer_logo_id'         => absint( wp_unslash( $_POST['footer_logo_id'] ?? 0 ) ),
				'admin_bar_logo_id'      => absint( wp_unslash( $_POST['admin_bar_logo_id'] ?? 0 ) ),
				'admin_bar_logo_url'     => esc_url_raw( wp_unslash( $_POST['admin_bar_logo_url'] ?? '' ) ),
				'hide_wp_logo_admin_bar' => ! empty( $_POST['hide_wp_logo_admin_bar'] ),
				'visit_site_new_tab'     => ! empty( $_POST['visit_site_new_tab'] ),
			],
			'columns' => $this->sanitizeColumnsTab(),
			'media' => [
				'disable_big_image_scaling'  => ! empty( $_POST['disable_big_image_scaling'] ),
				'disable_year_month_folders' => ! empty( $_POST['disable_year_month_folders'] ),
				'randomize_upload_filenames' => ! empty( $_POST['randomize_upload_filenames'] ),
				'rename_file_to_post_title'  => ! empty( $_POST['rename_file_to_post_title'] ),
				'disable_avatars'            => ! empty( $_POST['disable_avatars'] ),
			],
			'security' => [
				'hide_wp_version'              => ! empty( $_POST['hide_wp_version'] ),
				'disable_file_editing'         => ! empty( $_POST['disable_file_editing'] ),
				'disable_rest_users_endpoint'  => ! empty( $_POST['disable_rest_users_endpoint'] ),
				'remove_rest_api_link'         => ! empty( $_POST['remove_rest_api_link'] ),
				'remove_x_pingback_header'     => ! empty( $_POST['remove_x_pingback_header'] ),
				'disable_search_frontend'      => ! empty( $_POST['disable_search_frontend'] ),
			],
			'notifications' => [
				'disable_site_health_emails'          => ! empty( $_POST['disable_site_health_emails'] ),
				'disable_update_emails'               => ! empty( $_POST['disable_update_emails'] ),
				'disable_email_verification_screen'   => ! empty( $_POST['disable_email_verification_screen'] ),
				'disable_new_user_admin_email'        => ! empty( $_POST['disable_new_user_admin_email'] ),
				'disable_password_change_admin_email' => ! empty( $_POST['disable_password_change_admin_email'] ),
			],
			'cleanup' => [
				'limit_post_revisions'    => max( -1, (int) sanitize_text_field( wp_unslash( $_POST['limit_post_revisions'] ?? -1 ) ) ),
				'trash_auto_delete_days'  => max( 0, min( 365, (int) sanitize_text_field( wp_unslash( $_POST['trash_auto_delete_days'] ?? 30 ) ) ) ),
				'hide_admin_bar_frontend' => in_array( sanitize_key( wp_unslash( $_POST['hide_admin_bar_frontend'] ?? '' ) ), [ 'none', 'all', 'non_admins' ], true )
					? sanitize_key( wp_unslash( $_POST['hide_admin_bar_frontend'] ) )
					: 'none',
				'disable_rss_feeds'       => ! empty( $_POST['disable_rss_feeds'] ),
				'remove_category_prefix'  => ! empty( $_POST['remove_category_prefix'] ),
				'remove_tag_prefix'       => ! empty( $_POST['remove_tag_prefix'] ),
				'remove_author_prefix'    => ! empty( $_POST['remove_author_prefix'] ),
			],
			default => [],
		};

		if ( empty( $fields ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid tab.', 'mudrava-admin-tweaks' ) ], 400 );
		}

		$this->saveOptions( array_merge( $current, $fields ) );
		wp_send_json_success( [ 'message' => __( 'Settings saved.', 'mudrava-admin-tweaks' ) ] );
	}

	/**
	 * Sanitize the Columns tab fields.
	 */
	private function sanitizeColumnsTab(): array {
		// Nonce verified in ajaxSave().
		$selectedTypes = [];
		$publicTypes   = get_post_types( [ 'public' => true ] );

		foreach ( $publicTypes as $type ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in ajaxSave().
			if ( post_type_supports( $type, 'thumbnail' ) && ! empty( $_POST[ 'feat_' . $type ] ) ) {
				$selectedTypes[] = $type;
			}
		}

		return [
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in ajaxSave().
			'show_id_column'             => ! empty( $_POST['show_id_column'] ),
			'featured_image_post_types'  => $selectedTypes,
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in ajaxSave().
			'show_modified_date_column'  => ! empty( $_POST['show_modified_date_column'] ),
		];
	}

	/* ------------------------------------------------------------------
	 * AJAX: Force logout all users
	 * ----------------------------------------------------------------*/

	public function ajaxForceLogout(): void {
		check_ajax_referer( 'mudrava_mt_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'You are not allowed to do this.', 'mudrava-admin-tweaks' ) ], 403 );
		}

		$currentUserId = get_current_user_id();
		$currentToken  = wp_get_session_token();

		/*
		 * Destroy sessions through the token API (per user): raw usermeta
		 * deletes hit every site in a multisite network (usermeta is
		 * global) and wp_cache_flush() hammers persistent caches.
		 * The acting admin keeps the current token only.
		 */
		$userIds = get_users( [
			'fields'  => 'ID',
			'number'  => 1000,
			'blog_id' => get_current_blog_id(),
		] );

		foreach ( $userIds as $userId ) {
			$manager = \WP_Session_Tokens::get_instance( (int) $userId );
			if ( (int) $userId === $currentUserId ) {
				$manager->destroy_others( $currentToken );
			} else {
				$manager->destroy_all();
			}
		}

		wp_send_json_success( [
			'message' => __( 'All other users have been logged out.', 'mudrava-admin-tweaks' ),
		] );
	}

	/* ------------------------------------------------------------------
	 * AJAX: Send test email
	 * ----------------------------------------------------------------*/

	public function ajaxSendTestEmail(): void {
		check_ajax_referer( 'mudrava_mt_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'You are not allowed to do this.', 'mudrava-admin-tweaks' ) ], 403 );
		}

		$to = sanitize_email( wp_unslash( $_POST['test_email'] ?? '' ) );
		if ( ! is_email( $to ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid email address.', 'mudrava-admin-tweaks' ) ] );
		}

		$siteName = get_bloginfo( 'name' );
		$subject  = sprintf(
			/* translators: %s = site name */
			__( '[%s] Test Email', 'mudrava-admin-tweaks' ),
			$siteName,
		);
		$body = sprintf(
			/* translators: %1$s site name, %2$s timestamp */
			__( "This is a test email sent from %1\$s.\n\nTimestamp: %2\$s\nSent by: Admin Tweaks module (MUDRAVA Kit)", 'mudrava-admin-tweaks' ),
			$siteName,
			current_time( 'Y-m-d H:i:s' ),
		);

		$sent = wp_mail( $to, $subject, $body );

		if ( $sent ) {
			wp_send_json_success( [
				'message' => sprintf(
					/* translators: %s = recipient email */
					__( 'Test email sent successfully to %s.', 'mudrava-admin-tweaks' ),
					$to,
				),
			] );
		} else {
			wp_send_json_error( [
				'message' => __( 'Failed to send email. Check your server mail configuration.', 'mudrava-admin-tweaks' ),
			] );
		}
	}

	/* ==================================================================
	 * GENERAL TAB — hook methods
	 * ================================================================*/

	/**
	 * Skip the email confirmation step when changing site admin email.
	 */
	private function skipAdminEmailConfirm(): void {
		$apply = static function ( $oldValue, $value ): void {
			if ( is_string( $value ) && is_email( $value ) ) {
				update_option( 'admin_email', $value );
				delete_option( 'new_admin_email' );
				delete_option( 'adminhash' );
			}
		};

		add_action( 'add_option_new_admin_email', $apply, 10, 2 );
		add_action( 'update_option_new_admin_email', $apply, 10, 2 );
	}

	/**
	 * Skip the email confirmation step when a user changes their own email.
	 */
	private function skipUserEmailConfirm(): void {
		remove_action( 'personal_options_update', 'send_confirmation_on_profile_email' );
	}

	/**
	 * Allow editing the username field on profile pages.
	 */
	private function enableUsernameEdit(): void {
		$this->enqueueUsernameEditScript();
		add_action( 'personal_options_update', [ $this, 'handleUsernameChange' ] );
		add_action( 'edit_user_profile_update', [ $this, 'handleUsernameChange' ] );
	}

	/**
	 * Enqueue the profile-page script that unlocks the username field.
	 *
	 * Only for administrators — the server-side handler rejects the
	 * change for anyone else, so unlocking the field for them would
	 * just be confusing UX.
	 */
	private function enqueueUsernameEditScript(): void {
		add_action(
			'admin_enqueue_scripts',
			function ( string $hookSuffix ): void {
				if ( ! in_array( $hookSuffix, [ 'profile.php', 'user-edit.php' ], true ) ) {
					return;
				}

				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				$this->enqueueScript( 'admin-tweaks-username-edit', 'username-edit.js' );
			},
		);
	}

	/**
	 * Process a username change on profile save.
	 */
	public function handleUsernameChange( int $userId ): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress profile update handler.
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['user_login'] ) ) {
			return;
		}

		$newLogin = sanitize_user( wp_unslash( $_POST['user_login'] ), true );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$user     = get_userdata( $userId );

		if ( ! $user || $newLogin === $user->user_login || $newLogin === '' ) {
			return;
		}

		$existing = username_exists( $newLogin );
		if ( $existing && (int) $existing !== $userId ) {
			return; // Username taken.
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- No WP API to rename user_login.
		$wpdb->update(
			$wpdb->users,
			[
				'user_login'    => $newLogin,
				'user_nicename' => sanitize_title( $newLogin ),
			],
			[ 'ID' => $userId ],
		);

		clean_user_cache( $userId );
	}

	/**
	 * Disable block editor full-screen mode by default.
	 */
	private function disableFullscreenEditor(): void {
		add_action( 'enqueue_block_editor_assets', static function (): void {
			$js = "window.addEventListener('load',function(){"
				. "if(wp.data&&wp.data.select('core/edit-post')&&wp.data.select('core/edit-post').isFeatureActive('fullscreenMode')){"
				. "wp.data.dispatch('core/edit-post').toggleFeature('fullscreenMode');"
				. '}});';
			wp_add_inline_script( 'wp-edit-post', $js );
		} );
	}

	/**
	 * Redirect users to a custom URL after login.
	 *
	 * wp-login validates the target with wp_safe_redirect(), which
	 * rewrites any off-site URL back to home — register the configured
	 * host in allowed_redirect_hosts so "any URL" actually works.
	 */
	private function setupLoginRedirect( string $url ): void {
		$this->allowRedirectHost( $url );
		add_filter( 'login_redirect', static fn () => $url, 999, 0 );
	}

	/**
	 * Redirect users to a custom URL after logout.
	 *
	 * Implemented via `logout_redirect` (core then safe-redirects with
	 * proper filter chaining) instead of exiting inside `wp_logout`,
	 * which bypassed the redirect pipeline and other plugins.
	 */
	private function setupLogoutRedirect( string $url ): void {
		$this->allowRedirectHost( $url );
		add_filter( 'logout_redirect', static fn () => $url, 999, 0 );
	}

	/**
	 * Permit an off-site redirect target configured by the admin.
	 */
	private function allowRedirectHost( string $url ): void {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! is_string( $host ) || '' === $host ) {
			return;
		}
		add_filter(
			'allowed_redirect_hosts',
			static function ( array $hosts ) use ( $host ): array {
				$hosts[] = $host;
				return $hosts;
			},
		);
	}

	/* ==================================================================
	 * BRANDING TAB — hook methods
	 * ================================================================*/

	/**
	 * Replace admin footer text (optionally with a small logo).
	 */
	private function customFooterText( array $opts ): void {
		add_filter( 'admin_footer_text', static function () use ( $opts ): string {
			$html = '';
			$logoId = (int) $opts['footer_logo_id'];
			if ( $logoId > 0 ) {
				$logoUrl = wp_get_attachment_image_url( $logoId, 'thumbnail' );
				if ( $logoUrl ) {
					$html .= '<img src="' . esc_url( $logoUrl ) . '" alt="" style="height:20px;vertical-align:middle;margin-right:6px;">';
				}
			}
			$html .= wp_kses_post( $opts['footer_text'] );
			return $html;
		}, 999 );
	}

	/**
	 * Hide or replace the WordPress logo in the admin bar.
	 */
	private function customizeAdminBarLogo( array $opts ): void {
		add_action( 'admin_bar_menu', static function ( \WP_Admin_Bar $bar ) use ( $opts ): void {
			if ( $opts['hide_wp_logo_admin_bar'] && (int) $opts['admin_bar_logo_id'] === 0 ) {
				$bar->remove_node( 'wp-logo' );
				return;
			}

			$logoId = (int) $opts['admin_bar_logo_id'];
			if ( $logoId > 0 ) {
				$logoUrl = wp_get_attachment_image_url( $logoId, 'thumbnail' );
				if ( $logoUrl ) {
					/*
					 * Remove the core node first: add_node() with an
					 * existing id only merges fields and would keep
					 * core's children (About WordPress, wp.org, …)
					 * under the "custom" logo.
					 */
					$bar->remove_node( 'wp-logo' );
					$bar->add_node( [
						'id'    => 'mudrava-logo',
						'title' => '<img src="' . esc_url( $logoUrl )
							. '" alt="" style="height:20px;width:auto;vertical-align:middle;padding:6px 0;">',
						'href'  => $opts['admin_bar_logo_url'] !== '' ? esc_url( $opts['admin_bar_logo_url'] ) : admin_url(),
					] );
				}
			}
		}, 11 );
	}

	/**
	 * Open the "Visit Site" admin-bar link in a new tab.
	 */
	private function visitSiteNewTab(): void {
		add_action( 'admin_bar_menu', static function ( \WP_Admin_Bar $bar ): void {
			$node = $bar->get_node( 'view-site' );
			if ( $node ) {
				$meta           = (array) $node->meta;
				$meta['target'] = '_blank';
				$bar->add_node( [
					'id'   => 'view-site',
					'meta' => $meta,
				] );
			}
		}, 999 );
	}

	/* ==================================================================
	 * COLUMNS TAB — hook methods
	 * ================================================================*/

	/**
	 * Enqueue list-table CSS for the custom admin columns.
	 */
	private function enqueueAdminColumnStyles(): void {
		add_action(
			'admin_enqueue_scripts',
			function ( string $hookSuffix ): void {
				if ( ! in_array( $hookSuffix, [ 'edit.php', 'upload.php', 'users.php', 'edit-tags.php' ], true ) ) {
					return;
				}

				$this->enqueueStyle( 'admin-tweaks-columns', 'admin-tweaks-columns.css' );
			},
		);
	}

	/**
	 * Add ID column to Posts, Pages, Media, Categories, Users.
	 */
	private function addIdColumns(): void {
		add_action( 'admin_init', static function (): void {
			/* Posts & Pages */
			foreach ( get_post_types( [ 'public' => true ] ) as $type ) {
				if ( 'attachment' === $type ) {
					continue; // Media Library has its own list-table hooks below.
				}

				add_filter( "manage_{$type}_posts_columns", static function ( array $cols ): array {
					return [ 'mdkit_id' => 'ID' ] + $cols;
				} );
				add_action( "manage_{$type}_posts_custom_column", static function ( string $col, int $postId ): void {
					if ( $col === 'mdkit_id' ) {
						echo (int) $postId;
					}
				}, 10, 2 );
				add_filter( "manage_edit-{$type}_sortable_columns", static function ( array $cols ): array {
					$cols['mdkit_id'] = 'ID';
					return $cols;
				} );
			}

			/* Media Library */
			add_filter( 'manage_upload_columns', static function ( array $cols ): array {
				return [ 'mdkit_id' => 'ID' ] + $cols;
			} );
			add_action( 'manage_media_custom_column', static function ( string $col, int $postId ): void {
				if ( $col === 'mdkit_id' ) {
					echo (int) $postId;
				}
			}, 10, 2 );
			add_filter( 'manage_upload_sortable_columns', static function ( array $cols ): array {
				$cols['mdkit_id'] = 'ID';
				return $cols;
			} );

			/* Categories & Tags */
			foreach ( get_taxonomies( [ 'public' => true ] ) as $tax ) {
				add_filter( "manage_edit-{$tax}_columns", static function ( array $cols ): array {
					return [ 'mdkit_id' => 'ID' ] + $cols;
				} );
				add_filter( "manage_{$tax}_custom_column", static function ( string $content, string $col, int $termId ): string {
					return $col === 'mdkit_id' ? (string) $termId : $content;
				}, 10, 3 );
			}

			/* Users */
			add_filter( 'manage_users_columns', static function ( array $cols ): array {
				return [ 'mdkit_id' => 'ID' ] + $cols;
			} );
			add_filter( 'manage_users_custom_column', static function ( string $content, string $col, int $userId ): string {
				return $col === 'mdkit_id' ? (string) $userId : $content;
			}, 10, 3 );
		} );
	}

	/**
	 * Add featured image thumbnail column to selected post types.
	 */
	private function addFeaturedImageColumns( array $postTypes ): void {
		foreach ( $postTypes as $type ) {
			add_filter( "manage_{$type}_posts_columns", static function ( array $cols ): array {
				$newCols = [];
				foreach ( $cols as $key => $label ) {
					if ( $key === 'title' ) {
						$newCols['mdkit_thumb'] = __( 'Image', 'mudrava-admin-tweaks' );
					}
					$newCols[ $key ] = $label;
				}
				return $newCols;
			} );

			add_action( "manage_{$type}_posts_custom_column", static function ( string $col, int $postId ): void {
				if ( $col === 'mdkit_thumb' ) {
					$thumb = get_the_post_thumbnail( $postId, [ 40, 40 ], [ 'style' => 'border-radius:4px;' ] );
					echo $thumb !== '' ? wp_kses_post( $thumb ) : '—';
				}
			}, 10, 2 );
		}
	}

	/**
	 * Add "Last Modified" date column to post list tables.
	 */
	private function addModifiedDateColumn(): void {
		add_action( 'admin_init', static function (): void {
			foreach ( get_post_types( [ 'public' => true ] ) as $type ) {
				add_filter( "manage_{$type}_posts_columns", static function ( array $cols ): array {
					$cols['mdkit_modified'] = __( 'Modified', 'mudrava-admin-tweaks' );
					return $cols;
				} );

				add_action( "manage_{$type}_posts_custom_column", static function ( string $col, int $postId ): void {
					if ( $col === 'mdkit_modified' ) {
						$post = get_post( $postId );
						echo $post ? esc_html( get_the_modified_date( 'Y/m/d', $post ) ) : '—';
					}
				}, 10, 2 );

				add_filter( "manage_edit-{$type}_sortable_columns", static function ( array $cols ): array {
					$cols['mdkit_modified'] = 'modified';
					return $cols;
				} );
			}

			/* Media Library list uses upload-specific hooks, not {$type}_posts. */
			add_filter( 'manage_upload_columns', static function ( array $cols ): array {
				$cols['mdkit_modified'] = __( 'Modified', 'mudrava-admin-tweaks' );
				return $cols;
			} );
			add_action( 'manage_media_custom_column', static function ( string $col, int $postId ): void {
				if ( $col === 'mdkit_modified' ) {
					$post = get_post( $postId );
					echo $post ? esc_html( get_the_modified_date( 'Y/m/d', $post ) ) : '—';
				}
			}, 10, 2 );
			add_filter( 'manage_upload_sortable_columns', static function ( array $cols ): array {
				$cols['mdkit_modified'] = 'modified';
				return $cols;
			} );
		} );
	}

	/* ==================================================================
	 * MEDIA TAB — hook methods
	 * ================================================================*/

	/**
	 * Rename uploaded files to an MD5 hash.
	 */
	private function randomizeFilenames(): void {
		add_filter( 'wp_handle_upload_prefilter', static function ( array $file ): array {
			$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
			$file['name'] = md5( $file['name'] . wp_generate_password( 12, false ) ) . '.' . strtolower( $ext );
			return $file;
		} );
	}

	/**
	 * Rename uploaded file to the parent post slug.
	 */
	private function renameToPostTitle(): void {
		add_filter( 'wp_handle_upload_prefilter', static function ( array $file ): array {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress media upload handler.
			$postId = absint( wp_unslash( $_POST['post_id'] ?? 0 ) );
			if ( $postId > 0 ) {
				$post = get_post( $postId );
				if ( $post && $post->post_name !== '' ) {
					$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
					$file['name'] = sanitize_file_name( $post->post_name ) . '.' . strtolower( $ext );
				}
			}
			return $file;
		} );
	}

	/**
	 * Disable Gravatar requests.
	 */
	private function disableAvatars(): void {
		add_filter( 'pre_option_show_avatars', '__return_zero' );
		add_filter( 'pre_get_avatar', '__return_empty_string' );
	}

	/* ==================================================================
	 * SECURITY TAB — hook methods
	 * ================================================================*/

	/**
	 * Remove WordPress version from head, RSS, scripts & styles.
	 */
	private function hideWpVersion(): void {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );

		$stripVer = static function ( string $src ): string {
			if ( str_contains( $src, 'ver=' ) ) {
				return remove_query_arg( 'ver', $src );
			}
			return $src;
		};

		add_filter( 'style_loader_src', $stripVer, 999 );
		add_filter( 'script_loader_src', $stripVer, 999 );
	}

	/**
	 * Block unauthenticated access to /wp-json/wp/v2/users*.
	 *
	 * Core already gates the user *collection* behind list_users; this
	 * makes the guard explicit while keeping /users/me and author lookups
	 * working for logged-in editors/authors/contributors.
	 */
	private function disableRestUsersEndpoint(): void {
		add_filter( 'rest_pre_dispatch', static function ( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {
			$route = $request->get_route();
			if ( preg_match( '#^/wp/v2/users#', $route ) && ! is_user_logged_in() ) {
				return new \WP_Error(
					'rest_forbidden',
					__( 'Access denied.', 'mudrava-admin-tweaks' ),
					[ 'status' => 403 ],
				);
			}
			return $result;
		}, 10, 3 );
	}

	/**
	 * Remove the X-Pingback HTTP header.
	 */
	private function removeXPingbackHeader(): void {
		add_filter( 'wp_headers', static function ( array $headers ): array {
			unset( $headers['X-Pingback'] );
			return $headers;
		} );
	}

	/**
	 * Disable /?s= search on the frontend.
	 *
	 * Uses 302 (not 301) so the redirect is not cached permanently by
	 * browsers/search engines — the tweak must stay reversible.
	 */
	private function disableFrontendSearch(): void {
		add_action( 'parse_query', static function ( \WP_Query $query ): void {
			if ( ! is_admin() && $query->is_search() && $query->is_main_query() ) {
				wp_safe_redirect( home_url(), 302 );
				exit;
			}
		} );
	}

	/* ==================================================================
	 * NOTIFICATIONS TAB — hook methods
	 * ================================================================*/

	/**
	 * Disable Site Health status notification emails.
	 *
	 * Core hooks the check as array( $instance, 'wp_cron_scheduled_check' )
	 * and builds the instance on `init` (priority 10), so both the action
	 * detach and the cron clear must run AFTER that — on a cron request,
	 * not only after an admin page load.
	 */
	private function disableSiteHealthEmails(): void {
		add_action(
			'init',
			static function (): void {
				if ( class_exists( \WP_Site_Health::class ) ) {
					remove_action(
						'wp_site_health_scheduled_check',
						array( \WP_Site_Health::get_instance(), 'wp_cron_scheduled_check' ),
					);
				}
				if ( wp_next_scheduled( 'wp_site_health_scheduled_check' ) ) {
					wp_clear_scheduled_hook( 'wp_site_health_scheduled_check' );
				}
			},
			20,
		);
	}

	/**
	 * Disable all auto-update email notifications.
	 */
	private function disableUpdateEmails(): void {
		add_filter( 'auto_core_update_send_email', '__return_false' );
		add_filter( 'auto_plugin_update_send_email', '__return_false' );
		add_filter( 'auto_theme_update_send_email', '__return_false' );
	}

	/**
	 * Prevent admin from receiving new-user registration emails.
	 *
	 * Empties the recipient list; a `pre_wp_mail` guard then short-circuits
	 * the (recipient-less) wp_mail() call so PHPMailer never logs an
	 * "empty recipient" error.
	 */
	private function disableNewUserAdminEmail(): void {
		add_filter( 'wp_new_user_notification_email_admin', static function ( array $email ): array {
			$email['to'] = '';
			return $email;
		} );

		add_filter(
			'pre_wp_mail',
			static function ( $shortCircuit, $atts ) {
				if ( null === $shortCircuit
					&& is_array( $atts )
					&& isset( $atts['to'] )
					&& ( '' === $atts['to'] || [] === $atts['to'] )
				) {
					return true;
				}
				return $shortCircuit;
			},
			10,
			2,
		);
	}

	/* ==================================================================
	 * CLEANUP TAB — hook methods
	 * ================================================================*/

	/**
	 * Override trash auto-delete interval.
	 */
	private function setupTrashDays( int $days ): void {
		remove_action( 'wp_scheduled_delete', 'wp_scheduled_delete' );

		if ( $days <= 0 ) {
			return; // Disable auto-delete.
		}

		add_action( 'wp_scheduled_delete', static function () use ( $days ): void {
			global $wpdb;
			$ts = time() - ( DAY_IN_SECONDS * $days );

			/*
			 * Age is measured from the moment an item was TRASHED
			 * (_wp_trash_meta_time), not from creation/modification —
			 * same source core uses in wp_scheduled_delete(). Using
			 * comment_date_gmt/post_modified_gmt would force-delete
			 * freshly trashed items that merely have an old date.
			 */
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk trash cleanup requires direct query.
			$posts = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT pm.post_id FROM {$wpdb->postmeta} pm"
					. " INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id"
					. " WHERE p.post_status = 'trash' AND pm.meta_key = '_wp_trash_meta_time' AND pm.meta_value < %d LIMIT 200",
					$ts,
				),
			);
			foreach ( $posts as $id ) {
				wp_delete_post( (int) $id, true );
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk comment cleanup requires direct query.
			$comments = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT cm.comment_id FROM {$wpdb->commentmeta} cm"
					. " INNER JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id"
					. " WHERE c.comment_approved IN ('trash', 'spam') AND cm.meta_key = '_wp_trash_meta_time' AND cm.meta_value < %d LIMIT 200",
					$ts,
				),
			);
			foreach ( $comments as $id ) {
				wp_delete_comment( (int) $id, true );
			}

			/*
			 * Full batch => likely a backlog. Re-queue immediately so
			 * large backlogs drain instead of stalling at 200/day.
			 */
			if ( count( $posts ) >= 200 || count( $comments ) >= 200 ) {
				wp_schedule_single_event( time() + 60, 'wp_scheduled_delete' );
			}
		} );
	}

	/**
	 * Hide admin bar on the frontend.
	 */
	private function setupHideAdminBar( string $mode ): void {
		add_filter( 'show_admin_bar', static function ( bool $show ) use ( $mode ): bool {
			if ( $mode === 'all' ) {
				return false;
			}
			if ( $mode === 'non_admins' && ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			return $show;
		} );
	}

	/**
	 * Completely disable RSS/Atom feeds.
	 */
	private function disableRssFeeds(): void {
		$redirect = static function (): void {
			wp_safe_redirect( home_url(), 302 );
			exit;
		};

		add_action( 'do_feed', $redirect, 1 );
		add_action( 'do_feed_rss', $redirect, 1 );
		add_action( 'do_feed_rss2', $redirect, 1 );
		add_action( 'do_feed_atom', $redirect, 1 );
		add_action( 'do_feed_rdf', $redirect, 1 );

		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}

	/**
	 * Remove Category: / Tag: / Author: prefixes from archive titles.
	 */
	private function removeArchivePrefixes( array $opts ): void {
		add_filter( 'get_the_archive_title', static function ( string $title ) use ( $opts ): string {
			if ( $opts['remove_category_prefix'] && is_category() ) {
				return single_cat_title( '', false ) ?: $title;
			}
			if ( $opts['remove_tag_prefix'] && is_tag() ) {
				return single_tag_title( '', false ) ?: $title;
			}
			if ( $opts['remove_author_prefix'] && is_author() ) {
				return get_the_author() ?: $title;
			}
			return $title;
		} );
	}

	/* ------------------------------------------------------------------
	 * Defaults
	 * ----------------------------------------------------------------*/

	private function getDefaults(): array {
		return [
			/* General */
			'change_admin_email_without_confirm' => false,
			'change_user_email_without_confirm'  => false,
			'allow_username_change'              => false,
			'disable_fullscreen_editor'          => false,
			'redirect_after_login'               => '',
			'redirect_after_logout'              => '',

			/* Branding */
			'footer_text'            => '',
			'footer_logo_id'        => 0,
			'admin_bar_logo_id'     => 0,
			'admin_bar_logo_url'    => '',
			'hide_wp_logo_admin_bar' => false,
			'visit_site_new_tab'    => false,

			/* Columns */
			'show_id_column'            => false,
			'featured_image_post_types' => [],
			'show_modified_date_column' => false,

			/* Media */
			'disable_big_image_scaling'  => false,
			'disable_year_month_folders' => false,
			'randomize_upload_filenames' => false,
			'rename_file_to_post_title'  => false,
			'disable_avatars'            => false,

			/* Security */
			'hide_wp_version'             => false,
			'disable_file_editing'        => false,
			'disable_rest_users_endpoint' => false,
			'remove_rest_api_link'        => false,
			'remove_x_pingback_header'    => false,
			'disable_search_frontend'     => false,

			/* Notifications */
			'disable_site_health_emails'          => false,
			'disable_update_emails'               => false,
			'disable_email_verification_screen'   => false,
			'disable_new_user_admin_email'        => false,
			'disable_password_change_admin_email' => false,

			/* Cleanup */
			'limit_post_revisions'    => -1,
			'trash_auto_delete_days'  => 30,
			'hide_admin_bar_frontend' => 'none',
			'disable_rss_feeds'       => false,
			'remove_category_prefix'  => false,
			'remove_tag_prefix'       => false,
			'remove_author_prefix'    => false,
		];
	}
}
