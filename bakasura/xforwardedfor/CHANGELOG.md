# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2026-10-23

### Added
- `CHANGELOG.md` documenting the full project history.
- Root-level `CHANGELOG.md` symlink for better repository visibility on GitHub.

### Fixed
- Version-check directory reference in `composer.json` corrected from `main` to `master`
  (the repository's actual default branch), fixing in-phpBB update notifications.

### Changed
- Version bumped to 1.1.1 in `composer.json`, `version_check.json`, and `README.md`.
- Version-check download URL updated to point to the `v1.1.1` release archive.

## [1.1.0] - 2026-10-23

### Added
- **Cloudflare mode** — new ACP setting that reads the real client IP exclusively from
  the `CF-Connecting-IP` header; the trusted IP list is ignored when this mode is active (SEC-01).
- New `xff_cloudflare_mode` config_text key seeded by the migration (SEC-01).
- `revert_data()` method to the migration class to allow clean extension uninstallation (BUG-06).
- PHPDoc blocks on all classes and public/private methods across the codebase (DOC-02 – DOC-05).
- `support` section (`issues`, `source`) to `composer.json` (DOC-06).
- `public: false` declaration to the service definition in `config/services.yml` (STYLE-03).
- Language keys `XFF_ACP_CLOUDFLARE_MODE` and `XFF_ACP_CLOUDFLARE_MODE_EXPLAIN` (SEC-01).
- Root-level `README.md` and `LICENSE` symlinks for better repository visibility on GitHub.
- Comprehensive `README.md` with security model, installation, configuration, and usage sections (DOC-01).

### Changed
- `core_session_ip_after()` now uses RFC 7239 §5.3 right-to-left XFF walking: candidates are
  evaluated from right to left and the first non-trusted, non-private IP is used as the real
  client IP, preventing header spoofing (SEC-01 / SEC-02).
- Private and reserved IP ranges are now filtered out using
  `FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE` in all `filter_var()` calls (SEC-02).
- `{{ XFF_TRUSTED_IPS }}` in the ACP template is now escaped with `|e` to prevent XSS (SEC-03).
- ACP template JavaScript refactored from an inline `onchange` attribute to a proper
  `addEventListener('change', ...)` call for improved security and maintainability.
- Cloudflare mode explanation changed from `<p>` to `<em>` for improved accessibility.
- `str_replace` + `explode` in `is_trusted_ip()` replaced with
  `array_map('trim', explode(...))` (STYLE-05).
- `listener::ip_in_range(` changed to `self::ip_in_range(` inside the same class (STYLE-02).
- All control-structure bodies now use curly braces consistently (STYLE-01).

### Fixed
- PHP minimum version constraint corrected from `>=5.4` to `>=7.1.3` in `composer.json` (BUG-02).
- phpBB soft-require version range corrected from `>=3.1.10,<3.3.0@dev` to
  `>=3.3.0,<4.0.0@dev` in `composer.json` (BUG-01).
- Both `trigger_error()` calls in the ACP module now pass the required error-level constant
  (`E_USER_WARNING` and `E_USER_NOTICE` respectively) (BUG-03).
- `effectively_installed()` clarified with an inline comment explaining the `null`-vs-empty
  distinction for `config_text->get()` (BUG-07).
- Invalid Matrix ID in `composer.json` author `email` field removed (STYLE-04).
- Language string `XFF_ACP_TRUSTED_IPS` rewritten for clarity (GRAM-01).
- Language string `XFF_ACP_SETTING_SAVED` changed from exclamation mark to period (GRAM-05).
- Docblock on `core_session_ip_after()` corrected for grammar and accuracy (GRAM-02 – GRAM-04 / DOC-05).

### Security
- Removed hardcoded Cloudflare IP ranges as the default value for `xff_trusted_ips`; the
  default is now an empty string. Shipping static third-party CIDR blocks is a maintenance
  liability and a false trust signal (SEC-04).

## [1.0.1] - 2026-10-22

### Added
- `version_check.json` to enable in-phpBB version-update notifications.
- `version-check` entry in `composer.json` pointing to the version check file on GitHub.

## [1.0.0] - 2023-08-15

### Added
- Initial release of the X-Forwarded-For phpBB extension.
- Event listener (`event/listener.php`) that overrides the phpBB session IP from the
  `X-Forwarded-For` header when the connecting IP is in the configured trusted proxy list.
- CIDR range support via Symfony `IpUtils` for both IPv4 and IPv6.
- ACP settings page (`acp/x_forwarded_for_module.php`) to manage the trusted proxy IP list.
- Migration (`migrations/x_forwarded_for_acp.php`) that creates the `xff_trusted_ips`
  config_text key and registers the ACP module.
- English language file (`language/en/info_acp_x_forwarded_for.php`).
- ACP HTML template (`adm/style/acp_x_forwarded_for.html`).

[1.1.1]: https://github.com/BakasuraRCE/phpbb-ext-xforwardedfor/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/BakasuraRCE/phpbb-ext-xforwardedfor/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/BakasuraRCE/phpbb-ext-xforwardedfor/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/BakasuraRCE/phpbb-ext-xforwardedfor/releases/tag/v1.0.0
