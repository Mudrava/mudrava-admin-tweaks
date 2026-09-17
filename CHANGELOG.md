# Changelog

All notable changes to this project are documented here.
This project adheres to [Semantic Versioning](https://semver.org/).

## [1.1.3] - 2026-09-18

### Changed
- Verified compatibility metadata for WordPress 7.1.1: activation, all admin screens and AJAX endpoints
  smoke-tested on PHP 8.5. No functional changes.

## [1.1.2] - 2026-09-16

### Fixed
- Admin list-table columns no longer collapse the title column. Widths are now declared as `min-width`
  on cells with an auto table layout (core's `.fixed` layout ignored `min-width`), so ID / thumbnail /
  modified columns keep their size at every breakpoint and long titles wrap instead of being crushed.
- "Hide WordPress version" no longer strips cache-busting query strings from the plugin's own CSS and JS.
  The version-stripping filter now only removes the WordPress core version string; plugin assets are
  versioned by file mtime (`assetVersion()`), so style/script updates apply immediately.
- The WordPress version string in the admin footer is removed on recent WordPress versions: core renders
  it through the `update_footer` filter, which is now filtered to an empty string.

### Changed
- Core em-dash placeholders in empty list-table cells (author, categories, tags, comments) render as a
  plain hyphen, matching the plugin's typographic convention.
- Documentation: `README.md` restructured (badges, banner, feature tables, screenshot index),
  `readme.txt` changelog and stable tag updated, screenshots refreshed with real demo data.

## [1.1.1] - 2026-09-16

### Fixed
- "Disable file editor" now actually works: core passes contexts like `capability_edit_themes` through
  `wp_is_file_mod_allowed()`, so the old context whitelist never matched. Plugin/theme/file-editor primitive
  caps are now stripped via `user_has_cap` - the same mechanism core uses for `DISALLOW_FILE_EDIT`.
- Trash auto-delete no longer force-deletes freshly trashed items that merely have an old creation date:
  age is now measured from the `_wp_trash_meta_time` meta (matching core's `wp_scheduled_delete()`).
  Full 200-item batches now re-queue so large backlogs actually drain.
- Site Health notification email suppression was a no-op (core hooks the instance method
  `wp_cron_scheduled_check`). The action is now detached and the cron cleared on `init` (priority 20),
  which covers cron requests too - not just admin page loads.
- Login/logout redirect tweaks now work with off-site URLs: the configured host is registered via
  `allowed_redirect_hosts` (previously `wp_safe_redirect()` silently rewrote external URLs to home).
  Logout redirect moved from an `exit` inside `wp_logout` to the core `logout_redirect` filter, so the
  redirect pipeline and other plugins stay in the chain.
- Force-logout now uses the `WP_Session_Tokens` API per user (no raw `usermeta` DELETE, no
  `wp_cache_flush()`): multisite-safe (global usermeta is no longer wiped network-wide) and gentle on
  persistent object caches; the acting admin keeps only the current token.
- Admin-bar logo replacement removes the core `wp-logo` node before adding its own, so the WordPress
  dropdown (About WordPress / wp.org / Support) no longer hangs off a "custom" logo.
- REST users guard now blocks only logged-out requests; `/users/me` and author lookups keep working for
  logged-in editors/authors/contributors (previously every non-`list_users` role was locked out of core
  editor features).
- Uninstall now clears the per-site option on every site of a multisite network.
- Media Library list table: sortable ID column added, and the "Modified" column now appears there too
  (it previously covered post types only, contradicting its own description).
- ID column is sortable on all post-type and Media Library list tables.
- Media picker preview builds its `<img>` via DOM APIs instead of `innerHTML`.
- Shipped `languages/mudrava-admin-tweaks.pot` (152 strings) to back the i18n claims and GlotPress.
- Remaining AJAX strings (`Forbidden`, `Invalid tab.`) translated; unused `Sanitize` import removed;
  readme tags switched to canonical WordPress.org slugs and copy synced with actual behavior.
- `tools/build-zip.sh` added - produces a clean wp.org release zip from the tree.

## [1.1.0] - 2026-09-16

### Fixed
- Standalone build: base stylesheet/script and AJAX config (`ajaxUrl`, nonce) are now correctly registered via `admin_enqueue_scripts` and localized - save / force-logout / test-email work end-to-end.
- Single admin menu entry on standalone installs (removed duplicate/orphaned menu registration).
- ID column now renders in the Media Library list table (`manage_upload_columns`).
- Site Health email suppression no longer depends on an admin page load (`remove_action` instead of cron-only cleanup).
- Frontend search / feed redirects switched from 301 to 302 so toggles stay reversible.
- Username-field unlock is limited to administrators (matches server-side rule).
- Suppressed PHPMailer "empty recipient" errors caused by disabled new-user emails (`pre_wp_mail` guard).
- AJAX feedback messages render via `textContent` (no HTML injection path).
- readme: archive-prefix FAQ now correctly describes title-prefix behavior.

### Added
- `uninstall.php` - deleting the plugin removes all stored options.
- `load_plugin_textdomain()` + `languages/*.pot` scaffolding.
- Translatable manifest feature strings.

### Changed
- Options stored with autoload (read on every request → 1 cached query).
- Plugin/author URLs and in-UI links point to `https://mudrava.com/en/`.
- Verified against WordPress 7.1.

## [1.0.3]

- Verified compatibility metadata for WordPress 7.0.

## [1.0.2]

- Fixed standalone plugin text domain metadata for WordPress.org profile and translation indexing.

## [1.0.1]

- Fixed companion loading when the full MUDRAVA plugin is active.
- Updated WordPress.org screenshots.

## [1.0.0]

- Initial release: 30+ individually toggleable admin tweaks in six categories.
- AJAX-powered settings with instant save, force-logout and test-email utilities.
