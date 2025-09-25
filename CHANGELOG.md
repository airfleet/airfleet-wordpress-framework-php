# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [1.18.0] - 2025-09-25

### Added

- Added Environment helpers (`Helpers\Environment`) - determine environment (local, staging, production)
- Added `CacheManager` utility class for working with cache
- Added `Cacheable` trait to easily access global `CacheManager` instance
- Added filter `airfleet/cache/enabled` to enable/disable cache
- Added filter `airfleet/cache/expiration` to set cache duration in seconds (0 to not expire)
- Added filter `airfleet/cache/cache_dir_checks` to enable/disable caching directory modification checks
- Added filter `airfleet/cache/expiration_dir_check` to set cache duration for directory modification checks in seconds (0 to not expire)
- Added filter `airfleet/cache/manager` to set cache manager
- Added action `airfleet/cache/flush` to clear the cache
- Added action `airfleet/cache/hit` that is triggered whenever cache is hit, but only if WP_DEBUG is enabled
- Added action `airfleet/cache/miss` that is triggered whenever there is a cache miss, but only if WP_DEBUG is enabled
- Added class `Benchmark` to setup benchmarking feature. When enabled, an HTML comment will be printed at the end of the page with the amount of time in milliseconds it took to run certain features. The comment is only displayed for authenticated users.
- Added filter `airfleet/benchmark/enabled` to enable/disable performance benchmark
- Added filter `airfleet/benchmark/log_to_error_log` to enable/disable logging benchmark results to the error log. This is in addition to the HTML comment and dependent on benchmarking being enabled
- Added action `airfleet/benchmark/start` to start tracking a workload benchmark (must pass ID)
- Added action `airfleet/benchmark/stop` to stop tracking a workload benchmark (must pass ID)
- Added utility classes `Timer` and `NestedTimerRegistry` used for benchmarking

### Changed

- `LocalJsonLoadFromCache`: Refactored to use `Cacheable` and improve cache invalidation when ACF Local JSON directory contents change
- `LocalJsonCacheSettings`: Automatically set default value for filter `airfleet/cache/enabled` based on environment (enabled for all except local)
- Renamed filter `airfleet/framework/acf/local_json_cache/enabled` to `airfleet/acf/local_json_cache/enabled`
- Renamed filter `airfleet/framework/acf/local_json_cache/expiration` to `airfleet/acf/local_json_cache/expiration`
- Renamed action `airfleet/framework/acf/local_json_cache/invalidate` to `airfleet/acf/local_json_cache/invalidate`

## [1.17.1] - 2025-09-15

### Fixed

- `LocalJsonLoadFromCache`: Fixed automatic cache invalidation when saving field groups

## [1.17.0] - 2025-09-09

### Added

- Added object caching for ACF Local JSON through `Acf\LocalJson` classes
- Added filter `airfleet/framework/acf/local_json_cache/enabled` to enable/disable cache for ACF Local JSON
- Added filter `airfleet/framework/acf/local_json_cache/expiration` to set cache duration in seconds for ACF Local JSON
- Added action `airfleet/framework/acf/local_json_cache/invalidate` to invalidate ACF Local JSON cache

## [1.16.0] - 2025-09-09

### Added

- `Helpers\DisplayImplementation`: Added `is_string` check in `attributes()` function to differentiate between regular string and callable functions as attribute value.

## [1.15.0] - 2025-06-18

### Added

- Added `InlineScriptRegistry` for handling inline scripts, instead of using `wp_add_inline_script`
- Added filter `airfleet/framework/inline-script-registry/content` to modify inline script content before output
- Added filter `airfleet/framework/inline-script-registry/data-attributes` to modify script data attributes before output
- Added filter `airfleet/framework/inline-script-registry/deps` to modify script dependencies before output

## [1.14.0] - 2025-05-06

### Added

- Added Remove button in `Options\Fields\EncrypytedPasswordField` field to change value only if user want to update the value.

