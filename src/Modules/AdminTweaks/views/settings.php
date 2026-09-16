<?php
/**
 * Admin Tweaks - settings view.
 *
 * Renders all 7 tabs with a live-search bar.
 *
 * @var array<string, mixed>  $mudrava_admin_tweaks_options
 * @var array<string, string> $mudrava_admin_tweaks_post_types  slug => label (types supporting thumbnails)
 */

defined( 'ABSPATH' ) || exit;

use Mudrava\AdminTweaks\Core\AdminUI;
use Mudrava\AdminTweaks\Core\Icons;

(static function ( array $mudrava_admin_tweaks_options = [], array $mudrava_admin_tweaks_post_types = [] ): void {

/* ---- extract options ---- */
$mdkit_at_o             = $mudrava_admin_tweaks_options;
$mdkit_at_post_types    = $mudrava_admin_tweaks_post_types;
$mdkit_at_allowed_html  = AdminUI::allowedHtml();
$mdkit_at_feat_img_types = (array) ( $mdkit_at_o['featured_image_post_types'] ?? [] );
?>

<!-- ============================================================
     Search bar
     ============================================================ -->
<div class="mdkit-at-search-wrap">
	<span class="mdkit-at-search-icon"><?php echo wp_kses( Icons::render( 'search', 16 ), $mdkit_at_allowed_html ); ?></span>
	<input type="search"
	       id="mdkit-at-search"
	       class="mdkit-at-search"
	       placeholder="<?php echo esc_attr__( 'Search settings…', 'mudrava-admin-tweaks' ); ?>"
	       autocomplete="off">
</div>

<!-- ============================================================
     Tab panels
     ============================================================ -->
<div class="mdkit-tab-panels">

<!-- ==================== GENERAL ==================== -->
<div class="mdkit-tab-panel" id="mdkit-panel-general">
<?php
echo wp_kses(
	AdminUI::card(
		__( 'Email & Account', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="admin email confirm verification site settings general">'
			. AdminUI::toggle( 'change_admin_email_without_confirm', (bool) $mdkit_at_o['change_admin_email_without_confirm'], __( 'Change admin email without confirmation', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Skip the confirmation email when changing the site admin email in Settings → General. The new address takes effect immediately.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="user email confirm profile account">'
			. AdminUI::toggle( 'change_user_email_without_confirm', (bool) $mdkit_at_o['change_user_email_without_confirm'], __( 'Change user email without confirmation', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Skip the confirmation step when a user changes their own email on the profile page.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="username login rename edit change user account">'
			. AdminUI::toggle( 'allow_username_change', (bool) $mdkit_at_o['allow_username_change'], __( 'Allow username changes', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Make the Username field editable on user profile pages. Only Administrators can change usernames.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'user',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'Editor & Redirects', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="fullscreen editor gutenberg block sidebar">'
			. AdminUI::toggle( 'disable_fullscreen_editor', (bool) $mdkit_at_o['disable_fullscreen_editor'], __( 'Disable fullscreen editor mode', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Open the block editor with the sidebar visible by default. Clients often get confused by the fullscreen mode.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="redirect login url after">'
			. AdminUI::input(
				'redirect_after_login',
				(string) $mdkit_at_o['redirect_after_login'],
				'url',
				__( 'Redirect after login', 'mudrava-admin-tweaks' ),
				__( 'Custom URL to redirect users to after login (e.g., /wp-admin/edit.php?post_type=shop_order). Leave empty for default.', 'mudrava-admin-tweaks' )
			)
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="redirect logout url after">'
			. AdminUI::input(
				'redirect_after_logout',
				(string) $mdkit_at_o['redirect_after_logout'],
				'url',
				__( 'Redirect after logout', 'mudrava-admin-tweaks' ),
				__( 'Custom URL to redirect users to after logout. Leave empty to stay on the login screen.', 'mudrava-admin-tweaks' )
			)
		. '</div>',
		'log-in',
	),
	$mdkit_at_allowed_html,
);
?>
<div class="mdkit-at-actions">
	<?php echo wp_kses( AdminUI::button( __( 'Save Settings', 'mudrava-admin-tweaks' ), 'primary', [ 'type' => 'button', 'class' => 'mdkit-at-save', 'data-tab' => 'general' ], 'check' ), $mdkit_at_allowed_html ); ?>
</div>
</div>

<!-- ==================== BRANDING ==================== -->
<div class="mdkit-tab-panel" id="mdkit-panel-branding" hidden>
<?php
$mdkit_at_footer_logo_id  = (int) ( $mdkit_at_o['footer_logo_id'] ?? 0 );
$mdkit_at_footer_logo_url = $mdkit_at_footer_logo_id > 0 ? wp_get_attachment_image_url( $mdkit_at_footer_logo_id, 'thumbnail' ) : '';
$mdkit_at_bar_logo_id     = (int) ( $mdkit_at_o['admin_bar_logo_id'] ?? 0 );
$mdkit_at_bar_logo_url    = $mdkit_at_bar_logo_id > 0 ? wp_get_attachment_image_url( $mdkit_at_bar_logo_id, 'thumbnail' ) : '';

echo wp_kses(
	AdminUI::card(
		__( 'Admin Footer', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="footer text copyright thank you creating wordpress branding">'
			. AdminUI::textarea(
				'footer_text',
				(string) $mdkit_at_o['footer_text'],
				__( 'Footer text', 'mudrava-admin-tweaks' ),
				__( 'Replaces "Thank you for creating with WordPress" in the admin footer. Basic HTML allowed (links, bold, etc.).', 'mudrava-admin-tweaks' ),
				3
			)
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="footer logo image copyright branding">'
			. '<label class="mdkit-label">' . esc_html__( 'Footer logo', 'mudrava-admin-tweaks' ) . '</label>'
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Small logo displayed before the footer text.', 'mudrava-admin-tweaks' ) . '</p>'
			. '<div id="mdkit-at-footer-logo-preview" class="mdkit-at-logo-preview">'
			. ( $mdkit_at_footer_logo_url ? '<img src="' . esc_url( $mdkit_at_footer_logo_url ) . '" alt="" class="mdkit-at-logo-preview-image">' : '' )
			. '</div>'
			. '<input type="hidden" id="mdkit-at-footer-logo-id" name="footer_logo_id" value="' . esc_attr( (string) $mdkit_at_footer_logo_id ) . '">'
			. AdminUI::button( __( 'Choose', 'mudrava-admin-tweaks' ), 'secondary', [ 'type' => 'button', 'id' => 'mdkit-at-choose-footer-logo' ], 'image' )
			. ' '
			. AdminUI::button( __( 'Remove', 'mudrava-admin-tweaks' ), 'link', [ 'type' => 'button', 'id' => 'mdkit-at-remove-footer-logo' ], 'x' )
		. '</div>',
		'file-text',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'Admin Bar Logo', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="admin bar logo wordpress wp remove hide replace branding">'
			. AdminUI::toggle( 'hide_wp_logo_admin_bar', (bool) $mdkit_at_o['hide_wp_logo_admin_bar'], __( 'Hide WordPress logo from admin bar', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Remove the WordPress logo in the top-left corner of the admin bar. If a custom logo is set below, it replaces the WP logo instead.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="admin bar logo custom image replace branding">'
			. '<label class="mdkit-label">' . esc_html__( 'Custom admin bar logo', 'mudrava-admin-tweaks' ) . '</label>'
			. '<div id="mdkit-at-bar-logo-preview" class="mdkit-at-logo-preview">'
			. ( $mdkit_at_bar_logo_url ? '<img src="' . esc_url( $mdkit_at_bar_logo_url ) . '" alt="" class="mdkit-at-logo-preview-image mdkit-at-logo-preview-image--admin-bar">' : '' )
			. '</div>'
			. '<input type="hidden" id="mdkit-at-bar-logo-id" name="admin_bar_logo_id" value="' . esc_attr( (string) $mdkit_at_bar_logo_id ) . '">'
			. AdminUI::button( __( 'Choose', 'mudrava-admin-tweaks' ), 'secondary', [ 'type' => 'button', 'id' => 'mdkit-at-choose-bar-logo' ], 'image' )
			. ' '
			. AdminUI::button( __( 'Remove', 'mudrava-admin-tweaks' ), 'link', [ 'type' => 'button', 'id' => 'mdkit-at-remove-bar-logo' ], 'x' )
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="admin bar logo url link branding">'
			. AdminUI::input(
				'admin_bar_logo_url',
				(string) $mdkit_at_o['admin_bar_logo_url'],
				'url',
				__( 'Logo link URL', 'mudrava-admin-tweaks' ),
				__( 'Where the admin bar logo links to. Leave empty for the dashboard.', 'mudrava-admin-tweaks' )
			)
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="visit site new tab blank branding">'
			. AdminUI::toggle( 'visit_site_new_tab', (bool) $mdkit_at_o['visit_site_new_tab'], __( 'Open "Visit Site" link in a new tab', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'The admin bar "Visit Site" link will open the front-end in a new browser tab.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'monitor',
	),
	$mdkit_at_allowed_html,
);
?>
<div class="mdkit-at-actions">
	<?php echo wp_kses( AdminUI::button( __( 'Save Settings', 'mudrava-admin-tweaks' ), 'primary', [ 'type' => 'button', 'class' => 'mdkit-at-save', 'data-tab' => 'branding' ], 'check' ), $mdkit_at_allowed_html ); ?>
</div>
</div>

<!-- ==================== COLUMNS ==================== -->
<div class="mdkit-tab-panel" id="mdkit-panel-columns" hidden>
<?php
echo wp_kses(
	AdminUI::card(
		__( 'ID Column', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="id column posts pages media categories users list table">'
			. AdminUI::toggle( 'show_id_column', (bool) $mdkit_at_o['show_id_column'], __( 'Show ID column', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Adds an ID column to Posts, Pages, Media, Categories and Users list tables.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'hash',
	),
	$mdkit_at_allowed_html,
);

$mdkit_at_feat_html  = '<div class="mdkit-at-item" data-keywords="featured image thumbnail column post type">';
$mdkit_at_feat_html .= '<p class="mdkit-label">' . esc_html__( 'Show featured image column for:', 'mudrava-admin-tweaks' ) . '</p>';
$mdkit_at_feat_html .= '<p class="mdkit-at-item__desc">' . esc_html__( 'Adds a thumbnail preview column before the title in the selected post types.', 'mudrava-admin-tweaks' ) . '</p>';
$mdkit_at_feat_html .= '<div class="mdkit-at-inline-stack">';
foreach ( $mdkit_at_post_types as $mdkit_at_slug => $mdkit_at_label ) {
	$mdkit_at_feat_html .= AdminUI::checkbox( 'feat_' . $mdkit_at_slug, in_array( $mdkit_at_slug, $mdkit_at_feat_img_types, true ), $mdkit_at_label, '', '1' );
}
if ( empty( $mdkit_at_post_types ) ) {
	$mdkit_at_feat_html .= '<p class="mdkit-description">' . esc_html__( 'No public post types with thumbnail support found.', 'mudrava-admin-tweaks' ) . '</p>';
}
$mdkit_at_feat_html .= '</div></div>';

echo wp_kses( AdminUI::card( __( 'Featured Image Column', 'mudrava-admin-tweaks' ), $mdkit_at_feat_html, 'image' ), $mdkit_at_allowed_html );

echo wp_kses(
	AdminUI::card(
		__( 'Modified Date Column', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="modified date column last updated changed sort">'
			. AdminUI::toggle( 'show_modified_date_column', (bool) $mdkit_at_o['show_modified_date_column'], __( 'Show "Last Modified" date column', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Adds a sortable "Modified" date column to all public post type list tables.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'clock',
	),
	$mdkit_at_allowed_html,
);
?>
<div class="mdkit-at-actions">
	<?php echo wp_kses( AdminUI::button( __( 'Save Settings', 'mudrava-admin-tweaks' ), 'primary', [ 'type' => 'button', 'class' => 'mdkit-at-save', 'data-tab' => 'columns' ], 'check' ), $mdkit_at_allowed_html ); ?>
</div>
</div>

<!-- ==================== MEDIA ==================== -->
<div class="mdkit-tab-panel" id="mdkit-panel-media" hidden>
<?php
echo wp_kses(
	AdminUI::card(
		__( 'Image Processing', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="big image scaling resize 2560 large threshold downscale">'
			. AdminUI::toggle( 'disable_big_image_scaling', (bool) $mdkit_at_o['disable_big_image_scaling'], __( 'Disable big image scaling', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'WordPress automatically scales images larger than 2560px. Disable this to keep original dimensions.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'image',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'Upload Organization', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="year month folders upload organize directory path">'
			. AdminUI::toggle( 'disable_year_month_folders', (bool) $mdkit_at_o['disable_year_month_folders'], __( 'Disable year/month upload folders', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Uploads go into /wp-content/uploads/ directly instead of /wp-content/uploads/2026/02/. Same as unchecking "Organize my uploads into month- and year-based folders" in Settings → Media.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'folder',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'File Naming', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="randomize filename hash md5 upload security privacy rename">'
			. AdminUI::toggle( 'randomize_upload_filenames', (bool) $mdkit_at_o['randomize_upload_filenames'], __( 'Randomize upload filenames', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Rename uploaded files to an MD5 hash (e.g., a1b2c3d4.jpg) for security and privacy. Overrides "Rename to post title" if both are active.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="rename file post title slug seo upload">'
			. AdminUI::toggle( 'rename_file_to_post_title', (bool) $mdkit_at_o['rename_file_to_post_title'], __( 'Rename file to post title', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Automatically rename uploaded media files to match the parent post slug (e.g., my-blog-post.jpg). Good for SEO.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'edit',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'Avatars', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="avatar gravatar disable gdpr privacy speed">'
			. AdminUI::toggle( 'disable_avatars', (bool) $mdkit_at_o['disable_avatars'], __( 'Disable avatars (Gravatar)', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Completely disable Gravatar requests. Improves page speed and helps with GDPR compliance.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'user',
	),
	$mdkit_at_allowed_html,
);
?>
<div class="mdkit-at-actions">
	<?php echo wp_kses( AdminUI::button( __( 'Save Settings', 'mudrava-admin-tweaks' ), 'primary', [ 'type' => 'button', 'class' => 'mdkit-at-save', 'data-tab' => 'media' ], 'check' ), $mdkit_at_allowed_html ); ?>
</div>
</div>

<!-- ==================== SECURITY ==================== -->
<div class="mdkit-tab-panel" id="mdkit-panel-security" hidden>
<?php
echo wp_kses(
	AdminUI::card(
		__( 'Version & Fingerprinting', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="wordpress version hide remove generator head rss scripts styles ver">'
			. AdminUI::toggle( 'hide_wp_version', (bool) $mdkit_at_o['hide_wp_version'], __( 'Hide WordPress version', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Removes the WP version number from the <head> generator tag, RSS feeds, and the ?ver= parameter on scripts and stylesheets.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'shield',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'File & Code Protection', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="file editing disable editor theme plugin code admin">'
			. AdminUI::toggle( 'disable_file_editing', (bool) $mdkit_at_o['disable_file_editing'], __( 'Disable file editing', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Disables the built-in Theme and Plugin file editor in the admin area. Prevents accidental site breakage by clients.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'lock',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'REST API', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="rest api users endpoint json disable block security enum">'
			. AdminUI::toggle( 'disable_rest_users_endpoint', (bool) $mdkit_at_o['disable_rest_users_endpoint'], __( 'Disable REST API users endpoint', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Blocks unauthenticated access to /wp-json/wp/v2/users. Prevents user enumeration via the REST API.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="rest api link head remove tag">'
			. AdminUI::toggle( 'remove_rest_api_link', (bool) $mdkit_at_o['remove_rest_api_link'], __( 'Remove REST API link tag', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Remove the <link rel="https://api.w.org/"> tag from the HTML head. The API itself still works, but the endpoint URL is no longer advertised.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="x-pingback header remove xmlrpc pingback http">'
			. AdminUI::toggle( 'remove_x_pingback_header', (bool) $mdkit_at_o['remove_x_pingback_header'], __( 'Remove X-Pingback header', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Removes the X-Pingback HTTP header that advertises the XML-RPC pingback endpoint. Reduces the attack surface.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'code',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'Frontend Protection', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="search disable frontend query parameter s">'
			. AdminUI::toggle( 'disable_search_frontend', (bool) $mdkit_at_o['disable_search_frontend'], __( 'Disable frontend search', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Completely disables the /?s= search function on the frontend. Useful for landing pages and brochure sites to prevent junk search result pages from being indexed.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'eye',
	),
	$mdkit_at_allowed_html,
);
?>
<div class="mdkit-at-actions">
	<?php echo wp_kses( AdminUI::button( __( 'Save Settings', 'mudrava-admin-tweaks' ), 'primary', [ 'type' => 'button', 'class' => 'mdkit-at-save', 'data-tab' => 'security' ], 'check' ), $mdkit_at_allowed_html ); ?>
</div>
</div>

<!-- ==================== NOTIFICATIONS ==================== -->
<div class="mdkit-tab-panel" id="mdkit-panel-notifications" hidden>
<?php
echo wp_kses(
	AdminUI::card(
		__( 'System Notifications', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="site health email notification disable check status">'
			. AdminUI::toggle( 'disable_site_health_emails', (bool) $mdkit_at_o['disable_site_health_emails'], __( 'Disable Site Health emails', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Stops the periodic "Your site health needs attention" emails sent to the site administrator.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="update email notification disable auto plugin theme core">'
			. AdminUI::toggle( 'disable_update_emails', (bool) $mdkit_at_o['disable_update_emails'], __( 'Disable auto-update emails', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Stops notification emails about automatic updates for plugins, themes, and WordPress core.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="email verification screen admin confirm disable">'
			. AdminUI::toggle( 'disable_email_verification_screen', (bool) $mdkit_at_o['disable_email_verification_screen'], __( 'Disable admin email verification screen', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Disables the "Confirm your admin email address" screen that appears every 6 months after login.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'bell-off',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'User Notifications', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="new user notification email admin registration disable">'
			. AdminUI::toggle( 'disable_new_user_admin_email', (bool) $mdkit_at_o['disable_new_user_admin_email'], __( 'Disable new user admin email', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Stops emailing the site administrator when a new user registers. The new user still receives their own welcome email.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="password change notification email admin disable">'
			. AdminUI::toggle( 'disable_password_change_admin_email', (bool) $mdkit_at_o['disable_password_change_admin_email'], __( 'Disable password change admin email', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Stops emailing the site administrator when a user changes their password.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'mail',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'Test Email', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="test email send smtp check verify mail">'
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Send a quick test email to verify that your server can deliver mail. No need for a heavy SMTP plugin just to check.', 'mudrava-admin-tweaks' ) . '</p>'
			. '<div class="mdkit-at-inline-actions">'
			. '<div class="mdkit-at-inline-actions__field">'
			. AdminUI::input( 'test_email', wp_get_current_user()->user_email, 'email', __( 'Recipient', 'mudrava-admin-tweaks' ) )
			. '</div>'
			. '<div class="mdkit-at-inline-actions__button">'
			. AdminUI::button( __( 'Send Test Email', 'mudrava-admin-tweaks' ), 'secondary', [ 'type' => 'button', 'id' => 'mdkit-at-send-test' ], 'send' )
			. '</div>'
			. '</div>'
			. '<div id="mdkit-at-test-result" class="mdkit-at-test-result"></div>'
		. '</div>',
		'send',
	),
	$mdkit_at_allowed_html,
);
?>
<div class="mdkit-at-actions">
	<?php echo wp_kses( AdminUI::button( __( 'Save Settings', 'mudrava-admin-tweaks' ), 'primary', [ 'type' => 'button', 'class' => 'mdkit-at-save', 'data-tab' => 'notifications' ], 'check' ), $mdkit_at_allowed_html ); ?>
</div>
</div>

<!-- ==================== CLEANUP ==================== -->
<div class="mdkit-tab-panel" id="mdkit-panel-cleanup" hidden>
<?php
echo wp_kses(
	AdminUI::card(
		__( 'Revisions & Trash', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="post revisions limit disable restrict history version">'
			. AdminUI::input(
				'limit_post_revisions',
				(string) (int) $mdkit_at_o['limit_post_revisions'],
				'number',
				__( 'Limit post revisions', 'mudrava-admin-tweaks' ),
				__( 'Maximum number of revisions to keep per post. Set 0 to disable revisions, -1 for unlimited (WordPress default).', 'mudrava-admin-tweaks' ),
				[ 'min' => '-1', 'max' => '100', 'step' => '1' ]
			)
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="trash auto delete days empty clean purge">'
			. AdminUI::input(
				'trash_auto_delete_days',
				(string) (int) $mdkit_at_o['trash_auto_delete_days'],
				'number',
				__( 'Trash auto-delete (days)', 'mudrava-admin-tweaks' ),
				__( 'Number of days before trashed posts and comments are permanently deleted. Default is 30. Set 0 to disable auto-deletion.', 'mudrava-admin-tweaks' ),
				[ 'min' => '0', 'max' => '365', 'step' => '1' ]
			)
		. '</div>',
		'trash-2',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'Admin Bar', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="admin bar hide frontend show top toolbar">'
			. AdminUI::select(
				'hide_admin_bar_frontend',
				[
					'none'       => __( 'Don\'t hide (default)', 'mudrava-admin-tweaks' ),
					'all'        => __( 'Hide for everyone', 'mudrava-admin-tweaks' ),
					'non_admins' => __( 'Hide for non-administrators', 'mudrava-admin-tweaks' ),
				],
				(string) $mdkit_at_o['hide_admin_bar_frontend'],
				__( 'Hide admin bar on frontend', 'mudrava-admin-tweaks' ),
				__( 'Controls whether the admin toolbar is visible on the public-facing side of the site.', 'mudrava-admin-tweaks' )
			)
		. '</div>',
		'monitor',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'Feeds & Archives', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="rss feed atom disable landing page brochure">'
			. AdminUI::toggle( 'disable_rss_feeds', (bool) $mdkit_at_o['disable_rss_feeds'], __( 'Disable RSS feeds', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Completely disables all RSS and Atom feeds. Useful for landing pages or business-card sites.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="category prefix remove archive title heading">'
			. AdminUI::toggle( 'remove_category_prefix', (bool) $mdkit_at_o['remove_category_prefix'], __( 'Remove "Category:" prefix', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Removes the "Category:" prefix from archive page headings.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="tag prefix remove archive title heading">'
			. AdminUI::toggle( 'remove_tag_prefix', (bool) $mdkit_at_o['remove_tag_prefix'], __( 'Remove "Tag:" prefix', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Removes the "Tag:" prefix from archive page headings.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>'
		. '<div class="mdkit-at-item" data-keywords="author prefix remove archive title heading">'
			. AdminUI::toggle( 'remove_author_prefix', (bool) $mdkit_at_o['remove_author_prefix'], __( 'Remove "Author:" prefix', 'mudrava-admin-tweaks' ) )
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Removes the "Author:" prefix from archive page headings.', 'mudrava-admin-tweaks' ) . '</p>'
		. '</div>',
		'list',
	),
	$mdkit_at_allowed_html,
);

echo wp_kses(
	AdminUI::card(
		__( 'Force Logout', 'mudrava-admin-tweaks' ),
		'<div class="mdkit-at-item" data-keywords="force logout users sessions destroy invalidate security hack breach">'
			. '<p class="mdkit-at-item__desc">' . esc_html__( 'Immediately log out all other users. Useful after a security incident, compromised credentials, or a bulk password change. Your own session is preserved.', 'mudrava-admin-tweaks' ) . '</p>'
			. '<div class="mdkit-at-inline-stack">'
			. AdminUI::button( __( 'Force Logout All Users', 'mudrava-admin-tweaks' ), 'danger', [ 'type' => 'button', 'id' => 'mdkit-at-force-logout' ], 'log-out' )
			. '</div>'
		. '</div>',
		'log-out',
	),
	$mdkit_at_allowed_html,
);
?>
<div class="mdkit-at-actions">
	<?php echo wp_kses( AdminUI::button( __( 'Save Settings', 'mudrava-admin-tweaks' ), 'primary', [ 'type' => 'button', 'class' => 'mdkit-at-save', 'data-tab' => 'cleanup' ], 'check' ), $mdkit_at_allowed_html ); ?>
</div>
</div>

</div><!-- .mdkit-tab-panels -->

<div id="mdkit-at-no-results" class="mdkit-at-no-results" hidden>
	<?php echo esc_html__( 'No settings match your search.', 'mudrava-admin-tweaks' ); ?>
</div>
<?php
})(
	is_array( $mudrava_admin_tweaks_options ?? null ) ? $mudrava_admin_tweaks_options : [],
	is_array( $mudrava_admin_tweaks_post_types ?? null ) ? $mudrava_admin_tweaks_post_types : []
);
