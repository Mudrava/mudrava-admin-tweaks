# MUDRAVA Admin Tweaks

A collection of **30+ individually toggleable admin-area tweaks** for WordPress — branding, list-table columns, media handling, security hardening, notification control and cleanup utilities. One lightweight plugin instead of a dozen micro-plugins.

> Standalone companion extracted from [MUDRAVA Kit](https://mudrava.com/en/).

## Highlights

- **Every tweak is off by default** — disabled tweaks register zero hooks.
- **Per-tab AJAX saving** with live settings search and instant feedback.
- **Branding** — custom footer text/logo, admin bar logo replacement, "Visit Site" in a new tab.
- **Columns** — ID, featured-image and sortable "Last Modified" columns on list tables (including Media Library).
- **Media** — disable big-image scaling / year-month folders, randomize or post-slug file naming, kill Gravatars.
- **Security** — hide WP version, block Theme/Plugin file editor, block `/wp-json/wp/v2/users` enumeration, strip X-Pingback, frontend search off.
- **Notifications** — silence Site Health, auto-update, email-verification, new-user and password-change emails.
- **Utilities** — force-logout all sessions, send a test email, revision limits, trash intervals, feed control.

## Requirements

| | |
|---|---|
| WordPress | 6.6+ (tested through 7.1) |
| PHP | 8.2+ |

## Installation

1. Upload the `mudrava-admin-tweaks` folder to `/wp-content/plugins/`.
2. Activate **MUDRAVA Admin Tweaks** on the Plugins screen.
3. Open **Admin Tweaks** in the admin menu, flip switches, Save.

## Architecture

```
mudrava-admin-tweaks.php   # standalone loader (guard vs. Kit hub, autoloader, bootstrap)
src/Core/                  # Kit-compatible core: AbstractModule, AdminUI, Icons, Sanitize
src/Modules/AdminTweaks/   # the module: hooks, view, assets
uninstall.php              # complete option cleanup
```

The module is written Kit-first (`Mudrava\Kit\…` namespace, `manifest()` contract), so the exact same codebase runs inside the MUDRAVA Kit hub — when the Kit plugin is active, the standalone loader silently stands down.

## Development

```bash
# lint
find . -name '*.php' -exec php -l {} \;

# build release zip (wp.org-ready)
./tools/build-zip.sh
```

## Security & Privacy

- No telemetry, no remote calls, no data leaves your site.
- All AJAX endpoints: nonce + `manage_options` capability checks.
- All stored input sanitized (`esc_url_raw`, `wp_kses_post`, allow-lists); all output escaped.
- Sensitive toggles (username edit, force logout) are admin-only, server-side enforced.

## License

GPL-2.0-or-later — see [LICENSE](LICENSE).

## Support

[support@mudrava.com](mailto:support@mudrava.com) · [mudrava.com/en/](https://mudrava.com/en/)