## [1.13.0] - 2025-03-04

### Added

- Added `@wpengine.local` to `is_airfleet_email` list (affects `is_airfleet_user` function).

## [1.12.1] - 2025-02-17

### Fixed

- `Helpers\Screen`: Fixed `is_editing_acf` causing `Undefined array key ID` warnings while editing some blocks.

## [1.12.0] - 2025-02-13

### Changed

- `SettingsLink`: changed default base url to `options-general.php` from `admin.php`. This affects the settings link in the Plugins page for both core option pages and ACF option pages. This should make the URLs consistent with the menu entries.

## [1.11.1] - 2025-01-23

### Fixed

- `Helpers\Screen`: Fixed `in_block_editor` and `in_block_editor_admin` which could give wrong results under certain circumstances

## [1.11.0] - 2025-01-22

### Added

- `Helpers\String`: added utilities `kebabToPascal` and `kebabToTitle`

## [1.10.0] - 2025-01-22

### Changed

- `Helpers\Screen`: performance improvements for `is_editing_acf`, `in_block_editor`, and `in_block_editor_admin`

## [1.9.0] - 2025-01-20

### Changed

- `Helpers\Screen`: added cache for `is_editing_acf` and `in_block_editor`

## [1.8.2] - 2025-01-15

### Fixed

- Fixed required field validation when it is hidden (field is not visible but still required)

## [1.8.1] - 2024-12-13

### Fixed

- Fixed screen detection utilities `in_block_editor_ajax`, `in_block_editor`, and `screen` (due to change in ACF or WP, `in_block_editor_ajax` was not detecting correctly)

## [1.8.0] - 2024-11-18

### Changed

- Options `Wizard` now also supports `Field` object instances or custom class names to instance set in the `type` property

## [1.7.1] - 2024-11-18

### Fixed

- Fixed PHP warnings related to Screen helpers `in_block_editor_ajax()` and `in_block_editor()`

## [1.7.0] - 2024-10-30

### Changed

- `LocalJsonByLocationParam` and `LocalJsonCustomFilenameByLocationParam` now accept optional argument to specify operators to match

## [1.6.0] - 2024-09-06

- Added `wysiwyg` options field type (wysiwyg input)

## [1.5.0] - 2024-05-28

### Added

- Added `Options\Tabs\LazyOptionsTab` (pass a callable to get options `Group` that is only called when used)
- Added `Options\Pages\LazyOptionsPage` (options page that uses `LazyOptionsTab`)

## [1.4.0] - 2024-04-22

### Changed

- Updated `description` field for `Section` to allow using links

## [1.3.0] - 2024-04-18

### Added

- Added `hidden` options field type (hidden input)
- Added `image` options field type (file upload)

## [1.2.2] - 2024-04-15

### Fixed

- Fixed options page not appearing under Settings menu and option tabs not working for multi-sites

## [1.2.1] - 2024-02-08

### Fixed

- ACF\LocalJson - fixed warning when reading non-ACF local JSON files

## [1.2.0] - 2024-02-08

### Added

- Added Acf\LocalJsonCustomFilename - syncs ACF JSON from a custom path and saves to a specific filename
- Added Acf\LocalJsonCustomFilenameByLocationParam - syncs ACF local JSON for groups with a specific param value in the location rules and saves to specific filename
- Added Acf\LocalJsonCustomFilenameCustomTemplate - syncs ACF local JSON for custom page templates and saves to custom filename

### Changed

- Acf\LocalJson - now syncs deletion regardless of filename (previously only synced deletion if file was named `group_*.json`)

## [1.1.0] - 2024-01-03

### Added

- Added Acf\LocalJsonCategorized - syncs ACF JSON from a custom path, using natural filenames and different sub-folders based on ACF type

## [1.0.0] - 2023-08-25

### Changed

- BREAKING: updated AcfSettingsOptionsSubPage, AcfToolsOptionsSubPage, AcfAirfleetOptionsSubPage, AcfSettingsLink to use plugin slug instead of ACF generated slug

## [0.6.0] - 2023-08-24

### Added

- Added Plugin\AcfSettingsOptionsSubPage - add ACF options sub-page under core "Settings" menu based on plugin config
- Added Plugin\AcfToolsOptionsSubPage - add ACF options sub-page under core "Tools" menu based on plugin config

### Changed

- Updated MenuTabsPage to support "tools" menu type

## [0.5.0] - 2023-07-14

### Added

- Added Helpers\Display::attributes and Helpers\Display::data_attributes - render attributes for HTML elements
- Added Acf\LocalJsonByLocationParam - sync local JSON for groups that match a specific location rule
- Added Acf\LocalJsonCustomTemplate - sync local JSON for a specific custom page template

## [0.4.0] - 2023-06-28

### Added

- Added Strings helper (Helpers\Strings) - convert between different cases

## [0.3.0] - 2023-04-13

#### Added

- Added ACF options feature classes (Acf\OptionsPage, Acf\OptionsSubPage, Acf\AirfleetOptionsSubPage)
- Added Plugin\AcfAirfleetOptionsSubPage - add ACF options sub-page under Airfleet menu based on plugin config
- Added Plugin\AcfSettingsLink - add settings link on Plugins admin table for ACF options sub-page based on plugin config

## [0.2.1] - 2023-04-12

### Changed

- Added login styles/scripts support to Assets\Enqueue

## [0.2.0] - 2023-04-12

### Added

- Added enqueue utilities (Assets\Enqueue)
- Added inline script and variables utilities (Assets\InlineScript, Assets\ScriptVariables, Assets\FrontendVariables, Assets\EditorVariables, Assets\AdminVariables, Assets\CriticalVariables)
- Added utility to add plugin action links on the Plugins table page (Plugin\ActionLinks)
- Added utility to add plugin settings link on the Plugins table page (Plugin\SettingsLink)
- Added Airfleet helper (Helpers\Airfleet) - detect Airfleet Starter Theme, Airfleet users, and post types/taxonomies
- Added Screen helper (Helpers\Screen) - detect current screen and post type
- Added Assets helper (Helpers\Assets) - get asset URL and get manifest
- Added Display helper (Helpers\Display) - pretty print, dump, and render reusable blocks
- Added plugin feature utilities (Features\PluginFeature, Features\BasePluginFeature and Features\PluginFeatures) - extra plugin specific hooks compared to Feature (activation, deactivation, uninstall)
- Added Hooks helper (Helper\Hooks) - remove class filter/action
- Added Acf\LocalJson utility

## [0.1.0] - 2023-04-06

### Added

- Created package
- Added Options utilities
- Added Features classes

[unreleased]: https://github.com/airfleet/airfleet-wordpress-framework-php/compare/1.18.0...main
[0.1.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.1.0
[0.2.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.2.0
[0.2.1]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.2.1
[0.3.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.3.0
[0.4.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.4.0
[0.5.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.5.0
[0.6.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.6.0
[1.0.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.0.0
[1.1.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.1.0
[1.2.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.2.0
[1.2.1]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.2.1
[1.2.2]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.2.2
[1.3.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.3.0
[1.4.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.4.0
[1.5.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.5.0
[1.6.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.6.0
[1.7.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.7.0
[1.7.1]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.7.1
[1.8.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.8.0
[1.8.1]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.8.1
[1.8.2]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.8.2
[1.9.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.9.0
[1.10.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.10.0
[1.11.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.11.0
[1.11.1]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.11.1
[1.12.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.12.0
[1.12.1]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.12.1
[1.13.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.13.0
[1.14.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.14.0
[1.15.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.15.0
[1.16.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.16.0
[1.17.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.17.0
[1.17.1]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.17.1

[1.18.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.18.0
